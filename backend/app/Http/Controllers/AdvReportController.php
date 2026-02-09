<?php

namespace App\Http\Controllers;

use App\Exports\AdvReport;
use App\Http\Requests\AdvReport\ReportRequest;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\ArrayShape;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AdvReportController extends Controller
{
    public function report(ReportRequest $request): JsonResponse
    {
        $data = $this->collectData($request);
        return $this->resourceItemResponse('data', $data);
    }

    protected function getPeriodData($q, $startPeriod, $endPeriod, $format): object
    {
        $onlineUsers = DB::table("online_history")
            ->where("created_at", ">=", $startPeriod)
            ->where("created_at", '<', $endPeriod)->sum("users_count");

        $vq = clone $q;
        $viewsCount = $vq
            ->whereNotNull("adv_banner_user_session.created_at")
            ->where("adv_banner_user_session.created_at", ">=", $startPeriod)
            ->where("adv_banner_user_session.created_at", "<", $endPeriod)->count();

        $cc = clone $q;
        $clicksCount = $cc
            ->whereNotNull("adv_banner_user_session.clicked_at")
            ->where("adv_banner_user_session.clicked_at", ">=", $startPeriod)
            ->where("adv_banner_user_session.clicked_at", "<", $endPeriod)->count();

        return (object)[
            'value' => Carbon::parse($startPeriod)->format($format),
            'online_users' => $onlineUsers,
            'views_count' => $viewsCount,
            'clicks_count' => $clicksCount,
        ];
    }

    public function export(ReportRequest $request): BinaryFileResponse
    {
        $data = $this->collectData($request);
        return Excel::download(new AdvReport($data), 'report.xlsx');
    }

    protected function collectData(Request $request): array
    {
        $dateFrom = Carbon::parse($request->get("date_from"));
        $dateTo = Carbon::parse($request->get("date_to"));
        $advPlaceId = $request->get("adv_place_id");
        $advertiserId = $request->get("advertiser_id");
        $vehicleTypesId = $request->get("vehicle_types_id");
        $advBannerId = $request->get("adv_banner_id");

        $q = DB::table("adv_banners");

        if ($advPlaceId)
            $q->where("adv_banners.adv_place_id", $advPlaceId);

        if ($advertiserId)
            $q->where("adv_banners.advertiser_id", $advertiserId);

        if ($vehicleTypesId)
            $q->join("adv_banner_vehicle_type", 'adv_banners.id', '=', 'adv_banner_vehicle_type.adv_banner_id')
                ->whereIn("adv_banner_vehicle_type.vehicle_type_id", $vehicleTypesId);

        if ($advBannerId)
            $q->where("adv_banners.id", $advBannerId);

        $data = [];
        switch ($request->get("group")) {
            case "day":
                $q->leftJoin("adv_banner_user_session", 'adv_banners.id', '=', 'adv_banner_user_session.adv_banner_id');
                $pointer = Carbon::parse($dateFrom);
                while ($pointer <= $dateTo) {
                    $startPeriod = Carbon::parse($pointer)->startOfDay();
                    $endPeriod = Carbon::parse($pointer)->endOfDay();
                    $data[] = $this->getPeriodData($q, $startPeriod, $endPeriod, 'Y-m-d');
                    $pointer->addDay();
                }
                break;
            case "month":
                $q->leftJoin("adv_banner_user_session", 'adv_banners.id', '=', 'adv_banner_user_session.adv_banner_id');
                $pointer = Carbon::parse($dateFrom);
                while ($pointer <= $dateTo) {
                    $startPeriod = Carbon::parse($pointer)->startOfMonth();
                    $endPeriod = Carbon::parse($pointer)->endOfMonth();
                    $data[] = $this->getPeriodData($q, $startPeriod, $endPeriod, 'Y-m');
                    $pointer->addMonth();
                }
                break;
            case "advertiser":
                $startPeriod = Carbon::parse($dateFrom)->startOfDay()->format("Y-m-d H:i:s");
                $endPeriod = Carbon::parse($dateTo)->endOfDay()->format("Y-m-d H:i:s");
                $q->selectRaw("advertiser_id,
       (select count(*) from adv_banner_user_session abus where abus.adv_banner_id = adv_banners.id and abus.created_at >= '$startPeriod' and abus.created_at < '$endPeriod') as views_count,
       (select count(*) from adv_banner_user_session abus where abus.adv_banner_id = adv_banners.id and abus.clicked_at is not null and abus.clicked_at >= '$startPeriod' and abus.clicked_at < '$endPeriod') as clicks_count");
                $bindings = $q->getBindings();
                $subQuery = $q->toSql();

                $data = DB::select("select a.name as value, sum(views_count) as views_count, sum(clicks_count) as clicks_count
from ($subQuery) t
join advertisers a on a.id = t.advertiser_id
group by t.advertiser_id, a.name", $bindings);
                break;
            case "banner":
                $startPeriod = Carbon::parse($dateFrom)->startOfDay()->format("Y-m-d H:i:s");
                $endPeriod = Carbon::parse($dateTo)->endOfDay()->format("Y-m-d H:i:s");
                $q->selectRaw("adv_banners.id, adv_banners.title, adv_banners.token,
       (select count(*) from adv_banner_user_session abus where abus.adv_banner_id = adv_banners.id and abus.created_at >= '$startPeriod' and abus.created_at < '$endPeriod') as views_count,
       (select count(*) from adv_banner_user_session abus where abus.adv_banner_id = adv_banners.id and abus.clicked_at is not null and abus.clicked_at >= '$startPeriod' and abus.clicked_at < '$endPeriod') as clicks_count");
                $bindings = $q->getBindings();
                $subQuery = $q->toSql();
                $data = DB::select(DB::raw("select t.id, CONCAT_WS(' ', t.title, IF(t.token IS NOT NULL, CONCAT('(', t.token, ')'), '')) AS value, sum(views_count) as views_count, sum(clicks_count) as clicks_count from ({$subQuery}) as t group by t.id, concat(t.title, t.token)"), $bindings);;
                break;
        }
        return $data;
    }
}
