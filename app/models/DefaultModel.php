<?php

class DefaultModel extends BaseModel
{
	protected $model = 'Categories';
	protected $table = 'categories';
	protected $hasMany = array('Categories', 'categories');	
}