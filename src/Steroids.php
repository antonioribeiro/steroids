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

class Steroids
{
	private $config;

	private $fileSystem;

	private $keywords = array(
		'extends' 	=> array('hasBody' => false, 'template' => '<whatever>'),
		'php'		=> array('hasBody' => true, 'template' => '<whatever>'),
		'input' 	=> array('hasBody' => false, 'template' => '<whatever>'),
		'section' 	=> array('hasBody' => true, 'template' => '<whatever>'),
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
	}

	public function show()
	{
		$blade = "	@input-default(x=1,x=2)

					@extends('views.site._layouts.page')

					@section('pageContent') qualquer porra @@

					@input(x=1,x=2)

					@input-default(x=1,x=2)

					@php
						\$requiredJavascript\[\] = 'javascript.formLoader';
						\$widgetTitle = 'Novo usuário';
						\$widgetIcon = 'fa fa-check';
					@@

					@section('pageContent')
						<!-- widget grid -->
						<section id=\"widget-grid\" class=\"\">

							<!-- row -->
								<div class=\"row\">

								</div>
							<!-- row -->
						</section>
					@@
					";

		// $blade = "select * from x where mlima='foda-se essa merda' and caralho=maria.casaDoCacete;";

		$this->parser = new \PragmaRX\Steroids\Support\BladeParser; 
		$this->parser->setKeywords($this->keywords);
		$this->parser->scan($blade);
		dd($this->parser);

		return "show!";
	}

}
