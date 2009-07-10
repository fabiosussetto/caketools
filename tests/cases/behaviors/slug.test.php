<?php

if (!defined('CAKEPHP_UNIT_TEST_EXECUTION')) {
	define('CAKEPHP_UNIT_TEST_EXECUTION', 1);
}

App::import('Core', array('AppModel', 'Model'));
//require_once(dirname(dirname(__FILE__)) . DS . 'models' . DS . 'models.php');

class SlugBehaviorTest extends CakeTestCase {
	var $fixtures = array('app.article');
	
	function startTest() {
		$this->Article =& ClassRegistry::init('Article');
		$this->Article->Behaviors->attach('Slug');
	}
	
	function endTest() {
		unset($this->Article);
		ClassRegistry::flush();
	}
    
	function testSlugSimpleGeneration() {
	    $data = array(
	        'Article' => array(
	            'title' => 'This is a test title'
	        )    
	    );
	    
	    $this->Article->save($data);
	    $results = $this->Article->read();
	    $this->assertEqual($results['Article']['slug'], 'this-is-a-test-title');
	}
	
	function testConfiguration() {
	    $data = array(
	        'Article' => array(
	            'title' => 'This is a test title'
	        )    
	    );
	    
	    $config = array('slugField' => 'permalink', 'separator' => '_');
	    $this->Article->Behaviors->attach('Slug', $config);
	    
	    $this->Article->save($data);
	    $results = $this->Article->read();
	    $this->assertEqual($results['Article']['permalink'], 'this_is_a_test_title');
	}
	
	function testSpecialChars() {
	    $data = array(
	        'Article' => array(
	            'title' => 'Questo è un titolo, sarà così giù perché sì vò!'
	        )    
	    );
	    
	    $this->Article->save($data);
	    $results = $this->Article->read();
	    $this->assertEqual($results['Article']['slug'], 'questo-e-un-titolo-sara-cosi-giu-perche-si-vo');
	}
	
	function testFieldConcatenation() {
	    $data = array(
	        'Article' => array(
	            'title' => 'This is a test title',
	            'category' => 'My category'
	        )    
	    );
	    
	    $config = array('labelField' => array('category', 'title'));
	    $this->Article->Behaviors->attach('Slug', $config);
	    
	    $this->Article->save($data);
	    $results = $this->Article->read();
	    $this->assertEqual($results['Article']['slug'], 'my-category-this-is-a-test-title');
	}
	
	function testSlugLength() {
	    $data = array(
	        'Article' => array(
	            'title' => 'This is a test title',
	        )    
	    );
	    
	    $config = array('length' => 12);
	    $this->Article->Behaviors->attach('Slug', $config);
	    
	    $this->Article->save($data);
	    $results = $this->Article->read();
	    $this->assertEqual($results['Article']['slug'], 'this-is-a-te');
	}
	
	function testCollision() {
	    $data = array(
	        'Article' => array(
	            'title' => 'collision title',
	        )    
	    );
	    
	    $this->Article->save($data);
	    
	    $results = $this->Article->read();
	    $this->assertEqual($results['Article']['slug'], 'collision-title-1');
	    
	    $this->Article->create();
	    $this->Article->save($data);
	    
	    $results = $this->Article->read();
	    $this->assertEqual($results['Article']['slug'], 'collision-title-2');
	}
	
	function testOverwriteFalse() {
	    $this->Article->Behaviors->attach('Slug', array('overwrite' => false));
	    $data = $this->Article->read(null, 5);
	    $data['Article']['title'] = 'A new title';
	    
	    $this->Article->save($data);
	    $results = $this->Article->read();
	   
	    $this->assertEqual($results['Article']['title'], 'A new title');
	    $this->assertEqual($results['Article']['slug'], 'another-test-title');
	}
	
	function testOverwriteTrue() {
	    $this->Article->Behaviors->attach('Slug', array('overwrite' => true));
	    $data = $this->Article->read(null, 5);
	    $data['Article']['title'] = 'A new title';
	    
	    $this->Article->save($data);
	    $results = $this->Article->read();
	   
	    $this->assertEqual($results['Article']['title'], 'A new title');
	    $this->assertEqual($results['Article']['slug'], 'a-new-title');
	}
	
	function testResetSlugs() {
	    $this->Article->resetSlugs();
	    
	    $results = $this->Article->find('all', array(
	        'conditions' => array('Article.id' => array(1, 2, 3, 4)),
	        'fields' => array('slug'), 
	        'recursive' => -1,
	        'order' => 'Article.id ASC'
	    ));
	    
	    $results = Set::extract('/Article/slug', $results);
	    $expected = array('collision-title', 'i-have-no-slug', 'me-too-no-slug', 'me-too-no-slug-1');
	    
	    $this->assertEqual($results, $expected);
	}
	
}

?>