<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Creates a new controller
 */
class Task_Autogen_Controller extends Minion_Task {

	/**
	 * An array of input params that this task can accept
	 *
	 * @var array
	 */
	protected $_params = array
	(
		'name'    => 'Controller name (or more, separated by commas)',
		'actions' => 'List the actions, separated by commas',
		'extends' => 'Parent class name (optional)'
	);

	protected function _execute(array $params)
	{
		foreach ($this->_params as $param => $message)
		{
			$params[$param] = Minion_CLI::read($message);
		}

		$params = Arr::map('strtolower', $params);

		$params['actions'] = explode(',', $params['actions']);
		$params['name']    = explode(',', $params['name']);

		$data = Arr::extract($params, array('actions', 'extends'));

		foreach ($params['name'] as $name)
		{
			$contents = View::factory('minion/autogen/controller', $data)
				->set('name', $name);

			$name = str_replace('_', ' ', $name);
			$name = UTF8::ucwords($name);
			$name = str_replace(' ', DIRECTORY_SEPARATOR, $name);

			try
			{
				$filename = APPPATH
					.'classes'.DIRECTORY_SEPARATOR
					.'Controller'.DIRECTORY_SEPARATOR
					.$name.EXT;

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