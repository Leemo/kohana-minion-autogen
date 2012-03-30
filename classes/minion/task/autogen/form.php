<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Creates a new form
 *
 * Requred config options:
 *
 * --filename
 * --fields
 *
 */
class Minion_Task_Autogen_Form extends Minion_Task {

	/**
	 * An array of config options that this task can accept
	 *
	 * @var array
	 */
	protected $_config = array
	(
		'filename',
		'fields'
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

	public function execute(array $config)
	{
		$config['fields'] = explode(',', $config['fields']);

		$fields = array();

		foreach ($config['fields'] as $field)
		{
			list($name, $type) = explode(':', $field);

			if (in_array($type, $this->_types))
			{
				$fields[$name] = ($type == 'text') ? 'input' : $type;
			}
		}

		$contents = View::factory('minion/task/autogen/form')
			->bind('fields', $fields);

		try
		{
			$filename = APPPATH.'views'.DIRECTORY_SEPARATOR.$config['filename'];

			Autogen::write($filename, $contents);
		}
		catch (Exception $e)
		{
			return Minion_CLI::write($e->getMessage(), 'red');
		}

		return Minion_CLI::write('The form has been successfully created', 'green');
	}

} // End Minion_Task_Autogen_Form