<?php namespace PragmaRX\Steroids\Vendor\Laravel\Artisan;

use Steroids;
use File;
use Config;
use App;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class Report extends Base {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'steroids:list';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'List all Blade on Steroids commands available';

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
		$this->table = $this->getHelperSet()->get('table');

		$filter = $this->argument('filter');

		$list = array();

		foreach (Steroids::getCommands() as $folder => $commands) 
		{
			foreach ($commands as $instruction => $command)
			{
				if ($filter && strpos($folder.'#'.$instruction, $filter) == false)
				{
					continue;	
				}

				$list[] = array(
									'@',
									$folder == 'default' ? '' : $folder,
									$instruction,
									$this->makeVariablesString($command['variables']), 
								);
			}
		}

		$this->table->setHeaders(array('', 'Folder', 'Command', 'Parameters'))->setRows($list);

		$this->table->render($this->getOutput());		
	}

	private function makeVariablesString($variables) 
	{
		$v = array();

		foreach ($variables as $key => $variable)
		{
			$v[$variable['name']] = '@_'.$variable['name'];
		}

		ksort($v);

		return implode(', ', $v);
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array(
			array('filter', InputArgument::OPTIONAL, 'Filter a particular command or folder'),
		);
	}

}
