<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Goodby\CSV\Import\Standard\Lexer;
use Goodby\CSV\Import\Standard\Interpreter;
use Goodby\CSV\Import\Standard\LexerConfig;
use App\CsvInsolvencyMasterHead;
use App\CsvInsolvencyMaster;
use App\CsvInsolvencyClientHead;
use App\CsvInsolvencyClient;
use App\CsvImportTemporaryInsolvency;
use App\CsvImportInsolvency;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Session;
use DateTime;
use Config;


class InsolvencyAnalysisController extends Controller
{

  public function __construct()
  {
    $this->middleware('auth');
  }



  public function index($id)
  {
    Session::put('masterId', $id);
    if ($id == 0) {
      // 全マスタ
      $contentHeadM = '<ul><li>全マスタ Wランキング</li></ul>';
      $dataList = $this->getInsolvencyMasterHighRank();
      $contentListM = $this->makeDisplayListAll($dataList);
      $exportHeadM = ['全マスタ Wランキング'];
      $exportListM = $this->makeExportListAll($dataList);
      $fiscalYear = '';
    } else {
      // 各マスタ
      $dataHead = $this->getCsvInsolvencyHead(1, $id);
      list($contentHeadM, $fiscalYear) = $this->makeDisplayHead($dataHead);
      $dataList = $this->getInsolvencyMasterDetail($id);
      $contentListM = $this->makeDisplayListMaster($dataList, $fiscalYear);

      $exportHeadM = $this->makeExportHead($dataHead);
      $exportListM = $this->makeExportListMaster($dataList, $fiscalYear);
    }

    Session::put('contentHeadM', $contentHeadM);
    Session::put('contentListM', $contentListM);
    Session::put('fiscalYear', $fiscalYear);

    // エクセル出力用データ
    Session::put('exportHeadM', $exportHeadM);
    Session::put('exportListM', $exportListM);

    return view('admin.insolvency_analysis', compact('contentHeadM', 'contentListM'));

  }




  // --------------------------------------------------------------------------
  // CSVファイルインポート
  //   +
  // リスト表示
  // --------------------------------------------------------------------------
  public function store(Request $request) {

    // CSV import:クライアントデータ start -----------------------------------
    if (isset($_POST['import_c'])) {
      $contentHeadM = Session::get('contentHeadM');
      $contentListM = Session::get('contentListM');

      $rules = [
        'csv_c' => 'required|file|mimes:csv,txt|mimetypes:text/plain',
      ];

      $messages = [
        'csv_c.required' => Config::get('consts.wording.ERROR_REQUIRE_FILE'),
        'csv_c.file' => Config::get('consts.wording.ERROR_REQUIRE_FILE'),
        'csv_c.mimes' => Config::get('consts.wording.ERROR_CSV'),
      ];

      $validator = Validator::make($request->all(), $rules, $messages);
      if ($validator->fails()) {
          return redirect(route('admin.insolvency_analysis.index', ['id' => Session::get('masterId')], compact('contentHeadM', 'contentListM')))
                      ->withErrors($validator)
                      ->withInput();
      }


      if ($request->hasFile('csv_c') && $request->file('csv_c')->isValid()) {
          // CSVファイル保存
          $tmpName = uniqid("CSV_C_S_") . '.' . $request->file('csv_c')->getClientOriginalExtension();
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

          // 登録前にtmptableデータ削除
          // 管理者1名のみ使用する想定なのでTRUNCATE行っています
          $this->delCsvTemp();

          // DB登録
          // tmptableに全データを登録
          $i = 0;
          $yearMonthRowNo = 0;
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
                    $yearMonthRowNo = $n;
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
                'year_month_row_no' => $yearMonthRowNo,
                'bl_code_row_no' => $blCodeRowNo,
                'item_number_row_no' => $itemNumberRowNo,
                'item_name_row_no' => $itemNameRowNo,
                'quantity_row_no' => $quantityRowNo,
              ];
            } else {
              $csvDetails = $this->getCsvDetails($row, $rowNo);
              // DB登録項目がブランクの場合、行を飛ばす
              if (!empty($csvDetails['yyyy_mm']) && !empty($csvDetails['bl_code']) &&
                  !empty($csvDetails['item_name']) && !empty($csvDetails['quantity'])) {
                  // TODO 数量のカラムに数値以外の場合は飛ばす
                  if (!is_numeric($csvDetails['quantity'])) { continue; }
                  $this->registCsvTemp($csvDetails);
              }
            }
            ++$i;
          }

