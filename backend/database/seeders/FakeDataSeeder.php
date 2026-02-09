<?php

namespace Database\Seeders;

use App\Models\Chat;
use App\Models\City;
use App\Models\Claim;
use App\Models\ClaimDocument;
use App\Models\Company;
use App\Models\CompanyDocument;
use App\Models\CompanyType;
use App\Models\FormAnswer;
use App\Models\FormQuestion;
use App\Models\Message;
use App\Models\Order;
use App\Models\OrderAddress;
use App\Models\OrderDocument;
use App\Models\Photo;
use App\Models\PhotoGroup;
use App\Models\Recommendation;
use App\Models\Region;
use App\Models\Report;
use App\Models\ReportDocument;
use App\Models\ReportType;
use App\Models\User;
use App\Models\VehicleType;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class FakeDataSeeder extends Seeder
{
    const COMPANIES_COUNT = 15;
    const EMPLOYEES_COUNT = 7;
    const COMPANY_DOCUMENTS_COUNT = 6;
    const PHOTO_GROUPS_COUNT = 7;
    const PHOTOS_COUNT = 25;
    const ORDERS_COUNT = 10;
    const MAX_ORDER_ADDRESSES_COUNT = 5;
    const MAX_ORDER_DOCUMENTS_COUNT = 5;
    const MAX_RECOMMENDATIONS_COUNT = 3;
    const MAX_REPORTS_COUNT = 5;
    const MAX_REPORT_DOC_COUNTS = 3;
    const MAX_CLAIMS_COUNT = 5;
    const MAX_CLAIM_DOC_COUNTS = 5;
    const MAX_MESSAGES_IN_CHAT = 15;

    protected function createCompany(): Company
    {
        $companyName = fake('ru_RU')->company;
        $company = new Company([
            'company_type_id' => CompanyType::query()->inRandomOrder()->first()->id,
            'kpp' => fake('ru_RU')->kpp(),
            'inn' => fake('ru_RU')->inn(),
            'title' => $companyName,
            'full_title' => "$companyName",
            'ogrn' => random_int(100000, 999999),
            'okpo' => random_int(100000, 999999),
            'legal_address' => fake('ru_RU')->address,
            'address' => fake('ru_RU')->streetAddress,
            'director' => fake('ru_RU')->name,
            'phone' => fake()->phoneNumber,
            'email' => fake()->safeEmail,
            'website' => fake()->url,
            'description' => fake('ru_RU')->sentence(12)
        ]);
        $company->moderation_status = Company::MODERATION_STATUSES[random_int(0, count(Company::MODERATION_STATUSES) - 1)];

        if ($company->moderation_status === Company::MODERATION_STATUSES[3]) {
            $company->moderation_message = fake('ru_RU')->sentence(12);
        }

        $company->save();

        $vehicles = VehicleType::query()->inRandomOrder()->take(random_int(1, 3))->pluck('id');
        $company->vehicleTypes()->attach($vehicles);

        return $company;
    }

    protected function createBoss(Company $company, Region $region): User
    {
        $gender = random_int(0, 3) ? 'male' : 'female';
        $companyAdminUser = new User([
            'name' => fake('ru_RU')->firstName($gender),
            'surname' => fake('ru_RU')->lastName(),
            'email' => fake()->safeEmail,
            'phone' => fake()->phoneNumber,
            'password' => Hash::make(Str::random(123456)),
            'avatar' => fake()->imageUrl,
            'city_id' => City::query()->where('region_id', $region->id)->inRandomOrder()->first()?->id,
            'region_id' => $region->id
        ]);

        $companyAdminUser->rating = random_int(0, 5);
        $companyAdminUser->company_id = $company->id;
        $companyAdminUser->company_role = User::COMPANY_ROLES[1];
        $companyAdminUser->save();
        return $companyAdminUser;
    }

    protected function createEmployee(Company $company, Region $region): User
    {
        $gender = random_int(0, 3) ? 'male' : 'female';
        $employee = new User([
            'name' => fake('ru_RU')->firstName($gender),
            'surname' => fake('ru_RU')->lastName($gender),
            'email' => fake()->safeEmail,
            'phone' => fake()->phoneNumber,
            'password' => Hash::make(Str::random(123456)),
            'avatar' => fake()->imageUrl,
            'city_id' => City::query()->where('region_id', $region->id)->inRandomOrder()->first()?->id,
            'region_id' => $region->id
        ]);

        $employee->company_id = $company->id;
        $employee->company_role = User::COMPANY_ROLES[0];
        $employee->save();
        return $employee;
    }

    protected function createCompanyDocument(Company $company): CompanyDocument
    {
        $type = random_int(0, 2) ? 'doc' : 'image';
        $doc = new CompanyDocument([
            'company_id' => $company->id,
            'type' => $type,
            'url' => $type === 'image' ? fake()->url : fake()->imageUrl,
        ]);
        $doc->save();
        return $doc;
    }

    protected function createPhotos(Company $company): PhotoGroup
    {
        $group = new PhotoGroup([
            'company_id' => $company->id,
            'title' => fake()->sentence
        ]);
        $group->save();
        for ($i = 0; $i < self::PHOTOS_COUNT; $i++) {
            $photo = new Photo([
                'photo_group_id' => $group->id,
                'url' => fake()->imageUrl,
                'description' => fake()->sentence(3)
            ]);
            $photo->save();
        }
        return $group;
    }

    protected function createOrder(Company $company): Order
    {
        $employee = $company->users()->inRandomOrder()->first();
        $vehicleType = VehicleType::query()->inRandomOrder()->first();
        $byAgreement = random_int(0, 1);
        $amount = random_int(10000, 100000);

        $order = new Order();
        $order->company_id = $company->id;
        $order->user_id = $employee->id;
        $order->vehicle_type_id = $vehicleType->id;
        $order->vehicles_count = random_int(1, 5);
        $order->start_date = Carbon::now()->addDays()->addHours()->addMinutes();
        $order->publish_date = $order->start_date->addHours();
        $order->finish_date = $order->start_date->addDays();
        $order->amount_by_agreement = $byAgreement;
        if (!$byAgreement) {
            $order->amount_account_vat = $amount * 1.35;
            $order->amount_account = $amount * 1.15;
            $order->amount_cash = $amount;
        }
        $order->description = fake()->sentence(6);
        $order->views_count = random_int(0, 3000);
        $order->title = $vehicleType->title;
        $order->save();

        foreach ($vehicleType->questions()->get() as $question) {
            $this->createFormAnswer($order, $question);
        }

        $order->generateTitle();

        $order->moderation_status = Order::MODERATION_STATUSES[random_int(0, count(Order::MODERATION_STATUSES) - 1)];
        if ($order->moderation_status === Order::MODERATION_STATUSES[3]) {
            $order->moderation_message = fake()->sentence(10);
        }
        $order->save();

        return $order;
    }

    protected function createFormAnswer(Order $order, FormQuestion $question): FormAnswer
    {
        if ($question->options) {
            $value = $question->options[random_int(0, count($question->options) - 1)];
        } else {
            $value = fake()->sentence(1);
        }

        $answer = new FormAnswer();
        $answer->form_question_id = $question->id;
        $answer->order_id = $order->id;
        $answer->value = $value;
        $answer->save();
        return $answer;
    }

    protected function createOrderAddress(Order $order): OrderAddress
    {
        $address = new OrderAddress();
        $address->order_id = $order->id;
        $address->address = fake('ru_RU')->address();
        $address->lat = fake()->latitude;
        $address->lng = fake()->longitude;
        $address->city_id = $order->company->boss->city_id;
        $address->region_id = $order->company->boss->region_id;
        $address->save();
        return $address;
    }

    protected function createOrderDoc(Order $order): OrderDocument
    {
        $doc = new OrderDocument([
            'order_id' => $order->id,
            'type' => 'doc',
            'url' => fake()->url,
        ]);
        $doc->save();
        return $doc;
    }

    protected function createRecommendation(User $author): Recommendation|null
    {
        $targetCompany = Company::query()->where("id", "!=", $author->company_id)
            ->inRandomOrder()->first();
        if (!$targetCompany) return null;

        $targetUser = User::query()->where("company_id", $targetCompany->id)->inRandomOrder()->first();
        if (!$targetUser) return null;

        $recommendation = new Recommendation([
            'author_user_id' => $author->id,
            'company_id' => $targetCompany->id,
            'target_user_id' => $targetUser->id,
            'text' => fake('ru_RU')->sentence(6)
        ]);
        $recommendation->save();

        $recommendation->status = Recommendation::STATUSES[random_int(0, count(Recommendation::STATUSES) - 1)];
        $recommendation->save();


        return $recommendation;
    }

    protected function createClaim($randomUser): Claim|null
    {
        $targetOrder = Order::query()->where("user_id", "!=", $randomUser->id)
            ->where("company_id", "!=", $randomUser->company_id)
            ->inRandomOrder()->first();
        if (!$targetOrder) return null;

        $claim = new Claim([
            'order_id' => $targetOrder->id,
            'text' => fake()->sentence(32),
        ]);
        $claim->user_id = $randomUser->id;
        $claim->status = Claim::STATUSES[random_int(0, count(Claim::STATUSES) - 1)];
        $claim->save();

        for ($i = 0; $i < random_int(0, self::MAX_CLAIM_DOC_COUNTS); $i++) {
            $doc = new ClaimDocument([
                'claim_id' => $claim->id,
                'type' => 'doc',
                'url' => fake()->url,
            ]);
            $doc->save();
        }

        return $claim;
    }

    protected function createReport(User $author): Report|null
    {
        $type = ReportType::query()->inRandomOrder()->first();
        $targetCompany = Company::query()->where("id", "!=", $author->company_id)
            ->inRandomOrder()->first();
        if (!$targetCompany) return null;

        $targetUser = User::query()->where("company_id", $targetCompany->id)->inRandomOrder()->first();
        if (!$targetUser) return null;

        $targetOrder = Order::query()->where("company_id", $targetCompany->id)->inRandomOrder()->first();

        $report = new Report([
            'report_type_id' => $type->id,
            'author_user_id' => $author->id,
            'company_id' => $targetCompany->id,
            'target_user_id' => $targetUser->id,
            'order_id' => $targetOrder?->id,
            'amount' => random_int(1000, 10000),
            'text' => fake('ru_RU')->sentence(6),
            //'status' => Report::STATUSES[1]
            'status' => Report::STATUSES[random_int(0, count(Report::STATUSES) - 1)]
        ]);
        $report->save();

        for ($i = 0; $i < random_int(0, self::MAX_REPORT_DOC_COUNTS); $i++) {
            $doc = new ReportDocument([
                'report_id' => $report->id,
                'type' => 'doc',
                'url' => fake()->url,
            ]);
            $doc->save();
        }

        if ($report->status !== Report::STATUSES[0]) {
            $this->createChat($report);
        }

        return $report;
    }

    protected function createChat(Report $report)
    {
        $chat = new Chat(['report_id' => $report->id]);
        $chat->save();

        $chat->inviteParties();

        for ($i = 0; $i < random_int(0, self::MAX_MESSAGES_IN_CHAT); $i++) {
            $author = [$report->author_user_id, $report->target_user_id][random_int(0, 1)];
            $rand = random_int(0, 10);
            if ($rand < 2) {
                $fileType = 'image';
                $fileUrl = fake()->imageUrl;
            } elseif ($rand < 5) {
                $fileType = 'doc';
                $fileUrl = fake()->url;
            } else {
                $fileType = null;
                $fileUrl = null;
            }
            // send some messages
            $message = new Message([
                'chat_id' => $chat->id,
                'author_id' => $author,
                'text' => fake('ru_RU')->sentence(14),
                'file_type' => $fileType,
                'file_url' => $fileUrl,
            ]);
            $message->save();
        }
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function run()
    {
        echo "Inserting fake data...\n";

        if (config('database.clear_data')) {
            Message::query()->delete();
            Chat::query()->delete();
            ReportDocument::query()->delete();
            Report::query()->delete();

            Recommendation::query()->delete();

            ClaimDocument::query()->delete();
            Claim::query()->delete();

            OrderDocument::query()->delete();
            OrderAddress::query()->delete();
            FormAnswer::query()->delete();
            Order::query()->delete();

            Photo::query()->delete();
            PhotoGroup::query()->delete();

            User::query()->user()->delete();

            CompanyDocument::query()->delete();
            Company::query()->delete();
        }

        for ($i = 0; $i < self::COMPANIES_COUNT; $i++) {
            $company = $this->createCompany();

            $region = Region::query()->inRandomOrder()->first();
            $boss = $this->createBoss($company, $region);
            $companyUsers = [$boss];
            for ($k = 0; $k < self::EMPLOYEES_COUNT; $k++) {
                $user = $this->createEmployee($company, $region);
                $companyUsers[] = $user;
            }

            for ($j = 0; $j < self::COMPANY_DOCUMENTS_COUNT; $j++) {
                $this->createCompanyDocument($company);
            }

            for ($x = 0; $x < self::PHOTO_GROUPS_COUNT; $x++) {
                $this->createPhotos($company);
            }

            for ($y = 0; $y < self::ORDERS_COUNT; $y++) {
                $order = $this->createOrder($company);

                for ($m = 0; $m < random_int(1, self::MAX_ORDER_ADDRESSES_COUNT); $m++) {
                    // order addresses
                    $this->createOrderAddress($order);
                }

                for ($n = 0; $n < random_int(1, self::MAX_ORDER_DOCUMENTS_COUNT); $n++) {
                    // order documents
                    $this->createOrderDoc($order);
                }

            }

            for ($z = 0; $z < random_int(0, self::MAX_RECOMMENDATIONS_COUNT); $z++) {
                $randomUser = $companyUsers[random_int(0, count($companyUsers) - 1)];
                $this->createRecommendation($randomUser);
            }

            for ($r = 0; $r < random_int(0, self::MAX_REPORTS_COUNT); $r++) {
                $randomUser = $companyUsers[random_int(0, count($companyUsers) - 1)];
                $this->createReport($randomUser);
            }

            for ($r = 0; $r < random_int(0, self::MAX_CLAIMS_COUNT); $r++) {
                $randomUser = $companyUsers[random_int(0, count($companyUsers) - 1)];
                $this->createClaim($randomUser);
            }
        }
    }
}
