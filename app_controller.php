<?php
class AppController extends Controller {
    var $helpers = array('Html', 'Form', 'Javascript');
    var $components = array('Session', 'Auth', 'DebugKit.Toolbar');
    
}
?>