<?php

namespace App\Models;

class Article_helpful_status_model extends Crud_model {

    protected $table = null;

    function __construct() {
        $this->table = 'article_helpful_status';
        parent::__construct($this->table);
    }

}
