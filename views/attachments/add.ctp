<?php echo $form->create('Attachment', array('type' => 'file')); ?>
<?php echo $form->input('Attachment.title'); ?>
<?php echo $form->file('Attachment.filename'); ?>
<?php echo $form->submit('Submit'); ?>
<?php echo $form->end(); ?>

