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
 
class Lexer extends \Doctrine\Common\Lexer\AbstractLexer {
	// All tokens that are not valid identifiers must be < 100
	const T_NONE                = 1;
	const T_INTEGER             = 2;
	const T_STRING              = 3;
	const T_INPUT_PARAMETER     = 4;
	const T_FLOAT               = 5;
	const T_CLOSE_PARENTHESIS   = 6;
	const T_OPEN_PARENTHESIS    = 7;
	const T_COMMA               = 8;
	const T_DIVIDE              = 9;
	const T_DOT                 = 10;
	const T_EQUALS              = 11;
	const T_GREATER_THAN        = 12;
	const T_LOWER_THAN          = 13;
	const T_MINUS               = 14;
	const T_MULTIPLY            = 15;
	const T_NEGATE              = 16;
	const T_PLUS                = 17;
	const T_OPEN_CURLY_BRACE    = 18;
	const T_CLOSE_CURLY_BRACE   = 19;

	// All tokens that are also identifiers should be >= 100
	const T_IDENTIFIER          = 100;
	const T_ALL                 = 101;
	const T_AND                 = 102;
	const T_ANY                 = 103;
	const T_AS                  = 104;
	const T_ASC                 = 105;
	const T_AVG                 = 106;
	const T_BETWEEN             = 107;
	const T_BOTH                = 108;
	const T_BY                  = 109;
	const T_CASE                = 110;
	const T_COALESCE            = 111;
	const T_COUNT               = 112;
	const T_DELETE              = 113;
	const T_DESC                = 114;
	const T_DISTINCT            = 115;
	const T_EMPTY               = 116;
	const T_ESCAPE              = 117;
	const T_EXISTS              = 118;
	const T_FALSE               = 119;
	const T_FROM                = 120;
	const T_GROUP               = 121;
	const T_HAVING              = 122;
	const T_IN                  = 123;
	const T_INDEX               = 124;
	const T_INNER               = 125;
	const T_INSTANCE            = 126;
	const T_IS                  = 127;
	const T_JOIN                = 128;
	const T_LEADING             = 129;
	const T_LEFT                = 130;
	const T_LIKE                = 131;
	const T_MAX                 = 132;
	const T_MEMBER              = 133;
	const T_MIN                 = 134;
	const T_NOT                 = 135;
	const T_NULL                = 136;
	const T_NULLIF              = 137;
	const T_OF                  = 138;
	const T_OR                  = 139;
	const T_ORDER               = 140;
	const T_OUTER               = 141;
	const T_SELECT              = 142;
	const T_SET                 = 143;
	const T_SIZE                = 144;
	const T_SOME                = 145;
	const T_SUM                 = 146;
	const T_TRAILING            = 147;
	const T_TRUE                = 148;
	const T_UPDATE              = 149;
	const T_WHEN                = 150;
	const T_WHERE               = 151;
	const T_WITH                = 153;
	const T_PARTIAL             = 154;
	const T_MOD                 = 155;

	private $commands;

	/**
	 * Creates a new query scanner object.
	 *
	 * @param string $input a query string
	 */
	public function __construct($input = null)
	{
		if ($input)
		{
			$this->setInput($input);	
		}
	}

	/**
	 * @inheritdoc
	 */
	protected function getCatchablePatterns()
	{
		return array(
			'\@(('.implode('|', $this->commands).')?:(-.*)?)',
			".*",
		);
	}

	/**
	 * @inheritdoc
	 */
	protected function getNonCatchablePatterns()
	{
		return array('\s+', '(.)');
	}

	/**
	 * @inheritdoc
	 */
	protected function getType(&$value)
	{
		$type = self::T_NONE;

		// Recognizing numeric values
		if (is_numeric($value)) {
			return (strpos($value, '.') !== false || stripos($value, 'e') !== false) 
					? self::T_FLOAT : self::T_INTEGER;
		}

		// Differentiate between quoted names, identifiers, input parameters and symbols
		if ($value[0] === "'") {
			$value = str_replace("''", "'", substr($value, 1, strlen($value) - 2));
			return self::T_STRING;
		} else if (ctype_alpha($value[0]) || $value[0] === '_') {
			$name = 'Doctrine\ORM\Query\Lexer::T_' . strtoupper($value);

			if (defined($name)) {
				$type = constant($name);

				if ($type > 100) {
					return $type;
				}
			}

			return self::T_IDENTIFIER;
		} else if ($value[0] === '?' || $value[0] === ':') {
			return self::T_INPUT_PARAMETER;
		} else {
			switch ($value) {
				case '@@': return self::T_DOT;
				case ',': return self::T_COMMA;
				case '(': return self::T_OPEN_PARENTHESIS;
				case ')': return self::T_CLOSE_PARENTHESIS;
				case '=': return self::T_EQUALS;
				case '>': return self::T_GREATER_THAN;
				case '<': return self::T_LOWER_THAN;
				case '+': return self::T_PLUS;
				case '-': return self::T_MINUS;
				case '*': return self::T_MULTIPLY;
				case '/': return self::T_DIVIDE;
				case '!': return self::T_NEGATE;
				case '{': return self::T_OPEN_CURLY_BRACE;
				case '}': return self::T_CLOSE_CURLY_BRACE;
				default:
					// Do nothing
					break;
			}
		}

		return $type;
	}

	public function setCommands($commands)
	{
		$this->commands = $commands;
	}

}