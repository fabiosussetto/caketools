<?php 
/* SVN FILE: $Id$ */
/* File Fixture generated on: 2009-06-26 18:06:08 : 1246035128*/

class ArticleFixture extends CakeTestFixture {
	var $name = 'Article';
	var $table = 'articles';
	
    var $fields = array(
    		'id' => array('type'=>'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
    		'title' => array('type'=>'string', 'null' => false, 'default' => NULL, 'length' => 100),
    		'slug' => array('type'=>'string', 'null' => false, 'default' => NULL, 'length' => 100),
    		'permalink' => array('type'=>'string', 'null' => false, 'default' => NULL, 'length' => 100),
    		'category' => array('type'=>'string', 'null' => false, 'default' => NULL, 'length' => 50),
    		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1))
    	);        	
            	
	var $records = array(
	    array('id' => 1, 'title' => 'collision title', 'slug' => 'collision-title'),
	    array('id' => 2, 'title' => 'I have no slug', 'slug' => ''),
	    array('id' => 3, 'title' => 'Me too no slug', 'slug' => ''),
	    array('id' => 4, 'title' => 'Me too no slug', 'slug' => ''),
	    array('id' => 5, 'title' => 'Another test title', 'slug' => 'another-test-title')
	);
}
?>