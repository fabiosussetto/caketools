<?php
class SlugBehavior extends ModelBehavior {

    var $name = 'Slug';
    var $resetting = false;

    var $_defaultSettings = array(
    	'labelField' => 'title',    // field from which generate slug, or array of fields to concatenate
		'slugField' => 'slug',      // field in which save the slug
        'separator' => '-',         // separator used to replace whitespace and other special chars
        'length' => 100,            // max length of the generated slug
        'overwrite' => false        // if false, the slug is not re-generated when updating title
	);

    function setup(&$Model, $config = array()) {
		$this->settings[$Model->alias] = am ($this->_defaultSettings, $config);
		
		if (!is_array($this->settings[$Model->alias]['labelField'])) {
           $this->settings[$Model->alias]['labelField'] = array($this->settings[$Model->alias]['labelField']); 
        }
	}

    function beforeSave(&$Model){
        $return = parent::beforeSave($Model);
        $fields = array();
        
        extract($this->settings[$Model->alias]);
        
        if (empty($Model->id) || $overwrite || $this->resetting) {
            // consider multiple fields to concatenate for slug
            foreach ($labelField as $field) {
                // if field to slug doesn't exist, throw warning
                if ($Model->hasField($field)) {
                    $fields[] = $Model->data[$Model->alias][$field];
                } else {
                    trigger_error(
                        'Slug Behavior: the field "' . $field . '" does not exist in model ' . $Model->alias . ' schema.', 
                        E_USER_WARNING
                    );
                }
            }
            
            $slug = $this->slug($fields, $this->settings[$Model->alias]);
            
            $collisionConditions = array($Model->alias . '.' . $slugField . ' LIKE' => $slug . '%');
            
            if (!empty($Model->id)) {
                $collisionConditions[$Model->alias . '.' . $Model->primaryKey . ' != '] = $Model->id;
            }
            
            // check for collisions
            $collisions = $Model->find('all', array(
                'conditions' => $collisionConditions,
                'recursive' => -1,
                'fields' => array($slugField)
            ));
            
            if (!empty($collisions)) {
                $count = 1;
                $collisions = Set::extract('/' . $Model->alias . '/' . $slugField, $collisions);
                $slugBeginning = $slug;
              
                while (in_array($slug, $collisions)) {
                    $slug = $slugBeginning . $separator . $count;
                    $count++;
                }
            }
            
            $Model->data[$Model->alias][$slugField] = $slug;
        }
            
        return $return;
    }
    
    function slug($fields, $settings){
        if (!is_array($fields)) {
            $input = $fields;
        } else {
            $input = join($settings['separator'], $fields);
        }
        
        if (strlen($input) > $settings['length']) {
			$input = substr($input, 0, $settings['length']);
		}
        return strtolower(Inflector::slug($input, $settings['separator']));
    }
    
    function resetSlugs(&$Model){
        $fields = array();
        extract($this->settings[$Model->alias]);
        
        // tell the behaviour not to consider overwrite setting
        $this->resetting = true;
        
        $data = $Model->find('all', array('recursive' => -1, 'fields' => am($labelField, $Model->primaryKey)));

        foreach($data as $k => $row){
            foreach ($labelField as $field) {
                $fields[] = $row[$Model->alias][$field];
            }
            
            $data[$k][$Model->alias][$slugField] = $this->slug($fields, $this->settings[$Model->alias]);
        }
        
        $Model->saveAll($data, array('fieldList' => array($slugField)));
    }

}

?>