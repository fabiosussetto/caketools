<?php

if (!defined('CAKEPHP_UNIT_TEST_EXECUTION')) {
	define('CAKEPHP_UNIT_TEST_EXECUTION', 1);
}

App::import('Core', array('AppModel', 'Model'));
require_once(dirname(dirname(__FILE__)) . DS . 'models' . DS . 'models.php');

class FgUploadBehaviorTest extends CakeTestCase {
	var $fixtures = array('app.attachment', 'app.post', 'app.image');
	var $uploadPath;

	function startTest() {
		$this->Attachment =& ClassRegistry::init('Attachment');
		$this->Post =& ClassRegistry::init('Post');
		
		$this->Attachment->Behaviors->attach('FgUpload', array('baseDir' => TESTS));
		$this->tmpFolder = dirname(__FILE__) . DS . 'tmp_files' . DS;

		$this->uploadPath = TESTS . 'uploaded_attachments' . DS;
		
		$Folder = new Folder();
		$Folder->delete($this->uploadPath);
	}
	
	function endTest() {
		unset($this->Attachment);
		$Folder = new Folder();
        $Folder->delete($this->uploadPath);
		ClassRegistry::flush();
	}
	
	function testSingleUpload() {
	    $data = array(
	        'Attachment' => array(
	            'title' => 'test',
	            'filename' => array(
	                'name' => 'test_file.pdf',
	                'type' => 'application/pdf',
	                'tmp_name' => $this->tmpFolder . 'test_pdf.tmp',
	                'error' => '0',
	                'size' => '16128'
	            )    
	        )    
	    );
	    
	    $this->Attachment->save($data);
	    $this->assertTrue(file_exists($this->uploadPath . 'test_file.pdf'));
	    
	    $results = $this->Attachment->findByTitle('test');
	    $this->assertEqual($results['Attachment']['filename'], 'test_file.pdf');
	}
	
	function testNoOverwrite() {
	    $data = array(
	        'Attachment' => array(
	            'title' => 'test',
	            'filename' => array(
	                'name' => 'test_file.pdf',
	                'type' => 'application/pdf',
	                'tmp_name' => $this->tmpFolder . 'test_pdf.tmp',
	                'error' => '0',
	                'size' => '16128'
	            )    
	        )    
	    );
	    
	    $this->Attachment->save($data);
	    $this->Attachment->create($data);
	    $this->Attachment->save($data);
	    
	    $this->assertTrue(file_exists($this->uploadPath . 'test_file.pdf'));
	    $this->assertTrue(file_exists($this->uploadPath . 'test_file_1.pdf'));
	    
	    $results = $this->Attachment->find('all');
	    
	    $this->assertEqual($results[0]['Attachment']['filename'], 'test_file.pdf');
	    $this->assertEqual($results[1]['Attachment']['filename'], 'test_file_1.pdf');
	}
	
	function testOverwrite() {
	    $data = array(
	        'Attachment' => array(
	            'title' => 'test',
	            'filename' => array(
	                'name' => 'test_file.pdf',
	                'type' => 'application/pdf',
	                'tmp_name' => $this->tmpFolder . 'test_pdf.tmp',
	                'error' => '0',
	                'size' => '16128'
	            )    
	        )    
	    );
	    
	    $this->Attachment->Behaviors->attach('FgUpload', array('overwrite' => true));
	    
	    $this->Attachment->save($data);
	    $this->Attachment->create($data);
	    $this->Attachment->save($data);
	    
	    $this->assertTrue(file_exists($this->uploadPath . 'test_file.pdf'));
	    $this->assertFalse(file_exists($this->uploadPath . 'test_file_1.pdf'));
	    
	    $results = $this->Attachment->find('all');
	    
	    $this->assertEqual($results[0]['Attachment']['filename'], 'test_file.pdf');
	    $this->assertEqual($results[1]['Attachment']['filename'], 'test_file.pdf');
	}
	
	function testForceFilePresence() {
	    $data = array(
	        'Attachment' => array(
	            'title' => 'test',
	            'filename' => array(
	                'name' => '',
	                'type' => '',
	                'tmp_name' => '',
	                'error' => '4',
	                'size' => '0'
	            )    
	        )    
	    );
	    
	    $this->Attachment->save($data);
	    $this->assertFalse(file_exists($this->uploadPath . 'test_file.pdf'));
	    
	    $results = $this->Attachment->findByTitle('test');
	    $this->assertTrue(empty($results));
	    $this->assertEqual(count($this->Attachment->validationErrors), 1);
	}
	
