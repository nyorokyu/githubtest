<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CsvInsolvencyClient extends Model
{
    protected $table = 'csv_insolvency_client';
    protected $guarded = ['id'];

    public function csvInsolvencyClientHead() {
      return $this->belongsTo('App\CsvInsolvencyClientHead', 'clinrt_id', 'id');
    }
}
