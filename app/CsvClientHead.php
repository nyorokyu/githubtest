<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CsvClientHead extends Model
{
    protected $table = 'csv_client_head';
    protected $guarded = ['id'];
}
