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

use PragmaRX\Steroids\Exceptions\SyntaxError;
use PragmaRX\Steroids\Support\Command;

use Exception;

class BladeParser {

	/**
	 * Line commands start with @ and have no block ending
	 * 
	 * 		@h1(this is a line command)
	 * 		
	 */
	const T_LINE_COMMAND        = 1;

	/**
	 * Block commands start with @ and must end with @@
	 *
	 * 		@php
	 * 			$var = 'this is a block command';
	 * 		@@
	 */
	const T_BLOCK_COMMAND       = 2;

	/**
	 * The block ending marker: @@
	 * 
	 */
	const T_END_COMMAND         = 3;

	/** 
	 * Everything which is not a command
	 * 
	 */
	const T_NON_COMMAND         = 4;

	/**
	 * All commands
	 * 
	 * @var array
	 */
	private $commands = array();

	/**
	 * The list of keywords
	 * 
	 * @var array
	 */
	private $keywords = array();

	/**
	 * The view to be processed
	 * 	
	 * @var string
	 */
	private $input;

	/**
	 * Set the keyword list
	 * 
	 * @param array $keywords
	 */
	public function setKeywords($keywords)
	{
		$this->keywords = $keywords;
	}

	/**
	 * Parse the view
	 * 
	 * @param  string $input 
	 * @return void        
	 */
	public function parse($input)
	{
		$this->input = $input;

		$this->scan();

		$this->numberAll();

		$this->syntaxCheck();
	}

	/**
	 * Scans the view and break all commands and non-commands in lines
	 * 	
	 * @return void
	 */
	private function scan() 
	{
		$this->commands = array();

		$this->commandCount = 0;

		$matches = preg_split(
								'/(@@)|(\R)|(!####n####!)|(!####r####!)|(?=@)/',
								$this->input, 
								-1, 
								PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_OFFSET_CAPTURE
							);

		foreach ($matches as $key => $match) {
			$command = new Command($match[0]);

			$type = $this->getCommandAndType($match[0], $command);

			$command->setType($type);

			$command->setStart($match[1]);

			$command->setEnd(isset($matches[$key+1][1]) ? $matches[$key+1][1] : strlen($this->input));

			$command->setNumber(NULL);

			$this->commands[] = $command;
		}
	}

	/**
	 * Guess the command type by kind of marker and store the instruction in the command object
	 * 
	 * @param  string  $value   
	 * @param  Command $command 
	 * @return integer Command type
	 */
	protected function getCommandAndType($value, Command &$command)
	{
		$key = $command->getFullInstruction();

		$marker = $command->getMarker();

		if ($marker === '@@') 
		{
			return static::T_END_COMMAND;
		}
		else if ($marker == '@' && $keyword = array_get($this->keywords, $key))
		{
			$command->setInstruction($keyword);

			$this->commandCount++;

			if ($keyword['hasBody'])
			{
				return static::T_BLOCK_COMMAND;	
			}
			else
			{
				return static::T_LINE_COMMAND;	
			}
		}

		return static::T_NON_COMMAND;
	}

	/**
	 * Enumerate all block commands by searching all @ to the equivalent @@
	 * 
	 * @return void 
	 */
	private function numberAll()
	{
		$number = 0;

		while($end = $this->getFirstUnumeratedEndCommand())
		{
			$start = $this->getPriorUnumeratedBlockCommand($end);

			$this->commands[$start]->setNumber($number);

			$this->commands[$end]->setNumber($number);

			$this->commands[$start]->setBody($this->getBody($start, $end));

			$this->commands[$start]->setEnd($this->commands[$end]->getStart() + strlen($this->commands[$end]->getLine()));

			$number++;
		}
	}

	/**
	 * Get the command body, which is the text between @ and @@
	 * 
	 * @param  integer $start 
	 * @param  integer $end   
	 * @return string        
	 */
	private function getBody($start, $end)
	{
		if ($end > $start+1)
		{
			$start = $this->commands[$start];

			$end = $this->commands[$end];

			$s = $start->getStart() + strlen($start->getLine());

			$l = $end->getStart()-1-$s;

			return substr($this->input, $s, $l);
		}
	}

	/**
	 * Locate the first non numbered end (@@) of command block
	 * 
	 * @return integer
	 */
	private function getFirstUnumeratedEndCommand()
	{
		foreach($this->commands as $key => $command)
		{
			if ($command->getType() == static::T_END_COMMAND && is_null($command->getNumber()))
			{
				return $key;
			}
		}
	}

	/** 
	 * Locate the first non numbered start of block (@). The difference between
	 *  a block and a non block command is the $_BODY inside that command.
	 * 
	 * @param  integer starting line
	 * @return integer
	 */
	private function getPriorUnumeratedBlockCommand($line)
	{
		while($line >= 0)
		{
			if ($this->commands[$line]->getType() == static::T_BLOCK_COMMAND && is_null($this->commands[$line]->getNumber()))
			{
				return $line;
			}

			$line--;
		}

		throw new SyntaxError("Found a block end (@@) but not a start. Check if your command has a @_BODY.", 1);
	}

	/**
	 * Check if the blocks are well formed and throws an exceptions if it's not
	 * 
	 * @return void
	 */
	private function syntaxCheck() 
	{
		foreach($this->commands as $key => $command)
		{
			/**
			 * All block commands should be numbered at this point, if they aren't
			 * code has a syntax error.
			 */
			if ($command->getType() == static::T_BLOCK_COMMAND && is_null($command->getNumber()))
			{
				throw new SyntaxError("One or more code blocks are not closed (@@).", 1);
			}
		}
	}

	/**
	 * Parse and return true if commands were found
	 * 
	 * @param  string  $view 
	 * @return boolean       
	 */
	public function hasCommands($view) 
	{
		$this->parse($view);

		return $this->commandCount > 0;
	}
 
 	/**
 	 * Locate and return the fist Steroids command
 	 * 
 	 * @return Command
 	 */
	public function getFirstCommand() 
	{
		foreach($this->commands as $key => $command)
		{
			if ($command->getType() == static::T_BLOCK_COMMAND || $command->getType() == static::T_LINE_COMMAND)
			{
				break;
			}
		}

		return $command;
	}

}