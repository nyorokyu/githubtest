<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Goodby\CSV\Import\Standard\Lexer;
use Goodby\CSV\Import\Standard\Interpreter;
use Goodby\CSV\Import\Standard\LexerConfig;
use App\CsvExcellentMasterHead;
use App\CsvExcellentMaster;
use App\CsvClientHead;
use App\CsvClient;
use App\CsvImportTemporary;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Session;
use Config;


class CsvImportController extends Controller
{
  // --------------------------------------------------------------------------
  // CSVファイルインポート
  //   +
  // リスト表示
  // --------------------------------------------------------------------------
    public function store(Request $request) {
      $openAreaSection1 = 'hide';
      $openAreaSection2 = 'hide';
      $openAreaSection3 = 'hide';
      $openAreaSection4 = 'hide';
      $disableOpenAreaSection2 = 'disabled';
      $disableOpenAreaSection3 = 'disabled';

      // -----------------------------------------------
      // CSV import:マスタデータ
      // -----------------------------------------------
      if (isset($_POST['submit_m'])) {
        $rules = [
          // 'select_master' => 'required',
          'csv_m' => 'required|file|mimes:csv,txt|mimetypes:text/plain',
          'name_m' => 'required|max:255',
          'cnt_m' => 'required|integer|max:999999999',
        ];

        $messages = [
          // 'select_master' => 'ファイルを選択してください。',
          'csv_m.required' => Config::get('consts.wording.ERROR_WORDING_FILE'),
          'csv_m.file' => Config::get('consts.wording.ERROR_WORDING_FILE'),
          'csv_m.mimes' => Config::get('consts.wording.ERROR_CSV'),
          'name_m.required' => Config::get('consts.wording.ERROR_REQUIRE_NAME'),
          'name_m.max' => Config::get('consts.wording.ERROR_NAME_MAX'),
          'cnt_m.required' => '入庫台数は必須項目です。',
          'cnt_m.integer' => '整数を入力してください。',
          'cnt_m.max' => '入庫台数は10桁以内で入力してください。',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
          $openAreaSection1 = 'active';
            return redirect(route('admin.excellent_comparison.index', compact('openAreaSection1', 'disableOpenAreaSection2', 'disableOpenAreaSection3')))
                        ->withErrors($validator)
                        ->withInput();
        }

        if ($request->hasFile('csv_m') && $request->file('csv_m')->isValid()) {
          // CSVファイル保存
          $tmpName = uniqid("CSV_M_") . '.' . $request->file('csv_m')->getClientOriginalExtension();
          $request->file('csv_m')->move(public_path() . '/csv/tmp', $tmpName);
          $tmpPath = public_path() . '/csv/tmp/' . $tmpName;

          // --------------------------------------------------------------
          // *** カラムがズレる問題を対応
          // SJISのCSVの中身を文字列で取り出す
          $sjis = file_get_contents($tmpPath);
          // UTF-8に変換
          $utf8 = mb_convert_encoding($sjis, 'UTF-8', 'SJIS-win');
          // UTF-8のCSVファイル書き出し
          file_put_contents($tmpPath, $utf8);
          // --------------------------------------------------------------
          // Goodby CSVの設定（configの設定）
          $config = new LexerConfig();
          $config
              // ->setFromCharset('SJIS-win')
              ->setFromCharset('UTF-8')
              ->setToCharset('UTF-8')
              // ->setIgnoreHeaderLine(true)
          ;
          $lexer = new Lexer($config);

          $dataList = array();

          $interpreter = new Interpreter();
          $interpreter->unstrict();
          $interpreter->addObserver(function (array $row) use (&$dataList){
             // 各列のデータを取得
             $dataList[] = $row;
          });

          // CSVデータをパース
          $lexer->parse($tmpPath, $interpreter);

          // TMPファイル削除
          unlink($tmpPath);

          $companyName = $request->input('name_m');
          $receivingCount = $request->input('cnt_m');

          // 登録前にtmptableデータ削除
          // 管理者1名のみ使用する想定なのでTRUNCATE行っています
          $this->delCsvTemp();

          // DB登録 start ------------------------------------------------>
          // tmptableに全データを登録
          $i = 0;
          $fiscalYearRowNo = 0;
          $blCodeRowNo = 0;
          $itemNumberRowNo = 0;
          $itemNameRowNo = 0;
          $quantityRowNo = 0;
          foreach($dataList as $row) {
            // ヘッダの対象項目の列取得
            if ($i == 0) {
              for( $n = 0; $n < count($row); ++$n ) {
                switch ($row[$n]) {
                  case "伝票日付":
                    $fiscalYearRowNo = $n;
                    break;
                  case "BLｺｰﾄﾞ":
                    $blCodeRowNo = $n;
                    break;
                  case "品番":
                    $itemNumberRowNo = $n;
                    break;
                  case "品名":
                    $itemNameRowNo = $n;
                    break;
                  case "数量":
                    $quantityRowNo = $n;
                    break;
                }
              }
              $rowNo = [
                'fiscal_year_row_no' => $fiscalYearRowNo,
                'bl_code_row_no' => $blCodeRowNo,
                'item_number_row_no' => $itemNumberRowNo,
                'item_name_row_no' => $itemNameRowNo,
                'quantity_row_no' => $quantityRowNo,
              ];
            } else {
              $csvDetails = $this->getCsvDetails($row, $rowNo);
              // DB登録項目がブランクの場合、行を飛ばす
              if (!empty($csvDetails['fiscal_year']) && !empty($csvDetails['bl_code']) &&
                  !empty($csvDetails['item_name']) && !empty($csvDetails['quantity'])) {
                  // TODO 数量のカラムに数値以外の場合は飛ばす
                  if (!is_numeric($csvDetails['quantity'])) { continue; }
                  $fiscal_year = $csvDetails['fiscal_year'];
                  $this->registCsvTemp($csvDetails);
              }
            }
            ++$i;
          }

          // tmptableよりmastertableへ登録
          // 上位50件
          if (empty($companyName)) {
            $companyName = 'registration name ' . $fiscal_year;
          }
          if (empty($receivingCount)) {
            $receivingCount = 0;
          }
          $dateHighRank = $this->getTmpDateHighRank();
          $headDate = [
            'fiscal_year' => $fiscal_year,
            'company_name' => $companyName,
            'receiving_count' => $receivingCount,
            'created_user' => Auth::id(),
            'updated_user' => Auth::id(),
          ];
          $insertId = $this->registCsvTableHead(1, $headDate);
          $this->registCsvTable(1, $dateHighRank, $insertId);
          Session::put('masterId', $insertId);

          // tmptableデータ削除
          $this->delCsvTemp();
          // DB登録 end <------------------------------------------------

          // Session delete
          Session::forget('contentHeadM');
          Session::forget('contentListM');
          Session::forget('contentHeadC');
          Session::forget('contentListC');
          Session::forget('exportHeaderM');
          Session::forget('exportDateM');

          $wording = Config::get('consts.wording.WORDING_REGISTRATION');
          return redirect()->route('admin.excellent_comparison.index')->with('success', $wording);

        }
      }

      // -----------------------------------------------
      // CSV import:クライアントデータ
      // -----------------------------------------------
      if (isset($_POST['submit_c'])) {
        $rules = [
          'csv_c' => 'required|file|mimes:csv,txt|mimetypes:text/plain',
          'name_c' => 'required|max:255',
          'cnt_c' => 'required|integer|max:999999999'
        ];

        $messages = [
          'csv_c.required' => Config::get('consts.wording.ERROR_WORDING_FILE'),
          'csv_c.file' => Config::get('consts.wording.ERROR_WORDING_FILE'),
          'csv_c.mimes' => Config::get('consts.wording.ERROR_CSV'),
          'name_c.required' => Config::get('consts.wording.ERROR_REQUIRE_NAME'),
          'name_c.max' => Config::get('consts.wording.ERROR_NAME_MAX'),
          'cnt_c.required' => '入庫台数は必須項目です。',
          'cnt_c.integer' => '整数を入力してください。',
          'cnt_c.max' => '入庫台数は10桁以内で入力してください。'
        ];

        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            $openAreaSection2 = 'active';
            return redirect(route('admin.excellent_comparison.index', compact('openAreaSection2', 'disableOpenAreaSection2', 'disableOpenAreaSection3')))
                        ->withErrors($validator)
                        ->withInput();
        }

        // クライアントデータ
        if ($request->hasFile('csv_c') && $request->file('csv_c')->isValid()) {
          // CSVファイル保存
          $tmpName = uniqid("CSV_C_") . '.' . $request->file('csv_c')->getClientOriginalExtension();
          $request->file('csv_c')->move(public_path() . '/csv/tmp', $tmpName);
          $tmpPath = public_path() . '/csv/tmp/' . $tmpName;

          // --------------------------------------------------------------
          // *** カラムがズレる問題を対応
          // SJISのCSVの中身を文字列で取り出す
          $sjis = file_get_contents($tmpPath);
          // UTF-8に変換
          $utf8 = mb_convert_encoding($sjis, 'UTF-8', 'SJIS-win');
          // UTF-8のCSVファイル書き出し
          file_put_contents($tmpPath, $utf8);
          // --------------------------------------------------------------
          // Goodby CSVの設定（configの設定）
          $config = new LexerConfig();
          $config
              // ->setFromCharset('SJIS-win')
              ->setFromCharset('UTF-8')
              ->setToCharset('UTF-8')
              // ->setIgnoreHeaderLine(true)
          ;
          $lexer = new Lexer($config);

          $dataList = array();

          $interpreter = new Interpreter();
          $interpreter->unstrict();
          $interpreter->addObserver(function (array $row) use (&$dataList){
             // 各列のデータを取得
             $dataList[] = $row;
          });

          // CSVデータをパース
          $lexer->parse($tmpPath, $interpreter);

          // TMPファイル削除
          unlink($tmpPath);

          $companyName = $request->input('name_c');
          $receivingCount = $request->input('cnt_c');

          // 登録前にtmptableデータ削除
          // 管理者1名のみ使用する想定なのでTRUNCATE行っています
          $this->delCsvTemp();

          // DB登録 start ------------------------------------------------>
          // tmptableに全データを登録
          $i = 0;
          $fiscalYearRowNo = 0;
          $blCodeRowNo = 0;
          $itemNumberRowNo = 0;
          $itemNameRowNo = 0;
          $quantityRowNo = 0;
          foreach($dataList as $row) {
            // ヘッダの対象項目の列取得
            if ($i == 0) {
              for( $n = 0; $n < count($row); ++$n ) {
                switch ($row[$n]) {
                  case "伝票日付":
                    $fiscalYearRowNo = $n;
                    break;
                  case "BLｺｰﾄﾞ":
                    $blCodeRowNo = $n;
                    break;
                  case "品番":
                    $itemNumberRowNo = $n;
                    break;
                  case "品名":
                    $itemNameRowNo = $n;
                    break;
                  case "数量":
                    $quantityRowNo = $n;
                    break;
                }
              }
              $rowNo = [
                'fiscal_year_row_no' => $fiscalYearRowNo,
                'bl_code_row_no' => $blCodeRowNo,
                'item_number_row_no' => $itemNumberRowNo,
                'item_name_row_no' => $itemNameRowNo,
                'quantity_row_no' => $quantityRowNo,
              ];
            } else {
              $csvDetails = $this->getCsvDetails($row, $rowNo);
              // DB登録項目がブランクの場合、行を飛ばす
              if (!empty($csvDetails['fiscal_year']) && !empty($csvDetails['bl_code']) &&
                  !empty($csvDetails['item_name']) && !empty($csvDetails['quantity'])) {
                  // TODO 数量のカラムに数値以外の場合は飛ばす
                  if (!is_numeric($csvDetails['quantity'])) { continue; }
                  $fiscal_year = $csvDetails['fiscal_year'];
                  $this->registCsvTemp($csvDetails);
              }
            }
            ++$i;
          }

          // clienttableデータ削除
          $this->delCsvClient();

          // tmptableよりclienttableへ登録
          // 上位50件
          if (empty($companyName)) {
            $companyName = 'registration name ' . $fiscal_year;
          }
          if (empty($receivingCount)) {
            $receivingCount = 0;
          }
          $dateHighRank = $this->getTmpDateHighRank();
          $headDate = [
            'fiscal_year' => $fiscal_year,
            'company_name' => $companyName,
            'receiving_count' => $receivingCount,
            'created_user' => Auth::id(),
            'updated_user' => Auth::id(),
          ];
          $insertId = $this->registCsvTableHead(2, $headDate);
          $this->registCsvTable(2, $dateHighRank, $insertId);
          Session::put('clientId', $insertId);

          // tmptableデータ削除
          $this->delCsvTemp();
          // DB登録 end <------------------------------------------------

          $contentHeadM = Session::get('contentHeadM');
          $contentListM = Session::get('contentListM');
          // SELCTBOX生成
          $selectBoxDate = $this->getCsvMasterTableHeadAll();
          $selectBoxMaster = $this->makeDisplaySelectBox($selectBoxDate, Session::get('masterId'));

          //非表示エリア用
          $openAreaSection2 = '';
          $disableOpenAreaSection2 = 'enabled';
          $openAreaSection3 = 'hide';
          $openAreaSection4 = 'hide';

          $wording = Config::get('consts.wording.WORDING_REGISTRATION');
          Session::flash('success', $wording);
          return view('admin.excellent_comparison', compact('contentHeadM', 'contentListM', 'selectBoxMaster', 'openAreaSection2', 'disableOpenAreaSection2', 'openAreaSection3', 'openAreaSection4', 'disableOpenAreaSection3'));

        }
      }

