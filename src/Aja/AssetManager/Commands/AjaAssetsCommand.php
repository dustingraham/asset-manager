<?php namespace Aja\AssetManager\Commands;

use Asset;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class AjaAssetsCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'aja:assets';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Compile production assets.';

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
		$this->info('Building...');
	    Asset::build($this->argument('asset'), $this->option('overwrite'));
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array(
			array('asset', InputArgument::OPTIONAL, 'Asset to build.'),
		);
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return array(
			array('overwrite', null, InputOption::VALUE_NONE, 'Force overwrite of output.'),
		);
	}

}