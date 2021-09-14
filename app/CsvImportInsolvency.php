<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CsvImportInsolvency extends Model
{
    protected $table = 'csv_import_insolvency_data';
    protected $guarded = ['id'];
}
