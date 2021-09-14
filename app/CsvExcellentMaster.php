<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CsvExcellentMaster extends Model
{
    protected $table = 'csv_excellent_master';
    protected $guarded = ['id'];

    public function csvExcellentMasterHead() {
      return $this->belongsTo('App\CsvExcellentMasterHead', 'master_id', 'id');
    }
}
