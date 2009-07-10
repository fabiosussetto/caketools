<?php 
/* SVN FILE: $Id$ */
/* File Test cases generated on: 2009-06-26 18:06:08 : 1246035128*/
App::import('Model', 'Attachment');

class AttachmentTestCase extends CakeTestCase {
	var $Attachment = null;
	var $fixtures = array('app.attachment');

	function startTest() {
		$this->File =& ClassRegistry::init('Attachment');
	}

	function testFileInstance() {
		$this->assertTrue(is_a($this->Attachment, 'Attachment'));
	}

	function testFileFind() {
		$this->File->recursive = -1;
		$results = $this->File->find('first');
		$this->assertTrue(!empty($results));

		$expected = array('File' => array(
			'id'  => 1,
			'title'  => 'Lorem ipsum dolor sit amet',
			'description'  => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida,phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam,vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit,feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
			'order'  => 1,
			'dir'  => 'Lorem ipsum dolor sit amet',
			'filename'  => 'Lorem ipsum dolor sit amet',
			'ext'  => 'Lorem ip',
			'original'  => 'Lorem ipsum dolor sit amet',
			'post_id'  => 1,
			'created'  => '2009-06-26 18:52:08',
			'updated'  => '2009-06-26 18:52:08'
		));
		$this->assertEqual($results, $expected);
	}
}
?>