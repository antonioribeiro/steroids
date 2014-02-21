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

class ListLexer extends Lexer {
    const NAME      = 2;
    const COMMA     = 3;
    const LBRACK    = 4;
    const RBRACK    = 5;
    static $tokenNames = array("n/a", "<EOF>",
                               "NAME", "COMMA",
                               "LBRACK", "RBRACK" );
 
    public function getTokenName($x) {
        return ListLexer::$tokenNames[$x];
    }
 
    public function ListLexer($input) {
        parent::__construct($input);
    }
 
    public function isLETTER() {
        return $this->c >= 'a' &&
               $this->c <= 'z' ||
               $this->c >= 'A' &&
               $this->c <= 'Z';
    }
 
    public function nextToken() {
        while ( $this->c != self::EOF ) {
            switch ( $this->c ) {
                case ' ' :  case '\t': case '\n': case '\r': $this->WS();
                           continue;
                case ',' : $this->consume();
                           return new Token(self::COMMA, ",");
                case '[' : $this->consume();
                           return new Token(self::LBRACK, "[");
                case ']' : $this->consume();
                           return new Token(self::RBRACK, "]");
                default:
                    if ($this->isLETTER() ) return $this->NAME();
                    // throw new \Exception("invalid character: " . $this->c);
            }
        }
        return new Token(self::EOF_TYPE,"<EOF>");
    }
 
    /** NAME : ('a'..'z'|'A'..'Z')+; // NAME is sequence of >=1 letter */
    public function NAME() {
        $buf = '';
        do {
            $buf .= $this->c;
            $this->consume();
        } while ($this->isLETTER());
 
        return new Token(self::NAME, $buf);
    }
 
    /** WS : (' '|'\t'|'\n'|'\r')* ; // ignore any whitespace */
    public function WS() {
        while(ctype_space($this->c)) {
            $this->consume();
        }
    }
}