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

	const T_ATTRIBUTE_PARAMETER  = 0; // class=hidden
	const T_VARIABLE_PARAMETER   = 1; // $var=hidden
	const T_CONSTANT_PARAMETER   = 2; // #const=1

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
		$pattern = '/(@{1,2})(\w+)?([.]?\w+)?\(?(\w*[^(].*[^)]+)?\)?(.*)?/';

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

	private function splitParameters($string) 
	{
		$pattern = "/(?:\'[^\']*[^\"]\'|\"[^\"]*[^\']*\"|\[.*\]|\(.*\)|)\K(,|;|$)/";

		preg_match_all($pattern, $string, $matches, PREG_OFFSET_CAPTURE);

		$parameters = array();
		$start = 0;

		foreach(range(0,count($matches[1])-1) as $i)
		{
			$parameters[] = substr($string, $start, $matches[1][$i][1]-$start);

			$start = $matches[1][$i][1]+1;
		}

		return $parameters;
	}

	private function parseParameters($string) 
	{
		$parameters = $this->splitParameters($string);

		foreach ($parameters as $key => $value) {
			$parameters[$key] = $this->parseParameter($value);
		}	

		return $parameters;		
	}

	private function parseParameter($string) 
	{
		$pattern = '/([$#]?\w+)?(=\>|=)?(.*)?/';
		preg_match($pattern, $string, $matches);

		$parameter = array();

		if ($matches[2] !== "=")
		{
			$parameter['type'] = self::T_ATTRIBUTE_PARAMETER;
			$parameter['variable'] = $this->parseValue($matches[1]);
		}
		else
		{
			$start = 1;

			switch ($matches[1][0]) {
				case '$':
					$parameter['type'] = self::T_VARIABLE_PARAMETER;
					break;

				case '#':
					$parameter['type'] = self::T_CONSTANT_PARAMETER;
					break;
				
				default:
					$parameter['type'] = self::T_ATTRIBUTE_PARAMETER;
					$start = 0;
					break;
			}

			$parameter['variable'] = substr($matches[1], $start);
			$parameter['value'] = $this->parseValue($matches[3]);
		}

		return $parameter;

		//// ----------- keep this for future use



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
				$attrValue = $attrValue[0] == "$" ? '< ?='.$attrValue.'? >' : $attrValue;
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

	private function parseValue($value) 
	{
		if (is_array($value = $this->parseArray($value)))
		{
			return $value;
		}

		return $value;
	}

	private function parseArray($value) 
	{
		$pattern = '/^(?:array\(|\[)(.*)(?:\)|\])$/';
		preg_match($pattern, $value, $matches);

		if ($matches)
		{
			return $this->parseArrayItems($matches[1]);
		}

		return $value;
	}

	private function parseArrayItems($arrayItems) 
	{
		$items = array();
		$number = 0;

		foreach($matches = $this->splitParameters($arrayItems) as $item)
		{
			list($key,$value) = $this->parseArrayItem($item);

			$items[$key ?: $this->nextArrayKey($items, $number)] = $value;
		}

		return $items;
	}

	private function parseArrayItem($item) 
	{
		$pattern = '/([$#]?\w+)?(=\>|=)?(.*)?/';

		preg_match($pattern, $item, $matches);

		$key   = $matches[2] == "=>" ? $matches[1] : null;
		$value = $matches[2] == "=>" ? $matches[3] : $matches[1];

		return array($key, $this->parseArray($value));
	}

	private function nextArrayKey($array, &$number)
	{
		while(isset($array[$number]))
		{
			$number++;
		}

		return $number;
	}
}
