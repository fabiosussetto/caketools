<?php
/* SVN FILE: $Id$ */

if (!defined('CAKEPHP_UNIT_TEST_EXECUTION')) {
	define('CAKEPHP_UNIT_TEST_EXECUTION', 1);
}

App::import('Core', array('AppModel', 'Model'));

class SpamComment extends CakeTestModel {

	var $name = 'SpamComment';

	var $actsAs = array('Akismet');

	var $belongsTo = array('User');
/*
	var $_schema = array(
		'id' => array('type' => 'integer', 'key' => 'primary'),
		'user_id' => array('type' => 'integer'),
		'author' => 'string',
		'content' => 'text',
		'type' => 'string',
		'is_spam' => array('type' => 'integer', 'length' => 2, 'default' => -1, 'null' => false),
		'is_spam_null' => array('type' => 'integer', 'length' => 2),
	);
*/
}

class User extends CakeTestModel {

	var $name = 'User';

}

class AkismetTest extends CakeTestCase {

	var $fixtures = array('core.user', 'spam_comment');

    function startTest() {
        Configure::write('Akismet.key', '353022b8db62');
        Configure::write('Akismet.url', 'caketools');
    }

	function endTest() {
		ClassRegistry::flush();
	}

	function testNotSpamDirectly() {
		$TestModel =& new SpamComment();

		$data = array(
			'author' => 'viagra-test-123',
			'content' => 'This is a random string to test',
			'type' => 'comment'
		);
		$TestModel->set($data);

		$result = $TestModel->notSpam();
		$this->assertFalse($result);

		$result = $TestModel->notSpam(null, true);
		$this->assertFalse($result);

		$result = $TestModel->notSpam(null, null);
		$this->assertFalse($result);

		$data = array(
			'User' => array(
				'user' => 'viagra-test-123',
			),
			'SpamComment' => array(
				'content' => 'This is a random string to test',
				'type' => 'comment'
			)
		);
		$TestModel->create();
		$TestModel->set($data);
		$TestModel->Behaviors->attach('Akismet', array('author'=>'User.user'));

		$result = $TestModel->notSpam();
		$this->assertFalse($result);

		$result = $TestModel->notSpam(null, true);
		$this->assertFalse($result);

		$result = $TestModel->notSpam(null, null);
		$this->assertFalse($result);
	}

	function testNotSpamDirectlyInvalid() {
		$config = Configure::read('Akismet');
		Configure::write('Akismet', array('key' => 'invalid_test_key', 'blog' => 'http://www.example.com'));
		$TestModel =& new SpamComment();

		$data = array(
			'author' => 'viagra-test-123',
			'content' => 'This is a random string to test',
			'type' => 'comment'
		);
		$TestModel->set($data);

		$result = $TestModel->notSpam();
		$this->assertTrue($result);

		$result = $TestModel->notSpam(null, true);
		$this->assertFalse($result);

		$result = $TestModel->notSpam(null, null);
		$this->assertNull($result);

		Configure::write('Akismet', $config);
	}

	function testNotSpamValidation() {
		$TestModel =& new SpamComment();
		$data = array(
			'author' => 'viagra-test-123',
			'content' => 'This is a random string to test',
			'type' => 'comment'
		);
		$TestModel->set($data);

		$TestModel->validate = array('content'=>array('rule'=>'notSpam'));
		$result = $TestModel->validates();
		$this->assertFalse($result);

		$TestModel->validate = array('content'=>array('rule'=>'notSpam', true));
		$result = $TestModel->validates();
		$this->assertFalse($result);

		$data = array(
			'User' => array(
				'user' => 'viagra-test-123',
			),
			'SpamComment' => array(
				'content' => 'This is a random string to test',
				'type' => 'comment'
			)
		);
		$TestModel->create();
		$TestModel->set($data);
		$TestModel->Behaviors->attach('Akismet', array('author'=>'User.user'));

		$TestModel->validate = array('content'=>array('rule'=>'notSpam'));
		$result = $TestModel->validates();
		$this->assertFalse($result);

		$TestModel->validate = array('content'=>array('rule'=>'notSpam', true));
		$result = $TestModel->validates();
		$this->assertFalse($result);
	}

	function testNotSpamBeforeSave() {
		$TestModel =& new SpamComment();
		$TestModel->recursive = -1;
		$data = array('SpamComment' => array(
			'author' => 'viagra-test-123',
			'content' => 'This is a random string to test',
			'type' => 'comment'
		));
		$expected = $data;

		$result = $TestModel->save($data);
		$this->assertTrue(!empty($result));

		$expected['SpamComment']['is_spam'] = -1;
		$result = $TestModel->read(array('author', 'content', 'type', 'is_spam'), $TestModel->id);
		$this->assertEqual($result, $expected);

		$TestModel->Behaviors->attach('Akismet', array('is_spam'=>'is_spam'));
		$TestModel->create();
		$result = $TestModel->save($data);
		$this->assertTrue(!empty($result));

		$expected['SpamComment']['is_spam'] = 1;
		$result = $TestModel->read(array('author', 'content', 'type', 'is_spam'), $TestModel->id);
		$this->assertEqual($result, $expected);

		$expected = $data;

		$TestModel->create();
		$result = $TestModel->save($data);
		$this->assertTrue(!empty($result));

		$expected['SpamComment']['is_spam_null'] = null;
		$result = $TestModel->read(array('author', 'content', 'type', 'is_spam_null'), $TestModel->id);
		$this->assertIdentical($result, $expected);

		$TestModel->Behaviors->attach('Akismet', array('is_spam'=>'is_spam_null'));
		$TestModel->create();
		$result = $TestModel->save($data);
		$this->assertTrue(!empty($result));

		$expected['SpamComment']['is_spam_null'] = 1;
		$result = $TestModel->read(array('author', 'content', 'type', 'is_spam_null'), $TestModel->id);
		$this->assertEqual($result, $expected);
	}

