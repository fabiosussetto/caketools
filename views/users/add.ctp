<?php echo $form->create('User', array('action' => 'add')); ?>
    <?php echo $form->input('User.username'); ?>
    <?php echo $form->input('User.password'); ?>
    <?php echo $form->input('User.email'); ?>
<?php echo $form->end('Register'); ?>