<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MovieCat extends Model
{
    protected $table = 'movie_categories';
    protected $guarded = ['id'];
}
