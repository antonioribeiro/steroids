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

return array(

    'create_steroids_alias' => true,

    'steroids_alias' => 'Steroids',

    /*
    |--------------------------------------------------------------------------
    | Code and message for blocked responses
    |--------------------------------------------------------------------------
    |
    */

    'block_response_code' => 403,

    'block_response_message' => null,

    /*
    |--------------------------------------------------------------------------
    | Do you wish to redirect non whitelisted accesses to an error page?
    |--------------------------------------------------------------------------
    |
    */

    'redirect_non_whitelisted_to' => null,


    /*
    |--------------------------------------------------------------------------
    | How long should we keep IP addresses in cache?
    |--------------------------------------------------------------------------
    |
    */

    'cache_expire_time' => 2, // minutes

    /*
    |--------------------------------------------------------------------------
    | Send suspicious events to log?
    |--------------------------------------------------------------------------
    |
    */

    'enable_log' => true,

    /*
    |--------------------------------------------------------------------------
    | Which PHP Framework is your application using?
    |--------------------------------------------------------------------------
    |
    |   Supported: "laravel", "none"
    |   
    */

    'framework' => 'laravel',

    /*
    |--------------------------------------------------------------------------
    | Default Database Driver
    |--------------------------------------------------------------------------
    |
    | This option controls the database driver that will be utilized.
    |
    |
    */

    'driver' => 'eloquent',

    /*
    |--------------------------------------------------------------------------
    | Models
    |--------------------------------------------------------------------------
    |
    | When using the "eloquent" driver, we need to know which Eloquent models 
    | should be used.
    |
    */

    'steroids_model' => 'PragmaRX\Steroids\Vendor\Laravel\Models\Steroids',

);
