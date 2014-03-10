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

	public function equals($s1, $s2)
	{
		$this->assertEquals(AsciiToInt($s1), AsciiToInt($s2));

		$this->assertEquals($s1, $s2); /// just to certify we don't have a test bug
	}

	public function testPatternH1WithTwoNonAssignmentStrings() 
	{
		$this->equals(
						'<h1 >Hello, Laravel!</h1>',
						$this->steroids->inject('@h(1,"Hello, Laravel!")')
					);
	}

	public function testPatternH1WithTwoNonAssignmentStringsNoQuotes()
	{
		$this->equals(
						"<h1 >Hello Laravel!</h1>",
						$this->steroids->inject("@h(1,Hello Laravel!)")
					);
	}

	public function testPatternPWithPositionalString()
	{
		$this->equals(
						'<p >Hello, Laravel!</p>',
						$this->steroids->inject("@p('Hello, Laravel!')")
					);
	}

	public function testPatternPWithPositionalStringNoQuotes() 
	{
		$this->equals(
						'<p >Hello Laravel!</p>',
						$this->steroids->inject("@p(Hello Laravel!)")
					);

	}

	public function testPatternEmptyRowBlock() 
	{
		$this->equals(
						"<div class=\"row\">\n\t\n</div>",
						$this->steroids->inject("@row @@")
					);
	}

	public function testPatternSecBlockWithNoBody()
	{
		$this->equals(
						"<section class=\"col col-1\">\n\t\n</section>",
						$this->steroids->inject("@sec(1)@@")
					);
	}

	public function testPatternSecBlockWithNoBodyAndBigNumber()
	{
		$this->equals(
						"<section class=\"col col-88888888\">\n\t\n</section>",
						$this->steroids->inject("@sec(88888888)@@")
					);
	}

	public function testPatternSecBlockWithNoBodyAndQuotedValue()
	{
		$this->equals(
						"<section class=\"col col-a\">\n\t\n</section>",
						$this->steroids->inject("@sec('a')@@")
					);
	}	

	public function testPatternSecBlockWithNoBodyAndBiggerQuotedValue()
	{
		$this->equals(
						"<section class=\"col col-aaaaaaaaaaaaaaaa\">\n\t\n</section>",
						$this->steroids->inject("@sec('aaaaaaaaaaaaaaaa')@@")
					);
	}

	public function testPatternSecBlockWithBody()
	{
		$this->equals(
						"<section class=\"col col-aaaa\">\n\tHello, Laravel!\n</section>",
						$this->steroids->inject("@sec('aaaa')Hello, Laravel!@@")
					);
	}

    /**
     * @expectedException PragmaRX\Steroids\Exceptions\SyntaxError
     */	
	public function testRaisesSyntaxError()
	{
		$this->equals(
						"<section class=\"col col-1\">\n\t\n</section>",
						$this->steroids->inject("@sec(1)")
					);

	}

	public function testPatternSecBlockWithTwoLinesBody()
	{
		$this->equals(
						"<section class=\"col col-aaaa\">\n\tHello, Laravel!\nHello, Laravel, Again!\n</section>",
						$this->steroids->inject("@sec('aaaa')Hello, Laravel!\nHello, Laravel, Again!\n@@")
					);
	}

	public function testPatternFormSeverelAssignmentsAndNoBody()
	{
		$this->equals(
						"<?php \n\t\n  \$options = array(\n  \t\t\t\t\t'url' => 'coming/soon', \n  \t\t\t\t\t'method' => ('POST' ?: 'POST'), \n  \t\t\t\t\t'class' => 'form-inline',\n  \t\t\t\t\t'role' => true ? 'form' : 'default'\n  \t\t\t\t);\n?>\n\n{{ Form::open(\$options) }}\n    \n{{ Form::close() }}",
						$this->steroids->inject("@form(#url=coming/soon,#method=POST,class=form-inline,#role=form)@@")
					);
	}

	public function testBlockInBlock()
	{
		$this->equals(
						"<section class=\"col col-aaaa\">\n\tHello, Laravel!\nHello, Laravel, Again!\n<?php \n\t\necho 'Hello, Laravel!';\n?>\n</section>",
						$this->steroids->inject("@sec('aaaa')Hello, Laravel!\nHello, Laravel, Again!\n@php\necho 'Hello, Laravel!'\n@@\n@@")
					);
	}

	public function testNonExsistentCommand()
	{
		$this->equals(
						"@thisisanonexistentcommand",
						$this->steroids->inject("@thisisanonexistentcommand")
					);
	}

	public function testCheckIsLoadingFromTheCorrectDir()
	{
		$this->assertContains(
								'<!--BS-->', 
								$this->steroids->inject("@bs.input(name=loading)")
		);

		$this->assertContains(
								'<!--DEFAULT-->', 
								$this->steroids->inject("@input(name=loading)")
		);
	}

	public function testSingleAssignment()
	{
		$this->equals(
						'<p class="laravel">whatever string</p>',
						$this->steroids->inject("@p(whatever string,class=laravel)")
					);
	}

	public function testMultipleAssignments()
	{
		$this->equals(
						"<div title=\"Hi there!\" placeholder=\"Hi there!\">\n\tHello, Laravel!\n</div>",
						$this->steroids->inject("@d(#label=title=placeholder=Hi there!)Hello, Laravel!@@")
					);
	}

	public function testHtmlAttributeWithNoValue()
	{
		$this->equals(
						"<div disabled enabled>\n\tHello, Laravel!\n</div>",
						$this->steroids->inject("@d(disabled,enabled)Hello, Laravel!@@")
					);
	}

	public function testHtmlAttributeWithDashedName()
	{
		$this->equals(
						"<div label-data=\"x\">\n\tHello, Laravel!\n</div>",
						$this->steroids->inject("@d(label-data=\"x\")Hello, Laravel!@@")
					);
	}

	public function testSameAttributePassedTwiceShouldBeOnlyOneAttribute()
	{
		$this->equals(
						"<div class=\"a b\">\n\tHello, Laravel!\n</div>",
						$this->steroids->inject("@d(class=a,class=b)Hello, Laravel!@@")
					);
	}

	public function testPatternCssWithSingleString()
	{
		$this->equals(
						"<link rel=\"stylesheet\" type=\"text/css\" media=\"screen\" href=\"/assets/css/bootstrap.min.css\">",
						$this->steroids->inject("@css(/assets/css/bootstrap.min.css)")
					);
	}

    /**
     * @expectedException PragmaRX\Steroids\Exceptions\SyntaxError
     */	
	public function testRaisesSyntaxErrorOn()
	{
		$this->steroids->inject("@row @@ @@");
	}

    /**
     * @expectedException PragmaRX\Steroids\Exceptions\TemplatesDirectoryNotAvailable
     */	
	public function testRaisesErrorOnNonExistentTemplatesDir()
	{
		$this->steroids->setTemplatesDir('/ihopethisisanonexistentpathinyourroot');
	}

	public function testLoadingAWrongFile()
	{
		$this->steroids->setTemplatesDir(__DIR__.'/templates');

		$this->steroids->inject("@notvalidfilename");

		$this->equals(
						"@notvalidfilename",
						$this->steroids->inject("@notvalidfilename")
					);
	}

    /**
     * @expectedException PragmaRX\Steroids\Exceptions\SyntaxError
     */	
	public function testExceptionWithCompiler()
	{
		$this->equals(
						"@notvalidfilename",
						$this->steroids->inject("@row @@ @@", new CompilerForTest)
					);
	}

	public function testGetConfig()
	{
		$this->equals(
						"/default",
						$this->steroids->getConfig("default_template_dir")
					);
	}

	public function testGetCommands()
	{
		/// Must return 2 directories bs and default:
		$this->assertEquals(
						2,
						count($this->steroids->getCommands())
					);
	}

	public function testGetACorrectVariableValue()
	{
		$this->steroids->setTemplatesDir(__DIR__.'/templates');

		$this->equals(
						"label=Name - title=Name",
						$this->steroids->inject("@variable(#label=title=Name)")
					);
	}

	public function testLabelIsPrintedCorrectly()
	{
		$this->steroids->setTemplatesDir(__DIR__.'/templates');

		$this->assertContains(
								'<!--label1--><label class="label">Name</label><!--/label1-->', 
								$this->steroids->inject("@input(text,#label=title=Name)")
		);

		$this->assertContains(
								'<!--label1--><label class="label">Name</label><!--/label1-->', 
								$this->steroids->inject("@text(text,#label=title=Name)")
		);
	}

	public function testTemplatesAreCorrectlyLoaded()
	{
		$this->steroids->setTemplatesDir(__DIR__.'/templates');

		$keywords = $this->steroids->getCommands();

		// 4 valid templates in /templates
		$this->assertEquals(4, count($keywords['default']));

		// 4 properties in input
		$this->assertEquals(4, count($keywords['default']['input']));

		// input keyword must be a string
		$this->assertTrue(is_string($keywords['default']['input']['keyword']));
	}

	public function testPatternTextWithResendingParameters()
	{
		$this->equals(
						"<!--DEFAULT-->\n@if (true) \n\t<label class=\"label\">Nome</label>\n@endif\n<input type=\"text\" class=\"form-input\" />",
						$this->steroids->inject("@text(#label=Nome,#name=first_name,class=form-input)")
					);

		$this->equals(
						"<!--DEFAULT-->\n@if (true) \n\t<label class=\"label\">Logradouro</label>\n@endif\n<input type=\"text\" title=\"Logradouro\" placeholder=\"Logradouro (Rua, Av., Travessa...)\" class=\"form-control\" name=\"address_street\" id=\"address_street\" />",
						$this->steroids->inject('@text(#label=title=Logradouro,placeholder="Logradouro (Rua, Av., Travessa...)",class=form-control,#icon=user,name=id=address_street)')
					);
	}

	public function testVariableHasAndValueAreCorrectlySetGet()
	{
		$this->equals(
			"<button {{true ? 'type=\"submit\"' : ''}} class=\"btn btn-{{ true ? 'danger' : 'primary' }}\">Danger</button>",
			$this->steroids->inject('@submit(Danger, #color=danger)')
		);

		$this->equals(
			"<button {{true ? 'type=\"submit\"' : ''}} class=\"btn btn-{{ false ? '' : 'primary' }}\">Danger</button>",
			$this->steroids->inject('@submit(Danger)')
		);
	}

}

// ------------- helpers

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
	                $success = $success.ord($char[$i]);
	        }
	        else
	        {
	        	$success = $success.$char[$i];
	        }
        }
        return "char(".$success.")";
    }
}
