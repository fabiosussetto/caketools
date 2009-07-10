<?php echo $form->create('Image', array('type' => 'file', 'url' => array('action' => 'add'))); ?>
    <?php echo $form->input('Image.title'); ?>
    <?php echo $form->input('Image.filename', array('type' => 'file')); ?>
    <?php echo $form->submit('Submit'); ?>
<?php echo $form->end(); ?>
