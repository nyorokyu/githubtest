<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CsvImportTemporary extends Model
{
    protected $table = 'csv_import_temporary';
    protected $guarded = ['id'];
}
