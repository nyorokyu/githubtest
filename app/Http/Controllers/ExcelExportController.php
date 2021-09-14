<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// use Goodby\CSV\Import\Standard\Lexer;
// use Goodby\CSV\Import\Standard\Interpreter;
// use Goodby\CSV\Import\Standard\LexerConfig;
// use App\CsvExcellentMasterHead;
// use App\CsvExcellentMaster;
// use App\CsvClientHead;
// use App\CsvClient;
// use App\CsvImportTemporary;

use Goodby\CSV\Export\Standard\Exporter;
use Goodby\CSV\Export\Standard\ExporterConfig;

use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExcelExportComparisonData;

use App\Exports\ExcelExportComparisonDataView;

use Illuminate\Support\Facades\DB;
use Session;


class ExcelExportController extends Controller
{

    /**
     * エクセルファイルのエクスポート
     * セッションから差分情報を取得し、viewを通してxlsx出力する。
     *
     * @return void
     */
    public function export() {

      // 出力ファイル名
      $filename = 'exportdata_' . date("Ymd_His") . '.xlsx';

      // データの取得
      $exportComparisonData = Session::get('exportComparisonData');
      $exportDateC = Session::get('exportDateC');
      $exportDateM = Session::get('exportDateM');
      $exportHeaderM = Session::get('exportHeaderM');
      $exportHeaderC = Session::get('exportHeaderC');

      // 長さを比較
      $maxLength = max([count($exportComparisonData), count($exportDateC), count($exportDateM)]);

      // ここで整形する。
      $xlsxData = [];
      for ($i=0; $i < $maxLength; $i++) {
        $product_name = (isset($exportDateM[$i]['name']))? $exportDateM[$i]['name'] : "";
        $product_amount = (isset($exportDateM[$i]['amt']))? $exportDateM[$i]['amt'] : "";
        $amt_by_nums = (isset($exportDateM[$i]['amt_by_nums']))? $exportDateM[$i]['amt_by_nums'] : "";
        $product_name_c = (isset($exportDateC[$i]['name']))? $exportDateC[$i]['name'] : "";
        $product_amount_c = (isset($exportDateC[$i]['amt']))? $exportDateC[$i]['amt'] : "";
        $amt_by_nums_c = (isset($exportDateC[$i]['amt_by_nums']))? $exportDateC[$i]['amt_by_nums'] : "";
        $diffs_name = (isset($exportComparisonData[$i]['name']))? $exportComparisonData[$i]['name'] : "";
        $diffs = (isset($exportComparisonData[$i]['diff']))? $exportComparisonData[$i]['diff'] : "";
        $diffs_to_nums = (isset($exportComparisonData[$i]['diffs_to_nums']))? $exportComparisonData[$i]['diffs_to_nums'] : "";
        $xlsxData[] = [
          'product_name' => $product_name,
          'product_amount' => $product_amount,
          'amt_by_nums' => $amt_by_nums,
          'product_name_c' => $product_name_c,
          'product_amount_c' => $product_amount_c,
          'amt_by_nums_c' => $amt_by_nums_c,
          'diffs_name' => $diffs_name,
          'diffs' => $diffs,
          'diffs_to_nums' => $diffs_to_nums,
        ];
      }

      // Viewでエクスポート
      $view = \view('admin.export_diff', compact('xlsxData', 'exportHeaderC', 'exportHeaderM'));
      return \Excel::download(new ExcelExportComparisonDataView($view), $filename);

    }



  /*
  |--------------------------------------------------------------------------
  | private function
  |--------------------------------------------------------------------------
  */


    // 利用箇所が不明??
    private function getDataList($id) {
      $dates = DB::table('csv_excellent_master AS cem')
                ->leftjoin('csv_client AS cc', 'cem.bl_code', '=', 'cc.bl_code')
                ->select(DB::raw('cem.bl_code, cem.item_name, cem.quantity, cc.item_name AS item_name_client, cc.quantity AS quantity_client'))
                ->where([['cem.is_deleted', 0], ['cem.master_id', $id]])
                ->orderBy('cem.id', 'ASC')
                ->limit(30)
                ->get();
      return $dates;
    }


}
