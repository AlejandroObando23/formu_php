<?php

use Jenssegers\Mongodb\Eloquent\Model;

class Singer extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'cantantes';
    protected $guarded = [];
}
