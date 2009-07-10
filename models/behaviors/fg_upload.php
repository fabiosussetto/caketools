<?php

App::import('Core', 'Folder');
App::import('Core', 'File');

class FgUploadBehavior extends ModelBehavior {

    var $name = 'FgUpload';
    var $errors = array();
    var $tmpFilePath = null;
    var $fileInfo = array(); // Uploaded file info

    var $_defaultSettings = array(
		'forceFilePresence' => true,
		'allowedMimes' => '*',
		'allowedExt' => array('jpg', 'jpeg', 'gif', 'png', 'pdf', 'doc', 'txt'),
		'allowedSize' => '1',
		'allowedSizeUnit' => 'MB',
		'overwrite' => false,
		'fileField' => 'filename',
		'messages' => array(
		    'filePresence' => 'You must select a file to upload',
		    'fileSize' => 'The file is too big',
		    'fileExt' => 'Extension not allowed',
		    'mimeType' => 'Mime type not allowed'
		)
	);
	
    function setup(&$Model, $config = array()) {
		$this->_defaultSettings[$Model->alias]['baseDir'] = APP;  
       
        $this->settings[$Model->alias] = array_merge($this->_defaultSettings, $config);
    }
    
    function beforeSave (&$Model) {
        if ($this->settings[$Model->alias]['forceFilePresence']) {
            $tmpFilename = $Model->data[$Model->alias]['filename']['name'];
            
            $destFolder = $this->uploadFolder($Model);
            $filename = $this->generateFilename(
                $tmpFilename, $destFolder, $this->settings[$Model->alias]['overwrite']
            );
             
            $destPath = $destFolder . $filename;
            
            $Folder = new Folder();
            $Folder->create($destFolder, '0755');
            
            if (defined('CAKEPHP_UNIT_TEST_EXECUTION')) {
                copy($this->tmpFilePath, $destPath);
            } else {
                move_uploaded_file($this->tmpFilePath, $destPath);    
            }
            
            $this->fileInfo['folder'] = $destFolder;
            $this->fileInfo['filename'] = $filename;
            
            // Set metadata to save in db
            $Model->data[$Model->alias]['filename'] = $filename;
            $Model->data[$Model->alias]['original'] = $destPath;
        }
        
        return true;
    }
    
    function beforeValidate(&$Model) {
        extract($this->settings[$Model->alias]);
        
        if ( isset($Model->data[$Model->alias]['filename']) && 
             !empty($Model->data[$Model->alias]['filename'])
        ) {
            $this->tmpFilePath = $Model->data[$Model->alias]['filename']['tmp_name'];
        }
        
        $rules = array();
        
        if ($forceFilePresence) {
			$rules['filePresence'] = array(
				'rule' => 'validateFilePresence',
				'message' => $messages['filePresence'],
				'last' => true,
				'required' => true,
				'allowEmpty' => false
			);
        }
        
		$rules['fileSize'] = array(
			'rule' => 'validateFileSize',
			'message' => $messages['fileSize'],
			'last' => true
		);
		
		if ($allowedExt != '*') {
    		$rules['fileExt'] = array(
    			'rule' => array('extension', $allowedExt),
    			'message' => $messages['fileExt'],
    			'last' => true
    		);
	    }
	    
	    $rules['mimeType'] = array(
			'rule' => 'validateMimeType',
			'message' => $messages['mimeType'],
			'last' => true
		);
        
        $Model->validate['filename'] = $rules;
        
        return true;
	}
	
	function beforeDelete(&$Model) {
	    $data = $Model->read();
	    unlink($this->uploadFolder($Model) . $data[$Model->alias]['filename']);
	}
	
	/* Validation functions */
	
	function validateFilePresence(&$Model, $fieldData) {
	    if ($fieldData['filename']['error'] == UPLOAD_ERR_NO_FILE) {
	        return false;
	    }
	    
        return true;
	}
	
	function validateFileSize(&$Model, $fieldData) {
	    extract($this->settings[$Model->alias]);
	    
	    switch(strtoupper($allowedSizeUnit)) {
	        case 'KB' : $maxSize = 1024 * $allowedSize; break;
	        case 'MB' : $maxSize = 1024 * 1024 * $allowedSize; break;
	        default : $maxSize = 1024 * $allowedSize; break;
	    }
	    
	    if ($fieldData['filename']['size'] > $maxSize) {
	        return false;
	    }
	    
        return true;
	}
	
	function validateMimeType(&$Model, $fieldData) {
	    /*
	        TODO this is an insecure way to test mime-types, implement a version using pecl finfo or shell command
	    */
        extract($this->settings[$Model->alias]);
        
        if ($allowedMimes == '*' || in_array($fieldData['filename']['type'], $allowedMimes)) {
            return true;
        }    
        
        return false;
	}
	
    /* End validation functions */
    
    function uploadFolder(&$Model) {
        return $this->settings[$Model->alias]['baseDir'] . 'uploaded_' . Inflector::tableize($Model->alias) . DS;
    }
	
    function generateFilename($filename, $destFolder, $overwrite = false) {
        $File = new File($filename);
        $name = $File->name();
        $ext = strtolower($File->ext());
        
        $name = str_replace(array(' ', '-'), array('_','_'), $name);
        $name = ereg_replace('[^A-Za-z0-9_]', '', $name);
        
        $filename = $name . '.' . $ext;
        
        if (!$overwrite) {
            if (file_exists($destFolder . $filename)) {
                $count = 1;
                while (file_exists($destFolder. $name . '_' . $count . '.' . $ext)) {
                   $count++;
                }
                
                $filename = $name . '_' . $count . '.' . $ext;
            }
        }
        
        return $filename;
    }

}

?>