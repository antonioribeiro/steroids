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

use PragmaRX\Support\Config;
use PragmaRX\Support\Filesystem;
use PragmaRX\Steroids\Support\VariableParser;

use Exception;

class BladeProcessor {

	private $variables;
	
	public function process($view, $command)
	{
		$template = $this->getTemplate($view, $command);

		return substr_replace($view, $template, $command->getStart(), $command->getLength());
	}

	private function getTemplate($view, $command)
	{
		$template = $command->getInstruction()['template'];

		$variableParser = new VariableParser($template);

		while($variableParser->parse($template))
		{
			$variable = $variableParser->first();

			$template = $this->replace(
										$variable['start'], 
										strlen($variable['text']), 
										$command->getAttribute($variable['name'], $variable['function']),
										$template
									);
		}

		return $template;
	}

	private function replace($start, $size, $string, $subject)
	{
		return substr_replace($subject, $string, $start, $size);
	}

}
