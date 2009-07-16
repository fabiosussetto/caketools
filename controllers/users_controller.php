<?php
class UsersController extends AppController {

	var $name = 'Users';
	
	function beforeFilter() {
	    parent::beforeFilter();
	    $this->Auth->authorize = 'controller';
        $this->Auth->allow('*');
    }
    
    function isAuthorized() {
        /*if ($this->action == 'delete') {
            if ($this->Auth->user('role') == 'admin') {
                return true;
            } else {
                return false;
            }
        }*/
        return true;
    }
	
	function add() {
	    if (!empty($this->data)) {
	        if ($this->User->save($this->data)) {
	            $this->Session->setFlash('Utente salvato');
	            $this->redirect(array('action' => 'index'));
	        }
	    }
	}
	
	function login() {
	
	}
	
	function logout() {
        $this->redirect($this->Auth->logout());
    }
    
    function index() {
        $data = $this->paginate();
        $this->set(compact('data'));
    }
    
    function delete($id) {
        $this->User->delete($id);
        $this->Session->setFlash('Utente cancellato');
        $this->redirect($this->referer());
    }

}
?>