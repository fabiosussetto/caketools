<?php

class SimpleBehavior extends ModelBehavior {

    var $name = 'Simple';

    function beforeSave (&$Model) {
        //debug($Model->data);
    }

}

?>