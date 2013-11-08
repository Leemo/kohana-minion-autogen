<?php echo Kohana::FILE_SECURITY ?>


return array
(
<?php foreach ($terms as $term => $data): ?>
<?php if ($data['translated']): ?>
<?php if (is_array($data['translate'])): ?>
	'<?php echo addcslashes($term, '\'') ?>' => array
	(
		'one'  => '<?php echo addcslashes($data['translate']['one'], '\'') ?>',
		'few'  => '<?php echo addcslashes($data['translate']['few'], '\'') ?>',
		'many' => '<?php echo addcslashes($data['translate']['many'], '\'') ?>',
	),
<?php else: ?>
	'<?php echo addcslashes($term, '\'') ?>' => '<?php echo addcslashes($data['translate'], '\'') ?>',
<?php endif ?>
<?php else: ?>
<?php if(is_array($data['translate'])): ?>
	//'<?php echo addcslashes($term, '\'') ?>' => array
	//(
	//	'one'  => '<?php echo addcslashes($data['translate']['one'], '\'') ?>',
	//	'few'  => '<?php echo addcslashes($data['translate']['few'], '\'') ?>',
	//	'many' => '<?php echo addcslashes($data['translate']['many'], '\'') ?>',
	//),
<?php else: ?>
	//'<?php echo addcslashes($term, '\'') ?>' => '<?php echo addcslashes($data['translate'], '\'') ?>',
<?php endif ?>
<?php endif ?>
<?php endforeach ?>
);