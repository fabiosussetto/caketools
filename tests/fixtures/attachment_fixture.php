<?php 
/* SVN FILE: $Id$ */
/* File Fixture generated on: 2009-06-26 18:06:08 : 1246035128*/

class AttachmentFixture extends CakeTestFixture {
	var $name = 'Attachment';
	var $table = 'attachments';
	
    var $fields = array(
    		'id' => array('type'=>'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
    		'title' => array('type'=>'string', 'null' => false, 'default' => NULL, 'length' => 50),
    		'filename' => array('type'=>'string', 'null' => true, 'default' => NULL, 'length' => 50),
    		'document' => array('type'=>'string', 'null' => true, 'default' => NULL, 'length' => 50),
    		'post_id' => array('type'=>'integer', 'null' => false, 'default' => NULL),
    		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1))
    	);        	
            	
	var $records = array(
	    array('id' => 1, 'title' => 'A test pdf', 'filename' => 'sample_file.pdf', 'document' => '')    
	);
}
?>