<?php 
/* SVN FILE: $Id$ */
/* ImagesController Test cases generated on: 2009-06-26 18:06:13 : 1246034653*/
App::import('Controller', 'Images');

class TestImages extends ImagesController {
	var $autoRender = false;
}

class ImagesControllerTest extends CakeTestCase {
	var $Images = null;

	function startTest() {
		$this->Images = new TestImages();
		$this->Images->constructClasses();
	}

	function testImagesControllerInstance() {
		$this->assertTrue(is_a($this->Images, 'ImagesController'));
	}

	function endTest() {
		unset($this->Images);
	}
}
?>