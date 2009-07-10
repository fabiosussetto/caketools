<?php echo $javascript->link(array('/tinymce/jscripts/tiny_mce/tiny_mce'), false); ?>
<?php echo $javascript->link(array('mce'), false); ?>

<a href="#" id="add-image">add image</a>

<?php echo $form->create('Image', array('url' => array('action' => 'testeditor'))); ?>
    <?php echo $form->textarea('Image.body', array('class' => 'mceEditor', 'style' => 'width:700px')); ?>
    

    <?php echo $form->submit('Submit'); ?>
   
<?php echo $form->end(); ?>