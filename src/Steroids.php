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

namespace PragmaRX\Steroids;

use PragmaRX\Support\Config;
use PragmaRX\Support\Filesystem;
use PragmaRX\Steroids\Support\KeywordList;
use PragmaRX\Steroids\Support\BladeParser;
use PragmaRX\Steroids\Support\BladeProcessor;

class Steroids
{
	/**
	 * Configuration object
	 * 
	 * @var Config
	 */
	private $config;

	/**
	 * Filesystem object
	 * 
	 * @var Filesystem
	 */
	private $fileSystem;

	/**
	 * KeywordList object
	 * 
	 * @var KeywordList
	 */
	private $keywordList;

	/**
	 * BladeParser object
	 * 
	 * @var BladeParser
	 */
	private $parser;

	/**
	 * Initialize Steroids object
	 * 
	 * @param Locale $locale
	 */
	public function __construct(Config $config, 
								Filesystem $fileSystem, 
								KeywordList $keywordList, 
								BladeParser $parser, 
								BladeProcessor $processor)
	{
		$this->config = $config;

		$this->fileSystem = $fileSystem;

		$this->keywordList = $keywordList;

		$this->parser = $parser;

		$this->processor = $processor;
	}

	/**
	 * The main parser and view processor.
	 * 
	 * @param  string $view     
	 * @param  Compiler $compiler 
	 * @return string
	 */
	public function inject($view, $compiler = null)
	{
		$this->parser->setKeywords($this->keywordList->all());

		try 
		{
			while($this->parser->hasCommands($view))
			{
				$view = $this->processor->process($view, $this->parser->getFirstCommand());
			}
		} 
		catch (\Exception $exception) 
		{
			return $this->treatException($exception, $compiler);
		}

		return $view;
	}

	/**
	 * Exception handler. Will process a handled exception and, if a compiler is available
	 * add the file name to the error message.
	 * 
	 * @param  Exception $exception 
	 * @param  Compiler $compiler  
	 * @return void
	 */
	private function treatException($exception, $compiler) 
	{
		$message = $compiler && method_exists($compiler, 'getPath') 
					? ' Template path: '.$compiler->getPath() 
					: '';

		if ($message && method_exists($exception, 'append'))
		{
			$exception->append($message);
		}

		throw $exception;
	}

	/**
	 * Retrieve all commands parsed.
	 * 
	 * @return array
	 */
	public function getCommands() 
	{
		return $this->keywordList->all();
	}

	/**
	 * Retrieve a configuration item
	 * 
	 * @param  string $key 
	 * @return mixed
	 */
	public function getConfig($key)
	{
		return $this->config->get($key);
	}

	/**
	 * Set the templates dir.
	 * 
	 * @param string $dir
	 */
	public function setTemplatesDir($dir) 
	{
		$this->keywordList->setTemplatesDir($dir);
	}

}