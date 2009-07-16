<?php

if (!defined('CAKEPHP_UNIT_TEST_EXECUTION')) {
	define('CAKEPHP_UNIT_TEST_EXECUTION', 1);
}

App::import('Core', array('AppModel', 'Model'));
require_once(dirname(dirname(__FILE__)) . DS . 'models' . DS . 'models.php');

class FgUploadBehaviorTest extends CakeTestCase {
	var $fixtures = array('app.attachment', 'app.post', 'app.image');
	var $uploadPath;
	
	function __construct() {
	    parent::__construct();
	    
	    // setting folders
		$this->uploadPath = TESTS . 'uploaded_attachments' . DS;
		$this->tmpFolder = dirname(__FILE__) . DS . 'tmp_files' . DS;
		
		$this->errMsg = array(
		    'filePresence' => 'You must select a file to upload',
		    'fileSize' => 'The file is too big',
		    'fileExt' => 'Extension not allowed',
		    'mimeType' => 'Mime type not allowed'
        );
	}

	function startTest() {
		$this->Attachment =& ClassRegistry::init('Attachment');
		$this->Post =& ClassRegistry::init('Post');
		$this->Attachment->Behaviors->attach('FgUpload', array('baseDir' => TESTS));
		$Folder = new Folder();
		$Folder->delete($this->uploadPath);
		
		// synch fixtures and files
		$Folder = new Folder();
        $Folder->create($this->uploadPath, '0755');
        
		copy($this->tmpFolder . 'sample_file.pdf', $this->uploadPath . 'sample_file.pdf');
	}
	
	function endTest() {
		unset($this->Attachment);
		@unlink($this->uploadPath . 'sample_file.pdf');
		
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
	    
	    $this->assertTrue($this->Attachment->save($data));
	    $this->assertTrue(file_exists($this->uploadPath . 'test_file.pdf'));
	    
	    $results = $this->Attachment->read();
	    $this->assertEqual($results['Attachment']['filename'], 'test_file.pdf');
	}
	
	function testDifferentFileField() {
	    $data = array(
	        'Attachment' => array(
	            'title' => 'test',
	            'document' => array(
	                'name' => 'test_file.pdf',
	                'type' => 'application/pdf',
	                'tmp_name' => $this->tmpFolder . 'test_pdf.tmp',
	                'error' => '0',
	                'size' => '16128'
	            )    
	        )    
	    );
	    
	    $this->Attachment->Behaviors->attach('FgUpload', array('fileField' => 'document'));
	    
	    $this->assertTrue($this->Attachment->save($data));
	    $this->assertTrue(file_exists($this->uploadPath . 'test_file.pdf'));
	    
	    $results = $this->Attachment->read();
	    $this->assertEqual($results['Attachment']['document'], 'test_file.pdf');
	}
	
	function testNoOverwrite() {
	    $data = array(
	        'Attachment' => array(
	            'title' => 'test',
	            'filename' => array(
	                'name' => 'sample_file.pdf',
	                'type' => 'application/pdf',
	                'tmp_name' => $this->tmpFolder . 'test_pdf.tmp',
	                'error' => '0',
	                'size' => '16128'
	            )    
	        )    
	    );
	    
	    $this->assertTrue($this->Attachment->save($data));
	    
	    $this->assertTrue(file_exists($this->uploadPath . 'sample_file.pdf'));
	    $this->assertTrue(file_exists($this->uploadPath . 'sample_file_1.pdf'));
	}
	
	function testOverwrite() {
	    $data = array(
	        'Attachment' => array(
	            'title' => 'test',
	            'filename' => array(
	                'name' => 'sample_file.pdf',
	                'type' => 'application/pdf',
	                'tmp_name' => $this->tmpFolder . 'test_pdf.tmp',
	                'error' => '0',
	                'size' => '16128'
	            )    
	        )    
	    );
	    
	    $this->Attachment->Behaviors->attach('FgUpload', array('overwrite' => true));
	    $this->assertTrue($this->Attachment->save($data));
	   
	    $this->assertTrue(file_exists($this->uploadPath . 'sample_file.pdf'));
	    $this->assertFalse(file_exists($this->uploadPath . 'sample_file_1.pdf'));
	    
	    $results = $this->Attachment->find('all');
	    
	    $this->assertEqual($results[0]['Attachment']['filename'], 'sample_file.pdf');
	    $this->assertEqual($results[1]['Attachment']['filename'], 'sample_file.pdf');
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
	    
	    $this->assertFalse($this->Attachment->save($data));
	    $this->assertEqual($this->Attachment->validationErrors['filename'], $this->errMsg['filePresence']);
	}
	
	function testForceFilePresenceEmpty() {
	    $data = array(
	        'Attachment' => array(
	            'title' => 'test'
	        )    
	    );
	    
	    $this->assertFalse($this->Attachment->save($data));
	    $this->assertEqual($this->Attachment->validationErrors['filename'], $this->errMsg['filePresence']);
	}
	
