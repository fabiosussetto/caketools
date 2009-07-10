<?php 
/* SVN FILE: $Id$ */
/* Image Fixture generated on: 2009-06-26 18:06:58 : 1246034638*/

class ImageFixture extends CakeTestFixture {
	var $name = 'Image';
	var $table = 'images';
	//var $path = TESTS . 'uploaded_images' . DS;
	
	var $fields = array(
    		'id' => array('type'=>'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
    		'title' => array('type'=>'string', 'null' => false, 'default' => NULL, 'length' => 50),
    		'filename' => array('type'=>'string', 'null' => false, 'default' => NULL, 'length' => 50),
    		'original' => array('type'=>'string', 'null' => false, 'default' => NULL),
    		'post_id' => array('type'=>'integer', 'null' => false, 'default' => NULL),
    		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1))
    	);        	
            	
	var $records = array();
	
	function __construct() {
	    parent::__construct();
	    
	    $this->path = TESTS . 'uploaded_images' . DS;
	    
	    $this->records = array(
	        array(
	           'id' => 1, 'title' => 'My image', 'filename' => 'my_image.jpg', 'original' => $this->path . 'my_image.jpg',
	           'post_id' => 1
	        ) 
    	);
	}
}
?>