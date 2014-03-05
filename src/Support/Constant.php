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

}
