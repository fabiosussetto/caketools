<?php 
/* SVN FILE: $Id$ */
/* File Fixture generated on: 2009-06-26 18:06:08 : 1246035128*/

class PostFixture extends CakeTestFixture {
	var $name = 'Post';
	var $table = 'posts';
	
    var $fields = array(
    		'id' => array('type'=>'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
    		'title' => array('type'=>'string', 'null' => false, 'default' => NULL, 'length' => 100),
    		'slug' => array('type'=>'string', 'null' => false, 'default' => NULL, 'length' => 100),
    		'permalink' => array('type'=>'string', 'null' => false, 'default' => NULL, 'length' => 100),
    		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1))
    	);        	
            	
	var $records = array(
	    array('id' => 1, 'title' => 'a test title', 'slug' => 'a-test-title', 'permalink' => '')    
	    
	);
}
?>