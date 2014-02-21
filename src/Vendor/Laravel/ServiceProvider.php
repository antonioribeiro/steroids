<?php 

/**
 * Part of the Steroids package.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the 3-clause BSD License.
 *
 * This source file is subject to the 3-clause BSD License that is
 * bundled with this package in the LICENSE file.  It is also available at
 * the following URL: http://www.opensource.org/licenses/BSD-3-Clause
 *
 * @package    Steroids
 * @version    0.1.0
 * @author     Antonio Carlos Ribeiro @ PragmaRX
 * @license    BSD License (3-clause)
 * @copyright  (c) 2013, PragmaRX
 * @link       http://pragmarx.com
 */

namespace PragmaRX\Steroids\Vendor\Laravel;

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

        // $this->commands('steroids.clear.command');
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
