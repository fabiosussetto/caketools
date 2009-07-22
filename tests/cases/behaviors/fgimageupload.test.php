<?php

if (!defined('CAKEPHP_UNIT_TEST_EXECUTION')) {
	define('CAKEPHP_UNIT_TEST_EXECUTION', 1);
}

App::import('Core', array('AppModel', 'Model'));
require_once(dirname(dirname(__FILE__)) . DS . 'models' . DS . 'models.php');

class FgImageUploadBehaviorTest extends CakeTestCase {
	var $fixtures = array('app.image', 'app.attachment', 'app.post');
	var $uploadPath;
	var $errMsg = array();
	
	function __construct() {
	    parent::__construct();
	    
	    // setting folders
		$this->uploadPath = TESTS . 'uploaded_images' . DS;
		$this->tmpFolder = dirname(__FILE__) . DS . 'tmp_files' . DS;
		$this->versionFolder = dirname(__FILE__) . DS . 'versions' . DS;
		$this->uploadVersionPath = $this->versionFolder . 'uploaded_images'. DS;
		
		$this->errMsg = array(
		    'filePresence' => 'You must select a file to upload',
		    'fileSize' => 'The file is too big',
		    'fileExt' => 'Extension not allowed',
		    'mimeType' => 'Mime type not allowed'
        );
	}

	function startTest() {
		$this->Image =& ClassRegistry::init('Image');
		$this->Post =& ClassRegistry::init('Post');
		
		// make sure that there are no extra files
		$Folder = new Folder();
		$Folder->delete($this->uploadPath);
		$Folder->delete($this->uploadVersionPath);
		
		// synch fixtures and files
		$Folder = new Folder();
        $Folder->create($this->uploadPath, '0755');
        $Folder->create($this->uploadVersionPath, '0755');
        
		copy($this->tmpFolder . 'my_image.jpg', $this->uploadPath . 'my_image.jpg');
		copy($this->tmpFolder . 'my_image.jpg', $this->uploadVersionPath . 'thumb_my_image.jpg');
		copy($this->tmpFolder . 'my_image.jpg', $this->uploadVersionPath . 'medium_my_image.png');
		
		// setup versions and error messages
		$versions = array(
    	    'thumb' => array(
	            'processing' => array(
	                'adaptiveResize' => array(100, 100),
	                'rotateImage' => array('CW')
	            ),
	            'format' => 'jpg',
	            'options' => array('jpegQuality' => 70)
    	    ),
    	    'medium' => array(
	            'processing' => array(
	                'adaptiveResize' => array(300, 100)
	            ),
	            'format' => 'png'
    	    )    
	    );
		
		// attaching behaviour
		$this->Image->Behaviors->attach('FgImageUpload', array(
		    'baseDir' => TESTS,
    		'versionBaseDir' => $this->versionFolder,
    		'versions' => $versions
	    ));
	    
	    // manage model relations
	    $this->Image->belongsTo = array();
	}
	
	function endTest() {
		unset($this->Image);
		
		@unlink($this->uploadPath . 'my_image.jpg');
		@unlink($this->uploadVersionPath . 'thumb_my_image.jpg');
		@unlink($this->uploadVersionPath . 'medium_my_image.png');
		
		$Folder = new Folder();
        $Folder->delete($this->uploadPath);
        $Folder->delete($this->uploadVersionPath);
		ClassRegistry::flush();
	}
    
	function getDataSingleWithVersions() {
	    $data = array(
	        'Image' => array(
	            'title' => 'test',
	            'filename' => array(
	                'name' => 'test_image.jpg',
	                'type' => 'image/jpeg',
	                'tmp_name' => $this->tmpFolder . 'test_image.tmp',
	                'error' => '0',
	                'size' => '102400'
	            )    
	        )    
	    );
	    
	    $versions = array(
    	    'thumb' => array(
	            'processing' => array(
	                'adaptiveResize' => array(100, 100),
	                'rotateImage' => array('CW')
	            ),
	            'format' => 'jpg',
	            'options' => array('jpegQuality' => 70)
    	    ),
    	    'medium' => array(
	            'processing' => array(
	                'adaptiveResize' => array(300, 100)
	            ),
	            'format' => 'png'
    	    )    
	    );
	
	    $this->Image->Behaviors->attach('FgImageUpload', array(
	        'versionBaseDir' => $this->versionFolder,
    	        'versions' => $versions
	    ));
	    
	    return $data;
	}
	
