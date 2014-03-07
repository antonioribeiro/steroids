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
use PragmaRX\Steroids\Exceptions\TemplatesDirectoryNotAvailable;
use PragmaRX\Steroids\Support\VariableParser;

class KeywordList {
	
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
	 * Array with all keywords
	 * 
	 * @var array
	 */
	private $keywords = array();

	/**
	 * Templates directory.
	 * 	
	 * @var string
	 */
	private $templatesDir;

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

	/**
	 * Load all keywords from disk
	 * 
	 * @return void
	 */
	private function load() 
	{
		$this->keywords = array();

		foreach($this->getFiles($this->getTemplatesDir()) as $file)
		{
			$this->addKeyword($file);
		}
	}

	/**
	 * Retrieve all keywords.
	 * 
	 * @return string
	 */
	public function all()
	{
		return $this->keywords;
	}

	/**
	 * Get the list of keywords files and directories.
	 * 	
	 * @param  string $dir
	 * @return array
	 */
	private function getFiles($dir) 
	{
		if ( ! $this->fileSystem->isDirectory($dir))
		{
			throw new TemplatesDirectoryNotAvailable("Error Processing Request", 1);
		}

		return $this->fileSystem->allFiles($dir);
	}

	/**
	 * Load a file, parse it and add a keyword to the list.
	 * 
	 * @param string $file
	 */
	private function addKeyword($file)
	{
		if ($keyword = $this->makeKeyword($file))
		{
			if (! $this->isInDefaultDir($file) && $file->getRelativePath() !== '')
			{
				$tree = explodeTree(array($file->getRelativePath() => $keyword), slash());
			}
			else
			{
				$tree = array('default' => $keyword);
			}

			$this->keywords = array_merge_recursive($this->keywords, $tree);
		}
	}

	/**
	 * Retrieves the templates directory.
	 * 
	 * @return string
	 */
	private function getTemplatesDir() 
	{
		return $this->templatesDir ?: $this->config->get('templates_dir');
	}

	/**
	 * Retrieves the templates directory of the default commands.
	 * 
	 * @return string
	 */
	private function getDefaultTemplateDir() 
	{
		return $this->getTemplatesDir() . $this->config->get('default_template_dir');
	}	

	/**
	 * Make a filename.
	 * 
	 * @param  string $file 
	 * @return string
	 */
	private function makeFileName($file) 
	{
		if ( ! is_string($file))
		{
			$file = $file->getRelativePathname();
		}

		return $this->getTemplatesDir().slash().$file;
	}

	/**
	 * Parse a file to create a keyword.
	 * 	
	 * @param  string $file
	 * @return array
	 */
	private function makeKeyword($file) 
	{
		if ( ! $keyword = $this->getKeywordName($file))
		{
			return;
		}

		$fileContents = $this->fileSystem->get($this->makeFileName($file));

		$hasBody = $this->hasBody($fileContents);

		$variableParser = new VariableParser($fileContents);

		return array(
			$keyword => array(
							'keyword' => $keyword, 
							'hasBody' => $hasBody, 
							'template' => $fileContents,
							'variables' => $variableParser->all(),
						)
		);
	}

	/**
	 * Extract the keyword from the filename.
	 * 
	 * @param  Symfony\Component\Finder\SplFileInfo $file 
	 * @return string       
	 */
	private function getKeywordName($file) 
	{
		$keyword = $file->getBasename('.blade.php');

		// If the file still has extention, it's not a good one.
		if (strpos($keyword, '.') !== false)
		{
			return;
		}

		return $keyword;
	}

	/**
	 * Check if the keyword has a body.
	 * 
	 * @param  string  $contents 
	 * @return boolean           
	 */
	private function hasBody($contents) 
	{
		return strpos($contents, '@_BODY') !== false;
	}

	/**
	 * Check if the keyword file is in the default directory.
	 * 
	 * @param  string  $file 
	 * @return boolean
	 */
	private function isInDefaultDir($file) 
	{
		return $this->makeFileName($file) === $this->getDefaultTemplateDir().slash().$file->getBasename();
	}

	/**
	 * Set the templates path.
	 * 
	 * @param string $path
	 */
	public function setTemplatesDir($dir) 
	{
		$this->templatesDir = $dir;

		$this->load();		
	}
}