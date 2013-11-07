<?php echo '<?php defined(\'SYSPATH\') or die(\'No direct access allowed.\');' ?>


return array
(
<?php foreach ($messages as $row => $row_messages): ?>
	'<?php echo $row ?>' => array
	(
<?php foreach ($row_messages as $rule => $message): ?>
		<?php if ($message['comment']): ?>// <?php endif ?>'<?php echo $rule ?>' => '<?php echo addcslashes($message['text'], '\'') ?>',
<?php endforeach ?>
	),

<?php endforeach ?>
);