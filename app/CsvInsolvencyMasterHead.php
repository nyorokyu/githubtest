<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CsvInsolvencyMasterHead extends Model
{
    protected $table = 'csv_insolvency_master_head';
    protected $guarded = ['id'];

    public function csvInsolvencyMaster() {
      return $this->hasMany('App\CsvInsolvencyMaster', 'master_id');
    }
}