      // -----------------------------------------------
      // show list:マスタデータ
      // -----------------------------------------------
      if (isset($_POST['submit_m_list'])) {
        if ($request->input('select_master') != 0) {
          Session::put('masterId', $request->input('select_master'));
        }
        $id = Session::get('masterId');
        $dateHead = $this->getCsvTableHead(1, $id);
        list($contentHeadM, $receivingCount) = $this->makeDisplayHead($dateHead);
        $exportHeader = $this->getExportHeaderData($dateHead);
        $dateList = $this->getCsvTable(1, $id);
        $contentListM = $this->makeDisplayList($dateList, $receivingCount);
        $exportDateList = $this->getExportData($dateList, $receivingCount);
        Session::put('contentHeadM', $contentHeadM);
        Session::put('contentListM', $contentListM);
        Session::put('exportHeaderM', $exportHeader);
        Session::put('exportDateM', $exportDateList);
        Session::put('receivingCountM', $receivingCount);
        $contentHeadC = Session::get('contentHeadC');
        $contentListC = Session::get('contentListC');
        // SELCTBOX生成
        $selectBoxDate = $this->getCsvMasterTableHeadAll();
        $selectBoxMaster = $this->makeDisplaySelectBox($selectBoxDate, $id);

        //非表示エリア用
        $openAreaSection2 = '';
        return view('admin.excellent_comparison',
                compact('contentHeadM', 'contentListM', 'contentHeadC', 'contentListC', 'selectBoxMaster', 'openAreaSection2', 'openAreaSection3', 'openAreaSection4', 'disableOpenAreaSection2', 'disableOpenAreaSection3')
        );
      }

