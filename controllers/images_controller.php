<?php
class ImagesController extends AppController {

	var $name = 'Images';
	
	function index() {
	    /*$new_versions = array(
    	    'thumb' => array(
    	        'processing' => array('adaptiveResize' => array(75, 75)),
    	        'format' => 'jpg',
    	        'options' => array('jpegQuality' => 60)
    	    ),
    	    'medium' => array(
    	        'processing' => array('resize' => array(300, 300)),
    	        'format' => 'jpg',
    	        'options' => array('jpegQuality' => 60)
    	    )
                );*/
	    
	    $data = $this->Image->find('all');
	    //$this->Image->reprocessAll($data, $new_versions);
	    $this->set(compact('data'));
	}
	
	function add() {
	    if (!empty($this->data)) {
	        if ($this->Image->save($this->data)) {
	            $this->Session->setFlash('Modifiche salvate');
	        }
	    }
	}
	
	function testeditor() {
	    if(!empty($this->data)) {
	        debug($this->data, true);
	    }
	}
	
	function edit($id) {
	    if (!empty($this->data)) {
	        if ($this->Image->save($this->data)) {
	            $this->Session->setFlash('Modifiche salvate');
	        }
	    }
	}
}
?>