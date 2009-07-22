<?php 
/* SVN FILE: $Id$ */
/* File Fixture generated on: 2009-06-26 18:06:08 : 1246035128*/

class TagFixture extends CakeTestFixture {
	var $name = 'Tag';
	var $table = 'tags';
	
    var $fields = array(
    		'id' => array('type'=>'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
    		'name' => array('type'=>'string', 'null' => false, 'default' => NULL, 'length' => 50),
    		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1))
    	);        	
            	
	var $records = array(
	    array('id' => 1, 'name' => 'science'),
	    array('id' => 2, 'name' => 'nature'),
	    array('id' => 3, 'name' => 'fiction'),
	    array('id' => 4, 'name' => 'fantasy'),
	);
}
?>