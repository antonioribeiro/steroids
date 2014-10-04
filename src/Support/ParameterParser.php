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

use PragmaRX\Steroids\Support\Constant;

class ParameterParser {

	/**
	 * List of all positional parameters.
	 * 
	 * @var array
	 */
	private $positionalParameters = array();

	/**
	 * HTMl attributes passed as parameters.
	 * 
	 * @var array
	 */
	private $attributes = array();

	/**
	 * Single string parameter.
	 *
	 * 		@h1(this is the single string passed as one single parameter)
	 * 		
	 * @var string
	 */
	private $singleString = '';

	/**
	 * Parameters list
	 * 
	 * @var array
	 */
	private $parameters = array();

	/**
	 * Raw parameters string
	 * 
	 * @var string
	 */
	private $parametersString;

	/**
	 * ParameterParser instantiator
	 * 
	 * @param string $parameters
	 */
	public function __construct($parameters = null)
	{
		if ($parameters)
		{
			$this->parse($parameters);
		}
	}

	/**
	 * Parse command parameters.
	 * 
	 * @param  string $string 
	 * @return array
	 */
	public function parse($string) 
	{
		$this->parametersString = $string;

		$parameters = $this->splitParameters($string);

		$this->parameters = array();

		foreach ($parameters as $key => $value) {
			$this->positionalParameters[] = $value;

			foreach ($this->parseParameter($value) as $parameter)
			{
				$this->parameters[] = $parameter;
			}
		}

		return $this->parameters;
	}

	/**
	 * Split a string of parameters in an array of parameters.
	 * 
	 * @param  string $string
	 * @return array
	 */
	private function splitParameters($string) 
	{
		preg_match_all("/(?:\'[^\']*[^\"]\'|\"[^\"]*[^\']*\"|\[.*\]|\(.*\)|)\K(,|;|$)/", $string, $matches, PREG_OFFSET_CAPTURE);

		$parameters = array();

		$start = 0;

		foreach (range(0,count($matches[1])-1) as $i)
		{
			$parameters[] = substr($string, $start, $matches[1][$i][1]-$start);

			$start = $matches[1][$i][1]+1;
		}

		return $parameters;
	}

	/** 
	 * Parse one single parameter, separating name, operator and value
	 *   #name=Laravel
	 *     
	 * @param  string $string
	 * @return array
	 */
	private function parseParameter($string) 
	{
		$this->checkParameterIsAssignment($string, $parts) || 
		$this->checkParameterIsSingleString($string, $parts) || 
		$this->checkParameterIsAnyOtherType($string, $parts);

		return $parts;
	}

	/**
	 * Check if the parameter is one or multiple assignments
	 *
	 * @d(#label=name=Name)
	 *
	 * @param  string $string
	 * @param $parts
	 * @internal param $parameter $parts
	 * @return boolean
	 */
	private function checkParameterIsAssignment($string, &$parts) 
	{
		$parts = array();

		$pos = 0;

		preg_match_all("/(?:\'[^\']*[^\"]\'|\"[^\"]*[^\']*\"|\[.*\]|\(.*\)|)\K(=|=>|$)/", $string, $matches, PREG_OFFSET_CAPTURE);

		if( ! $matches || empty($matches[0][0][0]))
		{
			return false;
		}

		$value = substr($string, $matches[0][count($matches[0])-2][1]+1);

		foreach (range(0, count($matches[0])-2) as $i)
		{
			// Trim spaces here because spaces might be relevant in any other places, but not here
			preg_match("/(?<type>[\$#])?(?<name>.*)/", trim(substr($string, $pos, $matches[0][$i][1]-$pos)), $name);

			$pos = $matches[0][$i][1]+1;

			$parts[] = $this->setParameterType(
												array(
														'type' => $name['type'], 
														'name' => $name['name'], 
														'value' => $value
													)
												);

		}

		return true;
	}

	/**
	 * Check if the parameter is a single string
	 *
	 *   @d(this is a single string)
	 *
	 * @param $string
	 * @param $parts
	 * @internal param $string
	 * @internal param $parameter
	 * @return boolean
	 */
	private function checkParameterIsSingleString($string, &$parts)
	{
		$parts = array();
		preg_match('/^[\s\\/\.]?(\w+([\s\\/\.]+\w+))+$/', $string, $matches);

		// Is this a quoted string?
		if (preg_match('/^[\s\\/\.]?(\w+([\s\\/\.]+\w+))+$/', $string, $matches))
		{
			$value = $matches[0];
		}
		else

		// Does the string has one or more spaces on it?
		if (preg_match('/^(.*\s+.*)+$/', $string, $matches))
		{
			$value = $string;
		}
		else

		// Is this it just a number? 
		if (is_numeric($string))
		{
			$value = $string;
		}

		if (isset($value))
		{
			$parts['type'] = Constant::T_VARIABLE_SINGLE_STRING;

			$parts['name'] = null;

			$parts['value'] = $value;

			$parts = array($parts);

			$this->singleString = $value;
		}

		return count($parts) > 0;
	}

