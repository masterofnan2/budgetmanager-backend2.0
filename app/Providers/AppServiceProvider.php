<?php

namespace App\Providers;

use App\Actions\AuthActions;
use App\Actions\BudgetActions;
use App\Actions\CategoryActions;
use App\Actions\CycleActions;
use App\Actions\Mail\ConfirmationActions;
use App\Actions\TokenActions;
use App\Actions\UserActions;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(TokenActions::class, fn() => new TokenActions($_SERVER['HTTP_USER_AGENT']));
        $this->app->singleton(BudgetActions::class, fn() => new BudgetActions);
        $this->app->singleton(AuthActions::class, fn() => new AuthActions);
        $this->app->singleton(CycleActions::class, fn() => new CycleActions);
        $this->app->singleton(ConfirmationActions::class, fn() => new ConfirmationActions);
        $this->app->singleton(UserActions::class, fn() => new UserActions);
        $this->app->singleton(CategoryActions::class, fn() => new CategoryActions);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);
    }
}
