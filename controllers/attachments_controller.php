<?php
class AttachmentsController extends AppController {

	var $name = 'Attachments';
	//var $scaffold;
	
	function add() {
	    if (!empty($this->data)) {
	        //debug($this->data);
	        if ($this->Attachment->save($this->data)) {
	            
	        } else{
	            //debug($this->Attachment->validationErrors);
	        }
	    }
	}
}
?>