	function testNotSpamBeforeSaveInvalid() {
		$config = Configure::read('Akismet');
		Configure::write('Akismet', array('key' => 'invalid_test_key', 'blog' => 'http://www.example.com'));
		$TestModel =& new SpamComment();
		$TestModel->recursive = -1;
		$data = array('SpamComment' => array(
			'author' => 'viagra-test-123',
			'content' => 'This is a random string to test',
			'type' => 'comment'
		));
		$expected = $data;
//		debug($TestModel->schema());

		$result = $TestModel->save($data);
		$this->assertTrue(!empty($result));

		$expected['SpamComment']['is_spam'] = -1;
		$result = $TestModel->read(array('author', 'content', 'type', 'is_spam'), $TestModel->id);
		$this->assertEqual($result, $expected);

		$TestModel->Behaviors->attach('Akismet', array('is_spam'=>'is_spam'));
		$TestModel->create();
		$result = $TestModel->save($data);
		$this->assertTrue(!empty($result));

		$expected['SpamComment']['is_spam'] = -1;
		$result = $TestModel->read(array('author', 'content', 'type', 'is_spam'), $TestModel->id);
		$this->assertEqual($result, $expected);

		$expected = $data;

		$TestModel->create();
		$result = $TestModel->save($data);
		$this->assertTrue(!empty($result));

		$expected['SpamComment']['is_spam_null'] = null;
		$result = $TestModel->read(array('author', 'content', 'type', 'is_spam_null'), $TestModel->id);
		$this->assertIdentical($result, $expected);

		$TestModel->Behaviors->attach('Akismet', array('is_spam'=>'is_spam_null'));
		$TestModel->create();
		$result = $TestModel->save($data);
		$this->assertTrue(!empty($result));

		$expected['SpamComment']['is_spam_null'] = null;
		$result = $TestModel->read(array('author', 'content', 'type', 'is_spam_null'), $TestModel->id);
		$this->assertIdentical($result, $expected);

		Configure::write('Akismet', $config);
	}

	function testMarkAs() {
		$TestModel =& new SpamComment();
		$data = array(
			'author' => 'viagra-test-123',
			'content' => 'This is a random string to test',
			'type' => 'comment'
		);
		$TestModel->set($data);

		$result = $TestModel->markAs('spam');
		$this->assertTrue($result);

		$result = $TestModel->markAs('spam', false);
		$this->assertFalse($result);

		$data = array(
			'User' => array(
				'user' => 'viagra-test-123',
			),
			'SpamComment' => array(
				'content' => 'This is a random string to test',
				'type' => 'comment'
			)
		);
		$TestModel->create();
		$TestModel->set($data);
		$TestModel->Behaviors->attach('Akismet', array('author'=>'User.user'));

		$result = $TestModel->markAs('spam');
		$this->assertTrue($result);

		$result = $TestModel->markAs('spam', false);
		$this->assertFalse($result);
	}

	function testMarkAsWithField() {
		$TestModel =& new SpamComment();
		$TestModel->Behaviors->attach('Akismet', array('is_spam'=>'is_spam'));
		$data = array(
			'id' => 1,
			'author' => 'viagra-test-123',
			'content' => 'This is a random string to test',
			'type' => 'comment'
		);
		$TestModel->set($data);
		$result = $TestModel->markAs('spam');
		$this->assertTrue($result);

		$result = $TestModel->markAs('spam', false);
		$this->assertTrue($result);

		$data = array(
			'User' => array(
				'user' => 'viagra-test-123',
			),
			'SpamComment' => array(
				'id' => 2,
				'content' => 'This is a random string to test',
				'type' => 'comment'
			)
		);
		$TestModel->create();
		$TestModel->set($data);
		$TestModel->Behaviors->attach('Akismet', array('author'=>'User.user'));

		$result = $TestModel->markAs('spam');
		$this->assertTrue($result);

		$result = $TestModel->markAs('spam', false);
		$this->assertTrue($result);
	}

	function testMarkAsWithFieldNull() {
		$TestModel =& new SpamComment();
		$TestModel->Behaviors->attach('Akismet', array('is_spam'=>'is_spam_null'));
		$data = array(
			'id' => 1,
			'author' => 'viagra-test-123',
			'content' => 'This is a random string to test',
			'type' => 'comment'
		);
		$TestModel->set($data);
		$result = $TestModel->markAs('spam');
		$this->assertTrue($result);

		$result = $TestModel->markAs('spam', false);
		$this->assertTrue($result);

		$result = $TestModel->markAs('spam', null);
		$this->assertTrue($result);
	}

}

?>