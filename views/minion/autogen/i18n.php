<?php echo Kohana::FILE_SECURITY ?>


return array
(
<?php foreach ($i18n as $term => $data): ?>
<?php if ($data['translated']): ?>
<?php if (is_array($data['translate'])): ?>
	'<?php echo str_replace('\"', '', addslashes($term)) ?>' => array
	(
		'one'  => '<?php echo str_replace('\"', '', addslashes($data['translate']['one'])) ?>',
		'few'  => '<?php echo str_replace('\"', '', addslashes($data['translate']['few'])) ?>',
		'many' => '<?php echo str_replace('\"', '', addslashes($data['translate']['many'])) ?>',
	),
<?php else: ?>
	'<?php echo str_replace('\"', '', addslashes($term)) ?>' => '<?php echo str_replace('\"', '', addslashes($data['translate'])) ?>',
<?php endif ?>
<?php else: ?>
<?php if($data['translate'] === Task_Autogen_I18n::SINGULAR): ?>
	//'<?php echo str_replace('\"', '', addslashes($term)) ?>' => '<?php echo str_replace('\"', '', addslashes($term)) ?>',
<?php else: ?>
	//'<?php echo str_replace('\"', '', addslashes($term)) ?>' => array
	//(
	//	'one'  => '<?php echo str_replace('\"', '', addslashes($term)) ?>',
	//	'few'  => '<?php echo str_replace('\"', '', addslashes($term)) ?>',
	//	'many' => '<?php echo str_replace('\"', '', addslashes($term)) ?>',
	//),
<?php endif ?>
<?php endif ?>
<?php endforeach ?>
);