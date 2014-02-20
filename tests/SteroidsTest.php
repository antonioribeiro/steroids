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

use Mockery as m;

use PragmaRX\Steroids\Steroids;

use PragmaRX\Steroids\Support\Config;
use PragmaRX\Steroids\Support\Filesystem;
use PragmaRX\Steroids\Support\CacheManager;

use PragmaRX\Steroids\Repositories\DataRepository;

use PragmaRX\Steroids\Repositories\Steroids\Steroids as SteroidsRepository;

// use PragmaRX\Steroids\Support\Sentence;
// use PragmaRX\Steroids\Support\Locale;
// use PragmaRX\Steroids\Support\SentenceBag;
// use PragmaRX\Steroids\Support\Mode;
// use PragmaRX\Steroids\Support\MessageSelector;

// use PragmaRX\Steroids\Repositories\DataRepository;
// use PragmaRX\Steroids\Repositories\Messages\Laravel\Message;
// use PragmaRX\Steroids\Repositories\Cache\Cache;

use Illuminate\Console\Application;

class SteroidsTest extends PHPUnit_Framework_TestCase {

	/**
	 * Setup resources and dependencies.
	 *
	 * @return void
	 */
	public function setUp()
	{
		$this->config = new Config(new Filesystem);

		$steroidsModel = $this->config->get('steroids_model');

		$this->cache = m::mock('PragmaRX\Steroids\Support\CacheManager');

		$this->validIpv4 = '1.1.1.1';
		$this->invalidIpv4 = '1.1.1';

		$this->validIpv6 = '1:1:1:1:1:1:1:1';
		$this->invalidIpv6 = '1:1:1:1:1:::1';

		$this->fileSystem = new Filesystem;

		$this->model = m::mock('StdClass');

		$this->cursor = m::mock('StdClass');

		$this->dataRepository = new DataRepository(

										new SteroidsRepository($this->model, $this->cache),

										$this->config,

										$this->cache,

										$this->fileSystem
									);


		$this->steroids = new Steroids(
			$this->config,
			$this->dataRepository,
			$this->cache,
			$this->fileSystem
		);
	}

	public function testValidIP()
	{
		// IPv4
		$this->assertTrue($this->steroids->isValid($this->validIpv4));
		$this->assertFalse($this->steroids->isValid($this->invalidIpv4));

		// IPv6
		$this->assertTrue($this->steroids->isValid($this->validIpv6));
		$this->assertFalse($this->steroids->isValid($this->invalidIpv6));
	}

	public function testReport()
	{
		$this->model->shouldReceive('all')->andReturn($this->cursor);
		$this->cursor->shouldReceive('toArray')->andReturn(array());

		$this->assertEquals($this->steroids->report(), array());
	}

	public function testWhitelist()
	{
		// $this->cache->shouldReceive('has')->andReturn(true);
		// $this->cache->shouldReceive('get')->andReturn(true);
		// $this->assertEquals($this->steroids->whitelist($this->validIpv4), true);
	}

}