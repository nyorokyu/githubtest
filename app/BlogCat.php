<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BlogCat extends Model
{
    protected $table = 'blog_categories';
    protected $guarded = ['id'];
}