	function testNoForceFilePresence() {
	    $data = array(
	        'Attachment' => array(
	            'title' => 'test'
	        )    
	    );
	    
	    $this->Attachment->Behaviors->attach('FgUpload', array('forceFilePresence' => false));
	    $this->assertTrue($this->Attachment->save($data));
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
	    
	    $this->assertFalse($this->Attachment->save($data));
	    $this->assertEqual($this->Attachment->validationErrors['filename'], $this->errMsg['fileSize']);
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
	    
	    $this->assertFalse($this->Attachment->save($data));
	    $this->assertEqual($this->Attachment->validationErrors['filename'], $this->errMsg['fileSize']);
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
	    
	    $this->assertFalse($this->Attachment->save($data));
	    $this->assertFalse(file_exists($this->uploadPath . 'test_file.exe'));
	    $this->assertEqual($this->Attachment->validationErrors['filename'], $this->errMsg['fileExt']);
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
	    
	    $this->assertTrue($this->Attachment->save($data));
	    $this->assertTrue(file_exists($this->uploadPath . 'test_file.mp3'));
	}
	
	function testMimeNotAllowed() {
	    $data = array(
	        'Attachment' => array(
	            'title' => 'test',
	            'filename' => array(
	                'name' => 'test_file.pdf',
	                'type' => 'audio/mpeg3',
	                'tmp_name' => $this->tmpFolder . 'test_pdf.tmp',
	                'error' => '0',
	                'size' => '16128'
	            )    
	        )    
	    );
	    
	    $this->Attachment->Behaviors->attach('FgUpload', array(
	        'allowedMimes' => array('application/pdf', 'image/gif')));
	    
	    $this->assertFalse($this->Attachment->save($data));
	    $this->assertFalse(file_exists($this->uploadPath . 'test_file.mp3'));
	    $this->assertEqual($this->Attachment->validationErrors['filename'], $this->errMsg['mimeType']);
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
	    
	    $this->assertTrue($this->Attachment->save($data));
	    $this->assertTrue(file_exists($this->uploadPath . 'test_file.pdf'));
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
	    
	    $results = $this->Post->read();
	    $this->assertEqual($results['Attachment'][0]['filename'], 'test_file.pdf');
	}
	
	function testSaveAllNoFilePresence() {
	    $data = array(
	        'Post' => array(
	            'title' => 'test post'
	        ),
	        'Attachment' => array(
	            0 => array(
    	            'filename' => array(
    	                'name' => '',
    	                'type' => '',
    	                'tmp_name' => '',
    	                'error' => '4',
    	                'size' => '0'
    	            )    
    	        )
	        )    
	    );
	    
	    $this->Attachment->Behaviors->attach('FgUpload', array('forceFilePresence' => false));
	    
	    $this->assertTrue($this->Post->saveAll($data, array('validate' => 'first')));
	}
	
	function testDelete() {
	    $this->Attachment->id = 1;
	    $this->Attachment->delete();
	    
	    $this->assertFalse(file_exists($this->uploadPath . 'sample_file.pdf'));
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
	
	function testReplaceAttachment() {
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
	    
	    $this->assertTrue($this->Attachment->save($data));
	    $this->assertTrue(file_exists($this->uploadPath . 'test_file.pdf'));
	    
	    $new = array(
	        'Attachment' => array(
	            'title' => 'test_edit',
	            'filename' => array(
	                'name' => 'test_pdf_2.pdf',
	                'type' => 'application/pdf',
	                'tmp_name' => $this->tmpFolder . 'test_pdf_2.tmp',
	                'error' => '0',
	                'size' => '16128'
	            )    
	        )        
	    );
	    
	    $this->Attachment->save($new);
	    $results = $this->Attachment->read();
	    
	    $this->assertEqual($results['Attachment']['title'], 'test_edit');
	    $this->assertTrue(file_exists($this->uploadPath . 'test_pdf_2.pdf'));
	    $this->assertFalse(file_exists($this->uploadPath . 'test_file.pdf'));
	}
	
	function testEditMantainingOldFile() {
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
	    
	    $this->Attachment->Behaviors->attach('FgUpload', array('forceFilePresence' => false));
	    
	    $this->assertTrue($this->Attachment->save($data));
	    $this->assertTrue(file_exists($this->uploadPath . 'test_file.pdf'));
	    
	    $new = array(
	        'Attachment' => array(
	            'title' => 'test_edit'
	        )        
	    );
	    
	    $this->assertTrue($this->Attachment->save($new));
	    $results = $this->Attachment->read();
	    
	    $this->assertEqual($results['Attachment']['title'], 'test_edit');
	    $this->assertEqual($results['Attachment']['filename'], 'test_file.pdf');
	    $this->assertTrue(file_exists($this->uploadPath . 'test_file.pdf'));
	}
	
	function testDeleteAttachmentOnlyId() {
	    $this->Attachment->id = 1;
	    $this->Attachment->deleteAttachment();
	    $results = $this->Attachment->read();
	    
	    $this->assertFalse(file_exists($this->uploadPath . 'sample_file.pdf'));
	    $this->assertEqual($results['Attachment']['filename'], '');
	}
	
}

?>