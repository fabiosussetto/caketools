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
    
    class Article extends CakeTestModel {
    	var $name = 'Article';
    }
    
    class Tag extends CakeTestModel {
    	var $name = 'Tag';
    	
        var $validate = array(
            'name' => array(
                'invalidChars' => array(
                    'rule' => array('custom', '/^[a-z0-9\ \.]{3,}$/i'),
                    'message' => 'Invalid chars'
                )
            )
    	);        
        

    }
?>