          // tmptableより最新年月を取得
          $dateLatestYearMonth = $this->getTmpDateLatestYearMonth();
          if (substr($dateLatestYearMonth[0]->max_yyyy_mm, -2) != 12) {
            // 12月が存在しない最新年はデータ削除
            $this->delTmpDateLatestYear(substr($dateLatestYearMonth[0]->max_yyyy_mm, 0, 4));
          }

          // tmptableよりclienttableへ登録
          // 登録するデータはマスタに紐づく
          $id = Session::get('masterId');
          $this->delCsvClient();
          $registrationName = $request->input('name_c');
          if (empty($registrationName)) {
            $registrationName = 'registration name';
          }
          $headDatas = [
            'registration_name' => $registrationName,
            'created_user' => Auth::id(),
            'updated_user' => Auth::id(),
          ];
          $insertId = $this->registCsvClientHead($headDatas);
          $this->registCsvClient($id, $insertId);
          Session::put('clientId', $insertId);
          // エクセル出力用データ
          $exportInsolvencyHead = [$registrationName];
          Session::put('exportInsolvencyHeadData', $exportInsolvencyHead);

          // tmptableデータ削除
          $this->delCsvTemp();

          // 年度のMAXとMINを取得
          $yearMaxMin = $this->getYearMaxMin($insertId);
          foreach($yearMaxMin as $key => $value) {
            $yearMax = $value->fiscal_year_max;
            $yearMin = $value->fiscal_year_min;
          }
          // show list
          $clientId = Session::get('clientId');
          $contentListC = $this->makeDisplayList($id, $yearMax, $yearMin, $clientId);

