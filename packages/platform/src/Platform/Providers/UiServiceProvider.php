<?php

declare(strict_types=1);

namespace Laravolt\Platform\Providers;

use BladeUI\Icons\Factory;
use Illuminate\Foundation\Application;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Laravolt\Asset\AssetManager;
use Laravolt\Platform\Services\Menu;
use Laravolt\Platform\Services\MenuBuilder;

/**
 * Class PackageServiceProvider.
 *
 * @see     http://laravel.com/docs/master/packages#service-providers
 * @see     http://laravel.com/docs/master/providers
 */
class UiServiceProvider extends BaseServiceProvider
{
    /**
     * Register the service provider.
     *
     * @see    http://laravel.com/docs/master/providers#the-register-method
     * @return void
     */
    public function register()
    {
        $this->app->singleton(
            'laravolt.menu.sidebar',
            function () {
                return new Menu();
            }
        );

        $this->bootConfig();

        // We add default menu in register() method,
        // to make sure it is always accessible by other providers.
        app('laravolt.menu.sidebar')->register(
            function ($menu) {
                $menu->add('Modules')->data('order', config('laravolt.ui.system_menu.order'));
            }
        );
        app('laravolt.menu.sidebar')->register(
            function ($menu) {
                $menu->add('System')->data('order', config('laravolt.ui.system_menu.order') + 1);
            }
        );

        if (! $this->app->runningInConsole()) {
            $this->overrideUi();
            $this->registerAssets();
            $this->registerIcons();
            $this->registerMenuBuilder();
        }
    }

    /**
     * Application is booting.
     *
     * @see    http://laravel.com/docs/master/providers#the-boot-method
     * @return void
     */
    public function boot()
    {
        $this
            ->bootViews()
            ->buildMenuFromConfig();
    }

    protected function bootConfig()
    {
        $this->mergeConfigFrom(
            platform_path('config/ui.php'),
            'laravolt.ui'
        );

        $this->publishes(
            [
                platform_path('config/ui.php') => config_path('laravolt/ui.php'),
            ]
        );

        $theme = $this->app['config']->get('laravolt.ui.sidebar_theme');
        $themeOptions = $this->app['config']->get('laravolt.ui.themes.'.$theme);
        $this->app['config']->set('laravolt.ui.options', $themeOptions);

        return $this;
    }

    /**
     * Register the package views.
     *
     * @see    http://laravel.com/docs/master/packages#views
     * @return self
     */
    protected function bootViews()
    {
        Paginator::defaultView('laravolt::pagination.default');
        Paginator::defaultSimpleView('laravolt::pagination.simple');

        return $this;
    }

    protected function registerMenuBuilder()
    {
        $this->app->singleton(
            'laravolt.menu.builder',
            function (Application $app) {
                return $app->make(MenuBuilder::class);
            }
        );
    }

    protected function buildMenuFromConfig()
    {
        View::composer(
            'laravolt::menu.sidebar',
            function () {
                foreach (config('laravolt.menu') as $menu) {
                    $this->app['laravolt.menu.builder']->loadArray($menu);
                }
            }
        );

        return $this;
    }

    protected function registerAssets()
    {
        if (! $this->app->bound('laravolt.asset.group.laravolt')) {
            $this->app->singleton(
                'laravolt.asset.group.laravolt',
                function () {
                    return new AssetManager(
                        [
                            'public_dir' => public_path('laravolt'),
                            'css_dir' => '',
                            'js_dir' => '',
                        ]
                    );
                }
            );
        }
    }

    private function registerIcons()
    {
        $this->callAfterResolving(
            Factory::class,
            function (Factory $factory) {
                $icons = [
                    'fad' => platform_path('resources/icons/duotone'),
                    'far' => platform_path('resources/icons/regular'),
                    'fal' => platform_path('resources/icons/light'),
                    'fas' => platform_path('resources/icons/solid'),
                ];
                foreach ($icons as $prefix => $path) {
                    $factory->add($prefix, ['path' => $path, 'prefix' => $prefix]);
                }
            }
        );
    }

    private function overrideUi()
    {
        $this->app->booted(function () {
            $uiSettings = collect(config('laravolt.platform.settings'))->pluck('name')->filter()
                ->transform(
                    function ($item) {
                        return "laravolt.ui.$item";
                    }
                )
                ->toArray();
            foreach ($uiSettings as $key) {
                config([$key => setting($key)]);
            }
        });
    }
}
