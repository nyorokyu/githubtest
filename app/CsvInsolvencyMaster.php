<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CsvInsolvencyMaster extends Model
{
    protected $table = 'csv_insolvency_master';
    protected $guarded = ['id'];

    public function csvInsolvencyMasterHead() {
      return $this->belongsTo('App\CsvInsolvencyMasterHead', 'master_id', 'id');
    }
}
