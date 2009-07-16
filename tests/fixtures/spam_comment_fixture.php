<?php
/* SVN FILE: $Id$ */

class SpamCommentFixture extends CakeTestFixture {

	var $name = 'SpamComment';

	var $fields = array(
		'id' => array('type' => 'integer', 'key' => 'primary'),
		'user_id' => array('type' => 'integer'),
		'author' => 'string',
		'content' => 'text',
		'type' => 'string',
		'is_spam' => array('type' => 'integer', 'length' => 2, 'default' => -1, 'null' => false),
		'is_spam_null' => array('type' => 'integer', 'length' => 2),
	);

	var $records = array(
		array('id' => 1, 'author' => 'Local User 1', 'content' => 'Comment By Local Author'),
		array('id' => 2, 'user_id' => 1, 'content' => 'Comment By Associated Author'),
	);
}

?>