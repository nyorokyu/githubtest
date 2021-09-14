<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CsvExcellentMasterHead extends Model
{
    protected $table = 'csv_excellent_master_head';
    protected $guarded = ['id'];

    public function csvExcellentMaster() {
      return $this->hasMany('App\CsvExcellentMaster', 'master_id');
    }
}
