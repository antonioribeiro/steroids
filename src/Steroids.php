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

namespace PragmaRX\Steroids;

use PragmaRX\Support\Config;
use PragmaRX\Support\Filesystem;

class Steroids
{
	private $config;

    private $fileSystem;

	/**
	 * Initialize Steroids object
	 * 
	 * @param Locale $locale
	 */
	public function __construct(Config $config, Filesystem $fileSystem)
	{
		$this->config = $config;

        $this->fileSystem = $fileSystem;
	}

	public function show()
	{
		$listLexer = new \PragmaRX\Steroids\Support\ListLexer('[a,b,c,d,e]');

		$token = $listLexer->nextToken();
		 
		while($token->type != 1) {
		    echo $token . "\n";
		    $token = $listLexer->nextToken();
		}		

		dd($listLexer);

		return "show!";
	}

}
