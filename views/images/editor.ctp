<?php echo $javascript->link(array('/tinymce/jscripts/tiny_mce/tiny_mce'), false); ?>
<?php echo $javascript->link(array('mce_editor'), false); ?>

<?php echo $form->create('Image', array('url' => array('action' => 'editor'))); ?>
    
<?php echo $form->end(); ?>
<textarea name="editor1" style="width:700px;">&lt;p&gt;Initial value.&lt;/p&gt;</textarea>
