<?php  

class ArticlesTagFixture extends CakeTestFixture {
	var $name = 'ArticlesTag';

	var $fields = array(
		'article_id' => array('type' => 'integer', 'null' => false),
		'tag_id' => array('type' => 'integer', 'null' => false),
		'indexes' => array(
		    
		    'UNIQUE_TAG' => array('column'=> array('article_id', 'tag_id'), 'unique'=>1)
		)
	);

	var $records = array(
        array('article_id' => 1, 'tag_id' => 1),
        array('article_id' => 1, 'tag_id' => 2),
        array('article_id' => 5, 'tag_id' => 4)
    );
}

?> 