      // show list:クライアントデータ
      if (isset($_POST['submit_c_list'])) {
        $id = Session::get('clientId');
        $masterId = Session::get('masterId');
        $dateHead = $this->getCsvTableHead(2, $id);
        list($contentHeadC, $receivingCount) = $this->makeDisplayHead($dateHead);
        $exportHeader = $this->getExportHeaderData($dateHead);
        $dateList = $this->getCsvTable(2, $id);
        $contentListC = $this->makeDisplayList($dateList, $receivingCount);
        $exportDateList = $this->getExportData($dateList, $receivingCount);
        Session::put('contentHeadC', $contentHeadC);
        Session::put('contentListC', $contentListC);
        Session::put('receivingCountC', $receivingCount);
        Session::put('exportDateC', $exportDateList);
        Session::put('exportHeaderC', $exportHeader);

        $contentHeadM = Session::get('contentHeadM');
        $contentListM = Session::get('contentListM');
        // SELCTBOX生成
        $selectBoxDate = $this->getCsvMasterTableHeadAll();
        $selectBoxMaster = $this->makeDisplaySelectBox($selectBoxDate, $masterId);

        //非表示エリア用
        $openAreaSection2 = '';
        $openAreaSection3 = '';
        $disableOpenAreaSection3 = 'enabled';

        $request->session()->forget('success');
        return view('admin.excellent_comparison',
                compact('contentHeadM', 'contentListM', 'contentHeadC', 'contentListC', 'selectBoxMaster', 'openAreaSection2', 'openAreaSection3', 'openAreaSection4', 'disableOpenAreaSection2', 'disableOpenAreaSection3')
        );
      }

