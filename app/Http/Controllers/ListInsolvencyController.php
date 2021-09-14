<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\CsvInsolvencyMasterHead;
use App\CsvInsolvencyMaster;
use Illuminate\Support\Facades\DB;
use Config;



class ListInsolvencyController extends Controller
{

  public function __construct()
  {
    $this->middleware('auth');
  }



  public function index()
  {
    // 全マスタ
    $dateList = $this->getInsolvencyMasterHighRank();
    $contentListAll = $this->makeDisplayListAll($dateList);

    // 登録された各マスタ
    $arrayList = array();
    $dataMstHead = $this->getInsolvencyMasterHead();
    $sort = 'desc'; // 初期値は減少率の降順
    $arrayList = $this->makeArrayList($dataMstHead, $sort);

    return view('admin.list_insolvency_master', compact('contentListAll', 'arrayList'));
  }



  public function destroy(Request $request) {

    if (isset($_POST['delete'])) {
      $mastarId = $request->input('delete');
      CsvInsolvencyMasterHead::where('id', $mastarId)->delete();
      CsvInsolvencyMaster::where('master_id', $mastarId)->delete();

      $wording = Config::get('consts.wording.WORDING_DELETE');
      return redirect()->route('admin.list_insolvency_master.index')->with('success', $wording);
    }

  }


  public function store(Request $request) {

    if (isset($_POST['sort_asc'])) {
      $mastarId = $request->input('sort_asc');

      // 全マスタ
      $dateList = $this->getInsolvencyMasterHighRank();
      $contentListAll = $this->makeDisplayListAll($dateList);

      // 登録された各マスタ
      $arrayList = array();
      $dataMstHead = $this->getInsolvencyMasterHead();
      $sort = 'asc';
      $arrayList = $this->makeArrayList($dataMstHead, $sort, $mastarId);

    } elseif (isset($_POST['sort_desc'])) {
      $mastarId = $request->input('sort_desc');

      // 全マスタ
      $dateList = $this->getInsolvencyMasterHighRank();
      $contentListAll = $this->makeDisplayListAll($dateList);

      // 登録された各マスタ
      $arrayList = array();
      $dataMstHead = $this->getInsolvencyMasterHead();
      $sort = 'desc';
      $arrayList = $this->makeArrayList($dataMstHead, $sort, $mastarId);
    }
    $areaName = json_encode($request->area_name);
    return view('admin.list_insolvency_master', compact('contentListAll', 'arrayList', 'areaName'));
    // return redirect('/admin/list_insolvency_master')->with(compact('contentListAll', 'arrayList'));


  }







  /*
  |--------------------------------------------------------------------------
  | private function
  |--------------------------------------------------------------------------
  */

  private function getItemName($blCode) {
    $itemName = DB::table('csv_insolvency_master')
              ->where([['is_deleted', 0], ['bl_code', $blCode]])
              ->value('item_name');
    return $itemName;
  }


  private function getInsolvencyMasterHighRank() {
    $sqlTmp = DB::table('csv_insolvency_master')
              // ->select(DB::raw('master_id, bl_code, SUM(quantity) AS quantity, SUM(occurrence_count) AS occurrence_count'))
              ->select(DB::raw('master_id, bl_code, SUM(quantity) AS quantity, SUM(decrease_rate) AS decrease_rate'))
              ->whereRaw('is_deleted = 0 AND SUBSTRING(bl_code, 1, 2) != 99')
              ->groupBy('master_id', 'bl_code')
              // ->orderByRaw('SUM(occurrence_count) DESC')
              ->orderByRaw('SUM(decrease_rate) DESC')
              ->toSql();

    $datas = DB::table(DB::raw('('.$sqlTmp.') AS temp'))
              ->select(DB::raw('bl_code, COUNT(bl_code) AS cnt'))
              ->groupBy('bl_code')
              ->orderByRaw('COUNT(bl_code) DESC')
              ->limit(20)
              ->get();
    return $datas;
  }


  private function getInsolvencyMasterHead() {
    $datas = DB::table('csv_insolvency_master_head')
              ->select(DB::raw('id, fiscal_year, registration_name, memo'))
              ->where('is_deleted', 0)
              ->orderBy('id', 'ASC')
              ->get();
    return $datas;
  }


  private function getInsolvencyMasterDetail($id, $sort) {
    if ($sort == 'desc' || empty($sort)) {
      $datas = DB::table('csv_insolvency_master')
                ->select(DB::raw('bl_code, item_name, quantity, decrease_rate'))
                ->where([['is_deleted', 0], ['master_id', $id]])
                ->whereRaw('SUBSTRING(bl_code, 1, 2) != 99')
                ->orderBy('decrease_rate', 'DESC')
                ->limit(30)
                ->get();
    } elseif ($sort == 'asc') {
      $sqlTmp = DB::table('csv_insolvency_master')
                ->select(DB::raw('bl_code, item_name, quantity, decrease_rate'))
                ->whereRaw('is_deleted = 0 AND master_id = ' . $id)
                ->whereRaw('SUBSTRING(bl_code, 1, 2) != 99')
                ->orderBy('decrease_rate', 'DESC')
                ->limit(30)
                ->toSql();

      $datas = DB::table(DB::raw('('.$sqlTmp.') AS temp'))
                ->select(DB::raw('bl_code, item_name, quantity, decrease_rate'))
                ->orderBy('decrease_rate', 'ASC')
                ->get();
    }
    return $datas;
  }


  private function getSumQuantity($blCode) {
    $sumQuantity = DB::table('csv_insolvency_master')
              ->where([['is_deleted', 0], ['bl_code', $blCode]])
              ->sum('quantity');
    return $sumQuantity;
  }


  private function makeDisplayListAll($datas) {
    $content = "";
    $content .=<<< EOM
    <table class="table content-margin-ss">
      <thead>
        <tr>
          <th>品名</th>
        </tr>
      </thead>
      <tbody>
EOM;
    foreach($datas as $key => $value) {
      $itemName = $this->getItemName($value->bl_code);
      $content .=<<< EOM
      <tr>
        <td>{$itemName}</td>
      </tr>
EOM;
    }
    $content .=<<< EOM
      </tbody>
    </table>
EOM;
    return $content;
  }



  private function makeArrayList($dataMstHead, $sort, $mastarId = '') {
    $arrayList = array();

    foreach($dataMstHead as $keyMstHead => $valueMstHead) {
      if ($valueMstHead->id == $mastarId) {
        $dataMstDetail = $this->getInsolvencyMasterDetail($valueMstHead->id, $sort);
      } else {
        $dataMstDetail = $this->getInsolvencyMasterDetail($valueMstHead->id, 'desc');
      }
      foreach($dataMstDetail as $key => $value) {
        $arrayTemp = [];
        $quantity = 0;
        // $sumQuantity = $this->getSumQuantity($value->bl_code);
        if (empty($value->quantity)) {
          $quantity = 0;
          $decreaseRate = 0;
        } else {
          $quantity = $value->quantity;
          $decreaseRate = ($value->decrease_rate) * 100;
        }
        $arrayTemp = [
          'master_id' => $valueMstHead->id,
          'fiscal_year' => $valueMstHead->fiscal_year,
          'registration_name' => $valueMstHead->registration_name,
          'memo' => $valueMstHead->memo,
          'item_name' => $value->item_name,
          'quantity' => $value->quantity,
          'decrease_rate' => $decreaseRate,
          'sort' => $sort,
        ];
        $arrayList = array_merge_recursive($arrayList, $arrayTemp);
      }
    }

    return $arrayList;

  }







}
