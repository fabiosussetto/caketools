<?php

require_once('fg_upload.php');

App::import('Vendor', 'PhpThumbFactory', array('file' => 'phpthumb/ThumbLib.inc.php'));

class FgImageUploadBehavior extends FgUploadBehavior {

	var $name = 'ImageUpload';
	var $findQueryType;

	var $imageTypes = array(1 => 'gif', 'jpeg', 'png', 'swf', 'psd', 'wbmp', 'jpeg' => 'jpeg', 'jpg' => 'jpeg');

	function setup (&$Model, $config = array()) {
		$this->_defaultSettings['allowedMimes'] = array(
		    'image/jpeg', 'image/gif', 'image/png', 'image/pjpeg', 'image/x-png'
		);
		
		$this->_defaultSettings['allowedExt'] = array('jpeg', 'jpg', 'gif', 'png');
		$this->_defaultSettings['versionBaseDir'] = WWW_ROOT;
		$this->_defaultSettings['folderName'] = 'uploaded_' . Inflector::tableize($Model->alias);
		$this->_defaultSettings['versions'] = array();

		parent::setup($Model, $config);
	}

    function beforeDelete(&$Model) {
        $data = $Model->read();
        $filename = $data[$Model->alias]['filename'];
        $this->deleteVersions($Model, $filename);
        
        parent::beforeDelete($Model);
    }
    
    function deleteVersions(&$Model, $filename) {
        foreach ($this->settings[$Model->alias]['versions'] as $version => $rules) {
            $ext = null;
            if (isset($rules['format'])) {
                $ext = $rules['format'];
            }
            
            $path = $this->versionFolder($Model) . $this->generateVersionFilename($filename, $version, $ext);
          
            if (file_exists($path)) {
                unlink($path);
            }
        }
    }

    function beforeSave(&$Model) {
        $return = parent::beforeSave($Model);

        $destFolder = $this->versionFolder($Model);

        $Folder = new Folder();
        $Folder->create($destFolder, '0755');
        
        $uploadedFile = $this->fileInfo['folder'] . $this->fileInfo['filename'];
        
        // if we are replacing the file, clear previous versions
        if (!empty($Model->id)) {
            $this->deleteVersions($Model, $Model->field('filename'));
        }
        
        $this->saveVersion($Model, $uploadedFile);
        
        return $return;
    }

    function saveVersion(&$Model, $original, $versions = null) {
        if (!$versions) {
            $versions = $this->settings[$Model->alias]['versions'];
        }

        foreach ($versions as $version => $rules) {
            $thumb = PhpThumbFactory::create($original);
            
            if (isset($rules['options'])) {
                $thumb->setOptions($rules['options']);
            } 
            
            foreach ($rules['processing'] as $operation => $params) {
                call_user_func_array(array($thumb, $operation), $params);
            }
            
            $format = null;
            if (isset($rules['format'])) {
                $format = $rules['format'];
            }
            
            $saveTo = $this->versionFolder($Model) . $this->generateVersionFilename($original, $version, $format);
            $thumb->save($saveTo, $format);
        }    
    }
    
    function beforeValidate(&$Model) {
        return parent::beforeValidate($Model);
    }
    
    function beforeFind(&$Model) {
        $this->findQueryType = $Model->findQueryType;
    }
    
    function versionData(&$Model, $original) {
        $versions = array();
        
        foreach ($this->settings[$Model->alias]['versions'] as $version => $rules) {
            $format = null;
            if (isset($rules['format'])) {
                $format = $rules['format'];
            }
            
            $versions[$version] = 
                $this->absoluteToRel($Model, $this->versionFolder($Model) . 
                $this->generateVersionFilename($original, $version, $format));
        }
        
        return $versions;
    }
    
    function afterFind(&$Model, $results, $primary = false) { 
        if (!empty($results) && $this->findQueryType != 'list' && $this->findQueryType != 'count') {
            foreach ($results as $n => $result) {
                $versions = array();
                $hasMany = true;
                
                // Make all results structures similar to simplify result manipulation
                if (!isset($result[$Model->alias][0])) {
                    $hasMany = false;
                    $result[$Model->alias] = array($result[$Model->alias]);
                }
             
                foreach ($result[$Model->alias] as $k => $row) {
                    if (isset($row['original'])) {
                        $result[$Model->alias][$k] = am($row, $this->versionData($Model, $row['original']));
                    }
                }
                
                // if necessary, revert to previous results structure
                if (!$hasMany) {
                    $result[$Model->alias] = $result[$Model->alias][0];
                }
                
                // append versions to real results
                $results[$n] = $result;
            }
        }  
        
        $this->findQueryType = null;
        return $results;
    }
    
    /*function afterFind(&$Model, $results, $primary = false) { 
        if (!empty($results) && $this->findQueryType != 'list' && $this->findQueryType != 'count') {
            foreach ($results as $n => $result) {
                $versions = array();
                if (is_array($result[$Model->alias][0])) {
                    foreach ($result[$Model->alias][0] as $k => $row) {
                        if (isset($row['original'])) {
                            $results[$n][$Model->alias][]['Versions'] = $this->versionData($Model, $row['original']);
                        }
                    }
                } else {
                    
                }
            }
        }  
        
        $this->findQueryType = null;
        return $results;
    }*/
    
    function reprocess(&$Model, $newVersions = null, $id = null) {
        if (!$Model->id) {
            $Model->id = $id;
        }
        
        $data = $Model->read();
        $original = $this->uploadFolder($Model) . $data[$Model->alias]['filename'];
        
        $this->deleteVersions($Model, $original);
        $this->saveVersion($Model, $original, $newVersions);
    }
    
    function reprocessAll(&$Model, $data, $newVersions) {
        foreach ($data as $row) {
            $original = $this->uploadFolder($Model) . $row[$Model->alias]['filename'];
            $this->deleteVersions($Model, $original);
            $this->saveVersion($Model, $original, $newVersions);
        }
    }
    
    function versionFolder(&$Model) {
        return $this->settings[$Model->alias]['versionBaseDir'] . $this->settings[$Model->alias]['folderName'] . DS;
    }
    
    function generateVersionFilename($original, $version, $ext = null) {
        $path_info = pathinfo($original);
        
        if ($ext) {
            $name = $path_info['filename'] . '.' . $ext; 
        } else {
            $name = $path_info['filename'] . '.' . $path_info['extension'];
        }
        
        return $version . '_' . $name;
    }
    
    function absoluteToRel(&$Model, $absolute_path){
        $rel_path = str_replace($this->settings[$Model->alias]['versionBaseDir'], '/', $absolute_path);
        $rel_path = str_replace(DS, '/', $rel_path);
        
        return $rel_path;
    }
}
?>