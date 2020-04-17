<?php

namespace Modules\Icommerceepayco\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Core\Traits\CanPublishConfiguration;
use Modules\Core\Events\BuildingSidebar;
use Modules\Core\Events\LoadingBackendTranslations;
use Modules\Icommerceepayco\Events\Handlers\RegisterIcommerceEpaycoSidebar;

class IcommerceEpaycoServiceProvider extends ServiceProvider
{
    use CanPublishConfiguration;
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerBindings();
        $this->app['events']->listen(BuildingSidebar::class, RegisterIcommerceEpaycoSidebar::class);

        $this->app['events']->listen(LoadingBackendTranslations::class, function (LoadingBackendTranslations $event) {
            $event->load('epaycoconfigs', array_dot(trans('icommerceepayco::epaycoconfigs')));
            // append translations

        });
    }

    public function boot()
    {
        $this->publishConfig('Icommerceepayco', 'permissions');
        $this->publishConfig('Icommerceepayco', 'settings');

        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array();
    }

    private function registerBindings()
    {
        $this->app->bind(
            'Modules\Icommerceepayco\Repositories\EpaycoconfigRepository',
            function () {
                $repository = new \Modules\Icommerceepayco\Repositories\Eloquent\EloquentEpaycoconfigRepository(new \Modules\Icommerceepayco\Entities\Epaycoconfig());

                if (! config('app.cache')) {
                    return $repository;
                }

                return new \Modules\Icommerceepayco\Repositories\Cache\CacheEpaycoconfigDecorator($repository);
            }
        );
// add bindings

    }
}
