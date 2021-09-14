<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class QuoteRequestTable extends Model
{
    protected $table = 'quote_request_table';
    protected $guarded = ['id'];

    public function quoteRequestMakeRelationTables() {
      return $this->hasOne('App\QuoteRequestMakeRelationTable','quote_request_id', 'id');
    }

    public function quoteMakeTables() {
      return $this->hasManyThrough('App\QuoteMakeTable', 'App\QuoteRequestMakeRelationTable', 'quote_request_id', 'id', null, 'quote_make_id');
    }
}
