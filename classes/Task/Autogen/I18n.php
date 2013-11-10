<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Scan PHP files and create i18n file
 */
class Task_Autogen_I18n extends Minion_Task {

	const SINGULAR = 1;
	const PLURAL   = 2;

	protected function _execute(array $params)
	{
		$token = Minion_CLI::read('Enter i18n file name (use only [a-zA-Z-_] symbols)');

		$validation = Validation::factory(array('token' => $token))
			->rules('token', array(
				array('not_empty'),
				array('min_length', array(':value', 2)),
				array('regex', array(':value', '=[a-zA-Z\-_]+$=')),
			))
			->label('token', 'File name');

		if ( ! $validation->check())
		{
			foreach ($validation->errors('task/i18n/create') as $error)
			{
				Minion_CLI::write($error, 'red');
			}

			return;
		}

		require_once Kohana::find_file('vendor', 'PHP-Parser/lib/bootstrap');

		$terms = Arr::merge($this->_scan_files(),
			$this->_scan_messages());

		$this->_save_file($token, $terms);
	}

	/**
	 * Scans classes and view directories and returns terms array
	 *
	 * @return array
	 */
	protected function _scan_files()
	{
		$terms = array();

		$parser = new PHPParser_Parser(new PHPParser_Lexer);

		foreach ($this->_list_files(array('views', 'classes')) as $file)
		{
			$statements = $parser->parse(file_get_contents($file));

			$terms = Arr::merge($terms, $this->_get_terms_from_statements($statements));
		}

		return $terms;
	}

	/**
	 * Returns terms array from PHPParser_Node[] array of statements
	 *
	 * @param  array
	 * @return array
	 */
	protected function _get_terms_from_statements($stmts)
	{
		$terms = array();

		if (is_array($stmts))
		{
			foreach ($stmts as $node)
			{
				$terms = Arr::merge($terms, $this->_get_terms_from_statements($node));
			}
		}
		elseif ($stmts instanceof PHPParser_Node)
		{
			if ($stmts instanceof PHPParser_Node_Expr_FuncCall
				AND sizeof($stmts->args) > 0)
			{
				$term = $stmts->args[0]
					->value;

				if ($stmts->name == '__'                             // This is i18n function
					AND $term instanceof PHPParser_Node_Scalar_String) // First of its parameters - a string
				{
					$args = sizeof($stmts->args);

					// If the function call has three parameters,
					// assume that the term is plural
					$terms[$term->value] = ($args == 3) ? self::PLURAL : self::SINGULAR;
				}
			}

			$subnode_names = $stmts->getSubNodeNames();

			foreach ($subnode_names as $subnode_name)
			{
				$terms = Arr::merge($terms, $this->_get_terms_from_statements($stmts->$subnode_name));
			}
		}

		return $terms;
	}

	/**
	 * Scans messages and returns terms array
	 *
	 * @return array
	 */
	protected function _scan_messages()
	{
		$exclude = 'messages'.DIRECTORY_SEPARATOR
			.'tests'.DIRECTORY_SEPARATOR;

		$terms = array();
		$files = array();

		// Exclude testscases
		foreach ($this->_list_files(array('messages')) as $file)
		{
			if (strstr($file, $exclude) === FALSE)
			{
				$files[] = $file;
			}
		}

		foreach ($files as $file)
		{
			$file_terms = require_once $file;

			if (is_array($file_terms))
			{
				$terms = Arr::merge($terms, $this->_deep_array_values($file_terms));
			}
		}

		$return = array();

		foreach ($terms as $term)
		{
			$return[$term] = self::SINGULAR;
		}

		return $return;
	}

	/**
	 * Recursive array_values function
	 *
	 * @param  array
	 * @return array
	 */
	protected function _deep_array_values(array $array)
	{
		$values = array();

		foreach ($array as $val)
		{
			if (is_array($val))
			{
				$values = Arr::merge($values, $this->_deep_array_values($val));
			}
			else
			{
				$values[] = $val;
			}
		}

		return $values;
	}

	/**
	 * Returns list of files including Kohana including paths
	 *
	 * @param  array subfolders list
	 * @return array
	 */
	protected function _list_files(array $subfolders)
	{
		$files = array();

		foreach (Kohana::include_paths() as $path)
		{
			foreach ($subfolders as $dir)
			{
				$pattern = $path.$dir.DIRECTORY_SEPARATOR.'/*'.EXT;

				$files = Arr::merge($files, $this->_list_files_recursively($pattern));
			}
		}

		return $files;
	}

	/**
	 * Scans a directory recursively by pattern
	 *
	 * @param  string pattern
	 * @return array
	 */
	protected function _list_files_recursively($pattern)
	{
		$files = glob($pattern);

		foreach (glob(dirname($pattern).DIRECTORY_SEPARATOR.'*', GLOB_ONLYDIR|GLOB_NOSORT) AS $dir)
		{
			$files = array_merge($files, $this->_list_files_recursively($dir.DIRECTORY_SEPARATOR.basename($pattern)));
		}

		return $files;
	}

	/**
	 * Save file
	 *
	 * @param type $token
	 * @param array $terms
	 */
	protected function _save_file($token, array $terms)
	{
		$filename = APPPATH.'i18n'.DIRECTORY_SEPARATOR.$token.EXT;

		$terms_to_save = array();
		$exist_terms   = array();

		if (is_file($filename))
		{
			$exist_terms = require_once $filename;
		}

		foreach (Arr::merge(array_keys($terms), array_keys($exist_terms)) as $term)
		{
			if (isset($exist_terms[$term]))
			{
				$terms_to_save[$term] = array
				(
					'translated' => TRUE,
					'translate'  => $exist_terms[$term]
				);
			}
			else
			{
				if ($terms[$term] === self::SINGULAR)
				{
					$terms_to_save[$term] = array
					(
						'translated' => FALSE,
						'translate'  => $term
					);
				}
				else
				{
					$terms_to_save[$term] = array
					(
						'translated' => FALSE,
						'translate'  => array
						(
							'one'  => $term,
							'few'  => $term,
							'many' => $term
						)
					);
				}
			}
		}

		ksort($terms_to_save);

		$contents = View::factory('minion/autogen/i18n')
			->bind('terms', $terms_to_save);

		try
		{
			Autogen::write($filename, $contents, TRUE);
		}
		catch (Exception $e)
		{
			return Minion_CLI::write('Error: '.$e->getMessage());
		}

		return Minion_CLI::write('Done!');
	}

} // End Task_Autogen_I18n_Create