	function testForceFilePresenceEmpty() {
	    $data = array(
	        'Attachment' => array(
	            'title' => 'test'
	        )    
	    );
	    
	    $this->Attachment->save($data);
	    $this->assertFalse(file_exists($this->uploadPath . 'test_file.pdf'));
	    
	    $results = $this->Attachment->findByTitle('test');
	    $this->assertTrue(empty($results));
	    $this->assertEqual(count($this->Attachment->validationErrors), 1);
	}
	
	function testNoForceFilePresence() {
	    $data = array(
	        'Attachment' => array(
	            'title' => 'test'
	        )    
	    );
	    
	    $this->Attachment->Behaviors->attach('FgUpload', array('forceFilePresence' => false));
	    
	    $this->Attachment->save($data);
	    
	    $this->Attachment->recursive = -1;
	    $results = $this->Attachment->findByTitle('test');
	   
	    $this->assertEqual(count($results), 1);
	}
	
	function testValidateIfPresent() {
	    $data = array(
	        'Attachment' => array(
	            'title' => 'test',
	            'filename' => array(
	                'name' => 'test_file.pdf',
	                'type' => 'application/pdf',
	                'tmp_name' => $this->tmpFolder . 'test_pdf.tmp',
	                'error' => '0',
	                'size' => '16128'
	            )    
	        )    
	    );
	    
	    $this->Attachment->Behaviors->attach('FgUpload', array(
	        'forceFilePresence' => false, 'allowedSize' => 1, 'allowedSizeUnit' => 'KB'));
	    
	    $this->Attachment->save($data);
	    $this->assertFalse(file_exists($this->uploadPath . 'test_file.pdf'));
	    
	    $results = $this->Attachment->findByTitle('test');
	    $this->assertTrue(empty($results));
	    $this->assertEqual(count($this->Attachment->validationErrors), 1);
	}
	
	function testMaxFileSize() {
	    $data = array(
	        'Attachment' => array(
	            'title' => 'test',
	            'filename' => array(
	                'name' => 'test_file.pdf',
	                'type' => 'application/pdf',
	                'tmp_name' => $this->tmpFolder . 'test_pdf.tmp',
	                'error' => '0',
	                'size' => '16128'
	            )    
	        )    
	    );
	    
	    $this->Attachment->Behaviors->attach('FgUpload', array('allowedSize' => 1, 'allowedSizeUnit' => 'KB'));
	    
	    $this->Attachment->save($data);
	    $this->assertFalse(file_exists($this->uploadPath . 'test_file.pdf'));
	    
	    $results = $this->Attachment->findByTitle('test');
	    $this->assertTrue(empty($results));
	    $this->assertEqual(count($this->Attachment->validationErrors), 1);
	}
	
	function testExtensionNotAllowed() {
	    $data = array(
	        'Attachment' => array(
	            'title' => 'test',
	            'filename' => array(
	                'name' => 'test_file.exe',
	                'type' => 'application/pdf',
	                'tmp_name' => $this->tmpFolder . 'test_pdf.tmp',
	                'error' => '0',
	                'size' => '16128'
	            )    
	        )    
	    );
	    
	    $this->Attachment->save($data);
	    $this->assertFalse(file_exists($this->uploadPath . 'test_file.exe'));
	    
	    $results = $this->Attachment->findByTitle('test');
	    $this->assertTrue(empty($results));
	    $this->assertEqual(count($this->Attachment->validationErrors), 1);
	}
	
	function testExtensionAllowed() {
	    $data = array(
	        'Attachment' => array(
	            'title' => 'test',
	            'filename' => array(
	                'name' => 'test_file.mp3',
	                'type' => 'audio/mpeg3',
	                'tmp_name' => $this->tmpFolder . 'test_pdf.tmp',
	                'error' => '0',
	                'size' => '16128'
	            )    
	        )    
	    );
	    
	    $this->Attachment->Behaviors->attach('FgUpload', array('allowedExt' => array('mp3')));
	    
	    $this->Attachment->save($data);
	    $this->assertTrue(file_exists($this->uploadPath . 'test_file.mp3'));
	    
	    $results = $this->Attachment->findByTitle('test');
	    $this->assertFalse(empty($results));
	}
	
