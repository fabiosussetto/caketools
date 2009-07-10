<?php
class Image extends AppModel {

	var $name = 'Image';
	var $actsAs = array('FgImageUpload' => array(
	    'baseDir' => APP,
	    'versions' => array(
    	    'thumb' => array(
    	        'processing' => array('adaptiveResize' => array(75, 75)),
    	        'format' => 'jpg',
    	        'options' => array('jpegQuality' => 60)
    	    ),
    	    'medium' => array(
    	        'processing' => array('resize' => array(300, 300)),
    	        'format' => 'jpg',
    	        'options' => array('jpegQuality' => 60)
    	    )
    	)
	));

}
?>