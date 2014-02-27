<?php namespace PragmaRX\Steroids\Vendor\Laravel\Artisan;

use File;
use Config;
use App;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class Clear extends Base {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'view:clear';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Delete all views from cache';

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
		$count = 0;

		foreach(File::files(App::make('path.storage').'/views') as $file) 
		{
			File::delete($file);
			$count++;
		}

		if ($count)
		{
			$this->info(sprintf('Deleted %s view%s', $count, $count == 1 ? '' : 's'));
		}
		else
		{
			$this->info('No views found.');
		}
	}

}
