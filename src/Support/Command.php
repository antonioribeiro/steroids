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
	 * Public with no getter to prioritize performance over design.
	 *
	 * @var string
	 */
	public $marker;

	/**
	 * Command line.
	 * 
	 * @var string
	 */
	public $line;

	/**
	 * Instruction name. In the example @input, 'input' is the instruction.
	 *
	 * Public with no getter to prioritize performance over design.
	 * 	
	 * @var string
	 */
	public $instruction;

	/**
	 * Template string.
	 *
	 * Public with no getter to prioritize performance over design.
	 *
	 * @var string
	 */
	public $template;

	/** 
	 * Command body.
	 *
	 * Public with no getter to prioritize performance over design.
	 *
	 * 	@input
	 * 	  This is the body
	 * 	@@
	 * @var [type]
	 */
	public $body;

	/**
	 * Command body, the one sent in the same line of the command
	 *
	 * Public with no getter to prioritize performance over design.
	 *
	 * 	 @php $x = 'this is a body in the same line' @
	 * 
	 * @var string
	 */
	public $lineBody;

	/**
	 * Command type
	 *
	 * Public with no getter to prioritize performance over design.
	 *
	 * @var integer
	 */
	public $type;

	/**
	 * Starting position of the command in the view
	 *
	 * Public with no getter to prioritize performance over design.
	 *
	 * @var integer
	 */
	public $start;

	/**
	 * Ending position of the command in the view
	 *
	 * Public with no getter to prioritize performance over design.
	 *
	 * @var integer
	 */
	public $end;

	/**
	 * Command block number. All blocks are numerated.
	 *
	 * Public with no getter to prioritize performance over design.
	 *
	 * @var integer
	 */
	public $number;

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
		if ( empty(trim($command)) || strpos($command, '@') === false )
		{
			$this->line = $command;

			$this->parameters = new ParameterParser;
		}
		else
		{
			preg_match('/(?<line>(?<marker>@{1,2})(?<name>[\w\.]*)(\(?(?<parameters>\w*[^(].*[^)]*)?\)\s*)?([.\s]*)?(?<body>.*))/', $command, $matches);

			if (count($matches) > 1)
			{
				$this->line = $matches['line'];

				$this->marker = $matches['marker'];

				list($this->instruction, $this->template) = $this->parseInstruction($matches['name']);

				$this->parameters = new ParameterParser($matches['parameters']);

				$this->lineBody = $matches['body'];
			}
		}
	}

	/**
	 * Parse the instruction name to separate the name from the folder:
	 *
	 *    bootstrap.input
	 *
	 * @param  string $string
	 * @return array
	 */
	private function parseInstruction($string) 
	{
		$parts = explode('.', $string);

		$instruction = array_pop($parts);

		$template = count($parts) ? implode('.', $parts) : 'default';

		return array($instruction, $template);
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
	 * Clear commands properties to prepare for a new parse.
	 * 
	 * @return string
	 */
	private function clear()
	{
		$this->body = null;

		$this->lineBody = null;

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
		if ($this->line)
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
		return array_pluck($this->instruction['variables'], 'name');
	}
}