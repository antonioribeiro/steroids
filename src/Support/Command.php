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
 
class Command {

	private $marker;

	private $line;

	private $instruction;

	private $template;

	private $parameters;

	private $body;

	public function __construct($command) 
	{
		$this->parse($command);
	}

	private function parse($command)
	{
		$pattern = '/(@{1,2})(\w+)?([-|\+|#]?\w+)?\(?([^\)]+)?\)?(.*)?/';

		preg_match($pattern, $command, $matches, PREG_OFFSET_CAPTURE);

		if(count($matches) > 1) {
			$this->line = $matches[0][0];
			$this->marker = $matches[1][0];
			$this->instruction = $matches[2][0];
			$this->template = $matches[3][0];
			$this->parameters = $this->parseParameters($matches[4][0]);
			$this->body = $matches[5][0];
		}
	}

	public function getLine() 
	{
		return $this->line;
	}

	public function getMarker() 
	{
		return $this->marker;
	}

	public function getInstruction() 
	{
		return $this->instruction;
	}

	public function getTemplate() 
	{
		return $this->template;
	}

	public function getParameters() 
	{
		return $this->parameters;
	}

	public function getBody() 
	{
		return $this->body;
	}	

	public function setInstruction($instruction) 
	{
		$this->instruction = $instruction;
	}

	private function parseParameters($string) 
	{
		// Separating parameters
		$pattern = "/(?:\'[^\']*\'|\"[^\"]*\"|)\K(,|;|$)/";

		preg_match_all($pattern, $string, $matches, PREG_OFFSET_CAPTURE);

		// d($string);
		// dd($matches);

		$parameters = array();

		foreach(range(0,count($matches[1])-1) as $i)
		{
			if ($matches[1][$i][0] !== "")
			{
				if ( ! count($parameters))
				{
					$parameters[] = substr($string, 0, $matches[1][$i][1]-1);	
				}

				$parameters[] = substr($string, $matches[1][$i][1]+1, $matches[1][$i+1][1]-$matches[1][$i][1]-1);
			}
		}

		d($string);
		d($matches);
		dd($parameters);

		foreach(explode(',', $string) as $parameter)
		{
			$parts = explode('=',$parameter);

			if (count($parts) == 1)
			{
				${$defaultAttribute} = $parts[0];
			}
			else
			if (count($parts) > 1)
			{
				$attrName = $parts[0];
				$attrValue = $parts[1];

				if (isset($attrValue[0]))
				{
					$attrValue = $attrValue[0] == "$" ? '<?='.$attrValue.'?>' : $attrValue;
				}
				else
				{
					$attrValue = '';	
				}
			
				$name = $attrName == 'name' ? $attrValue : $name;

				switch ($attrName) {
					case 'value':
						$value = $attrValue;
						break;

					case 'label':
						$label = $attrValue;
						break;

					case 'color':
						$classes[] = "btn-$attrValue";
						break;

					case 'md':
						$classes[] = "col-md-$attrValue";
						break;

					case 'xs':
						$classes[] = "col-xs-$attrValue";
						break;

					case 'sm':
						$classes[] = "col-sm-$attrValue";
						break;

					case 'class':
						$classes[] = "$attrValue";
						break;

					default:
						$attributes[] = "$attrName=\"$attrValue\"";
						break;
				}
			}

		}	
	}
}
