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

	const T_SINGLE_STRING	= 0; // "class=hidden"
	const T_GLOBAL_VARIABLE = 1; // $var=hidden
	const T_LOCAL_VARIABLE  = 2; // #const=1
	const T_HTML_ATTRIBUTE  = 3; // const=1

	private $marker;

	private $line;

	private $instruction;

	private $template;

	private $parameters;

	private $body;

	private $string;

	private $type;

	private $start;

	private $end;

	private $number;

	private $attributes;

	private $locals;

	public function __construct($command) 
	{
		$this->parse($command);
	}

	private function parse($command)
	{
		preg_match('/(@{1,2})([\w\.]*)\(?(\w*[^(].*[^)]+)?\)?(.*)?/', $command, $matches, PREG_OFFSET_CAPTURE);

		if (count($matches) > 1) 
		{
			list($instruction, $template) = $this->parseInstruction($matches[2][0]);

			$this->line = $matches[0][0];
			$this->marker = $matches[1][0];
			$this->instruction = $instruction;
			$this->template = $template;
			$this->parameters = $this->parseParameters($matches[3][0]);
			$this->body = $matches[4][0];
		}

		$this->boot();
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

	public function getFullInstruction() 
	{
		return $this->getTemplate() . '.' . $this->instruction;
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
		preg_match_all("/(?:\'[^\']*[^\"]\'|\"[^\"]*[^\']*\"|\[.*\]|\(.*\)|)\K(,|;|$)/", $string, $matches, PREG_OFFSET_CAPTURE);

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
		// Check if the parameter is just a ("single string").
		// 
		if(preg_match('/^(?:\s*)([\'\"].*[\'\"])(?:\s*)$/', $string, $matches))
		{
			$parameter['type'] = self::T_SINGLE_STRING;
			$parameter['variable'] = null;
			$parameter['value'] = $this->parseValue($matches[1]);

			return $parameter;			
		}

		// Check for any other type of parameter
		preg_match('/(?:\s*)([$#\"\']?\w+)?(=\>|=)?(.*)?/', $string, $matches);

		$parameter = array();

		if ($matches[2] !== "=")
		{
			$parameter['type'] = self::T_HTML_ATTRIBUTE;
			$parameter['variable'] = $this->parseValue($matches[1]);
		}
		else
		{
			$start = 1;

			switch ($matches[1][0]) {
				case '$':
					$parameter['type'] = self::T_GLOBAL_VARIABLE;
					break;

				case '#':
					$parameter['type'] = self::T_LOCAL_VARIABLE;
					break;
				
				default:
					$parameter['type'] = self::T_HTML_ATTRIBUTE;
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
		preg_match('/^(?:array\(|\[)(.*)(?:\)|\])$/', $value, $matches);

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
		preg_match('/([$#]?\w+)?(=\>|=)?(.*)?/', $item, $matches);

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

	private function parseInstruction($string) 
	{
		$parts = explode('.', $string);

		$instruction = array_pop($parts);

		$template = count($parts) ? implode('.', $parts) : 'default';

		return array($instruction, $template);
	}

	public function getString()
	{
		return $this->string;
	}

	public function getType()
	{
		return $this->type;
	}

	public function getStart()
	{
		return $this->start;
	}

	public function getEnd()
	{
		return $this->end;
	}

	public function getNumber()
	{
		return $this->number;
	}

	public function setString($string)
	{
		$this->string = $string;
	}

	public function setType($type)
	{
		$this->type = $type;
	}

	public function setStart($start)
	{
		$this->start = $start;
	}

	public function setEnd($end)
	{
		$this->end = $end;
	}

	public function setNumber($number)
	{
		$this->number = $number;
	}

	public function getAttributes() 
	{
		return $this->attributes;
	}

	private function clearMemory() 
	{
		$this->body = null;
		$this->attributes = array();
		$this->local = array();
	}

	public function boot()
	{
		$this->clearMemory();

		if($this->getLine())
		{
			foreach($this->getParameters() as $parameter)
			{
				if ($parameter['type'] == static::T_HTML_ATTRIBUTE)
				{
					if (isset($parameter['value']))
					{
						$this->addAtribute($parameter['variable'], $parameter['value']);		
					}
				}
				else
				if ($parameter['type'] == static::T_LOCAL_VARIABLE)
				{
					$this->addLocal($parameter['variable'], $parameter['value']);
				}
			}
		}
	}

	private function addAtribute($variable, $value) 
	{
		$this->attributes[$variable][$value] = $value;
	}

	private function addLocal($variable, $value) 
	{
		$this->local[$variable] = $value;
	}

	public function processVariables($view)
	{
		$view = str_replace('@_BODY_@', $this->body, $view);

		$view = str_replace('@_ATTRIBUTES_@', $this->getAttributesString(), $view);

		foreach ($this->local as $key => $value) 
		{
			str_replace('@_'.$key.'_@', $value, $view);
		}
	}

	private function getAttributesString($clean = false) 
	{
		foreach($this->getAttributes() as $key => $values)
		{
			$attributes = 
			$attributes[] = $key.'="'..'"';
		}

		dd($attributes);

		// return 
	}

}
