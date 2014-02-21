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
 
abstract class Lexer {
 
    const EOF       = -1; // represent end of file char
    const EOF_TYPE  = 1;  // represent EOF token type
    protected $input;     // input string
    protected $p = 0;     // index into input of current character
    protected $c;         // current character
 
    public function Lexer($input) {
        $this->input = $input;
        // prime lookahead
        $this->c = substr($input, $this->p, 1);
    }
 
    /** Move one character; detect "end of file" */
    public function consume() {
        $this->p++;
        if ($this->p >= strlen($this->input)) {
            $this->c = Lexer::EOF;
        }
        else {
            $this->c = substr($this->input, $this->p, 1);
        }
    }
 
    public abstract function nextToken();
    public abstract function getTokenName($tokenType);
}