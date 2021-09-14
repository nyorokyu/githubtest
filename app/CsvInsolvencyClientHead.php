<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CsvInsolvencyClientHead extends Model
{
    protected $table = 'csv_insolvency_client_head';
    protected $guarded = ['id'];

    public function csvInsolvencyClient() {
      return $this->hasMany('App\CsvInsolvencyClient', 'clinrt_id');
    }
}
