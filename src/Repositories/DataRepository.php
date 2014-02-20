<?php namespace PragmaRX\Steroids\Repositories;
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

use PragmaRX\Support\Config;
use PragmaRX\Support\Filesystem;
use PragmaRX\Support\Finder;
use PragmaRX\Support\CacheManager;

use PragmaRX\Steroids\Repositories\Steroids\SteroidsInterface;

class DataRepository implements DataRepositoryInterface {

	public $steroids;

	private $config;

	private $cache;

	private $fileSystem;

	/**
	 * Create instance of DataRepository
	 * @param MessageInterface          $message          
	 * @param TranslationInterface      $translation      
	 * @param LocaleRepositoryInterface $localeRepository 
	 * @param Config                    $config           
	 * @param Filesystem                $fileSystem       
	 */
	public function __construct(
									SteroidsInterface $steroids, 
									Config $config,
									CacheManager $cache,
									Filesystem $fileSystem
								)
	{
		$this->steroids = $steroids;

		$this->config = $config;

		$this->fileSystem = $fileSystem;

		$this->cache = $cache;
	}

}