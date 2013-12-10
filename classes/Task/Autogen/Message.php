<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Creates messages automatically from models
 */
class Task_Autogen_Message extends Minion_Task {

	protected function _execute(array $params)
	{
		// Define system messages
		$default_messages = require_once SYSPATH.'messages'
			.DIRECTORY_SEPARATOR.'validation'.EXT;

		$path        = APPPATH.'classes'.DIRECTORY_SEPARATOR;
		$model_files = $this->_get_array_keys_recursively(Kohana::list_files('Model', array($path)));

		$models      = $this->_get_models_from_files($model_files);

		foreach ($models as $model)
		{
			$rules = $this->_process_model($model, array_keys($default_messages));

			$this->_process_message_file($model, $rules);
		}

		Minion_CLI::write('Message update complete', 'green');
	}

	/**
	 * Recursively array keys
	 *
	 * @param  array
	 * @param  array
	 * @return array
	 */
	protected function _get_array_keys_recursively(array $array, array $result = array())
	{
		foreach ($array as $key => $val)
		{
			if (is_array($val))
			{
				$result = Arr::merge($result, $this->_get_array_keys_recursively($val, $result));
			}
			else if (is_file($val))
			{
				$result[] = $key;
			}
		}

		return $result;
	}

	/**
	 * Returns array of available non-abstract models
	 *
	 * @param  array files
	 * @return array
	 */
	protected function _get_models_from_files(array $files)
	{
		$models = array();

		foreach ($files as $file)
		{
			$model = str_replace(array(DIRECTORY_SEPARATOR, EXT), array('_', NULL), $file);

			$reflection = new ReflectionClass($model);

			if ( ! $reflection->isAbstract())
			{
				$models[] = $model;
			}
		}

		return $models;
	}

	/**
	 * Gets model rules array
	 *
	 * @param  string model name
	 * @param  array  default messages (see SYSPATH/messages/validation.php)
	 * @return array
	 */
	protected function _process_model($model_name, array $default_messages = array())
	{
		$model = new $model_name;
		$rules = $model->rules();

		$model_rules = array();

		foreach ($rules as $field => $rules_array)
		{
			$field_rules = array();

			foreach ($rules_array as $rule)
			{
				// if it's an object, get name of his method
				$field_rule = (is_array($rule[0])) ? $rule[0][1] : $rule[0];

				if ( ! in_array($field_rule, $default_messages))
				{
					$field_rules[] = $field_rule;
				}
			}

			if (sizeof($field_rules) > 0)
			{
				$model_rules[$field] = $field_rules;
			}
		}

		return $model_rules;
	}

	/**
	 * Creates or updates a message file
	 *
	 * @param  string  $model Model name
	 * @param  array   $rules Model rules
	 * @return void
	 */
	protected function _process_message_file($model, array $rules)
	{
		$messages_dir = APPPATH.'messages'.DIRECTORY_SEPARATOR;
		$message_file = UTF8::strtolower(str_replace('Model_', NULL, $model));

		$file = $messages_dir.$message_file.EXT;

		$exist_messages = array();

		if (is_file($file))
		{
			$exist_messages = require_once $file;
		}

		$messages = array();

		foreach ($rules as $field => $field_rules)
		{
			if ( ! isset($messages[$field]))
			{
				$messages[$field] = array();
			}

			foreach ($field_rules as $rule)
			{
				if (isset($exist_messages[$field][$rule]))
				{
					$messages[$field][$rule] = array
					(
						'text'    => $exist_messages[$field][$rule],
						'comment' => FALSE
					);
				}
				else
				{
					$messages[$field][$rule] = array
					(
						'text'    => NULL,
						'comment' => TRUE
					);
				}
			}
		}

		if (sizeof($messages) > 0)
		{
			$contents = View::factory('minion/autogen/message')
				->bind('messages', $messages);

			Autogen::write($file, $this->_beautify($contents), TRUE);

			Minion_CLI::write('Message file '.$message_file.' has been processed', 'green');
		}
	}

	/**
	 * Removes trailing commas and linebreaks
	 *
	 * @param  string contents to save
	 * @return string
	 */
	protected function _beautify($contents)
	{
		$r   = "\r";
		$nl  = "\n";
		$tab = "\t";

		$search = array
		(
			$r,
			','.$nl.$tab.'),',
			'),'.$nl.$nl.');'
		);

		$replace = array
		(
			NULL,
			$nl.$tab.'),',
			')'.$nl.');'
		);

		return str_replace($search, $replace, $contents);
	}

} // End Minion_Task_Autogen_Message