      // -----------------------------------------------
      // show list:比較データ
      // -----------------------------------------------
      if (isset($_POST['submit_comparison'])) {
        $id = Session::get('masterId');
        $dateList = $this->getCsvComparisonData($id);
        $contentComparison = $this->makeDisplayComparison($dateList, Session::get('receivingCountM'), Session::get('receivingCountC'));
        $exportComparisonData = $this->getExportComparisonData($dateList, Session::get('receivingCountM'), Session::get('receivingCountC'));
        Session::put('exportComparisonData', $exportComparisonData);
        $contentHeadM = Session::get('contentHeadM');
        $contentListM = Session::get('contentListM');
        $contentHeadC = Session::get('contentHeadC');
        $contentListC = Session::get('contentListC');
        // SELCTBOX生成
        $selectBoxDate = $this->getCsvMasterTableHeadAll();
        $selectBoxMaster = $this->makeDisplaySelectBox($selectBoxDate);

        //非表示エリア用
        $openAreaSection2 = '';
        $openAreaSection3 = '';
        $openAreaSection4 = '';
        return view('admin.excellent_comparison',
                compact('contentComparison', 'contentHeadM', 'contentListM', 'contentHeadC', 'contentListC', 'selectBoxMaster', 'openAreaSection2', 'openAreaSection3', 'openAreaSection4', 'disableOpenAreaSection2', 'disableOpenAreaSection3')
        );
      }

    }



  /*
  |--------------------------------------------------------------------------
  | private function
  |--------------------------------------------------------------------------
  */

    private function getCsvDetails($row, $rowNo) {
      $fiscalYearRowNo = $rowNo['fiscal_year_row_no'];
      $blCodeRowNo = $rowNo['bl_code_row_no'];
      $itemNumberRowNo = $rowNo['item_number_row_no'];
      $itemNameRowNo = $rowNo['item_name_row_no'];
      $quantityRowNo = $rowNo['quantity_row_no'];
      if (preg_match("/^[0-9]+$/", $row[$blCodeRowNo])){
        // 文字列が全て数字の場合、数値に変換（0削除）
        $blCode = intval($row[$blCodeRowNo]);
      } else {
        $blCode = $row[$blCodeRowNo];
      }
      $dates = [
        // 'fiscal_year' => substr($row[0], 0, 4),
        // 'bl_code' => $row[15],
        // 'item_number' => $row[14],
        // 'item_name' => $row[13],
        // 'quantity' => $row[21],
        'fiscal_year' => substr($row[$fiscalYearRowNo], 0, 4),
        'bl_code' => $blCode,
        'item_number' => $row[$itemNumberRowNo],
        'item_name' => $row[$itemNameRowNo],
        'quantity' => $row[$quantityRowNo],
      ];
      return $dates;
    }


    private function registCsvTemp($dates) {
      $csvTemp = new CsvImportTemporary;
      foreach($dates as $key => $value) {
        $csvTemp->$key = $value;
      }
      $csvTemp->save();
    }


    private function delCsvTemp() {
      $csvTemp = new CsvImportTemporary;
      $csvTemp->truncate();
    }


    private function delCsvClient() {
      $tblHead = new CsvClientHead;
      $tbl = new CsvClient;
      $tblHead->truncate();
      $tbl->truncate();
    }


    private function getTmpDateHighRank() {
      $dates = DB::table('csv_import_temporary')
                ->select(DB::raw('fiscal_year, bl_code, SUBSTRING(GROUP_CONCAT(DISTINCT item_name separator " | "), 1, 128) AS item_name, SUM(quantity) AS quantity'))
                ->where(DB::raw('SUBSTRING(bl_code, 1, 2)'), '!=', '99')
                ->groupBy('fiscal_year', 'bl_code')
                ->orderBy('quantity', 'DESC')
                ->orderBy('bl_code', 'ASC')
                ->limit(50)
                ->get();
      return $dates;
    }


    private function registCsvTableHead($type, $dates) {
      // type　1:マスタ / 2:クライアント
      $csvM = new CsvExcellentMasterHead;
      $csvC = new CsvClientHead;
      if ($type == 1) {
        $insertId = $csvM->create($dates)->id;
      } else {
        $insertId = $csvC->create($dates)->id;
      }
      return $insertId;
    }

    private function registCsvTable($type, $dates, $id) {
      $csvM = new CsvExcellentMaster;
      $csvC = new CsvClient;
      if ($type == 1) {
        foreach($dates as $key => $value) {
          $csvM->create([
            'master_id' => $id,
            'bl_code' => $value->bl_code,
            'item_name' => $value->item_name,
            'quantity' => $value->quantity,
            'created_user' => Auth::id(),
            'updated_user' => Auth::id(),
          ]);
        }
      } else {
        foreach($dates as $key => $value) {
          $csvC->create([
            'clinrt_id' => $id,
            'bl_code' => $value->bl_code,
            'item_name' => $value->item_name,
            'quantity' => $value->quantity,
            'created_user' => Auth::id(),
            'updated_user' => Auth::id(),
          ]);
        }
      }
    }


    private function getCsvTableHead($type, $id) {
      // type　1:マスタ / 2:クライアント
      if ($type == 1) {
        $dates = DB::table('csv_excellent_master_head')
                  ->select(DB::raw('fiscal_year, company_name, receiving_count'))
                  ->where([['is_deleted', 0], ['id', $id]])
                  ->get();
      } else {
        $dates = DB::table('csv_client_head')
                  ->select(DB::raw('fiscal_year, company_name, receiving_count'))
                  ->where([['is_deleted', 0], ['id', $id]])
                  ->get();
      }
      return $dates;
    }

    private function getCsvTable($type, $id) {
      // type　1:マスタ / 2:クライアント
      if ($type == 1) {
        $dates = DB::table('csv_excellent_master')
                  ->select(DB::raw('id, bl_code, item_name, quantity'))
                  ->where([['is_deleted', 0], ['master_id', $id]])
                  ->orderBy('id', 'ASC')
                  ->limit(50)
                  ->get();
      } else {
        $dates = DB::table('csv_client')
                  ->select(DB::raw('id, bl_code, item_name, quantity'))
                  ->where([['is_deleted', 0], ['clinrt_id', $id]])
                  ->orderBy('id', 'ASC')
                  ->limit(50)
                  ->get();
      }
      return $dates;
    }


    private function getCsvComparisonData($id) {
      $dates = DB::table('csv_excellent_master AS cem')
                ->leftjoin('csv_client AS cc', 'cem.bl_code', '=', 'cc.bl_code')
                ->select(DB::raw('cem.bl_code, cem.item_name, cem.quantity, cc.quantity AS quantity_client'))
                ->where([['cem.is_deleted', 0], ['cem.master_id', $id]])
                ->orderBy('cem.id', 'ASC')
                ->limit(50)
                ->get();
      return $dates;
    }


    private function getCsvMasterTableHeadAll() {
      $dates = DB::table('csv_excellent_master_head')
                ->select(DB::raw('id, fiscal_year, company_name'))
                ->where('is_deleted', 0)
                ->orderBy('id', 'ASC')
                ->get();
      return $dates;
    }


    private function makeDisplaySelectBox($dates, $id = "") {
      $list = "";
      foreach($dates as $key => $value) {
        if($value->id == $id) {
          $selected = 'selected="selected"';
        } else {
          $selected = "";
        }
        $list .=<<< EOM
        <option value="{$value->id}" {$selected}>{$value->company_name} {$value->fiscal_year}年度</option>
EOM;
      }
      return $list;
    }


    private function makeDisplayHead($dates) {
      $receivingCount = 0;
      $content = "";
      foreach($dates as $key => $value) {
        $receivingCount = $value->receiving_count;
        $content .=<<< EOM
        <ul>
          <li>登録名：{$value->company_name}</li>
          <li>入庫台数：{$receivingCount}</li>
        </ul>
EOM;
        break;
      }
      return [$content, $receivingCount];
    }

    private function makeDisplayList($dates, $cnt) {
      $content = "";
      $content .=<<< EOM
      <table class="table content-margin-ss">
        <thead>
          <tr>
            <th>品名</th>
            <th>個数</th>
            <th>個数/入庫</th>
          </tr>
        </thead>
        <tbody>
EOM;
      foreach($dates as $key => $value) {
        $calVal = round((intval($value->quantity) / intval($cnt)) * 100);
        $content .=<<< EOM
        <tr>
          <td>{$value->item_name}</td>
          <td>{$value->quantity}</td>
          <td>{$calVal}%</td>
        </tr>
EOM;
      }
      $content .=<<< EOM
        </tbody>
      </table>
EOM;
      return $content;
    }

    private function makeDisplayComparison($dates, $cntM, $cntC) {
      $content = "";
      $content .=<<< EOM
      <table class="table content-margin-ss">
        <thead>
          <tr>
            <th>品名</th>
            <th>差分</th>
            <th>台数換算</th>
          </tr>
        </thead>
        <tbody>
EOM;
      foreach($dates as $key => $value) {
        $calValM = round((intval($value->quantity) / intval($cntM)) * 100);
        $calValC = round((intval($value->quantity_client) / intval($cntC)) * 100);
        $diff = $calValM - $calValC;
        $cntConv = intval($cntC) * (intval($diff) / 100);
        $content .=<<< EOM
        <tr>
          <td>{$value->item_name}</td>
          <td>{$diff}%</td>
          <td>{$cntConv}</td>
        </tr>
EOM;
      }
      $content .=<<< EOM
        </tbody>
      </table>
EOM;
      return $content;
    }

    /**
     * エクセル出力用のヘッダデータを生成する
     *
     * @param array $dates
     * @return void
     */
    private function getExportHeaderData($dates) {
      $receivingCount = 0;
      $content = "";
      foreach($dates as $key => $value) {
        $receivingCount = $value->receiving_count;
        $content = $value->company_name;
      }
      return [ "registeredName" => $content, "registeredAmount" => $receivingCount];
    }

    /**
     * エクセル出力用のデータを生成する
     *
     * @param array $dates
     * @return void
     */
    private function getExportData($dates, $cnt) {
      $exportData = [];
      foreach($dates as $key => $value) {
        $calVal = round((intval($value->quantity) / intval($cnt)) * 100);
        $exportData[] = [
          'name' => $value->item_name,
          'amt' => $value->quantity,
          'amt_by_nums' => $calVal . '%',
        ];
      }
      return $exportData;
    }

    /**
     * エクセル出力用の結果データを生成する
     *
     * @param array $dates
     * @return void
     */
    private function getExportComparisonData($dates, $cntM, $cntC) {
      $exportData = [];
      foreach($dates as $key => $value) {
        $calValM = round((intval($value->quantity) / intval($cntM)) * 100);
        $calValC = round((intval($value->quantity_client) / intval($cntC)) * 100);
        $diff = $calValM - $calValC;
        $cntConv = intval($cntC) * (intval($diff) / 100);
        $exportData[] = [
          'name' => $value->item_name,
          'diff' => $diff . '%',
          'diffs_to_nums' => $cntConv,
        ];
      }
      return $exportData;
    }


}