	/**
	 * Checks if the parameter is a is a one word HTML attribute, like "disabled":
	 *
	 *        <div disabled></div>
	 *
	 * @param $string
	 * @param $parts
	 * @internal param $parts
	 * @return bool
	 */
	private function checkParameterIsAnyOtherType($string, &$parts) 
	{
		preg_match('/(?:\s*)([$#\"\']?[a-zA-z0-9\-]+)?(=\>|=)*(.*)?/', $string, $matches);

		$parts = array(
					array(
							'type' => Constant::T_VARIABLE_HTML_ATTRIBUTE, 
							'name' => $matches[0]
						)
				);

		return true;
	}

	/**
	 * Set a parameter type.
	 *
	 * @param array $parameter
	 * @return array
	 */
	private function setParameterType($parameter) 
	{
		switch ($parameter['type']) 
		{
				// Now using positional parameters ($_1, $_2)
				// case '$':
				// 	$parameter['type'] = Constant::T_VARIABLE_GLOBAL_VARIABLE;
				// 	break;

				case '#':
					$parameter['type'] = Constant::T_VARIABLE_LOCAL_VARIABLE;

					break;
				
				default:
					$parameter['type'] = Constant::T_VARIABLE_HTML_ATTRIBUTE;

					$start = 0;

					break;
		}

		return $parameter;
	}

	/**
	 * Get positional parameter.
	 * 
	 * @param  integer $item 
	 * @return string
	 */
	public function getPositional($item) 
	{
		return $this->positionalParameters[$item];
	}

	/**
	 * Get an unquoted positional parameter.
	 * 
	 * @param  integer $item 
	 * @return string       
	 */
	public function getPositionalUnquoted($item) 
	{
		return $this->unquote($this->getPositional($item));
	}

	/**
	 * Retrieves the parameters string.
	 * @return string
	 */
	public function getParametersString() 
	{
		return $this->parametersString;
	}

	/**
	 * Retrieves the list of parameters.
	 * 	
	 * @return array
	 */
	public function getParameters() 
	{
		return $this->parameters;
	}

	/**
	 * Retrieves the single string.
	 * 	
	 * @return string
	 */
	public function getSingleString() 
	{
		return $this->singleString;
	}

	/**
	 * Get the string of HTML attributes.
	 *
	 * @param null $exclusions
	 * @return string
	 */
	public function getHtmlAttributesString($exclusions = null)
	{
		$exclusions = (array) $exclusions ?: array();

		$attributes = array();

		foreach ($this->getParameters() as $key => $values)
		{
			// Must be an HTML constant
			// Parameter name (@_title) must not be in the exclusions array
			// Parameter number (@_1) must not be in the exclusions array
			if($values['type'] == Constant::T_VARIABLE_HTML_ATTRIBUTE
				&& ! in_array($values['name'], $exclusions)
				&& ! in_array($key+1, $exclusions))
			{
				$attributes[$values['name']] = $this->getAttribute($values['name']);
			}
		}

		return implode(' ', $attributes);
	}

	/**
	 * Quote a string.
	 * 
	 * @param  string $string 
	 * @return string
	 */
	private function quote($string)
	{
		if ( ! preg_match('/^(["\']).*\1$/m', $string))
		{
			return sprintf('"%s"', $string);
		}
		else
		{
			return $string;
		}
	}

	/**
	 * Unquote a string
	 * 
	 * @param  string $string 
	 * @return string
	 */
	private function unquote($string)
	{
		preg_match('/^(["\'])(.*)\1$/m', $string, $matches);

		if ($matches)
		{
			return $matches[2];
		}

		return $string;
	}

	/**
	 * Get a single attribute string.
	 *
	 * @param string $name
	 * @param string $function
	 * @param string $type
	 * @return string
	 */
	public function getAttribute($name, $function = 'plain', $type = null)
	{
		$values = array();

		foreach ($this->parameters as $parameter)
		{
			if ( ! $type or $type == $parameter['type'])
			{
				if ($name == $parameter['name'])
				{
					// If hadn't a type before, now it has and we will enforce it
					$type = $parameter['type'];

					$values[] = isset($parameter['value']) ? $parameter['value'] : null;
				}
			}
		}

		return $this->makeValue($name, $values, $function, $type);
	}

	/**
	 * Take all user's requirements and make a value.
	 * 
	 * @param  string $name     
	 * @param  array $values   
	 * @param  string $function 
	 * @param  integer $type     
	 * @return string           
	 */
	private function makeValue($name, $values, $function, $type) 
	{
		$values = implode(' ', (array) $values);

		if ($function == 'bare')
		{
			return $values;
		}
		else
		if ($function == 'plain') 
		{
			if ($type == Constant::T_VARIABLE_HTML_ATTRIBUTE)
			{
				return $name . ($values ? '='.$this->quote($values) : '');
			}
			else
			if ($type == Constant::T_VARIABLE_LOCAL_VARIABLE)
			{
				return $values;
			}
		}
		else
		if ($function == 'has')
		{
			return ! empty($values) && count($values) ? 'true' : 'false';
		}

		return 'false';
	}

}
