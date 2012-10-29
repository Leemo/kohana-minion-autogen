<?php echo '<?php echo Form::open() ?>' ?>


<?php foreach($fields as $name => $type): ?>
	<dl>
		<dt>
			<?php echo '<?php echo Form::label(\''.$name.'\', __(\''.UTF8::ucfirst($name).'\')) ?>' ?>
		</dt>
		<dd<?php echo '<?php if (isset($errors[\''.$name.'\'])): ?>' ?> class="error"<?php echo '<?php endif ?>' ?>>
			<?php echo '<?php echo '.View::factory('minion/autogen/form/'.$type)->bind('name', $name).' ?>' ?>

<?php echo '<?php if (isset($errors[\''.$name.'\'])): ?>' ?>

			<div class="error_note"><?php echo '<?php echo $errors[\''.$name.'\'] ?>' ?></div>
<?php echo '<?php endif ?>' ?>

		</dd>
	</dl>

<?php endforeach ?>
	<dl>
		<dt class="b"></dt>
		<dd>
			<?php echo '<?php echo Form::button(\'save\', __(\'Save\'), array(\'type\' => \'submit\', \'class\' => \'button gray_gradient\')) ?>' ?>
		</dd>
	</dl>

<?php echo '<?php echo Form::close() ?>' ?>