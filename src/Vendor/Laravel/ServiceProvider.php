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
use PragmaRX\Steroids\Support\KeywordList;
use PragmaRX\Steroids\Support\BladeParser;
use PragmaRX\Steroids\Support\BladeProcessor;

use PragmaRX\Steroids\Vendor\Laravel\Artisan\Templates as TemplatesCommand;
use PragmaRX\Steroids\Vendor\Laravel\Artisan\Clear as ClearCommand;
use PragmaRX\Steroids\Vendor\Laravel\Artisan\Report as ReportCommand;

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

        $this->registerKeywordList();

        $this->registerBladeParser();

        $this->registerBladeProcessor();

        $this->registerSteroids();

        $this->registerTemplatesCommand();

        $this->registerClearCommand();

        $this->registerReportCommand();

        $this->registerBladeExtension();

        $this->commands('steroids.templates.command');

        $this->commands('steroids.clear.command');

        $this->commands('steroids.report.command');
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
        $this->app->singleton('steroids.fileSystem', function($app)
        {
            return new Filesystem;
        });
    }

    /**
     * Register the KeywordList driver used by Steroids
     * 
     * @return void
     */
    private function registerKeywordList()
    {
        $this->app->singleton('steroids.keywordList', function($app)
        {
            return new KeywordList(
                                    $app['steroids.config'],
                                    $app['steroids.fileSystem']
                                );
        });
    }

    /**
     * Register the KeywordList driver used by Steroids
     * 
     * @return void
     */
    private function registerBladeProcessor()
    {
        $this->app->singleton('steroids.bladeProcessor', function($app)
        {
            return new BladeProcessor($app['steroids.config']);
        });
    }

    /**
     * Register the KeywordList driver used by Steroids
     * 
     * @return void
     */
    private function registerBladeParser()
    {
        $this->app->singleton('steroids.bladeParser', function($app)
        {
            return new BladeParser();
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
        $this->app->singleton('steroids', function($app)
        {
            $app['steroids.loaded'] = true;

            return new Steroids(
                                    $app['steroids.config'],
                                    $app['steroids.fileSystem'],
                                    $app['steroids.keywordList'],
                                    $app['steroids.bladeParser'],
                                    $app['steroids.bladeProcessor']
                                );
        });
    }

    /**
     * Register the Templates Artisan command
     *
     * @return void
     */ 
    private function registerTemplatesCommand()
    {
        $this->app->singleton('steroids.templates.command', function($app)
        {
            return new TemplatesCommand();
        });
    }

    /**
     * Register the Clear Artisan command
     *
     * @return void
     */ 
    private function registerClearCommand()
    {
        $this->app->singleton('steroids.clear.command', function($app)
        {
            return new ClearCommand();
        });
    }

    /**
     * Register the Report Artisan command
     *
     * @return void
     */ 
    private function registerReportCommand()
    {
        $this->app->singleton('steroids.report.command', function($app)
        {
            return new ReportCommand();
        });
    }

    private function registerBladeExtension()
    {
        $this->app['view']->getEngineResolver()->resolve('blade')->getCompiler()->extend(function ($view, $compiler) {

            return $this->app['steroids']->inject($view, $compiler);

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
