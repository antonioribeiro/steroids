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

use Exception;

class BladeProcessor {

	private $config;

	private $variables;
	
	public function __construct(Config $config)
	{
		$this->config = $config;
	}

	public function process($view, $command)
	{
		$template = $this->getTemplate($view, $command);

		return substr_replace($view, $template, $command->getStart(), $command->getLength());
	}

	private function getTemplate($view, $command)
	{
		$template = $command->getInstruction()['template'];

		while($this->parseVariables($template))
		{
			$template = $this->replace(
										$this->variables[0]['start'], 
										strlen($this->variables[0]['text']), 
										$command->getAttribute($this->variables[0]['name'], $this->variables[0]['function']),
										$template
									);
		}

		return $template;
	}

	private function parseVariables($template)
	{
		$count = preg_match_all('/(@_\w*->\w*)|(@_\w*)/', $template, $matches, PREG_OFFSET_CAPTURE);

		$this->variables = array();

		foreach($matches[0] as $match)
		{
			$text = $match[0];

			$start = $match[1];

			list($variable, $function) = $this->parseVariable($text);

			$this->variables[] = array(
										'text' => $text,
										'start' => $start,
										'name' => $variable,
										'function' => empty($function) ? 'plain' : $function,
									);
		}

		return $count;
	}

	private function parseVariable($text)
	{
		preg_match_all('/@_(\w*)(?:->)?(\w*)?/', $text, $matches);

		return array($matches[1][0], $matches[2][0]);
	}

	private function replace($start, $size, $string, $subject)
	{
		return substr_replace($subject, $string, $start, $size);
	}

}
