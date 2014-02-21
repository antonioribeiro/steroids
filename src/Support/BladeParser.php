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
 
class BladeParser {
	const T_LINE_COMMAND        = 1;
	const T_BLOCK_COMMAND       = 2;
	const T_ENDCOMMAND          = 2;
	const T_NON_COMMAND         = 3;

	private $tokens;

	private $keywords = array();

	public function setKeywords($keywords)
	{
		$this->keywords = $keywords;
	}

	public function addKeyword($keyword)
	{
		$this->keywords[] = $keyword;
	}

	public function scan($input)
	{
		static $regex;

		$this->tokens = array();

		if ( ! isset($regex)) {
			$regex = '/(' . implode(')|(', $this->getCatchablePatterns()) . ')|'
				   . implode('|', $this->getNonCatchablePatterns()) . '/i';
		}

		$flags = PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_OFFSET_CAPTURE;
		$matches = preg_split($regex, $input, -1, $flags);

		foreach ($matches as $match) {
			// Must remain before 'value' assignment since it can change content
			$type = $this->getType($match[0]);

			$this->tokens[] = array(
				'value' => $match[0],
				'type'  => $type,
				'position' => $match[1],
			);
		}

		$this->enumerateCommands();
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

	protected function getType($value)
	{
		$key = $this->extractKeyword($value);

		d($key);

		// if($value === '@@') {
		// 	return static::T_ENDCOMMAND;
		// }
		// else if($value[0] == '@' && array_key_exists($key, $this->keywords))
		// {
		// 	if($this->keywords[$key]['hasBody'])
		// 	{
		// 		return static::T_BLOCK_COMMAND;	
		// 	}
		// 	else
		// 	{
		// 		return static::T_LINE_COMMAND;	
		// 	}
		// }
		// else
		// {
		// 	return static::T_NON_COMMAND;
		// }

		return 1;
	}

	private function enumerateCommands()
	{
		// foreach($this->tokens as $key = $token)
		// {

		// }
	}

	private function extractKeyword($command)
	{
		$pattern = '/@(.*)((\(.*\))|)/';

		preg_match($pattern, $command, $matches, PREG_OFFSET_CAPTURE);
		
		dd($matches);
		if(count($matches) > 1) {
			return $matches[1];
		}
	}
}
