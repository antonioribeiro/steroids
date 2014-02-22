<?php namespace PragmaRX\Steroids\Vendor\Laravel\Artisan;

use Illuminate\Console\Command;

class Base extends Command {

	public function displayMessages($type, $messages)
	{
		foreach($messages as $message)
		{
			$this->$type($message);
		}
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return isset($this->arguments) ? $this->arguments : array();
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return isset($this->options) ? $this->options : array();
	}
}
