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

use Mockery as m;

use PragmaRX\Steroids\Steroids;
use PragmaRX\Steroids\Support\KeywordList;
use PragmaRX\Steroids\Support\BladeParser;
use PragmaRX\Steroids\Support\BladeProcessor;

use PragmaRX\Support\Config;
use PragmaRX\Support\Filesystem;
use Illuminate\Config\Repository;
use Illuminate\Config\FileLoader;

class SteroidsTest extends PHPUnit_Framework_TestCase {

	/**
	 * Setup resources and dependencies.
	 *
	 * @return void
	 */
	public function setUp()
	{
		$this->namespace = 'PragmaRX\Steroids';

		$this->rootDir = __DIR__.'/../src/config';

		$this->fileSystem = new Filesystem;

		$this->fileLoader = new FileLoader($this->fileSystem, __DIR__);

		$this->repository = new Repository($this->fileLoader, 'test');

        $this->repository->package($this->namespace, $this->rootDir, $this->namespace);

		$this->config = new Config($this->repository, $this->namespace);

		$this->keywordList = new keywordList($this->config, $this->fileSystem);

		$this->bladeParser = new BladeParser();

		$this->bladeProcessor = new BladeProcessor();

		$this->steroids = new Steroids(
										$this->config, 
										$this->fileSystem, 
										$this->keywordList,
										$this->bladeParser,
										$this->bladeProcessor
									);
	}

	public function testString0001() 
	{
		$this->assertEquals(
								'<h1 >Hello, Laravel!</h1>',
								$this->steroids->inject('@h(1,"Hello, Laravel!")')
								
							);
	}

	public function testString0002() 
	{
		$this->assertEquals(
								'<p >Hello, Laravel!</p>',
								$this->steroids->inject("@p('Hello, Laravel!')")
							);
	}

	public function testString0003() 
	{
		$this->assertEquals(
								"<div class=\"row\">\n\t\n</div>",
								$this->steroids->inject("@row @@")
							);
	}

	public function testString0004() 
	{
		$this->assertEquals(
								"<?php \n\t\n  \$options = array(\n  \t\t\t\t\t'url' => 'coming/soon', \n  \t\t\t\t\t'method' => ('POST' ?: 'POST'), \n  \t\t\t\t\t'class' => 'form-inline',\n  \t\t\t\t\t'role' => true ? 'form' : 'default'\n  \t\t\t\t);\n?>\n\n{{ Form::open(\$options) }}\n    \n{{ Form::close() }}",
								$this->steroids->inject("@form(#url=coming/soon,#method=POST,class=form-inline,#role=form)@@")
							);
	}

	public function testString0005()
	{
		$this->assertEquals(
								"<h1 >Hello Laravel!</h1>",
								$this->steroids->inject("@h(1,Hello Laravel!)")
							);
	}

	public function testString0006()
	{
		$this->assertEquals(
								"<section class=\"col col-1\">\n\t\n</section>",
								$this->steroids->inject("@sec(1)@@")
							);

		$this->assertEquals(
								"<section class=\"col col-10000\">\n\t\n</section>",
								$this->steroids->inject("@sec(10000)@@")
							);

		$this->assertEquals(
								"<section class=\"col col-a\">\n\t\n</section>",
								$this->steroids->inject("@sec('a')@@")
							);

		$this->assertEquals(
								"<section class=\"col col-aaaa\">\n\t\n</section>",
								$this->steroids->inject("@sec('aaaa')@@")
							);

		$this->assertEquals(
								"<section class=\"col col-aaaa\">\n\tHello, Laravel!\n</section>",
								$this->steroids->inject("@sec('aaaa')Hello, Laravel!@@")
							);
	}
}

function AsciiToInt($char){
	$success = "";

    if (strlen($char) == 1)
        return "char(".ord($char).")";
    else{
        for($i = 0; $i < strlen($char); $i++){
        	if (ord($char[$i]) < 33) {
	            if ($i == strlen($char) - 1)
	                $success = $success.ord($char[$i]);
	            else
	                $success = $success.ord($char[$i]).",";
	        }
	        else
	        {
	        	$success = $success.$char[$i];
	        }
        }
        return "char(".$success.")";
    }
}
