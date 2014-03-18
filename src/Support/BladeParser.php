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
	 * All end commands are stored here to speed up the process.
	 *
	 * @var
	 */
	private $commandsTypes = array();

	/**
	 * Set the keyword list
	 * 
	 * @param KeywordList $keywordList
	 */
	public function setKeywords(KeywordList $keywordList)
	{
		$this->keywordList = $keywordList;
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

		return $this;
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

		foreach ($matches as $key => $match)
		{
			$command = new Command($match[0]);

			$type = $this->getCommandAndType($match[0], $command);

			$command->type = $type;

			$command->start = $match[1];

			$command->end = isset($matches[$key+1][1]) ? $matches[$key+1][1] : strlen($this->input);

			$command->number = NULL;

			$this->commands[] = $command;

			$this->commandsTypes[$key] = $command->type;
		}
	}

	/**
	 * Guess the command type by kind of marker and store the instruction in the command object
	 * 
	 * @param  string  $value   
	 * @param  Command $command 
	 * @return integer Command type
	 */
	protected function getCommandAndType($value, Command $command)
	{
		$key = $command->template . '.' . $command->instruction;

		if ($command->marker === '@@')
		{
			return Constant::T_COMMAND_TYPE_BLOCK_END;
		}
		else if ($command->marker == '@' && $keyword = $this->keywordList->get($key))
		{
			$command->instruction = $keyword;

			$this->commandCount++;

			if ($keyword['hasBody'])
			{
				return Constant::T_COMMAND_TYPE_BLOCK_START;	
			}
			else
			{
				return Constant::T_COMMAND_TYPE_LINE;	
			}
		}

		return Constant::T_COMMAND_TYPE_NONE;
	}

	/**
	 * Enumerate all commands
	 * 
	 * @return void 
	 */
	private function numberAll()
	{
		$number = 0;

		// Enumerate Line commands
		foreach($this->commands as $key => $command)
		{
			if ($command->type == Constant::T_COMMAND_TYPE_LINE)
			{
				$command->number = $number;

				$number++;
			}
		}

		// Enumerate block commands
		while($end = $this->getFirstUnumeratedEndCommand())
		{
			$start = $this->getPriorUnumeratedBlockCommand($end);

			$this->commands[$start]->number = $number;

			$this->commands[$end]->number = $number;

			$this->commands[$start]->body = $this->getBody($start, $end);

			$this->commands[$start]->end = $this->commands[$end]->start + strlen($this->commands[$end]->line);

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

			$s = $start->start + strlen($start->line);

			$l = $end->start-1-$s;

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
		foreach($this->commandsTypes as $key => $type)
		{
			if ($type == Constant::T_COMMAND_TYPE_BLOCK_END)
			{
				$this->commandsTypes[$key] = 'processed';

				return $key;
			}
		}
	}

	/**
	 * Locate the first non numbered start of block (@). The difference between
	 *  a block and a non block command is the $_BODY inside that command.
	 *
	 * @param  integer starting line
	 * @throws \PragmaRX\Steroids\Exceptions\SyntaxError
	 * @return integer
	 */
	private function getPriorUnumeratedBlockCommand($line)
	{
		while($line >= 0)
		{
			if ($this->commandsTypes[$line] == Constant::T_COMMAND_TYPE_BLOCK_START)
			{
				$this->commandsTypes[$line] = 'processed';

				return $line;
			}

			$line--;
		}

		throw new SyntaxError("Found a block end (@@) but not a start. Check if your command has a @_BODY.", 1);
	}

	/**
	 * Check if the blocks are well formed and throws an exceptions if it's not
	 *
	 * @throws \PragmaRX\Steroids\Exceptions\SyntaxError
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
			if ($command->type == Constant::T_COMMAND_TYPE_BLOCK_START && is_null($command->getNumber()))
			{
				throw new SyntaxError("One or more code blocks are not closed (@@).", 1);
			}
		}
	}

	/**
	 * Get the number of commands found by the parser.
	 * 
	 * @return integer 
	 */
	public function getCommandCount()
	{
		return $this->commandCount;
	}

	/**
	 * Retrieves a command by its number.
	 *
	 * @param  integer $number
	 * @return Command
	 */
	public function getCommandByNumber($number)
	{
		foreach($this->commands as $key => $command)
		{
			if($command->getNumber() == $number)
			{
				if ($command->type == Constant::T_COMMAND_TYPE_BLOCK_START || $command->type == Constant::T_COMMAND_TYPE_LINE)
				{
					break;
				}
			}
		}

		return $command;
	}

	/**
	 * Retrieves the whole command text (view) by its number.
	 * 
	 * @param  integer $number 
	 * @return string         
	 */
	public function getCommandTextByNumber($number)
	{
		$command = $this->getCommandByNumber($number);

		return substr($this->input, $command->start, $command->getLength());
	}

}