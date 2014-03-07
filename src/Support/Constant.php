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
 
class Constant {

	/**
	 * Single string type.
	 */
	const T_VARIABLE_SINGLE_STRING	= 0; // "class=hidden"

	/**
	 * Global varialbe type.
	 */
	const T_VARIABLE_GLOBAL_VARIABLE = 1; // $var=hidden // temporarily deprecated

	/**
	 * Local variable type.
	 */
	const T_VARIABLE_LOCAL_VARIABLE  = 2; // #const=1

	/**
	 * HTML attribute type.
	 */
	const T_VARIABLE_HTML_ATTRIBUTE  = 3; // const=1

	/**
	 * Line commands start with @ and have no block ending
	 * 
	 * 		@h1(this is a line command)
	 * 		
	 */
	const T_COMMAND_TYPE_LINE = 1;

	/**
	 * Block commands start with @ and must end with @@
	 *
	 * 		@php
	 * 			$var = 'this is a block command';
	 * 		@@
	 */
	const T_COMMAND_TYPE_BLOCK_START = 2;

	/**
	 * The block ending marker: @@
	 * 
	 */
	const T_COMMAND_TYPE_BLOCK_END = 3;

	/** 
	 * Everything which is not a command
	 * 
	 */
	const T_COMMAND_TYPE_NONE = 4;

}
