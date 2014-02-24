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
 
namespace PragmaRX\Steroids\Support;

use PragmaRX\Support\Config;
use PragmaRX\Support\Filesystem;

class KeywordList {
	
	private $config;

	private $fileSystem;

	private $keywords = array(
		'extends' 	=> array('hasBody' => false, 'template' => '<whatever>'),
		'php'		=> array('hasBody' => true, 'template' => '<whatever>'),
		'input' 	=> array('hasBody' => false, 'template' => '<whatever>'),
		'box' 	=> array('hasBody' => true, 'template' => '<whatever>'),
	);

	/**
	 * Initialize Steroids object
	 * 
	 * @param Locale $locale
	 */
	public function __construct(Config $config, Filesystem $fileSystem)
	{
		$this->config = $config;

		$this->fileSystem = $fileSystem;

		$this->load();
	}

	private function load() 
	{

	}

	public function all() 
	{
		return $this->keywords;
	}

}