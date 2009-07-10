<?php 
/* SVN FILE: $Id$ */
/* Image Test cases generated on: 2009-06-26 18:06:59 : 1246034639*/
App::import('Model', 'Image');

class ImageTestCase extends CakeTestCase {
	var $Image = null;
	var $fixtures = array('app.image');

	function startTest() {
		$this->Image =& ClassRegistry::init('Image');
	}

	function testImageInstance() {
		$this->assertTrue(is_a($this->Image, 'Image'));
	}

	function testImageFind() {
		$this->Image->recursive = -1;
		$results = $this->Image->find('first');
		$this->assertTrue(!empty($results));

		$expected = array('Image' => array(
			'id'  => 1,
			'title'  => 'Lorem ipsum dolor sit amet',
			'description'  => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida,phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam,vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit,feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
			'order'  => 1,
			'dir'  => 'Lorem ipsum dolor sit amet',
			'filename'  => 'Lorem ipsum dolor sit amet',
			'ext'  => 'Lorem ip',
			'original'  => 'Lorem ipsum dolor sit amet',
			'post_id'  => 1,
			'created'  => '2009-06-26 18:43:58',
			'updated'  => '2009-06-26 18:43:58'
		));
		$this->assertEqual($results, $expected);
	}
}
?>