<?php echo Kohana::FILE_SECURITY ?>


return array
(
<?php foreach ($i18n as $term => $data): ?>
<?php if ($data['translated']): ?>
<?php if (is_array($data['translate'])): ?>
	'<?php echo addslashes($term) ?>' => array
	(
		'one'  => '<?php echo addslashes($data['translate']['one']) ?>',
		'few'  => '<?php echo addslashes($data['translate']['few']) ?>',
		'many' => '<?php echo addslashes($data['translate']['many']) ?>',
	),
<?php else: ?>
	'<?php echo addslashes($term) ?>' => '<?php echo addslashes($data['translate']) ?>',
<?php endif ?>
<?php else: ?>
<?php if($data['translate'] === Task_Autogen_I18n::SINGULAR): ?>
	//'<?php echo addslashes($term) ?>' => '<?php echo addslashes($term) ?>',
<?php else: ?>
	//'<?php echo addslashes($term) ?>' => array
	//(
	//	'one'  => '<?php echo addslashes($term) ?>',
	//	'few'  => '<?php echo addslashes($term) ?>',
	//	'many' => '<?php echo addslashes($term) ?>',
	//),
<?php endif ?>
<?php endif ?>
<?php endforeach ?>
);