    function getDataMultipleWithVersions() {
        $data = array(
	        'Image' => array(
	            0 => array(
    	            'title' => 'test 1',
    	            'post_id' => 3,
	                'filename' => array(
    	                'name' => 'test_image_1.jpg',
    	                'type' => 'image/jpeg',
    	                'tmp_name' => $this->tmpFolder . 'test_image.tmp',
    	                'error' => '0',
    	                'size' => '102400'
	                )    
    	        ),
    	        1 => array(
    	            'title' => 'test 2',
    	            'post_id' => 3,
	                'filename' => array(
    	                'name' => 'test_image_2.jpg',
    	                'type' => 'image/jpeg',
    	                'tmp_name' => $this->tmpFolder . 'test_image.tmp',
    	                'error' => '0',
    	                'size' => '102400'
	                )    
    	        )
	        )    
	    );
	    
	    $versions = array(
    	    'thumb' => array(
	            'processing' => array(
	                'adaptiveResize' => array(100, 100),
	                'rotateImage' => array('CW')
	            ),
	            'format' => 'jpg',
	            'options' => array('jpegQuality' => 70)
    	    ),
    	    'medium' => array(
	            'processing' => array(
	                'adaptiveResize' => array(300, 100)
	            ),
	            'format' => 'png'
    	    )    
	    );
	
	    $this->Image->Behaviors->attach('FgImageUpload', array(
	        'versionBaseDir' => $this->versionFolder,
    	    'versions' => $versions
	    ));
	    
	    return $data;
    }
	
	function testSingleUpload() {
	    $data = array(
	        'Image' => array(
	            'title' => 'test',
	            'filename' => array(
	                'name' => 'test_image.jpg',
	                'type' => 'image/jpeg',
	                'tmp_name' => $this->tmpFolder . 'test_image.tmp',
	                'error' => '0',
	                'size' => '102400'
	            )    
	        )    
	    );
	    
	    $this->Image->save($data);
	    $this->assertTrue(file_exists($this->uploadPath . 'test_image.jpg'));
	    
	    $results = $this->Image->read();
	    $this->assertEqual($results['Image']['filename'], 'test_image.jpg');
	}
	
    
	function testInvalidImageMime() {
	    $data = array(
	        'Image' => array(
	            'title' => 'test',
	            'filename' => array(
	                'name' => 'test_image.jpg',
	                'type' => 'application/pdf',
	                'tmp_name' => $this->tmpFolder . 'test_image.tmp',
	                'error' => '0',
	                'size' => '102400'
	            )    
	        )    
	    );
	    
	    $this->Image->save($data);
	    $this->assertFalse(file_exists($this->uploadPath . 'test_image.jpg'));
	    $this->assertEqual($this->Image->validationErrors['filename'], $this->errMsg['mimeType']);
	}
	
	function testInvalidImageExt() {
	    $data = array(
	        'Image' => array(
	            'title' => 'test',
	            'filename' => array(
	                'name' => 'test_image.pdf',
	                'type' => 'image/jpeg',
	                'tmp_name' => $this->tmpFolder . 'test_image.tmp',
	                'error' => '0',
	                'size' => '102400'
	            )    
	        )    
	    );
	    
	    $this->Image->save($data);
	    $this->assertFalse(file_exists($this->uploadPath . 'test_image.pdf'));
	    $this->assertEqual($this->Image->validationErrors['filename'], $this->errMsg['fileExt']);
	}
	
	function testVersions() {
	    $data = $this->getDataSingleWithVersions();

	    $this->assertTrue($this->Image->save($data));
	    $this->assertTrue(file_exists($this->uploadVersionPath . 'thumb_test_image.jpg'));
	    $this->assertTrue(file_exists($this->uploadVersionPath . 'medium_test_image.png'));
	    
	    $results = $this->Image->read();
	    $this->assertEqual($results['Image']['thumb'], '/uploaded_images/thumb_test_image.jpg');
	    $this->assertEqual($results['Image']['medium'], '/uploaded_images/medium_test_image.png');
	}
	

