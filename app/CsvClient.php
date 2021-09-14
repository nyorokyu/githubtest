<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CsvClient extends Model
{
    protected $table = 'csv_client';
    protected $guarded = ['id'];
}
