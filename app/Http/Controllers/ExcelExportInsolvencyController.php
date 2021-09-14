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

class ExcelExportInsolvencyController extends Controller
{

    /**
     * エクセルファイルのエクスポート
     * セッションから差分情報を取得し、viewを通してxlsx出力する。
     *
     * @return void
     */
    public function export() {

      // 出力ファイル名
      $filename = 'export_insolvency_data_' . date("Ymd_His") . '.xlsx';

      // データの取得
      $exportHeadM = Session::get('exportHeadM');
      $exportListM = Session::get('exportListM');
      $exportInsolvencyData = Session::get('exportInsolvencyData');
      $exportInsolvencyHeadData = Session::get('exportInsolvencyHeadData');

      // Title
      $masterTitle = (isset($exportHeadM[0]))? $exportHeadM[0] : '';
      $diffTitle = (isset($exportInsolvencyHeadData[0]))? $exportInsolvencyHeadData[0] : '';

      // 長さを比較
      $maxLength = max([count($exportInsolvencyData['body']), count($exportListM['body'])]);

      // カラム数チェック
      $masterColumnLength = count($exportListM['header']);
      $diffColumnLength = count($exportInsolvencyData['header']);

      // ここで整形する。
      $xlsxData = [];
      $xlsxDataHeader = [];
      $xlsxDataBody = [];
      // header
      for ($h=0; $h < $masterColumnLength; $h++) {
        $xlsxDataHeader[] = (isset($exportListM['header'][$h]))? $exportListM['header'][$h] : '';
      }
      // テーブル間のスペース用
      $xlsxDataHeader[] = false;
      for ($h=0; $h < $diffColumnLength; $h++) {
        $xlsxDataHeader[] = (isset($exportInsolvencyData['header'][$h]))? $exportInsolvencyData['header'][$h] : '';
      }

      // body
      for ($i=0; $i < $maxLength; $i++) {
        $arr = [];
        for ($h=0; $h < count($exportListM['body'][$i]); $h++) {
          $arr[] = (isset($exportListM['body'][$i][$h]))? $exportListM['body'][$i][$h] : '';
        }
        // テーブル間のスペース用
        $arr[] = false;
        for ($h=0; $h < $diffColumnLength; $h++) {
          if (isset($exportInsolvencyData['body'][$i])) {
            $arr[] = (isset($exportInsolvencyData['body'][$i][$h]))? $exportInsolvencyData['body'][$i][$h] : '';
          }
        }
        $xlsxDataBody[] = $arr;
      }

      // Viewでエクスポート
      $view = \view('admin.export_insolvency', compact('masterColumnLength', 'diffColumnLength', 'xlsxDataHeader', 'xlsxDataBody', 'masterTitle', 'diffTitle'));
      return \Excel::download(new ExcelExportComparisonDataView($view), $filename);

    }

}
