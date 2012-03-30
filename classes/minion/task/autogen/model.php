<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Creates a new model from table
 *
 * Requred config options:
 *
 * --name
 *
 */
class Minion_Task_Autogen_Model extends Minion_Task {

	/**
	 * An array of config options that this task can accept
	 *
	 * @var array
	 */
	protected $_config = array
	(
		'name'
	);

	/**
	 * Default rules
	 *
	 * @var array
	 */
	protected $_rules = array
	(
		'alpha'         => 'array(\'alpha\', array(\':value\', TRUE))',
		'alpha_dash'    => 'array(\'alpha_dash\', array(\':value\', TRUE))',
		'alpha_numeric' => 'array(\'alpha_numeric\', array(\':value\', TRUE))',
		'color'         => 'array(\'color\')',
		'credit_card'   => 'array(\'credit_card\', array(\':value\', NULL /* type */))',
		'date'          => 'array(\'date\')',
		'decimal'       => 'array(\'decimal\', array(\':value\', 2 /* places */, NULL /* digits */))',
		'digit'         => 'array(\'digit\', array(\':value\', TRUE))',
		'email'         => 'array(\'email\', array(\':value\', TRUE))',
		'email_domain'  => 'array(\'email_domain\')',
		'equals'        => 'array(\'equals\', array(\':value\', NULL /* required */))',
		'exact_length'  => 'array(\'exact_length\', array(\':value\', 0 /* length */))',
		'ip'            => 'array(\'ip\', array(\':value\', TRUE /* allow private */))',
		'luhn'          => 'array(\'luhn\')',
		'max_length'    => 'array(\'max_length\', array(\':value\', :null))',
		'min_length'    => 'array(\'min_length\', array(\':value\', :null))',
		'not_empty'     => 'array(\'not_empty\')',
		'numeric'       => 'array(\'numeric\')',
		'phone'         => 'array(\'phone\', array(\':value\', NULL /* lengths */))',
		'range'         => 'array(\'range\', array(\':value\', 0 /* min */, 0 /* max */))',
		'regex'         => 'array(\'regex\', array(\':value\', \'\' /* regular expression */))',
		'url'           => 'array(\'url\')',
	);

	/**
	 * Rules for any row types
	 *
	 * @var array
	 */
	protected $_rules_types = array
	(
		'int' => array
		(
			'credit_card',
			'digit',
			'equals',
			'luhn',
			'not_empty',
			'phone',
			'range',
		),

		'float' => array
		(
			'decimal',
			'equals',
			'numeric',
			'not_empty',
			'range',
		),

		'string' => array
		(
			'alpha',
			'alpha_dash',
			'alpha_numeric',
			'color',
			'email',
			'email_domain',
			'equals',
			'exact_length',
			'ip',
			'max_length',
			'min_length',
			'not_empty',
			'regex',
			'url',
		),

		'date' => array
		(
			'date',
			'not_empty',
		)
	);

	/**
	 * Default filters
	 *
	 * @var type
	 */
	protected $_filters = array
	(
		'strip_tags' => 'array(\'strip_tags\')',
		'trim'       => 'array(\'trim\')',
		'intval'     => 'array(\'intval\')',
		'floatval'   => 'array(\'floatval\')',
	);

	public function execute(array $config)
	{
		$config['name'] = explode(',', $config['name']);

		foreach ($config['name'] as $name)
		{
			$rules = $filters = array();

			$columns = Database::instance()
				->list_columns(Inflector::plural($name));

			foreach ($columns as $row => $info)
			{
				$apply_rules = $apply_filters = array();

				$rules_types = $this->_rules_types['string'];

				switch ($info['type'])
				{
					case 'int':

						$apply_rules   = array
						(
							'digit' => TRUE,
						);

						$apply_filters = array
						(
							'intval'
						);

						$rules_types = $this->_rules_types['int'];

						break;

					default:

						if ($info['data_type'] == 'varchar')
						{
							$apply_rules = array
							(
								'min_length' => array(':null' => 0),
								'max_length' => array(':null' => $info['character_maximum_length'])
							);

							$apply_filters = array
							(
								'strip_tags',
								'trim'
							);

							$rules_types = $this->_rules_types['string'];
						}
						elseif ($info['data_type'] == 'timestamp')
						{
							$apply_filters = array
							(
								'strip_tags',
								'trim'
							);

							$apply_rules = array
							(
								'date' => TRUE,
							);

							$rules_types = $this->_rules_types['date'];
						}

						break;
				}

				foreach ($rules_types as $rule)
				{
					$callback = $this->_rules[$rule];

					if (isset($apply_rules[$rule]))
					{
						if (is_array($apply_rules[$rule]))
						{
							$callback = str_replace(array_keys($apply_rules[$rule]), array_values($apply_rules[$rule]), $callback);
						}
						else
						{
							$callback = str_replace(':null', 0, $callback);
						}

						$rules[$row][$callback] = TRUE;
					}
					else
					{
						$rules[$row][$callback] = FALSE;
					}
				}

				foreach ($this->_filters as $filter => $callback)
				{
					$filters[$row][$callback] = (bool) in_array($filter, $apply_filters);
				}
			}

			$contents = View::factory('minion/task/autogen/model')
				->set('name', $name)
				->bind('filters', $filters)
				->bind('rules', $rules);

			try
			{
				$filename = APPPATH
					.'classes'.DIRECTORY_SEPARATOR
					.'model'.DIRECTORY_SEPARATOR
					.str_replace('_', DIRECTORY_SEPARATOR, $name);

				Autogen::write($filename, $contents);
			}
			catch (Exception $e)
			{
				return Minion_CLI::write($e->getMessage(), 'red');
			}
		}

		return Minion_CLI::write('Models has been successfully created', 'green');
	}

} // End Minion_Task_Autogen_Form