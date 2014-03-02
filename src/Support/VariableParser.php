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

class VariableParser {

	private $variables;

	public function __construct($template = null) 
	{
		if ($template)
		{
			$this->parse($template);
		}
	}

	public function parse($template)
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

	public function all() 
	{
		return $this->variables;
	}

	public function first()
	{
		if (isset($this->variables[0]))
		{
			return $this->variables[0];
		}
	}

}