<?php echo '<?php defined(\'SYSPATH\') or die(\'No direct access allowed.\');' ?>


class <?php echo Inflector::underscore(UTF8::ucwords(Inflector::humanize('controller_'.$name))) ?><?php if ( ! empty($extends)): ?> extends <?php echo Inflector::underscore(UTF8::ucwords(Inflector::humanize($extends))) ?><?php endif ?> {

	/**
	 * Controls access for separate actions
	 *
	 * @var array
	 */
	public $secure_actions = array
	(
	);

<?php foreach($actions as $action): ?>
	public function action_<?php echo $action ?>()
	{

	}

<?php endforeach ?>
} // End <?php echo Inflector::underscore(UTF8::ucwords(Inflector::humanize('controller_'.$name))) ?>