<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\Claim;
use App\Models\Company;
use App\Models\CompanyType;
use App\Models\Message;
use App\Models\Order;
use App\Models\Recommendation;
use App\Models\Report;
use App\Models\User;
use App\Models\VehicleGroup;
use App\Models\VehicleType;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    protected function getTotalUsersCount(): int
    {
        return User::query()->user()->count();
    }

    protected function getOnlineUsers(): int
    {
        return User::query()->online()->user()->count();
    }

    protected function getModerationCompaniesCount(): int
    {
        return Company::query()->onModeration()->count();
    }

    protected function getActiveCompaniesCount(): int
    {
        return Company::query()->published()->count();
    }

    protected function getDraftReportsCount(): int
    {
        return Report::query()->draft()->count();
    }

    protected function getRefereeReportsCount(): int
    {
        return Report::query()->referee()->count();
    }

    protected function getActiveReportsCount(): int
    {
        return Report::query()->active()->count();
    }

    protected function getRegistrationsGraph(Carbon $from, Carbon $to): array
    {
        $pointer = Carbon::parse($from);
        $users = User::query()
            #->boss()
            ->where("created_at", ">=", $from)
            ->where("created_at", "<=", $to)
            ->get();

        $data = [];

        while ($pointer->isBefore($to)) {
            $data[] = [
                'date' => $pointer->format("d.m.Y"),
                'count' => $users
                    ->where("created_at", ">", Carbon::parse($pointer)->startOfDay())
                    ->where("created_at", "<=", Carbon::parse($pointer)->endOfDay())
                    ->count(),
            ];
            $pointer->addDay();
        }
        return $data;
    }

    protected function getOnlineGraph(Carbon $from, Carbon $to): array
    {
        $pointer = Carbon::parse($from);

        $users = DB::table("online_history")
            ->where("created_at", ">=", $from)
            ->where("created_at", "<=", $to)
            ->get();

        $data = [];

        while ($pointer->isBefore($to)) {
            $data[] = [
                'date' => $pointer->format("d.m.Y"),
                'count' => $users
                    ->where("created_at", ">", Carbon::parse($pointer)->startOfDay())
                    ->where("created_at", "<=", Carbon::parse($pointer)->endOfDay())
                    ->sum('users_count'),
            ];
            $pointer->addDay();
        }
        return $data;
    }

    protected function getNewChats(Carbon $from, Carbon $to): array
    {
        $pointer = Carbon::parse($from);

        $chats = Chat::query()
            ->where("created_at", ">=", $from)
            ->where("created_at", "<=", $to)
            ->get();

        $data = [];

        while ($pointer->isBefore($to)) {
            $data[] = [
                'date' => $pointer->format("d.m.Y"),
                'count' => $chats
                    ->where("created_at", ">", Carbon::parse($pointer)->startOfDay())
                    ->where("created_at", "<=", Carbon::parse($pointer)->endOfDay())
                    ->count(),
            ];
            $pointer->addDay();
        }
        return $data;
    }

    protected function getNewMessages(Carbon $from, Carbon $to): array
    {
        $pointer = Carbon::parse($from);

        $messages = Message::query()
            ->where("created_at", ">=", $from)
            ->where("created_at", "<=", $to)
            ->get();

        $data = [];

        while ($pointer->isBefore($to)) {
            $data[] = [
                'date' => $pointer->format("d.m.Y"),
                'count' => $messages
                    ->where("created_at", ">", Carbon::parse($pointer)->startOfDay())
                    ->where("created_at", "<=", Carbon::parse($pointer)->endOfDay())
                    ->count(),
            ];
            $pointer->addDay();
        }
        return $data;
    }

    protected function getCompanyTypesGraph(): array
    {
        $companyTypes = CompanyType::query()
            ->withCount(['publishedCompanies'])
            ->get();
        return $companyTypes->map(function (CompanyType $type) {
            return [
                'label' => $type->title,
                'companies_count' => $type->published_companies_count
            ];
        })->toArray();
    }

    protected function getVehicleGroupsGraph(): array
    {
        return VehicleGroup::query()
            ->with(['types' => function ($q) {
                $q->withCount('publishedCompanies');
            }])->get()->each(function (VehicleGroup $vehicleGroup) {
                $vehicleGroup->append('published_companies_count');
            })->toArray();
    }

    protected function getOrdersGraph(Carbon $from, Carbon $to): array
    {
        $pointer = Carbon::parse($from);
        $orders = Order::query()
            #->active()
            ->where("created_at", ">=", $from)
            ->where("created_at", "<=", $to)
            ->get();

        $data = [];

        $additional_ids = request()->input("order_ids");
        $ids = $additional_ids ? explode(",", $additional_ids) : [];

        while ($pointer->isBefore($to)) {
            $windowOrders = $orders
                ->where("created_at", "<", Carbon::parse($pointer)->endOfDay()->toDateTimeLocalString())
                ->where("created_at", ">=", Carbon::parse($pointer)->startOfDay()->toDateTimeLocalString());

            $additional = [];
            foreach ($ids as $id) {
                $additional["orders_$id"] = $windowOrders->where("company_id", $id)->count();
            }

            $exceptAdditionalOrders = $windowOrders->whereNotIn("company_id", $ids)->count();

            $data[] = [
                'date' => $pointer->format("d.m.Y"),
                'count' => $windowOrders->count(),
                ...$additional,
                "except_additional" => $exceptAdditionalOrders,
            ];
            $pointer->addDay();
        }
        return $data;
    }

    protected function getOrderVehicleTypeGraph(): array
    {
        return VehicleType::query()->withCount('activeOrders')->get()->toArray();
    }

    protected function getViewedRecommendationsCount(): int
    {
        return Recommendation::query()->viewed()->count();
    }

    protected function getDraftRecommendationsCount(): int
    {
        return Recommendation::query()->draft()->count();
    }

    protected function getTotalOrdersCount(): int
    {
        return Order::count();
    }

    protected function getActiveOrdersCount(): int
    {
        return Order::active()->count();
    }

    protected function getModerationOrdersCount(): int
    {
        return Order::moderation()->count();
    }

    protected function getDraftClaimsCount(): int
    {
        return Claim::query()->draft()->count();
    }

    protected function getViewedClaimsCount(): int
    {
        return Claim::query()->viewed()->count();
    }

    protected function getActiveClaimsCount(): int
    {
        return Claim::query()->approved()->count();
    }

    public function getDevicesGraph()
    {
        $data = DB::table("users")
            ->groupBy("last_device")
            ->whereNull("deleted_at")
            ->selectRaw("IF(last_device IS NULL, 'unknown', last_device) as label, count(id) as devices_count");
        return $data->get();
    }

    public function getStatData(Request $request): JsonResponse
    {
        $from = Carbon::now()->subMonth()->startOfDay();
        $to = Carbon::now()->endOfDay();
        if ($request->has('date_range')) {
            $dateRange = explode(',', $request->get('date_range'));
            if (count($dateRange) === 2) {
                $from = Carbon::parse($dateRange[0])->startOfDay();
                $to = Carbon::parse($dateRange[1])->endOfDay();
            }
        }

        return $this->resourceItemResponse('data', [
            'users' => [
                'total' => $this->getTotalUsersCount(),
                'online' => $this->getOnlineUsers(),
            ],
            'companies' => [
                'moderation' => $this->getModerationCompaniesCount(),
                'active' => $this->getActiveCompaniesCount(),
            ],
            'reports' => [
                'draft' => $this->getDraftReportsCount(),
                'referee' => $this->getRefereeReportsCount(),
                'active' => $this->getActiveReportsCount(),
            ],
            'claims' => [
                'draft' => $this->getDraftClaimsCount(),
                'viewed' => $this->getViewedClaimsCount(),
            ],
            'recommendations' => [
                'draft' => $this->getDraftRecommendationsCount(),
                'viewed' => $this->getViewedRecommendationsCount(),
            ],
            'orders' => [
                'total' => $this->getTotalOrdersCount(),
                'active' => $this->getActiveOrdersCount(),
                'moderation' => $this->getModerationOrdersCount(),
            ],
            'users_graph' => $this->getRegistrationsGraph($from, $to),
            'online_graph' => $this->getOnlineGraph($from, $to),
            'company_types' => $this->getCompanyTypesGraph(),
            'vehicle_groups' => $this->getVehicleGroupsGraph(),
            'orders_graph' => $this->getOrdersGraph($from, $to),
            'order_vehicle_type_graph' => $this->getOrderVehicleTypeGraph(),
            'devices' => $this->getDevicesGraph(),
            'new_chats' => $this->getNewChats($from, $to),
            'new_messages' => $this->getNewMessages($from, $to),

        ]);
    }

    public function publicIndex(): JsonResponse
    {
        return $this->resourceItemResponse('data', [
            'users' => $this->getTotalUsersCount(),
            'companies' => $this->getActiveCompaniesCount(),
            'orders' => $this->getActiveOrdersCount(),
        ]);
    }
}
