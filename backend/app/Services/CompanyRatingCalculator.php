<?php

namespace App\Services;

use  App\Models\Company;
use App\Models\Recommendation;
use App\Models\Report;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CompanyRatingCalculator
{
    protected Company $company;

    const COMPANY_APPROVE_POINT_PERIOD = 180;
    const COMPANY_APPROVE_POINT_VALUE = 0.1;
    const COMPANY_APPROVE_POINT_MAX_VALUE = 0.5;

    const LEGAL_AGE_POINT_PERIOD = 120;
    const LEGAL_AGE_POINT_VALUE = 0.1;
    const LEGAL_AGE_POINT_MAX_VALUE = 1;

    const RECOMMENDATION_POINT_VALUE = 0.1 / 10;
    const RECOMMENDATION_POINT_MAX_VALUE = 0.5;

    const FAIRPLAY_POINT_VALUE = 0.1;
    const FAIRPLAY_POINT_MAX_VALUE = 2;

    const REPORT_PENALTY_VALUE = .25 / 2;
    const REPORT_PENALTY_MAX_VALUE = 5;

    const DETAILS_TEMPLATES = [
        [
            "title" => "Создание и подтверждение компании или ИП",
            "description" => "Начисляется 1 балл, после проверки и подтверждения компании модератором. <br/>
Название и местонахождение компании проверяется на основании  предоставленных администратору свидетельства на регистрацию организации или ИП, выписка ЕГРЮЛ. Так же при необходимости возможен запрос дополнительных документов.<br/>
Физические лица не могут получить данный балл. <br/>
Максимальный балл по этому критерию - 1.",
            "method" => "companyConfirmationPoints",
            "positive" => true
        ],
        [
            "title" => "Давность регистрации на  ASTT.SU",
            "description" => "Начисляется по 0.1 балла за каждые 180 дней с момента подтверждения компании модератором  на портале ASTT.SU<br/>
Балл начисляется только при наличии балла \"Компания подтверждена\". <br/>
Начисляется по 0.1 балла за каждые 180 дней с момента подтвержения компании модератором  на портале ASTT.SU<br/>
Максимальный балл по этому критерию - 0,5",
            "method" => "companyAgePoints",
            "positive" => true
        ],
        [
            "title" => "Время существования юридического лица или ИП",
            "description" => "Балл начисляется только при наличии балла \"Компания подтверждена\".<br/>
Начисляется по 0.1 балла за каждые 120 дней с момента регистрации юридического лица в ИФНС (по данным ЕГРЮЛ, ЕГРИП).<br/>
Максимальный балл по этому критерию - 1.",
            "method" => "legalAgePoints",
            "positive" => true
        ],
        /* [
             "title" => "Возможная аффилированность компаний ",
             "description" => "",
             "method" => ""
         ],*/
        [
            "title" => "Полученные рекомендации",
            "description" => "Балл начисляется только при наличии балла \"Компания подтверждена\".<br/>
Начисляется по 0.1 баллу за каждые 10 новых  положительных рекомендации от разных пользователей. Все рекомендации на портале проходят модерацию.<br/>
Максимальный балл по этому критерию - 0,5.",
            "method" => "recommendationPoints",
            "positive" => true
        ],
        [
            "title" => "Отсутствие подтвержденных претензий",
            "description" => "Балл начисляется только при наличии балла \"Компания подтверждена\". <br/>
За каждый месяц отсутствия подтвержденных претензий и полученной хотя бы одной рекомендации начисляется по 0,1 балла в месяц. <br/>
В случае получения подтвержденной претензии на профиль компании, бал не начисляется. <br/>
<i>Пример:</i> если в текущем месяце нет подтвержденных претензий и есть рекомендации + 0,10 баллов, если в следующем месяце есть подтвержденная претензия + 0 баллов. <br/>
Максимальный балл по этому критерию - 2.",
            "method" => "fairPlayPoints",
            "positive" => true
        ],
        [
            "title" => "Подтвержденные претензии",
            "description" => "За каждые 2 неурегулированные претензий начисляется минус 0,25 балла от рейтинга компании, в случае нулевого рейтинга компании применяется минусовой рейтинг, максимальный минусовой рейтинг -5 балла.<br/>
В случае урегулирования претензии с истцом, после получения минусового балла, рейтинг компании повышается.<br/>
<i>Пример:</i> Чем больше подтвержденных претензий — тем ниже рейтинг компании. Например, за 4 претензий будет начислено минус 0,5  балла и так далее.  В случае урегулирования претензии балл возвращается и рейтинг компании поднимается.",
            "method" => "reportPenalty",
            "positive" => false
        ],
    ];

    public function __construct(Company $company)
    {
        $this->company = $company;
    }

    public function getScore(): float
    {
        return $this->companyConfirmationPoints()
            + $this->companyAgePoints()
            + $this->legalAgePoints()
            + $this->recommendationPoints()
            + $this->fairPlayPoints()
            - $this->reportPenalty();
    }

    public function getDetails(): array
    {
        return array_map(function ($i) {
            return [
                'title' => $i['title'],
                'description' => $i['description'],
                'positive' => $i['positive'],
                'score' => ($i['positive'] ? 1 : -1) * round($this->{$i['method']}(), 1),
            ];
        }, self::DETAILS_TEMPLATES);
    }

    protected function companyConfirmationPoints(): int
    {
        return $this->company->isApproved() ? 1 : 0;
    }

    protected function companyAgePoints(): float
    {
        if (!$this->company->created_at) return 0;
        $daysOfApprove = Carbon::now()->diffInDays(Carbon::parse($this->company->created_at));
        $points = floor($daysOfApprove / self::COMPANY_APPROVE_POINT_PERIOD) * self::COMPANY_APPROVE_POINT_VALUE;
        return min($points, self::COMPANY_APPROVE_POINT_MAX_VALUE);
    }

    protected function legalAgePoints(): float
    {
        if (!$this->company->legal_registration_date) return 0;
        $daysOfApprove = Carbon::now()->diffInDays(Carbon::parse($this->company->legal_registration_date));
        $points = floor($daysOfApprove / self::LEGAL_AGE_POINT_PERIOD) * self::LEGAL_AGE_POINT_VALUE;
        return min($points, self::LEGAL_AGE_POINT_MAX_VALUE);

    }

    protected function recommendationPoints(): float
    {
        $recommendationsCount = $this->company->recommendations()->approved()->count();
        return min($recommendationsCount * self::RECOMMENDATION_POINT_VALUE, self::RECOMMENDATION_POINT_MAX_VALUE);
    }

    protected function fairPlayPoints(): float
    {
        if (!$this->company->approved_at) return 0;
        $companyId = $this->company->id;
        $recommendationStatus = Recommendation::STATUSES['APPROVED'];
        $recommendations = DB::select("
select DATE_FORMAT(created_at, '%Y-%m') as month, count(id) as recommendations_count
from recommendations r
where company_id = $companyId
  and status = '$recommendationStatus'
group by month;");

        $reportStatus = Report::STATUSES['CONFIRMED'];
        $reports = DB::select("
select DATE_FORMAT(created_at, '%Y-%m') as month, count(id) as reports_count
from reports r
where company_id = $companyId
  and status = '$reportStatus'
group by month;");

        $recommendations = collect($recommendations)->pluck("recommendations_count", "month");
        $reports = collect($reports)->pluck("reports_count", "month");

        $startMonth = Carbon::parse($this->company->approved_at);
        $endMonth = Carbon::now();
        $score = 0;
        while ($startMonth <= $endMonth) {
            $key = Carbon::parse($startMonth)->format("Y-m");
            $recommendationsCount = $recommendations[$key] ?? 0;
            $reportsCount = $reports[$key] ?? 0;
            if ($recommendationsCount > 0 && $reportsCount === 0) {
                $score = self::FAIRPLAY_POINT_VALUE;
            }

            $startMonth->addMonth();
        }

        return min($score, self::FAIRPLAY_POINT_MAX_VALUE);
    }

    protected function reportPenalty(): float
    {
        $count = $this->company->reports()->confirmed()->count();
        $points = $count * self::REPORT_PENALTY_VALUE;
        return min($points, self::REPORT_PENALTY_MAX_VALUE);
    }
}