	function testMimeNotAllowed() {
	    $data = array(
	        'Attachment' => array(
	            'title' => 'test',
	            'filename' => array(
	                'name' => 'test_file.mp3',
	                'type' => 'audio/mpeg3',
	                'tmp_name' => $this->tmpFolder . 'test_pdf.tmp',
	                'error' => '0',
	                'size' => '16128'
	            )    
	        )    
	    );
	    
	    $this->Attachment->Behaviors->attach('FgUpload', array(
	        'allowedMimes' => array('application/pdf', 'image/gif')));
	    
	    $this->Attachment->save($data);
	    $this->assertFalse(file_exists($this->uploadPath . 'test_file.mp3'));
	    
	    $results = $this->Attachment->findByTitle('test');
	    $this->assertTrue(empty($results));
	    $this->assertEqual(count($this->Attachment->validationErrors), 1);
	}
	
	function testMimeAllowed() {
	    $data = array(
	        'Attachment' => array(
	            'title' => 'test',
	            'filename' => array(
	                'name' => 'test_file.pdf',
	                'type' => 'application/pdf',
	                'tmp_name' => $this->tmpFolder . 'test_pdf.tmp',
	                'error' => '0',
	                'size' => '16128'
	            )    
	        )    
	    );
	    
	    $this->Attachment->Behaviors->attach('FgUpload', array(
	        'allowedMimes' => array('application/pdf', 'image/gif')));
	    
	    $this->Attachment->save($data);
	    $this->assertTrue(file_exists($this->uploadPath . 'test_file.pdf'));
	    
	    $results = $this->Attachment->findByTitle('test');
	    $this->assertFalse(empty($results));
	}
	
	function testSaveAll() {
	    $data = array(
	        'Post' => array(
	            'title' => 'test post'
	        ),
	        'Attachment' => array(
	            0 => array(
    	            'title' => 'test',
    	            'filename' => array(
    	                'name' => 'test_file.pdf',
    	                'type' => 'application/pdf',
    	                'tmp_name' => $this->tmpFolder . 'test_pdf.tmp',
    	                'error' => '0',
    	                'size' => '16128'
    	            )
    	        )
	        )    
	    );
	    
	    $this->Post->saveAll($data, array('validate' => 'first'));
	    $this->assertTrue(file_exists($this->uploadPath . 'test_file.pdf'));
	    
	    $results = $this->Post->findByTitle('test post');
	    $this->assertEqual($results['Attachment'][0]['filename'], 'test_file.pdf');
	}
	
	function testDelete() {
	    $data = array(
	        'Attachment' => array(
	            'title' => 'test delete',
	            'filename' => array(
	                'name' => 'test_delete_file.pdf',
	                'type' => 'application/pdf',
	                'tmp_name' => $this->tmpFolder . 'test_pdf.tmp',
	                'error' => '0',
	                'size' => '16128'
	            )    
	        )    
	    );
	    
	    $this->Attachment->save($data);
	    $this->assertTrue(file_exists($this->uploadPath . 'test_delete_file.pdf'));
	    
	    $this->Attachment->delete();
	    $this->assertFalse(file_exists($this->uploadPath . 'test_delete_file.pdf'));
	    
	}
	
	function testDeleteAll() {
	    $data = array(
	        'Attachment' => array(
	            0 => array(
    	            'title' => 'test 1',
    	            'post_id' => 3,
    	            'filename' => array(
    	                'name' => 'test_file_delete_1.pdf',
    	                'type' => 'application/pdf',
    	                'tmp_name' => $this->tmpFolder . 'test_pdf.tmp',
    	                'error' => '0',
    	                'size' => '16128'
    	            )
    	        ),
    	        1 => array(
    	            'title' => 'test 2',
    	            'post_id' => 3,
    	            'filename' => array(
    	                'name' => 'test_file_delete_2.pdf',
    	                'type' => 'application/pdf',
    	                'tmp_name' => $this->tmpFolder . 'test_pdf.tmp',
    	                'error' => '0',
    	                'size' => '16128'
    	            )
    	        )
	        )    
	    );
	    
	    $this->Attachment->saveAll($data['Attachment']);

	    $this->assertTrue(file_exists($this->uploadPath . 'test_file_delete_1.pdf'));
	    $this->assertTrue(file_exists($this->uploadPath . 'test_file_delete_2.pdf'));

	    $this->Attachment->deleteAll(array('post_id' => 3), true, true);

	    $this->assertFalse(file_exists($this->uploadPath . 'test_file_delete_1.pdf'));
	    $this->assertFalse(file_exists($this->uploadPath . 'test_file_delete_2.pdf'));
	}
	
}

?>