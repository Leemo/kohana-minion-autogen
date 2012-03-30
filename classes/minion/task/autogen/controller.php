<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Creates a new controller
 *
 * Requred config options:
 *
 * --name
 * --actions
 * --extends
 *
 */
class Minion_Task_Autogen_Controller extends Minion_Task {

	/**
	 * An array of config options that this task can accept
	 *
	 * @var array
	 */
	protected $_config = array
	(
		'name',
		'actions',
		'extends'
	);

	public function execute(array $config)
	{
		$config = Arr::map('strtolower', $config);

		$config['actions'] = explode(',', $config['actions']);
		$config['name']    = explode(',', $config['name']);

		$data = Arr::extract($config, array('actions', 'extends'));

		foreach ($config['name'] as $name)
		{
			$contents = View::factory('minion/task/autogen/controller', $data)
				->set('name', $name);

			try
			{
				$filename = APPPATH
					.'classes'.DIRECTORY_SEPARATOR
					.'controller'.DIRECTORY_SEPARATOR
					.str_replace('_', DIRECTORY_SEPARATOR, $name);

				Autogen::write($filename, $contents);
			}
			catch (Exception $e)
			{
				return Minion_CLI::write($e->getMessage(), 'red');
			}
		}

		return Minion_CLI::write('Controllers has been successfully created', 'green');
	}

} // End Minion_Task_Autogen_Form