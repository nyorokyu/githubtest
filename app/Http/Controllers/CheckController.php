<?php

namespace App\Http\Controllers;

use App\CsvExcellentMasterHead;
use App\CsvExcellentMaster;
use Illuminate\Support\Facades\DB;
use Session;
use Request;


class CheckController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth');
  }

  public function index()
  {

    $contentHeadM = Session::get('contentHeadM');
    $contentListM = Session::get('contentListM');

    // SELCTBOX生成
    $selectBoxDate = $this->getCsvMasterTableHeadAll();
    $selectBoxMaster = $this->makeDisplaySelectBox($selectBoxDate);

    //非表示エリア用
    $openAreaSection1 = 'hide';
    $openAreaSection2 = 'hide';
    $openAreaSection3 = 'hide';
    $openAreaSection4 = 'hide';
    $disableOpenAreaSection2 = 'disabled';
    $disableOpenAreaSection3 = 'disabled';

    if(Request::input('openAreaSection1') == 'active') {
      $openAreaSection1 = Request::input('openAreaSection1');
    }
    if(Request::input('openAreaSection2') == 'active') {
      $openAreaSection2 = Request::input('openAreaSection2');
    }
    if(Request::input('disableOpenAreaSection2') == 'enabled') {
      $disableOpenAreaSection2 = Request::input('disableOpenAreaSection2');
    }
    if(Request::input('disableOpenAreaSection3') == 'enabled') {
      $disableOpenAreaSection3 = Request::input('disableOpenAreaSection3');
    }


    // return view('admin.excellent_comparison');
    return view('admin.excellent_comparison',
            compact('contentHeadM', 'contentListM', 'selectBoxMaster', 'openAreaSection1', 'openAreaSection2', 'openAreaSection3', 'openAreaSection4', 'disableOpenAreaSection2', 'disableOpenAreaSection3')
    );

  }





  /*
  |--------------------------------------------------------------------------
  | private function
  |--------------------------------------------------------------------------
  */

  private function getCsvMasterTableHeadAll() {
    $dates = DB::table('csv_excellent_master_head')
              ->select(DB::raw('id, fiscal_year, company_name'))
              ->where('is_deleted', 0)
              ->orderBy('id', 'ASC')
              ->get();
    return $dates;
  }


  private function makeDisplaySelectBox($dates) {
    $list = "";
    foreach($dates as $key => $value) {
      $list .=<<< EOM
      <option value="{$value->id}">{$value->company_name} {$value->fiscal_year}年度</option>
EOM;
    }
    return $list;
  }



}
