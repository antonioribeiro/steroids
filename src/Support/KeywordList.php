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
use PragmaRX\Exceptions\TemplatesDirectoryNotAvailable;

class KeywordList {
	
	private $config;

	private $fileSystem;

	private $keywords = array();

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
		foreach($this->getFiles($this->getTemplatesDir()) as $file)
		{
			$this->addKeyword($file);
		}
	}

	public function all()
	{
		return $this->keywords;
	}

	private function getFiles($dir) 
	{
		if ( ! $this->fileSystem->isDirectory($dir))
		{
			throw new TemplatesDirectoryNotAvailable("Error Processing Request", 1);
		}

		return $this->fileSystem->allFiles($dir);
	}

	private function addKeyword($file) 
	{
		$keyword = $this->makeKeyword($file);

		if ($keyword && ! $this->isInDefaultDir($file))
		{
			$tree = explodeTree(array($file->getRelativePath() => $keyword), slash());
		}
		else
		{
			$tree = array('default' => $keyword);
		}

		$this->keywords = array_merge_recursive($this->keywords, $tree);
	}

	private function getTemplatesDir() 
	{
		return $this->config->get('templates_dir');
	}

	private function getDefaultTemplateDir() 
	{
		return $this->getTemplatesDir() . $this->config->get('default_template_dir');
	}	

	private function makeFileName($file) 
	{
		if ( ! is_string($file))
		{
			$file = $file->getRelativePathname();
		}

		return $this->getTemplatesDir().slash().$file;
	}

	private function makeKeyword($file) 
	{
		if ( ! $keyword = $file->getBasename('.blade.php'))
		{
			return;
		}

		$fileContents = $this->fileSystem->get($this->makeFileName($file));

		$hasBody = $this->hasBody($fileContents);

		return array(
			$keyword => array(
							'keyword' => $keyword, 
							'hasBody' => $hasBody, 
							'template' => $fileContents
						)
		);
	}

	private function hasBody($contents) 
	{
		return strpos($contents, '$__BODY') !== false;
	}

	private function isInDefaultDir($file) 
	{
		return $this->makeFileName($file) === $this->getDefaultTemplateDir().slash().$file->getBasename();
	}
}