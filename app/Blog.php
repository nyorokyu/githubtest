<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    protected $table = 'blogs';
    protected $guarded = ['id'];
    protected $dates = ['displayed_at'];

    public function blogCat() {
      return $this->belongsTo('App\BlogCat', 'category_id');
    }
}
