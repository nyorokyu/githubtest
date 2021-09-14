<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Movie extends Model
{
    protected $table = 'movies';
    protected $guarded = ['id'];
    protected $dates = ['displayed_at'];

    public function movieCat() {
      return $this->belongsTo('App\MovieCat', 'category_id');
    }
}
