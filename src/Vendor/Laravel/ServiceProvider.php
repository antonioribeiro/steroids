<?php namespace PragmaRX\Steroids\Vendor\Laravel;

use PragmaRX\Steroids\Steroids;

use PragmaRX\Support\Config;
use PragmaRX\Support\Filesystem;
use PragmaRX\Support\CacheManager;
use PragmaRX\Support\Response;

use PragmaRX\Steroids\Vendor\Laravel\Artisan\Whitelist as WhitelistCommand;
use PragmaRX\Steroids\Vendor\Laravel\Artisan\Blacklist as BlacklistCommand;
use PragmaRX\Steroids\Vendor\Laravel\Artisan\Report as ReportCommand;
use PragmaRX\Steroids\Vendor\Laravel\Artisan\Remove as RemoveCommand;
use PragmaRX\Steroids\Vendor\Laravel\Artisan\Clear as ClearCommand;

use PragmaRX\Steroids\Repositories\DataRepository;
use PragmaRX\Steroids\Repositories\Cache\Cache;
use PragmaRX\Steroids\Repositories\Steroids\Steroids as SteroidsRepository;

use PragmaRX\Support\ServiceProvider as PragmaRXServiceProvider;

class ServiceProvider extends PragmaRXServiceProvider {

    protected $packageVendor = 'pragmarx';
    protected $packageVendorCapitalized = 'PragmaRX';

    protected $packageName = 'steroids';
    protected $packageNameCapitalized = 'Steroids';

    /**
     * This is the boot method for this ServiceProvider
     *
     * @return void
     */
    public function wakeUp()
    {

    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->preRegister();

        $this->registerFileSystem();

        $this->registerCache();

        $this->registerSteroids();

        $this->registerDataRepository();

        $this->registerWhitelistCommand();
        $this->registerBlacklistCommand();
        $this->registerReportCommand();
        $this->registerRemoveCommand();
        $this->registerClearCommand();

        $this->registerFilters();

        $this->commands('steroids.whitelist.command');
        $this->commands('steroids.blacklist.command');
        $this->commands('steroids.list.command');
        $this->commands('steroids.remove.command');
        $this->commands('steroids.clear.command');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array('steroids');
    }

    /**
     * Register the Filesystem driver used by Steroids
     * 
     * @return void
     */
    private function registerFileSystem()
    {
        $this->app['steroids.fileSystem'] = $this->app->share(function($app)
        {
            return new Filesystem;
        });
    }

    /**
     * Register the Cache driver used by Steroids
     * 
     * @return void
     */
    private function registerCache()
    {
        $this->app['steroids.cache'] = $this->app->share(function($app)
        {
            return new CacheManager($app);
        });
    }

    /**
     * Register the Data Repository driver used by Steroids
     * 
     * @return void
     */
    private function registerDataRepository()
    {
        $this->app['steroids.dataRepository'] = $this->app->share(function($app)
        {
            $steroidsModel = $this->getConfig('steroids_model');

            return new DataRepository(
                                        new SteroidsRepository(
                                                                    new $steroidsModel, 
                                                                    $this->app['steroids.cache'],
                                                                    $this->app['steroids.config']
                                                            ),

                                        $this->app['steroids.config'],

                                        $this->app['steroids.cache'],

                                        $this->app['steroids.fileSystem']
                                    );
        });
    }

    /**
     * Takes all the components of Steroids and glues them
     * together to create Steroids.
     *
     * @return void
     */
    private function registerSteroids()
    {
        $this->app['steroids'] = $this->app->share(function($app)
        {
            $app['steroids.loaded'] = true;

            return new Steroids(
                                    $app['steroids.config'],
                                    $app['steroids.dataRepository'],
                                    $app['steroids.cache'],
                                    $app['steroids.fileSystem'],
                                    $app['request']
                                );
        });
    }
 
    /**
     * Register blocking and unblocking filters
     * 
     * @return void
     */
    private function registerFilters()
    {
        $this->app['router']->filter('fw-block-bl', $this->getBlacklistFilter());

        $this->app['router']->filter('fw-allow-wl', $this->getWhitelistFilter());
    }

    public function getBlacklistFilter()
    {
        return function($route) 
        {
            if ($this->app['steroids']->isBlacklisted()) {
                $this->log('[blocked] IP blacklisted: '.$this->app['steroids']->getIp());

                return $this->blockAccess();
            }
        };
    }

    public function getWhitelistFilter()
    {
        return function($route)
        {
            if ( ! $this->app['steroids']->isWhitelisted()) {
                if($to = $this->getConfig('redirect_non_whitelisted_to'))
                {
                    $action = 'redirected';
                    $response = $this->app['redirect']->to($to);
                }
                else
                {
                    $action = 'blocked';
                    $response = $this->blockAccess();
                }

                $this->log(sprintf('[%s] IP not whitelisted: %s', $action, $this->app['steroids']->getIp()));

                return $response;
            }
        };        
    }

    /**
     * Return a proper response for blocked access
     *
     * @return Response
     */ 
    private function blockAccess()
    {
        return Response::make(
                                $this->getConfig('block_response_message'), 
                                $this->getConfig('block_response_code')
                            );    
    }

    /**
     * Register messages in log
     *
     * @return void
     */ 
    private function log($message)
    {
        if ($this->getConfig('enable_log'))
        {
            $this->app['log']->info("Steroids: $message");
        }
    }

    /**
     * Register the Whitelist Artisan command
     *
     * @return void
     */ 
    private function registerWhitelistCommand()
    {
        $this->app['steroids.whitelist.command'] = $this->app->share(function($app)
        {
            return new WhitelistCommand;
        });
    }

    /**
     * Register the Blacklist Artisan command
     *
     * @return void
     */ 
    private function registerBlacklistCommand()
    {
        $this->app['steroids.blacklist.command'] = $this->app->share(function($app)
        {
            return new BlacklistCommand;
        });
    }

    /**
     * Register the List Artisan command
     *
     * @return void
     */ 
    private function registerReportCommand()
    {
        $this->app['steroids.list.command'] = $this->app->share(function($app)
        {
            return new ReportCommand;
        });
    }

    /**
     * Register the List Artisan command
     *
     * @return void
     */ 
    private function registerRemoveCommand()
    {
        $this->app['steroids.remove.command'] = $this->app->share(function($app)
        {
            return new RemoveCommand;
        });
    }

    /**
     * Register the List Artisan command
     *
     * @return void
     */ 
    private function registerClearCommand()
    {
        $this->app['steroids.clear.command'] = $this->app->share(function($app)
        {
            return new ClearCommand;
        });
    }

    /**
     * Get the root directory for this ServiceProvider
     * 
     * @return string
     */
    public function getRootDirectory()
    {
        return __DIR__.'/../..';
    }    
}
