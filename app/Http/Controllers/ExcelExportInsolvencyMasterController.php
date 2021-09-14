<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Goodby\CSV\Export\Standard\Exporter;
use Goodby\CSV\Export\Standard\ExporterConfig;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExcelExportComparisonData;
use App\Exports\ExcelExportComparisonDataView;
use Illuminate\Support\Facades\DB;
use Session;


class ExcelExportInsolvencyMasterController extends Controller
{

    /**
     * エクセルファイルのエクスポート
     * 倒産データマスタ取込後のリストを出力する
     *
     * @return void
     */
    public function export() {

      // 出力ファイル名
      $filename = 'export_insolvency_master_data_' . date("Ymd_His") . '.xlsx';

      // データの取得
      $exportList = Session::get('exportList');

      // 長さ
      $maxLength = count($exportList['body']);

      // カラム数チェック
      $masterColumnLength = count($exportList['header']);

      // ここで整形する。
      $xlsxData = [];
      $xlsxDataHeader = [];
      $xlsxDataBody = [];

      // header
      for ($h=0; $h < $masterColumnLength; $h++) {
        $xlsxDataHeader[] = (isset($exportList['header'][$h]))? $exportList['header'][$h] : '';
      }

      // body
      for ($i=0; $i < $maxLength; $i++) {
        $arr = [];
        for ($j=0; $j < count($exportList['body'][$i]); $j++) {
          $arr[] = (isset($exportList['body'][$i][$j]))? $exportList['body'][$i][$j] : '';
        }
        $xlsxDataBody[] = $arr;
      }

      // Viewでエクスポート
      $view = \view('admin.export_insolvency_master', compact('masterColumnLength', 'xlsxDataHeader', 'xlsxDataBody'));
      return \Excel::download(new ExcelExportComparisonDataView($view), $filename);

    }



}
