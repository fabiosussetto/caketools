<?php 
/* SVN FILE: $Id$ */
/* FilesController Test cases generated on: 2009-06-26 18:06:29 : 1246035149*/
App::import('Controller', 'Files');

class TestFiles extends FilesController {
	var $autoRender = false;
}

class FilesControllerTest extends CakeTestCase {
	var $Files = null;

	function startTest() {
		$this->Files = new TestFiles();
		$this->Files->constructClasses();
	}

	function testFilesControllerInstance() {
		$this->assertTrue(is_a($this->Files, 'FilesController'));
	}

	function endTest() {
		unset($this->Files);
	}
}
?>