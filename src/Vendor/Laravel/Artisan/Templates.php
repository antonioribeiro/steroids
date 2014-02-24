<?php namespace PragmaRX\Steroids\Vendor\Laravel\Artisan;

use File;
use Config;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class Templates extends Base {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'steroids:templates';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Publish Steroids templates.';

	protected $arguments = array();

	protected $options = array();

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function fire() 
	{
		$assetPath = base_path().'/vendor/pragmarx/steroids/src/templates';

		d($assetPath);

		$templatePath = Config::get('pragmarx/steroids::template_dir');

		dd($temp latePath);

		$this->copyDir($assetPath, $templatePath);
	}

	protected function copyDir($from, $to)
	{
		$files = File::allFiles($from);

		$this->checkDirectory($to);

		foreach ($files as $file) {
			$original = (string) $file;
			$filename = $file->getRelativePathname();
			$this->checkDirectory("{$to}/{$file->getRelativePath()}");
			File::copy($file, "{$to}/{$filename}");
			$this->info("Copied {$filename}");
		}
	}

	protected function checkDirectory($dir)
	{
		if (!File::isDirectory($dir)) {
			File::makeDirectory($dir, 0777, true);
		}
	}
}
