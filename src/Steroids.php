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
	private $config;

	private $fileSystem;

	private $keywordList;

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

	public function show() 
	{
		$view = "@input-default(x='Se essa porra não rolar',y=`bosta quadrada!`)
                    @extends('views.site._layouts.page')
					@extends('views.site._layouts.page')

					@input(x=1,x=2)

					@input-default(x=1,x=2)

					@php
						\$requiredJavascript\[\] = 'javascript.formLoader';
						\$widgetTitle = 'Novo usuário';
						\$widgetIcon = 'fa fa-check';
					@@

					@box('pageContent')
						<!-- widget grid -->
						<box id=\"widget-grid\" class=\"\">
							@input(x=1,x=2)
							@input(x=1,x=2)
							@input(x=1,x=2)
							@input(x=1,x=2)
							<!-- row -->
								<div class=\"row\">
									@php
										\$requiredJavascript\[\] = 'javascript.formLoader';
										\$widgetTitle = 'Novo usuário';
										\$widgetIcon = 'fa fa-check';
									@@
								</div>
							<!-- row -->
						</box>
					@@
					";

		return $this->processView($view);
	}

	public function processView($view)
	{
		$this->parser->setKeywords($this->keywordList->all());

		while($this->parser->hasCommands($view))
		{
			$view = $this->processor->process($view, $this->parser->getFirstCommand());
			break;
		}

		return $view;
	}

	public function getConfig($key)
	{
		return $this->config->get($key);
	}

}
