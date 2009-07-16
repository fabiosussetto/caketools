<?php

App::import('Core', 'Folder');
App::import('Core', 'File');

class FgUploadBehavior extends ModelBehavior {

    var $name = 'FgUpload';
    var $errors = array();
    var $tmpFilePath = null;
    var $fileInfo = array(); // Uploaded file info
    var $filePresent = false;
   
    var $_defaultSettings = array(
		'forceFilePresence' => true,
		'fileField' => 'filename',
		'allowedMimes' => '*',
		'allowedExt' => array('jpg', 'jpeg', 'gif', 'png', 'pdf', 'doc', 'txt'),
		'allowedSize' => '1',
		'allowedSizeUnit' => 'MB',
		'overwrite' => false,
		'fileField' => 'filename'
	);
	
    function setup(&$Model, $config = array()) {
		$this->_defaultSettings['baseDir'] = APP;
		$this->_defaultSettings['messages'] = array(
		    'filePresence' => __('You must select a file to upload', true),
		    'fileSize' => __('The file is too big', true),
		    'fileExt' => __('Extension not allowed', true),
		    'mimeType' => __('Mime type not allowed', true)
        );
       
        $this->settings[$Model->alias] = array_merge($this->_defaultSettings, $config);
    }
    
    function beforeSave (&$Model) {
        $fileField = $this->settings[$Model->alias]['fileField'];
        
        $this->filePresence($Model);
        
        if ($this->filePresent) {
            $this->tmpFilePath = $Model->data[$Model->alias][$fileField]['tmp_name'];
            $tmpFilename = $Model->data[$Model->alias][$fileField]['name'];
            
            $destFolder = $this->uploadFolder($Model);
            $filename = $this->generateFilename(
                $Model->data[$Model->alias][$fileField]['name'], $destFolder, $this->settings[$Model->alias]['overwrite']
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
            
            // If we are updating, delete previous attachment
            if (!empty($Model->id)) {
                $oldFile = $this->uploadFolder($Model) . $Model->field($fileField);
                if (file_exists($oldFile)) {
                    unlink($oldFile);
                }
            }
            
            // Set metadata to save in db
            $Model->data[$Model->alias][$fileField] = $filename;
        }
        
        if (isset($Model->data[$Model->alias][$fileField]) && is_array($Model->data[$Model->alias][$fileField])) {
            unset($Model->data[$Model->alias][$fileField]);
        }
        
        return true;
    }
    
    function filePresence(&$Model) {
        $fileField = $this->settings[$Model->alias]['fileField'];
        $this->filePresent = false;
        
        if (isset($Model->data[$Model->alias][$fileField])
            && is_array($Model->data[$Model->alias][$fileField])
            && $Model->data[$Model->alias][$fileField]['error'] == 0
        ) {
            $this->filePresent = true;       
        }    
    }
    
    function beforeValidate(&$Model) {
        extract($this->settings[$Model->alias]);
       
        $this->filePresence($Model);
        
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
        
        if ($this->filePresent) {
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
    	}
        
        $Model->validate[$fileField] = $rules;
        
        return true;
	}
	
	function beforeDelete(&$Model) {
	    $data = $Model->read();
	    $file = $this->uploadFolder($Model) . $data[$Model->alias][$this->settings[$Model->alias]['fileField']];
	    
	    if (file_exists($file)) {
	        unlink($file);
	    }
	}
	
	function deleteAttachment(&$Model, $id = null) {
	    if ($id) {
	        $Model->id = $id;
	    }
	    
	    $this->beforeDelete($Model);
	    $Model->saveField($this->settings[$Model->alias]['fileField'], '');
	}
	
	/* Validation functions */
	
	function validateFilePresence(&$Model, $fieldData) {
	    if ($fieldData[$this->settings[$Model->alias]['fileField']]['error'] != 0) {
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
	    
	    if ($fieldData[$fileField]['size'] > $maxSize) {
	        return false;
	    }
	    
        return true;
	}
	
	function validateMimeType(&$Model, $fieldData) {
	    /*
	        TODO this is an insecure way to test mime-types, implement a version using pecl finfo or shell command
	    */
        extract($this->settings[$Model->alias]);
        
        if ($allowedMimes == '*' || in_array($fieldData[$fileField]['type'], $allowedMimes)) {
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