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

	/**
	 * Single string type.
	 */
	const T_SINGLE_STRING	= 0; // "class=hidden"

	/**
	 * Global varialbe type.
	 */
	const T_GLOBAL_VARIABLE = 1; // $var=hidden // temporarily deprecated

	/**
	 * Local variable type.
	 */
	const T_LOCAL_VARIABLE  = 2; // #const=1

	/**
	 * HTML attribute type.
	 */
	const T_HTML_ATTRIBUTE  = 3; // const=1

	/**
	 * Command marker (@ or @@).
	 * 
	 * @var string
	 */
	private $marker;

	/**
	 * Command line.
	 * 
	 * @var string
	 */
	private $line;

	/**
	 * Instruction name. In the example @input, 'input' is the instruction.
	 * 	
	 * @var string
	 */
	private $instruction;

	/**
	 * Template string.
	 * 
	 * @var string
	 */
	private $template;

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
	 * Command body.
	 *
	 * 	@input
	 * 	  This is the body
	 * 	@@
	 * @var [type]
	 */
	private $body;

	/**
	 * Command body, the one sent in the same line of the command
	 *
	 * 	 @php $x = 'this is a body in the same line' @
	 * 
	 * @var string
	 */
	private $lineBody;

	/**
	 * Command type
	 * 
	 * @var integer
	 */
	private $type;

	/**
	 * Starting position of the command in the view
	 * 
	 * @var integer
	 */
	private $start;

	/**
	 * Ending position of the command in the view
	 * 
	 * @var integer
	 */
	private $end;

	/**
	 * Command block number. All blocks are numerated.
	 * 
	 * @var integer
	 */
	private $number;

	/**
	 * HTMl attributes passed as parameters.
	 * 
	 * @var array
	 */
	private $attributes = array();

	/**
	 * Local variables passed as parameters.
	 * 		
	 * @var array
	 */
	private $locals = array();

	/**
	 * List of all positional parameters.
	 * 
	 * @var array
	 */
	private $positionalParameters = array();

	/**
	 * Single string parameter.
	 *
	 * 		@h1(this is the single string passed as one single parameter)
	 * 		
	 * @var string
	 */
	private $singleString = '';

	/**
	 * Class constructor.
	 * 
	 * @param string $command
	 */
	public function __construct($command) 
	{
		$this->parse($command);
	}

	/**
	 * Analyses a string to find a command on it and also parse all it's parameters.
	 * 
	 * @param  string $command
	 * @return void
	 */
	private function parse($command)
	{
		$this->clear();

		$this->parseCommandLine($command);

		$this->setAllAttributesTypes();
	}

	/**
	 * Parse a command line string.
	 * 
	 * @param  string $command 
	 * @return void
	 */
	private function parseCommandLine($command) 
	{
		preg_match('/(?<line>(?<marker>@{1,2})(?<name>[\w\.]*)(\(?(?<parameters>\w*[^(].*[^)]*)?\)\s*)?([.\s]*)?(?<body>.*))/', $command, $matches);

		if (count($matches) > 1) 
		{
			$this->line = $matches['line'];

			$this->marker = $matches['marker'];

			list($this->instruction, $this->template) = $this->parseInstruction($matches['name']);

			$this->parameters = $this->parseParameters($matches['parameters']);

			$this->setLineBody($matches['body']);
		}
	}

	/**
	 * Retrieves the command line.
	 * 
	 * @return string
	 */
	public function getLine() 
	{
		return $this->line;
	}

	/**
	 * Retrieves the command marker.
	 * 	
	 * @return string
	 */
	public function getMarker() 
	{
		return $this->marker;
	}

	/**
	 * Retrieves the command instruction.
	 * 	
	 * @return string
	 */
	public function getInstruction() 
	{
		return $this->instruction;
	}

	/**
	 * Retrieves the whole instruction.
	 * 	
	 * @return string
	 */
	public function getFullInstruction() 
	{
		return $this->getTemplate() . '.' . $this->instruction;
	}

	/**
	 * Retrieves the template.
	 * 	
	 * @return string
	 */
	public function getTemplate() 
	{
		return $this->template;
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
	 * Retrieves the parameters string.
	 * @return string
	 */
	public function getParametersString() 
	{
		return $this->parametersString;
	}

	/**
	 * Retrieves the command body.
	 * 	
	 * @return string
	 */
	public function getBody() 
	{
		return $this->lineBody . $this->body;
	}	

	/**
	 * Sets the command body.
	 * 
	 * @param string $body
	 * @return  string
	 */
	public function setBody($body)
	{
		return $this->body = $body;
	}

	/**
	 * Sets the command 'in the same line' body
	 * 
	 * @param string $body
	 * @return string
	 */
	public function setLineBody($body) 
	{
		return $this->lineBody = $body;
	}

	/**
	 * Set the command instruction.
	 * 
	 * @param string $instruction
	 */
	public function setInstruction($instruction) 
	{
		$this->instruction = $instruction;
	}

	/**
	 * Parse command parameters.
	 * 
	 * @param  string $string 
	 * @return array
	 */
	private function parseParameters($string) 
	{
		$this->parametersString = $string;

		$parameters = $this->splitParameters($string);

		$return = array();

		foreach ($parameters as $key => $value) {
			$this->positionalParameters[] = $value;

			try {
				foreach($this->parseParameter($value) as $parameter)
				{
					$return[] = $parameter;
				}
			} catch (\Exception $e) {
				dd($value)	;
			}
		}

		return $return;
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

		foreach(range(0,count($matches[1])-1) as $i)
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
		if ($this->checkParameterIsAssignment($string, $parts) 
			|| $this->checkParameterIsSingleString($string, $parts)
			|| $this->checkParameterIsAnyOtherType($string, $parts))
		{
			return $parts;
		}

		return array();
	}

	/**
	 * Check if the parameter is one or multiple assignments
	 *
	 * 		@d(#label=name=Name)
	 * 		
	 * @param  string
	 * @param  $parameter
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

		foreach(range(0, count($matches[0])-2) as $i)
		{
			preg_match("/(?<type>[(\$#])?(?<name>.*)/", substr($string, $pos, $matches[0][$i][1]-$pos), $name);

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
	 * 		@d(this is a single string)
	 * 		
	 * @param  string
	 * @param  $parameter
	 * @return boolean
	 */
	private function checkParameterIsSingleString($string, &$parts)
	{
		$parts = array();

		// Is this a quoted string?
		if (preg_match('/^(?:\s*)(([\'\"])(.*)[\'\"])(?:\s*)$/', $string, $matches))
		{
			$value = $this->parseValue($matches[3]);
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
			$parts['type'] = self::T_SINGLE_STRING;

			$parts['name'] = null;

			$parts['value'] = $value;

			$parts = array($parts);
		}

		return count($parts) > 0;
	}

	/**
	 * Checks if the parameter is a is a one word HTML attribute, like "disabled":
	 * 
	 * 		<div disabled></div>
	 * 		
	 * @param  [type] $string [description]
	 * @param  [type] $parts  [description]
	 * @return [type]         [description]
	 */
	private function checkParameterIsAnyOtherType($string, &$parts) 
	{
		preg_match('/(?:\s*)([$#\"\']?[a-zA-z0-9\-]+)?(=\>|=)*(.*)?/', $string, $matches);

		$parts = array();

		if ($matches)
		{
			$parts = array(
						array(
								'type' => self::T_HTML_ATTRIBUTE, 
								'name' => $this->parseValue($matches[0])
							)
					);

			return true;
		}
	}

	/**
	 * Set all parameters types.
	 * 
	 * @param array $parameters
	 */
	private function setParametersTypes($parameters) 
	{
		foreach($parameters as $key => $parameter)
		{
			$parameters[$key] = $this->setParameterType($parameter);
		}
	}

	/**
	 * Set a parameter type.
	 * 
	 * @param array $parameter
	 */
	private function setParameterType($parameter) 
	{
		switch ($parameter['type']) 
		{
				// Now using positional parameters ($_1, $_2)
				// case '$':
				// 	$parameter['type'] = self::T_GLOBAL_VARIABLE;
				// 	break;

				case '#':
					$parameter['type'] = self::T_LOCAL_VARIABLE;

					break;
				
				default:
					$parameter['type'] = self::T_HTML_ATTRIBUTE;

					$start = 0;

					break;
		}

		return $parameter;
	}

	/**
	 * Parse the value of the parameter. Basically if the parameter is an array
	 * it will transform it in a real array, otherwise will return the string.
	 * 
	 * @param  string $value 
	 * @return mixed
	 */
	private function parseValue($value) 
	{
		if (is_array($value = $this->parseArray($value)))
		{
			return $value;
		}

		return $value;
	}

	/**
	 * Check if the parameter is an array and return the array or a string.
	 * 
	 * @param  string $value
	 * @return mixed
	 */
	private function parseArray($value) 
	{
		preg_match('/^(?:array\(|\[)(.*)(?:\)|\])$/', $value, $matches);

		if ($matches)
		{
			return $this->parseArrayItems($matches[1]);
		}

		return $value;
	}

	/**
	 * Parse all array items.
	 * 
	 * @param  string $arrayItems
	 * @return array
	 */
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

	/**
	 * Parse one array item.
	 * 
	 * @param  string $item 
	 * @return array
	 */
	private function parseArrayItem($item) 
	{
		preg_match('/([$#]?\w+)?(=\>|=)?(.*)?/', $item, $matches);

		$key   = $matches[2] == "=>" ? $matches[1] : null;
		$value = $matches[2] == "=>" ? $matches[3] : $matches[1];

		return array($key, $this->parseArray($value));
	}

	/**
	 * Get the next array key by its number.
	 * 
	 * @param  array $array  
	 * @param  integer $number 
	 * @return integer
	 */
	private function nextArrayKey($array, &$number)
	{
		while(isset($array[$number]))
		{
			$number++;
		}

		return $number;
	}

	/**
	 * Parse the instruction name to separate the name from the folder:
	 * 
	 * 		bootstrap.input
	 * 		
	 * @param  [type] $string [description]
	 * @return [type]         [description]
	 */
	private function parseInstruction($string) 
	{
		$parts = explode('.', $string);

		$instruction = array_pop($parts);

		$template = count($parts) ? implode('.', $parts) : 'default';

		return array($instruction, $template);
	}

	/**
	 * Retrieves the type of command.
	 * 
	 * @return string
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * Retrieves the starting position of the command.
	 * 
	 * @return integer
	 */
	public function getStart()
	{
		return $this->start;
	}

	/**
	 * Retrieves the ending position of the command.
	 * 
	 * @return integer
	 */
	public function getEnd()
	{
		return $this->end;
	}

	/**
	 * Retrieves the length of the command.
	 * 
	 * @return integer
	 */
	public function getLength()
	{
		return $this->end - $this->start;
	}

	/**
	 * Retrieves the numberm of a command, but only block commands are numbered.
	 * 
	 * @return integer
	 */
	public function getNumber()
	{
		return empty($this->number) && $this->number !== (int) 0 ? null : $this->number;
	}

	/**
	 * Sets the command type.
	 * 
	 * @return void
	 */
	public function setType($type)
	{
		$this->type = $type;
	}

	/**
	 * Sets the command starting position
	 * 
	 * @param integer $start 
	 */
	public function setStart($start)
	{
		$this->start = $start;
	}

	/**
	 * Sets the command ending position.
	 * 
	 * @param integer $end 
	 */
	public function setEnd($end)
	{
		$this->end = $end;
	}

	/**
	 * Sets the command number.
	 * 
	 * @param integer $number 
	 */
	public function setNumber($number)
	{
		$this->number = $number;
	}

	/**
	 * Retrieves the command attributes.
	 * 	
	 * @return array
	 */
	public function getAttributes() 
	{
		return $this->attributes;
	}

	/**
	 * Clear commands properties to prepare for a new parse.
	 * 
	 * @return string
	 */
	private function clear()
	{
		$this->setBody(null);

		$this->setLineBody(null);

		$this->attributes = array();

		$this->locals = array();

		$this->singleString = '';
	}

	/**
	 * Guess and set the type of all attributes.
	 *
	 * @return  void
	 */
	public function setAllAttributesTypes()
	{
		if ($this->getLine())
		{
			foreach($this->getParameters() as $parameter)
			{
				if ($parameter['type'] == static::T_HTML_ATTRIBUTE)
				{
					if (isset($parameter['value']))
					{
						$this->addAtribute($parameter['name'], $parameter['value']);		
					}
					else
					{
						$this->addAtribute($parameter['name']);
					}
				}
				else
				if ($parameter['type'] == static::T_LOCAL_VARIABLE)
				{
					$this->addLocal($parameter['name'], $parameter['value']);
				}
				else
				if ($parameter['type'] == static::T_SINGLE_STRING)
				{
					$this->singleString = $parameter['value'];
				}
			}
		}
	}

	/**
	 * Add an attribute to the list of attributes.
	 * 
	 * @param string $variable 
	 * @param mixed $value 
	 */
	private function addAtribute($variable, $value = null) 
	{
		$key = $value ?: $variable;

		$this->attributes[$variable][$key] = $value;
	}

	/**
	 * Add a local variable to the list of variables.
	 * 
	 * @param string $variable 
	 * @param mixed $value 
	 */
	private function addLocal($variable, $value) 
	{
		$this->locals[$variable] = $value;
	}

	/**
	 * Retrieve the list of local variables.
	 * 
	 * @return array
	 */
	public function getLocals() 
	{
		return $this->locals;
	}

	/**
	 * Get a string with all attributes.
	 * 	
	 * @return string
	 */
	private function getAttributesStrings() 
	{
		$attributes = array();

		foreach($this->getAttributes() as $key => $values)
		{
			$attributes[$key] = implode(' ', $values);
		}

		return $attributes;
	}

	/**
	 * Get the string of HTML attributes.
	 * 
	 * @return [type] [description]
	 */
	public function getHtmlAttributesString() 
	{
		$attributes = array();

		foreach($this->getAttributes() as $key => $values)
		{
			$attributes[] = $this->getAttribute($key);
		}

		return implode(' ', $attributes);
	}

	/**
	 * Get a single attribute string.
	 * 
	 * @param  string $name     
	 * @param  string $function 
	 * @return string
	 */
	public function getAttribute($name, $function = 'plain')
	{
		$attributes = $this->getAttributesStrings();

		if ($name == 'SINGLE')
		{
			return $this->singleString;
		}

		if ($name == 'ATTRIBUTES')
		{
			return $this->getHtmlAttributesString();
		}

		if ($name == 'PARAMETERS')
		{
			return $this->getParametersString();
		}

		if ($name == 'BODY')
		{
			return $this->getBody();
		}

		if (is_numeric($name))
		{
			return $this->unquote($this->positionalParameters[$name-1]);
		}

		if (isset($attributes[$name]))
		{
			if ($function == 'has')
			{
				return 'true';
			}
			else
			if ($function == 'bare')
			{
				return $attributes[$name];
			}
			else
			{
				// If the attribute exists but has no name, send just the name
				// For the cases where we need to create valueless attributes: <div id="name" disabled>
				if (empty($attributes[$name]))
				{
					return $name;
				}
				else
				{
					return $name.'='.$this->quote($attributes[$name]);	
				}
				
			}
		}

		if (isset($this->locals[$name]))
		{
			if ($function == 'has')
			{
				return 'true';
			}
			else
			{
				return $this->locals[$name];
			}
		}

		if ($function == 'has')
		{
			return 'false';
		}
	}

	/**
	 * Check if an attribute is available.
	 * 
	 * @param  string  $name 
	 * @return boolean      
	 */
	public function hasAttribute($name)
	{
		$attributes = $this->getAttributesStrings();

		return isset($attributes[$name]);		
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

}
