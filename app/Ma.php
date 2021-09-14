<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Ma extends Model
{
    protected $table = 'ma_infomations';
    protected $guarded = ['id'];
    protected $dates = ['displayed_at'];

    public function prefecture() {
      return $this->belongsTo('App\Prefecture', 'pref_id');
    }
}