          return view('admin.insolvency_analysis',
                    compact('contentHeadM', 'contentListM', 'contentListC')
          );


      }
    }
    // CSV import:クライアントデータ end -----------------------------------

    // マスタのソート順変更 start -------------------------------------------
    if (isset($_POST['sort_asc'])) {
      $id = Session::get('masterId');
      $clientId = Session::get('clientId');
      $yearMaxMin = $this->getYearMaxMin($clientId);
      foreach($yearMaxMin as $key => $value) {
        $yearMax = $value->fiscal_year_max;
        $yearMin = $value->fiscal_year_min;
      }
      $sort = 'asc';
      $contentListC = $this->makeDisplayList($id, $yearMax, $yearMin, $clientId, $sort);

      $contentHeadM = Session::get('contentHeadM');
      $fiscalYear = Session::get('fiscalYear');
      $dataList = $this->getInsolvencyMasterDetail($id, $sort);
      $contentListM = $this->makeDisplayListMaster($dataList, $fiscalYear);

      // エクセル出力用データ
      $exportListM = $this->makeExportListMaster($dataList, $fiscalYear);
      Session::put('exportListM', $exportListM);

      return view('admin.insolvency_analysis',
                compact('contentHeadM', 'contentListM', 'contentListC')
      );

    } elseif (isset($_POST['sort_desc'])) {
      $id = Session::get('masterId');
      $clientId = Session::get('clientId');
      $yearMaxMin = $this->getYearMaxMin($clientId);
      foreach($yearMaxMin as $key => $value) {
        $yearMax = $value->fiscal_year_max;
        $yearMin = $value->fiscal_year_min;
      }
      $sort = 'desc';
      $contentListC = $this->makeDisplayList($id, $yearMax, $yearMin, $clientId, $sort);

      $contentHeadM = Session::get('contentHeadM');
      $fiscalYear = Session::get('fiscalYear');
      $dataList = $this->getInsolvencyMasterDetail($id, $sort);
      $contentListM = $this->makeDisplayListMaster($dataList, $fiscalYear);

      // エクセル出力用データ
      $exportListM = $this->makeExportListMaster($dataList, $fiscalYear);
      Session::put('exportListM', $exportListM);

      return view('admin.insolvency_analysis',
                compact('contentHeadM', 'contentListM', 'contentListC')
      );

    }
    // マスタのソート順変更 end -------------------------------------------

      return view('admin.insolvency_analysis');

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
              ->select(DB::raw('master_id, bl_code, SUM(quantity) AS quantity, SUM(decrease_rate) AS decrease_rate'))
              ->whereRaw('is_deleted = 0 AND SUBSTRING(bl_code, 1, 2) != 99')
              ->groupBy('master_id', 'bl_code')
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


  private function makeDisplayListAll($datas) {
    $content = "";
    $content .=<<< EOM
    <table class="table content-margin-ss">
      <thead>
        <tr>
          <th class="w-200">品名</th>
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

  /**
   * エクセル出力用にデータを整形
   *
   * @param array $datas
   * @return void
   */
  private function makeExportListAll($datas) {
    $xlsxData = [];
    $header = [];
    $body = [];
    $header[] = '品名';
    $xlsxData['header'] = $header;
    foreach($datas as $key => $value) {
      $xlsxRow = [];
      $xlsxRow[] = $this->getItemName($value->bl_code);
      $body[] = $xlsxRow;
    }
    $xlsxData['body'] = $body;
    return $xlsxData;
  }


  private function getCsvInsolvencyHead($type, $id) {
    // $type 1:マスタ / 2:クライアント
    if ($type == 1) {
      $datas = DB::table('csv_insolvency_master_head')
                ->select(DB::raw('fiscal_year, registration_name'))
                ->where([['is_deleted', 0], ['id', $id]])
                ->get();
    } else {
      $datas = DB::table('csv_insolvency_client_head')
                ->select(DB::raw('registration_name'))
                ->where([['is_deleted', 0], ['id', $id]])
                ->get();
    }
    return $datas;
  }


  private function makeDisplayHead($datas) {
    $content = "";
    foreach($datas as $key => $value) {
      $fiscalYear = $value->fiscal_year;
      $content .=<<< EOM
      <ul>
        <li>{$value->registration_name}</li>
      </ul>
EOM;
      break;
    }
    return [$content, $fiscalYear];
  }

  /**
   * マスタデータのタイトルを取得
   *
   * @param [type] $datas
   * @return void
   */
  private function makeExportHead($datas) {
    $xlsxData = [];
    foreach($datas as $key => $value) {
      $xlsxData[] = $value->registration_name;
      break;
    }
    return $xlsxData;
  }


  private function getInsolvencyMasterDetail($id, $sort = '') {
    if ($sort == 'desc' || empty($sort)) {
      $datas = DB::table('csv_insolvency_master')
                ->select(DB::raw('bl_code, item_name, quantity, decrease_rate'))
                ->where([['is_deleted', 0], ['master_id', $id]])
                ->whereRaw('SUBSTRING(bl_code, 1, 2) != 99')
                ->orderBy('decrease_rate', 'DESC')
                ->limit(50)
                ->get();

    } elseif ($sort == 'asc') {
      $sqlTmp = DB::table('csv_insolvency_master')
                ->select(DB::raw('bl_code, item_name, quantity, decrease_rate'))
                ->whereRaw('is_deleted = 0 AND master_id = ' . $id)
                ->whereRaw('SUBSTRING(bl_code, 1, 2) != 99')
                ->orderBy('decrease_rate', 'DESC')
                ->limit(50)
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


  private function makeDisplayListMaster($datas, $fiscalYear) {
    $content = "";
    $content .=<<< EOM
    <table class="table content-margin-ss">
      <thead>
        <tr>
          <th class="w-200">品名</th>
          <th class="w-50">個数</th>
          <th class="w-50">{$fiscalYear}</th>
        </tr>
      </thead>
      <tbody>
EOM;
      foreach($datas as $key => $value) {
        // $sumQuantity = $this->getSumQuantity($value->bl_code);
        // if (empty($value->quantity)) {
        //   $quantity = 0;
        //   $decreaseRate = 0;
        // } else {
        //   $quantity = $value->quantity;
        //   $decreaseRate = round((intval($sumQuantity) / intval($quantity)) * 100);
        // }
        $decreaseRate = floatval($value->decrease_rate) * 100;
        $content .=<<< EOM
          <tr>
            <td>{$value->item_name}</td>
            <td>{$value->quantity}</td>
            <td>{$decreaseRate}%</td>
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
   * マスタデータのデータの整形
   *
   * @param array $datas
   * @param string $fiscalYear
   * @return void
   */
  private function makeExportListMaster($datas, $fiscalYear) {
    $xlsxData = [];
    $header = [];
    $body = [];
    $header[] = '品名';
    $header[] = '個数';
    $header[] = $fiscalYear;
    $xlsxData['header'] = $header;
    foreach($datas as $key => $value) {
      $xlsxRow = [];
      // $sumQuantity = $this->getSumQuantity($value->bl_code);
      // if (empty($value->quantity)) {
      //   $quantity = 0;
      //   $decreaseRate = 0;
      // } else {
      //   $quantity = $value->quantity;
      //   $decreaseRate = round((intval($sumQuantity) / intval($quantity)) * 100);
      // }
      $decreaseRate = floatval($value->decrease_rate) * 100;
      $xlsxRow[] = $value->item_name;
      $xlsxRow[] = $value->quantity;
      $xlsxRow[] = $decreaseRate;
      $body[] = $xlsxRow;
    }
    $xlsxData['body'] = $body;
    return $xlsxData;
  }


  private function getCsvDetails($row, $rowNo) {
    $yearMonthRowNo = $rowNo['year_month_row_no'];
    $blCodeRowNo = $rowNo['bl_code_row_no'];
    $itemNumberRowNo = $rowNo['item_number_row_no'];
    $itemNameRowNo = $rowNo['item_name_row_no'];
    $quantityRowNo = $rowNo['quantity_row_no'];
    $yearMonth = new DateTime($row[$yearMonthRowNo]);
    $yearMonth = $yearMonth->format('Ymd');
    if (preg_match("/^[0-9]+$/", $row[$blCodeRowNo])){
      // 文字列が全て数字の場合、数値に変換（0削除）
      $blCode = intval($row[$blCodeRowNo]);
    } else {
      $blCode = $row[$blCodeRowNo];
    }
    $datas = [
      'yyyy_mm' => substr($yearMonth, 0, 6),
      'bl_code' => $blCode,
      'item_number' => $row[$itemNumberRowNo],
      'item_name' => $row[$itemNameRowNo],
      'quantity' => $row[$quantityRowNo],
    ];
    return $datas;
  }


  private function registCsvTemp($datas) {
    $csvTemp = new CsvImportTemporaryInsolvency;
    foreach($datas as $key => $value) {
      $csvTemp->$key = $value;
    }
    $csvTemp->save();
  }


  private function getTmpDateLatestYearMonth() {
    $datas = DB::table('csv_import_temporary_insolvency')
              ->select(DB::raw('MAX(yyyy_mm) AS max_yyyy_mm'))
              ->get();
    return $datas;
  }


  private function delTmpDateLatestYear($target) {
    $csvTemp = new CsvImportTemporaryInsolvency;
    $csvTemp->where(DB::raw('SUBSTRING(yyyy_mm, 1, 4)'), $target)->delete();
  }


  private function delCsvClient() {
    $tblHead = new CsvInsolvencyClientHead;
    $tbl = new CsvInsolvencyClient;
    $tblHead->truncate();
    $tbl->truncate();
  }


  private function registCsvClientHead($datas) {
    $csvC = new CsvInsolvencyClientHead;
    $insertId = $csvC->create($datas)->id;
    return $insertId;
  }


  private function getTmpDateByBlcode($blcode) {
    $datas = DB::table('csv_import_temporary_insolvency')
              ->select(DB::raw('SUBSTRING(yyyy_mm, 1, 4) AS fiscal_year, bl_code,
                                SUBSTRING(GROUP_CONCAT(DISTINCT item_number separator " | "), 1, 128) AS item_number,
                                SUBSTRING(GROUP_CONCAT(DISTINCT item_name separator " | "), 1, 128) AS item_name,
                                SUM(quantity) AS quantity, COUNT(bl_code) AS occurrence_count'))
              ->where('bl_code', $blcode)
              ->groupBy(DB::raw('SUBSTRING(yyyy_mm, 1, 4)'), 'bl_code')
              ->get();
    return $datas;
  }


  private function registCsvClient($id, $insertId) {
    $csvC = new CsvInsolvencyClient;
    // マスタデータ
    if ($id == 0) {
      $dataList = $this->getInsolvencyMasterHighRank();
    } else {
      $dataList = $this->getInsolvencyMasterDetail($id);
    }
    foreach($dataList as $keyList => $valueList) {
      // クライアントデータ
      $datas = $this->getTmpDateByBlcode($valueList->bl_code);
      foreach($datas as $key => $value) {
        $csvC->create([
          'client_id' => $insertId,
          'fiscal_year' => $value->fiscal_year,
          'bl_code' => $value->bl_code,
          'item_number' => $value->item_number,
          'item_name' => $value->item_name,
          'quantity' => $value->quantity,
          'created_user' => Auth::id(),
          'updated_user' => Auth::id(),
        ]);
      }
    }
  }


  private function delCsvTemp() {
    $csvTemp = new CsvImportTemporaryInsolvency;
    $csvTemp->truncate();
  }


  private function getYearMaxMin($clientId) {
    $datas = DB::table('csv_insolvency_client')
              ->select(DB::raw('MAX(fiscal_year) AS fiscal_year_max, MIN(fiscal_year) AS fiscal_year_min'))
              ->where([['is_deleted', 0], ['client_id', $clientId]])
              ->get();
    return $datas;
  }


  private function getInsolvencyClientByBlcode($clientId, $targetYear, $blcode) {
    $datas = DB::table('csv_insolvency_client')
              ->select(DB::raw('bl_code, item_name, quantity'))
              ->where([['client_id', $clientId], ['fiscal_year', $targetYear], ['bl_code', $blcode]])
              ->get();
    return $datas;
  }


  private function getItemNameClient($blCode, $yearMax, $yearMin) {
    $itemName = DB::table('csv_insolvency_client')
              ->where([['is_deleted', 0], ['bl_code', $blCode]])
              ->whereBetween('fiscal_year', [$yearMin, $yearMax])
              ->value('item_name');
    return $itemName;
  }


  private function makeDisplayList($id, $yearMax, $yearMin, $clientId, $sort = '') {
    // エクセル出力用データ
    $xlsxData = [];
    $header = [];
    $body = [];

    $header[] = '品名';
    $header[] = '個数';
    if ($id == 0) {
      $dataList = $this->getInsolvencyMasterHighRank();
    } else {
      $dataList = $this->getInsolvencyMasterDetail($id, $sort);
    }
    $yearDiff = intval($yearMax) - intval($yearMin);
    $content = "";
    $content .=<<< EOM
    <table class="table content-margin-ss">
      <thead>
        <tr>
          <th class="w-200">品名</th>
          <th class="w-50">個数</th>
EOM;
          for( $i = 1; $i <= $yearDiff; ++$i ) {
            $fiscalYear = intval($yearMax) - $i;
            $header[] = $fiscalYear;
    $content .=<<< EOM
            <th class="w-50">{$fiscalYear}</th>
EOM;
          }
    $content .=<<< EOM
        </tr>
      </thead>
      <tbody>
EOM;

    foreach($dataList as $keyList => $valueList) {
      $xlsxRow = [];
      $itemName = $this->getItemNameClient($valueList->bl_code, $yearMax, $yearMin);
      if (empty($itemName)) {
        $itemName = '---';
      }
      $xlsxRow[] = $itemName;
      $content .=<<< EOM
      <tr>
        <td>{$itemName}</td>
EOM;
      for( $n = 0; $n <= $yearDiff; ++$n ) {
        $fiscalYear = intval($yearMax) - $n;
        $dataTmp = $this->getInsolvencyClientByBlcode($clientId, $fiscalYear, $valueList->bl_code);
        if ($n === 0) {
          $quantityLatestYear = 0;
        }
        $quantity = 0;
        $decreaseRate = 0;
        $cellValue = '';
        foreach($dataTmp as $keyTmp => $valueTmp) {
          if ($n === 0) {
            if (empty($valueTmp->quantity)) {
              $quantityLatestYear = 0;
            } else {
              $quantityLatestYear = $valueTmp->quantity;
            }
          }
          if (empty($valueTmp->quantity)) {
            $quantity = 0;
            $decreaseRate = 0;
          } else {
            $quantity = $valueTmp->quantity;
            $decreaseRate = round((intval($quantityLatestYear) / intval($quantity)) * 100);
          }
          if ($n == 0) {
            $cellValue = $quantityLatestYear;
          } else {
            $cellValue = $quantity . ' / ' . $decreaseRate . '%';
          }
          break;
        }
        $xlsxRow[] = $cellValue;
        $content .=<<< EOM
          <td>{$cellValue}</td>
EOM;
      }
      $content .=<<< EOM
      </tr>
EOM;
      $body[] = $xlsxRow;
    }
    $content .=<<< EOM
      </tbody>
    </table>
EOM;

    // エクセル出力用データをセッションに格納
    $xlsxData['header'] = $header;
    $xlsxData['body'] = $body;
    Session::put('exportInsolvencyData', $xlsxData);
    return $content;
  }




}
