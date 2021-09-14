<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class QuoteRequestMakeRelationTable extends Model
{
    protected $table = 'quote_request_make_relation_table';
    protected $guarded = ['id'];

    public function quoteRequestTables() {
      return $this->hasOne('App\QuoteRequestTable','id', 'quote_request_id');
    }

    public function quoteMakeTables() {
      return $this->hasOne('App\QuoteMakeTable','id', 'quote_make_id');
    }

}
