<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Creates a new form
 */
class Task_Autogen_Form extends Minion_Task {

	/**
	 * An array of input params that this task can accept
	 *
	 * @var array
	 */
	protected $_params = array
	(
		'filename' => 'File path (from APPPATH/views/, for example: views/some/form)',
		'fields'   => 'An array of fields, separated by commas (for example: name:text,password:password)'
	);

	/**
	 * Supported field types
	 *
	 * @var array
	 */
	protected $_types = array
	(
		'checkbox',
		'file',
		'text',
		'password',
		'select',
		'textarea'
	);

	protected function _execute(array $params)
	{
		foreach ($this->_params as $param => $message)
		{
			$params[$param] = Minion_CLI::read($message);
		}

		$params['fields'] = explode(',', $params['fields']);

		$fields = array();

		foreach ($params['fields'] as $field)
		{
			list($name, $type) = explode(':', $field);

			if (in_array($type, $this->_types))
			{
				$fields[$name] = ($type == 'text') ? 'input' : $type;
			}
		}

		$contents = View::factory('minion/autogen/form')
			->bind('fields', $fields);

		try
		{
			$filename = APPPATH.'views'.DIRECTORY_SEPARATOR.$params['filename'];

			Autogen::write($filename, $contents);
		}
		catch (Exception $e)
		{
			return Minion_CLI::write($e->getMessage(), 'red');
		}

		return Minion_CLI::write('The form has been successfully created', 'green');
	}

} // End Minion_Task_Autogen_Form