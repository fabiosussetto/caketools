<?php
    class Attachment extends CakeTestModel {
    	var $name = 'Attachment';
    	var $belongsTo = array('Post');
  
    }
    
    class Post extends CakeTestModel {
    	var $name = 'Post';
    	var $hasMany = array('Attachment', 'Image');
    	
    	function afterFind($results, $primary = false) {
    	    if ($this->Image->Behaviors->attached('FgImageUpload')) {
    	        $results = $this->Image->Behaviors->FgImageUpload->afterFind($this->Image, $results, $primary);
	        }
	        
    	    return $results;
    	}
    }
    
    class Image extends CakeTestModel {
    	var $name = 'Image';
    	var $belongsTo = array('Post');
    }
?>