	function testSaveAndDeleteAll() {
	    $data = $this->getDataMultipleWithVersions();
	    
	    $this->assertTrue($this->Image->saveAll($data['Image']));
	    $this->assertTrue(file_exists($this->uploadPath . 'test_image_1.jpg'));
	    $this->assertTrue(file_exists($this->uploadPath . 'test_image_2.jpg'));
	    
	    $this->assertTrue(file_exists($this->uploadVersionPath . 'thumb_test_image_1.jpg'));
	    $this->assertTrue(file_exists($this->uploadVersionPath . 'medium_test_image_1.png'));
	    $this->assertTrue(file_exists($this->uploadVersionPath . 'thumb_test_image_2.jpg'));
	    $this->assertTrue(file_exists($this->uploadVersionPath . 'medium_test_image_2.png'));

	    $this->Image->deleteAll(array('Image.title LIKE' => '%test%'), true, true);

	    $this->assertFalse(file_exists($this->uploadPath . 'test_image_1.jpg'));
	    $this->assertFalse(file_exists($this->uploadPath . 'test_image_2.jpg'));
	    
	    $this->assertFalse(file_exists($this->uploadVersionPath . 'thumb_test_image_1.jpg'));
	    $this->assertFalse(file_exists($this->uploadVersionPath . 'medium_test_image_1.png'));
	    $this->assertFalse(file_exists($this->uploadVersionPath . 'thumb_test_image_2.jpg'));
	    $this->assertFalse(file_exists($this->uploadVersionPath . 'medium_test_image_2.png'));
	    
	}
	
	function testImageReplacement() {
	    $data = array(
	        'Image' => array(
	            'title' => 'test',
	            'filename' => array(
	                'name' => 'test_image.jpg',
	                'type' => 'image/jpeg',
	                'tmp_name' => $this->tmpFolder . 'test_image.tmp',
	                'error' => '0',
	                'size' => '102400'
	            )    
	        )    
	    );
	    
	    $versions = array(
    	    'thumb' => array(
	            'processing' => array(
	                'adaptiveResize' => array(100, 100),
	                'rotateImage' => array('CW')
	            ),
	            'format' => 'jpg',
	            'options' => array('jpegQuality' => 70)
    	    )
	    );
	
	    $this->Image->Behaviors->attach('FgImageUpload', array(
	        'versionBaseDir' => $this->versionFolder,
    	        'versions' => $versions
	    ));
	    
	    $this->Image->save($data);
	    
	    $this->assertTrue(file_exists($this->uploadPath . 'test_image.jpg'));
	    $this->assertTrue(file_exists($this->uploadVersionPath . 'thumb_test_image.jpg'));
	    
	    $newData = $this->Image->read();  
	    
	    unset($newData['Image']['Versions']);
	    $newData['Image']['filename'] = array(
                'name' => 'another_image.jpg',
                'type' => 'image/jpeg',
                'tmp_name' => $this->tmpFolder . 'another_image.tmp',
                'error' => '0',
                'size' => '102400'
            );
        
        $this->Image->save($newData);
	    $this->assertEqual($this->Image->field('filename'), 'another_image.jpg');
	    
	    $this->assertTrue(file_exists($this->uploadPath . 'another_image.jpg'));
	    $this->assertTrue(file_exists($this->uploadVersionPath . 'thumb_another_image.jpg'));
	    $this->assertFalse(file_exists($this->uploadVersionPath . 'thumb_test_image.jpg.jpg'));
	}
	
    function testAfterFind() {
	    $results = $this->Image->findById(1);
	    $dir = '/uploaded_images/';

	    $this->assertEqual($results['Image']['thumb'], $dir . 'thumb_my_image.jpg');
	    $this->assertEqual($results['Image']['medium'], $dir . 'medium_my_image.png');
	}
	
	function testFindList() {
	    $results = $this->Image->find('list');
	    $this->assertEqual($results[1], 'My image');
	}
	
	function testReprocess() {
	    $new_versions = array(
    	    'newversion' => array(
	            'processing' => array(
	                'adaptiveResize' => array(200, 200)
	            )
    	    )
	    );
	    
	    $this->Image->id = 1;
	    $this->Image->reprocess($new_versions);
	    
	    $this->assertTrue(file_exists($this->uploadVersionPath . 'newversion_my_image.jpg'));
	    $this->assertFalse(file_exists($this->uploadVersionPath . 'thumb_my_image.jpg'));
	    $this->assertFalse(file_exists($this->uploadVersionPath . 'medium_my_image.png'));
	}
	
	function testAfterFindFromRelated() {
	    $results = $this->Post->find('all');
	    $this->assertEqual($results[0]['Image'][0]['thumb'], '/uploaded_images/thumb_my_image.jpg');
	    $this->assertEqual($results[0]['Image'][0]['medium'], '/uploaded_images/medium_my_image.png');
	}
}

?>