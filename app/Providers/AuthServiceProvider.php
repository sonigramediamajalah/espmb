<?php

namespace App\Providers;

use Auth;
use App\Ibrol\Libraries\RoleAccess;
use App\Menu;
use App\Action;
use App\Role;
use Cache;
use Illuminate\Contracts\Auth\Access\Gate as GateContract;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */

    public function boot(GateContract $gate)
    {
        $this->registerPolicies($gate);

        foreach($this->getMenus() as $key => $value) {
            foreach($value->module->actions as $action) {
                $gate->define($value->menu_name . '-' . $action->action_name, function($user) use ($value, $action) {
                    return RoleAccess::hasAccess(Auth::user()->roles, $value->module_id, $action->action_id);
                    //return true;
                });
            }
        }
    }

    public function getMenus()
    {
        if(!Cache::has('allMenu')) {
            Cache::add('allMenu', Menu::with('module','module.actions')->where('active', '1')->get(), 1440);
        }
        return Cache::get('allMenu');

        //return Menu::with('module','module.actions')->where('active', '1')->get();
    }
}
