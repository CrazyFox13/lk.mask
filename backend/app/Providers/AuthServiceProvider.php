<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use App\Models\Claim;
use App\Models\Company;
use App\Models\Message;
use App\Models\Order;
use App\Models\OrderFilter;
use App\Models\Photo;
use App\Models\PhotoGroup;
use App\Models\Recommendation;
use App\Models\Report;
use App\Models\User;
use App\Models\VehicleCategory;
use App\Models\VehicleGroup;
use App\Models\VehicleType;
use App\Policies\ClaimPolicy;
use App\Policies\CompanyPolicy;
use App\Policies\MessagePolicy;
use App\Policies\OrderFilterPolicy;
use App\Policies\OrderPolicy;
use App\Policies\PhotoGroupPolicy;
use App\Policies\PhotoPolicy;
use App\Policies\RecommendationPolicy;
use App\Policies\ReportPolicy;
use App\Policies\UserPolicy;
use App\Policies\VehicleCategoryPolicy;
use App\Policies\VehicleGroupPolicy;
use App\Policies\VehicleTypePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
        User::class => UserPolicy::class,
        Company::class => CompanyPolicy::class,
        Order::class => OrderPolicy::class,
        Report::class => ReportPolicy::class,
        Recommendation::class => RecommendationPolicy::class,
        Claim::class => ClaimPolicy::class,
        PhotoGroup::class => PhotoGroupPolicy::class,
        Photo::class => PhotoPolicy::class,
        Message::class => MessagePolicy::class,
        OrderFilter::class => OrderFilterPolicy::class,
        VehicleCategory::class => VehicleCategoryPolicy::class,
        VehicleGroup::class => VehicleGroupPolicy::class,
        VehicleType::class => VehicleTypePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::resource('companies', CompanyPolicy::class, [
            'approve' => 'moderate',
            'cancel' => 'moderate',
        ]);

        Gate::resource('orders', OrderPolicy::class, [
            'approve' => 'moderate',
            'cancel' => 'moderate',
        ]);

        Gate::resource('companies', CompanyPolicy::class, [
            'moderate' => 'moderate',
        ]);
        Gate::resource('orders', OrderPolicy::class, [
            'moderate' => 'moderate',
        ]);
        Gate::resource('claim', ClaimPolicy::class, [
            'moderate' => 'moderate',
        ]);

        Gate::resource('reports', ReportPolicy::class, [
            'referee' => 'referee',
            'moderate' => 'moderate',
        ]);
        Gate::resource('vehicleCategories', VehicleCategoryPolicy::class);
        Gate::resource('vehicleGroups', VehicleGroupPolicy::class);
        Gate::resource('vehicleTypes', VehicleTypePolicy::class);
    }
}
