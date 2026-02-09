<?php

namespace App\Http\Controllers;

use App\Models\Claim;
use App\Models\Company;
use App\Models\CompanyNotification;
use App\Models\Message;
use App\Models\Order;
use App\Models\Recommendation;
use App\Models\Report;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BadgeController extends Controller
{
    /**
     * User badges
     */

    public function totalCount(): JsonResponse
    {
        $user = User::query()->find(auth("sanctum")->id());
        $allBadges = $user->countBadges();
        return $this->resourceItemResponse('total_badges_value', $allBadges['total_count'], $allBadges);
    }

    public function reportMessagesCount(Request $request): JsonResponse
    {
        $outReports = Message::getOutReportsNewMessagesCount();
        $inReports = Message::getInReportsNewMessagesCount();
        $reports = $outReports + $inReports;
        $count = match ($request->get("type")) {
            "out" => $outReports,
            "in" => $inReports,
            default => $reports,
        };
        return $this->resourceItemResponse('messages_count', $count);
    }

    public function notificationsCount(): JsonResponse
    {
        $count = CompanyNotification::query()->visible()->new()->count();
        return $this->resourceItemResponse('new_notifications_count', $count);
    }

    public function recommendationsCount(): JsonResponse
    {
        $count = Recommendation::query()
            ->toMe()->approved()->new()->count();

        return $this->resourceItemResponse('new_recommendations_count', $count);
    }

    /**
     * ADMIN BADGES
     */


    public function moderationOrders(): JsonResponse
    {
        $count = Order::query()->moderation()->count();
        return $this->resourceItemResponse('moderation_orders_count', $count);
    }

    public function refereeReports(): JsonResponse
    {
        $count = Report::query()->referee()->count();
        return $this->resourceItemResponse('referee_reports_count', $count);
    }

    public function draftClaims(): JsonResponse
    {
        $count = Claim::query()->draft()->count();
        return $this->resourceItemResponse("draft_claims_count", $count);
    }

    public function draftRecommendations(): JsonResponse
    {
        $count = Recommendation::query()->draft()->count();
        return $this->resourceItemResponse('draft_recommendations_count', $count);
    }


    public function moderationCompanies(): JsonResponse
    {
        $count = Company::query()->onModeration()->count();
        return $this->resourceItemResponse('moderation_companies_count', $count);
    }
}
