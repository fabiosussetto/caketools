<?php

App::import('Core', array('AppModel', 'Model'));
require_once(dirname(dirname(__FILE__)) . DS . 'models' . DS . 'models.php');

class TaggableBehaviorTest extends CakeTestCase {
	var $fixtures = array('app.article', 'app.tag', 'app.articles_tag');
	
	function startTest() {
		$this->Article =& ClassRegistry::init('Article');
		$this->Tag =& ClassRegistry::init('Tag');
		
		$this->Tag->bind(array(
			'Article' => array('type' => 'hasAndBelongsToMany')
		));
		
		/*$this->Article->bind(array(
			'Tag' => array('type' => 'hasAndBelongsToMany')
        		));*/
		
		$this->Article->Behaviors->attach('Taggable');
	}
	
	function endTest() {
		unset($this->Article);
		unset($this->Tag);
		ClassRegistry::flush();
	}
    
	function testNewTagsAssociation() {
	    $data = array(
	        'Article' => array(
	            'title' => 'This is a test title',
	            'tags' => 'history science culture'
	        )
	    );
	    
	    $this->Article->save($data, false);
	    $results = $this->Article->read();
        $expected = array('culture', 'history', 'science');
        
	    $this->assertEqual(Set::extract('/Tag/name', $results), $expected);
	    $this->assertEqual($results['Article']['tags'], implode(' ', $expected));
	}
	
	function testPreserveTagsAssociation() {
	    $this->Article->id = 1;
	    $this->Article->read();
	    
	    $this->Article->data['Article']['tags'] = 'nature science history';
	    $this->Article->save();
	    $results = $this->Article->read();
	    
	    $expected = array('history', 'nature', 'science');
        
	    $this->assertEqual(Set::extract('/Tag/name', $results), $expected);
	    $this->assertEqual($results['Article']['tags'], implode(' ', $expected));
	}
	
	function testDifferentSeparator() {
	    $this->Article->Behaviors->attach('Taggable', array('separator' => ', '));
	    
	    $data = array(
	        'Article' => array(
	            'title' => 'This is a test title',
	            'tags' => 'history, science, culture'
	        )
	    );
	    
	    $this->Article->save($data);
	    $results = $this->Article->read();
        $expected = array('culture', 'history', 'science');
        
	    $this->assertEqual(Set::extract('/Tag/name', $results), $expected);
	    $this->assertEqual($results['Article']['tags'], implode(', ', $expected));
	}
	
	function testTagSanitize() {
	    $this->Article->Behaviors->attach('Taggable', array('separator' => ', '));
	    
	    $data = array(
	        'Article' => array(
	            'title' => 'This is a test title',
	            'tags' => ' , history,  test.tag,  science,   culture, multi word, '
	        )
	    );
	    
	    $this->assertTrue($this->Article->save($data));
	    $results = $this->Article->read();
        $expected = array('culture', 'history', 'multi word', 'science', 'test.tag');
       
	    $this->assertEqual(Set::extract('/Tag/name', $results), $expected);
	    $this->assertEqual($results['Article']['tags'], implode(', ', $expected));
	}
	
	function testTagValidation() {
	    $data = array(
	        'Article' => array(
	            'title' => 'This is a test title',
	            'tags' => 'inv@lid science'
	        )
	    );
	    
	    $this->assertFalse($this->Article->save($data));
	}
	
	function testAfterFind() {
	    $results = $this->Article->read(array('id'), 1);
	    $expected = array(
	        'Article' => array('id' => 1, 'tags' => 'nature science'),
	        'Tag' => array(
	            array('id' => 2, 'name' => 'nature'),
	            array('id' => 1, 'name' => 'science')
	        )    
	    );
	    
	    $this->assertEqual($results, $expected);
	}
	
	function testTagSuggetsion() {
	    $this->Article->suggestRemoteTags();
	}
	
	
}

?>