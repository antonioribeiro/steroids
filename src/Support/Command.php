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
 
class Command {

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

			$this->parameters = new ParameterParser($matches['parameters']);

			$this->setLineBody($matches['body']);
		}
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
			foreach($this->parameters->getParameters() as $parameter)
			{
				if ($parameter['type'] == Constant::T_VARIABLE_HTML_ATTRIBUTE)
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
				if ($parameter['type'] == Constant::T_VARIABLE_LOCAL_VARIABLE)
				{
					$this->addLocal($parameter['name'], $parameter['value']);
				}
				else
				if ($parameter['type'] == Constant::T_VARIABLE_SINGLE_STRING)
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
	 * Get a single attribute string.
	 * 
	 * @param  string $name     
	 * @param  string $function 
	 * @return string
	 */
	public function getAttribute($name, $function = 'plain')
	{
		if ($name == 'SINGLE' || $name == 'VALUE')
		{
			return $this->parameters->getSingleString();
		}

		if ($name == 'ATTRIBUTES')
		{
			return $this->parameters->getHtmlAttributesString($this->getExclusions());
		}

		if ($name == 'PARAMETERS')
		{
			return $this->parameters->getParametersString();
		}

		if ($name == 'BODY')
		{
			return $this->getBody();
		}

		if (is_numeric($name))
		{
			return $this->parameters->getPositionalUnquoted($name-1);
		}

		return $this->parameters->getAttribute($name, $function);
	}

	/**
	 * Get a list of items that should be excluded from HTML attribute strings.
	 *
	 * @return array
	 */
	private function getExclusions()
	{
		return array_column($this->instruction['variables'], 'name');
	}
}