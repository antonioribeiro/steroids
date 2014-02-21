<?php namespace PragmaRX\Steroids\Vendor\Laravel;

use PragmaRX\Steroids\Steroids;

use PragmaRX\Support\Config;
use PragmaRX\Support\Filesystem;

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

        $this->registerSteroids();

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
                                    $app['steroids.fileSystem']
                                );
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
