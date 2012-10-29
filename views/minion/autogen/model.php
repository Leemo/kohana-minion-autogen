<?php echo '<?php defined(\'SYSPATH\') or die(\'No direct access allowed.\');' ?>


class <?php echo Inflector::underscore(UTF8::ucwords(Inflector::humanize('model_'.$name))) ?> extends ORM {

	/**
	 * Table that store model data
	 *
	 * @var  string  Table name
	 */
	protected $_table_name = '<?php echo Inflector::plural($name) ?>';

	/**
	 * Model rules
	 *
	 * @return array
	 */
	public function rules()
	{
		return array
		(
<?php foreach ($rules as $row => $validations): ?>
			'<?php echo $row ?>' => array
			(
<?php foreach ($validations as $validation => $enabled): ?>
				<?php if ( ! $enabled): ?>// <?php endif ?><?php echo $validation ?>,
<?php endforeach ?>
			),

<?php endforeach ?>
		);
	}

	/**
	 * Model filters
	 *
	 * @return array
	 */
	public function filters()
	{
		return array
		(
<?php foreach ($filters as $row => $callbacks): ?>
			'<?php echo $row ?>' => array
			(
<?php foreach ($callbacks as $callback => $enabled): ?>
				<?php if ( ! $enabled): ?>// <?php endif ?><?php echo $callback ?>,
<?php endforeach ?>
			),

<?php endforeach ?>
		);
	}

} // End <?php echo Inflector::underscore(UTF8::ucwords(Inflector::humanize('model_'.$name))) ?>