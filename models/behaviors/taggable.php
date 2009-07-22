<?php
class TaggableBehavior extends ModelBehavior {

    var $name = 'Taggable';
    var $tags;
    
    var $_defaultSettings = array(
    	'tagField' => 'tags',    
		'tagModel' => 'Tag',
		'tagNameField' => 'name',
        'separator' => ' '      
	);

    function setup(&$Model, $config = array()) {
		$this->settings[$Model->alias] = am ($this->_defaultSettings, $config);
		extract($this->settings[$Model->alias]);
		
		// set up model relationship
        $Model->bindModel(
            array('hasAndBelongsToMany' => array(
                $tagModel => array('order' => $tagModel . '.' . $tagNameField . ' ASC')
            )), false
        );
	}

    function beforeSave(&$Model){
        extract($this->settings[$Model->alias]);
        $return = parent::beforeSave($Model);
        
        $this->tags = explode($separator, $Model->data[$Model->alias][$tagField]);
        unset($Model->data[$Model->alias][$tagField]);
        
        $newTags = $assoc = $lastInserted = array();
        $assoc = array();
        
        //find new tags
        $savedTags = $Model->{$tagModel}->find('all', array(
            'recursive' => -1, 
            'fields' => array($Model->{$tagModel}->primaryKey, $tagNameField)
        ));
        
        $savedTags = Set::combine($savedTags, '/Tag/id', '/Tag/name');
        
        foreach ($this->tags as $k => $tag) {
            //sanitize the tag to avoid extra whitespaces, empty tags, etc.
            $tag = trim($tag);

            if (empty($tag) || strlen($tag) < 2) {
                unset($this->tags[$k]);
                continue;
            }
            
            //validate each tag
            $Model->{$tagModel}->set(array($tagModel => array($tagNameField => $tag)));
            
            if (!$Model->{$tagModel}->validates()) {
                return false;
            }
            
            $this->tags[$k] = $tag;
            
            if (!in_array($tag, $savedTags)) {
                $newTags[] = array($tagNameField => $tag);
                unset($this->tags[$k]);
            }
        }
        
        //save new tags before associating them
        if (!empty($newTags)) {
            $data = array($tagModel => $newTags);
            $Model->{$tagModel}->saveAll($data[$tagModel]);
            
            // grab last inserted tag ids
            $lastInserted = $Model->{$tagModel}->find('all', array(
                'recursive' => -1,
                'fields' => array($Model->{$tagModel}->primaryKey),
                'conditions' => array($tagNameField => Set::extract('/name', $newTags))    
            ));
            
        }
        
        //find already present tag ids for association
        $existingTags = $Model->{$tagModel}->find('all', array(
            'recursive' => -1,
            'fields' => array($Model->{$tagModel}->primaryKey, $tagNameField),
            'conditions' => array($tagNameField => $this->tags)    
        ));
        
        $Model->data[$tagModel] = array(
            $tagModel => Set::extract('/Tag/id', array_merge($existingTags, $lastInserted))    
        );
       
        return $return;
    }
    
    function afterFind(&$Model, $results, $primary = false) { 
        extract($this->settings[$Model->alias]);
        
        foreach ($results as $k => $row) {
            if (isset($row[$tagModel])) {
                $results[$k][$Model->alias][$tagField] = 
                    implode($separator, Set::extract('/name', $row[$tagModel]));
            }
        }
        
        return $results;
    }
    
    function suggestRemoteTags() {
        $text = "L'igiene del cane è fondamentale per il suo ma anche per il nostro benessere che gli 
        stiamo vicini. Per questo ci sono una serie di attenzioni ed operazioni che vanno eseguite con regolarità.";
        
        $text = urlencode($text);
        $url = "http://tagthe.net/api/?text=" . $text;
        debug($url);
        $c = curl_init();
        curl_setopt($c, CURLOPT_URL, $url);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        $xmlContent = trim(curl_exec($c));
        curl_close($c);
        
        $xml = simplexml_load_string($xmlContent);

        debug($xml);
    }
}

?>