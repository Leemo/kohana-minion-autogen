<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Scan PHP files and create i18n file
 */
class Task_Autogen_I18n extends Minion_Task {

	const SINGULAR = 1;
	const PLURAL   = 2;

	protected function _execute(array $params)
	{
		$name = Minion_CLI::read('Enter i18n file name (use only [a-zA-Z-_] symbols)');

		$validation = Validation::factory(array('name' => $name))
			->rules('name', array(
				array('not_empty'),
				array('min_length', array(':value', 2)),
				array('regex', array(':value', '=[a-zA-Z\-_]+$=')),
			))
			->label('name', 'File name');

		if ( ! $validation->check())
		{
			foreach ($validation->errors('task/i18n/create') as $error)
			{
				Minion_CLI::write($error, 'red');
			}

			return;
		}

		require Kohana::find_file('vendor', 'PHP-Parser/lib/bootstrap');

		// Find all PHP files
		$files = $this->_scan_files();

		$terms = array();

		foreach ($files as $file)
		{
			foreach ($this->_parse_terms($file) as $term => $type)
			{
				if ( ! isset($terms[$term]))
				{
					$terms[$term] = $type;
				}
			}
		}

		try
		{
			$message = $this->_write($name, $terms);
		}
		catch (Exception $e)
		{
			return Minion_CLI::write($e->getMessage(), 'red');
		}

		return Minion_CLI::write($message, 'green');
	}

	protected function _write($name, array $terms)
	{
		$file = APPPATH.'i18n'.DIRECTORY_SEPARATOR.$name.EXT;

		$created = TRUE;

		$i18n = array();

		if (is_file($file))
		{
			$created = FALSE;
			$tmp     = Kohana::load($file);

			foreach ($tmp as $term => $translate)
			{
				$i18n[$term] = array
				(
					'translated' => TRUE,
					'translate'  => $translate
				);
			}
		}

		foreach ($terms as $term => $type)
		{
			if ( ! isset($i18n[$term]))
			{
				$i18n[$term] = array
				(
					'translated' => FALSE,
					'translate'  => $type
				);
			}
		}

		ksort($i18n);

		$contents = View::factory('minion/autogen/i18n')
			->bind('i18n', $i18n);

		file_put_contents($file, $contents);

		return 'New i18n file '.$file.' successfully '.($created ? 'created' : 'updated');
	}

	/**
	 * Find all PHP files
	 *
	 * @return array
	 */
	protected function _scan_files()
	{
		$files = array();

		$scan = array
		(
			'classes',
			'views'
		);

		foreach (Kohana::include_paths() as $path)
		{
			foreach ($scan as $dir)
			{
				$pattern = $path.$dir.DIRECTORY_SEPARATOR.'/*'.EXT;

				$files = Arr::merge($files, $this->_recursive_scan($pattern));
			}
		}

		return $files;
	}

	/**
	 * Recursively scans specified directory and returns PHP files array
	 *
	 * @param   string $pattern
	 * @return  array
	 */
	protected function _recursive_scan($pattern)
	{
		$files = glob($pattern);

		foreach (glob(dirname($pattern).DIRECTORY_SEPARATOR.'*', GLOB_ONLYDIR|GLOB_NOSORT) AS $dir)
		{
			$files = array_merge($files, $this->_recursive_scan($dir.DIRECTORY_SEPARATOR.basename($pattern)));
		}

		return $files;
	}

	protected function _parse_terms($file)
	{
		$terms = array();

		$source = file_get_contents($file);
		$source = str_replace(array("\r", "\n"), '', $source);

		preg_match_all('/__\(.*?\)/u', $source, $result);

		$parser = new PHPParser_Parser(new PHPParser_Lexer);

		foreach ($result[0] as $string)
		{
			$code = '<?php '.$string.'; ?>';

			try
			{
				if ($result = $this->_parse_statements($parser->parse($code)))
				{
					list($key, $type) = $result;

					if ( ! isset($terms[$key]) AND ! empty($key))
					{
						$terms[$key] = $type;
					}
				}
			}
			catch (PHPParser_Error $e)
			{
				// Ignore
			}
		}

		return $terms;
	}

	protected function _parse_statements(array $statements)
	{
		$statements = $statements[0];

		if ( ! $statements instanceof PHPParser_Node_Expr_FuncCall
			OR $statements->name->parts[0] != '__'
			OR ! isset($statements->args[0])
			OR $statements->args[0]->value instanceof PHPParser_Node_Expr_Variable)
		{
			return FALSE;
		}

		$args_count = sizeof($statements->args);

		if ($args_count == 1)
		{
			// It's singular term
			return array
			(
				$statements->args[0]->value->value,
				Task_Autogen_I18n::SINGULAR
			);
		}
		else
		{
			return array
			(
				$statements->args[0]->value->value,
				($statements->args[($args_count - 1)]->value instanceof PHPParser_Node_Expr_Variable) ? Task_Autogen_I18n::PLURAL : Task_Autogen_I18n::SINGULAR
			);
		}
	}

} // End Task_Autogen_I18n_Create