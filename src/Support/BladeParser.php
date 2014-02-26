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

use Exception;

class BladeParser {
	const T_LINE_COMMAND        = 1;
	const T_BLOCK_COMMAND       = 2;
	const T_END_COMMAND         = 3;
	const T_NON_COMMAND         = 4;

	private $commands;

	private $keywords = array();

	public function setKeywords($keywords)
	{
		$this->keywords = $keywords;
	}

	public function addKeyword($keyword)
	{
		$this->keywords[] = $keyword;
	}

	public function parse($input)
	{
		$this->scan($input);

		$this->enumerateCommands();
	}

	private function scan($input) 
	{
		static $regex;

		$this->commands = array();

		$this->commandCount = 0;

		if ( ! isset($regex)) {
			$regex = '/(' . implode(')|(', $this->getCatchablePatterns()) . ')|'
				   . implode('|', $this->getNonCatchablePatterns()) . '/i';
		}

		$flags = PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_OFFSET_CAPTURE;
		$matches = preg_split($regex, $input, -1, $flags);

		foreach ($matches as $key => $match) {
			// Must remain before 'value' assignment since it can change content
			$type = $this->getCommandAndType($match[0], $command);

			$command->setString($match[0]);
			$command->setType($type);
			$command->setStart($match[1]);
			$command->setEnd(isset($matches[$key+1][1]) ? $matches[$key+1][1] : strlen($input));
			$command->setNumber(NULL);

			$this->commands[] = $command;
		}
	}

	protected function getCatchablePatterns()
	{
		return array(
			".*",
		);
	}

	protected function getNonCatchablePatterns()
	{
		return array('\s+', '(.)');
	}

	protected function getCommandAndType($value, &$command)
	{
		$command = new Command($value);

		$key = $command->getFullInstruction();

		$marker = $command->getMarker();

		if ($marker === '@@') {
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
		else
		{
			return static::T_NON_COMMAND;
		}

		return 1;
	}

	private function enumerateCommands()
	{
		$number = 0;
		while($end = $this->getFirstUnumeratedEndCommand())
		{
			$start = $this->getPriorUnumeratedBlockCommand($end);

			$this->commands[$start]->setNumber($number);
			$this->commands[$end]->setNumber($number);

			$number++;
		}

		$this->syntaxCheck();
	}

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

		throw new SyntaxError("Could not find a code block start.", 1);
	}

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

	public function hasCommands($view) 
	{
		$this->parse($view);

		return $this->commandCount > 0;
	}
 
	public function getFirstCommand() 
	{
		foreach($this->commands as $key => $command)
		{
			if ($command->getType() == static::T_BLOCK_COMMAND || $command->getType() == static::T_LINE_COMMAND)
			{
				return $command;
			}
		}
	}

}
