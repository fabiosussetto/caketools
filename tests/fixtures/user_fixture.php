<?php 
/* SVN FILE: $Id$ */
/* User Fixture generated on: 2009-07-14 19:07:15 : 1247591295*/

class UserFixture extends CakeTestFixture {
	var $name = 'User';
	var $table = 'users';
	var $fields = array(
		'id' => array('type'=>'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'username' => array('type'=>'string', 'null' => false, 'default' => NULL, 'length' => 50),
		'password' => array('type'=>'string', 'null' => false, 'default' => NULL, 'length' => 100),
		'email' => array('type'=>'string', 'null' => false, 'default' => NULL, 'length' => 50),
		'fb_id' => array('type'=>'integer', 'null' => true, 'default' => NULL),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1))
	);
	var $records = array(array(
		'id'  => 1,
		'username'  => 'Lorem ipsum dolor sit amet',
		'password'  => 'Lorem ipsum dolor sit amet',
		'email'  => 'Lorem ipsum dolor sit amet',
		'fb_id'  => 1
	));
}
?>