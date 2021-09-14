<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Goodby\CSV\Import\Standard\Lexer;
use Goodby\CSV\Import\Standard\Interpreter;
use Goodby\CSV\Import\Standard\LexerConfig;
use App\CsvInsolvencyMasterHead;
use App\CsvInsolvencyMaster;
use App\CsvImportTemporaryInsolvency;
use App\CsvImportInsolvency;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Session;
use DateTime;
use Request as PostRequest;
use Config;


class CsvImportInsolvencyController extends Controller
{

  public function __construct()
  {
    $this->middleware('auth');
  }



  public function index()
  {

    $openAreaSection1 = 'hide';
    if(PostRequest::input('openAreaSection1') == 'active') {
      $openAreaSection1 = PostRequest::input('openAreaSection1');
    }
    return view('admin.insolvency_master_import', compact('openAreaSection1'));

  }




  // --------------------------------------------------------------------------
  // CSVファイルインポート
  //   +
  // リスト表示
  // --------------------------------------------------------------------------
    public function store(Request $request) {
      $openAreaSection1 = 'hide';
      // CSV import:マスタデータ
      if (isset($_POST['import_m'])) {
        if ($request->hasFile('csv_m') && $request->file('csv_m')->isValid()) {
          // CSVファイル保存
          $tmpName = uniqid("CSV_M_S_") . '.' . $request->file('csv_m')->getClientOriginalExtension();
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

          // 登録前にtmptableデータ削除
          // 管理者1名のみ使用する想定なのでTRUNCATE行っています
          $this->delCsvTemp();

          // DB登録 start ------------------------------------------------>
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
          // if (substr($dateLatestYearMonth['max_yyyy_mm'], -2) != 12) {
          if (substr($dateLatestYearMonth[0]->max_yyyy_mm, -2) != 12) {
            // 12月が存在しない最新年はデータ削除
            $this->delTmpDateLatestYear(substr($dateLatestYearMonth[0]->max_yyyy_mm, 0, 4));
          }

          // tmptableよりcsvimporttableへ登録
          $this->delCsvImport();
          $csvTmpDate = $this->getTmpDate();
          $this->registCsvImportInsolvency($csvTmpDate);

          // tmptableデータ削除
          $this->delCsvTemp();
          // DB登録 end <------------------------------------------------

          // 年度のMAXとMINを取得
          $yearMaxMin = $this->getYearMaxMin();
          foreach($yearMaxMin as $key => $value) {
            $yearMax = $value->fiscal_year_max;
            $yearMin = $value->fiscal_year_min;
          }
          // 最新年のデータを取得
          $dateList = $this->getCsvImportInsolvency($yearMax);
          // show list
          $contentList = $this->makeDisplayList($dateList, $yearMax, $yearMin);
          // make selectbox(year)
          $selectBoxMasterYear = $this->makeDisplaySelectBox($yearMax, $yearMin);

          // export data
          $exportList = $this->makeExportListMaster($dateList, $yearMax, $yearMin);

          Session::put('dataListYearMax', $dateList);
          Session::put('contentList', $contentList);
          Session::put('selectBoxMasterYear', $selectBoxMasterYear);
          // エクセル出力用データ
          Session::put('exportList', $exportList);

          $openAreaSection1 = '';
          return view('admin.insolvency_master_import',
                  compact('contentList', 'selectBoxMasterYear', 'openAreaSection1')
          );

        }
      }

      // -----------------------------------------------
      // マスタデータ登録
      // -----------------------------------------------
      if (isset($_POST['submit_m'])) {
        $rules = [
            // 'select_master_year' => 'required',
            'name_m' => 'required|max:255',
            'memo_m' => 'max:255'
        ];

        $messages = [
          // 'select_master_year' => '年を選択してください。',
          'name_m.required' => Config::get('consts.wording.ERROR_REQUIRE_NAME'),
          'name_m.max' => Config::get('consts.wording.ERROR_NAME_MAX'),
          'memo_m.max' => 'メモは255文字以内で入力してください。'
        ];

        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
          $openAreaSection1 = 'active';
          return redirect(route('admin.insolvency_master_import.index', compact('openAreaSection1')))
                        ->withErrors($validator)
                        ->withInput();
        }

        // mastertableへ登録
        // 全件登録しています
        $dataListYearMax = Session::get('dataListYearMax');
        $fiscalYear = $request->input('select_master_year');
        $registrationName  = $request->input('name_m');
        if (empty($registrationName)) {
          $registrationName = 'registration name ' . $fiscalYear;
        }
        $memo = $request->input('memo_m');
        $dateDetail = $this->getImportDateByYear($fiscalYear);
        $dataHead = [
          'fiscal_year' => $fiscalYear,
          'registration_name' => $registrationName,
          'memo' => $memo,
          'created_user' => Auth::id(),
          'updated_user' => Auth::id(),
        ];
        $insertId = $this->registCsvTableHead($dataHead);
        $this->registCsvTable($dateDetail, $insertId, $dataListYearMax);
        Session::put('masterId', $insertId);

        $contentList = Session::get('contentList');
        $selectBoxMasterYear = Session::get('selectBoxMasterYear');

        $wording = Config::get('consts.wording.WORDING_REGISTRATION');
        return redirect()->route('admin.list_insolvency_master.index')->with('success', $wording);

      }

      return redirect()->route('admin.insolvency_master_import.index', compact('openAreaSection1'));

    }



  /*
  |--------------------------------------------------------------------------
  | private function
  |--------------------------------------------------------------------------
  */

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
      $dates = [
        'yyyy_mm' => substr($yearMonth, 0, 6),
        'bl_code' => $blCode,
        'item_number' => $row[$itemNumberRowNo],
        'item_name' => $row[$itemNameRowNo],
        'quantity' => $row[$quantityRowNo],
      ];
      return $dates;
    }


    private function registCsvTemp($dates) {
      $csvTemp = new CsvImportTemporaryInsolvency;
      foreach($dates as $key => $value) {
        $csvTemp->$key = $value;
      }
      $csvTemp->save();
    }


    private function getTmpDateLatestYearMonth() {
      $dates = DB::table('csv_import_temporary_insolvency')
                ->select(DB::raw('MAX(yyyy_mm) AS max_yyyy_mm'))
                ->get();
      return $dates;
    }


    private function delTmpDateLatestYear($target) {
      $csvTemp = new CsvImportTemporaryInsolvency;
      $csvTemp->where(DB::raw('SUBSTRING(yyyy_mm, 1, 4)'), $target)->delete();
    }


    private function getTmpDate() {
      $dates = DB::table('csv_import_temporary_insolvency')
                ->select(DB::raw('SUBSTRING(yyyy_mm, 1, 4) AS fiscal_year, bl_code,
                                  SUBSTRING(GROUP_CONCAT(DISTINCT item_number separator " | "), 1, 128) AS item_number,
                                  SUBSTRING(GROUP_CONCAT(DISTINCT item_name separator " | "), 1, 128) AS item_name,
                                  SUM(quantity) AS quantity, COUNT(bl_code) AS occurrence_count'))
                // ->where(DB::raw('SUBSTRING(bl_code, 1, 2)'), '!=', '99')
                ->groupBy(DB::raw('SUBSTRING(yyyy_mm, 1, 4)'), 'bl_code')
                ->get();
      return $dates;
    }


    private function registCsvImportInsolvency($dates) {
      $csvImportInsolvency = new CsvImportInsolvency;
      foreach($dates as $key => $value) {
        $csvImportInsolvency->create([
          'fiscal_year' => $value->fiscal_year,
          'bl_code' => $value->bl_code,
          'item_number' => $value->item_number,
          'item_name' => $value->item_name,
          'quantity' => $value->quantity,
          'occurrence_count' => $value->occurrence_count,
        ]);
      }
    }


    private function delCsvTemp() {
      $csvTemp = new CsvImportTemporaryInsolvency;
      $csvTemp->truncate();
    }


    private function delCsvImport() {
      $csvTemp = new CsvImportInsolvency;
      $csvTemp->truncate();
    }


    private function getYearMaxMin() {
      $dates = DB::table('csv_import_insolvency_data')
                ->select(DB::raw('MAX(fiscal_year) AS fiscal_year_max, MIN(fiscal_year) AS fiscal_year_min'))
                ->get();
      return $dates;
    }


    private function getCsvImportInsolvency($targetYear) {
      $dates = DB::table('csv_import_insolvency_data')
                ->select(DB::raw('bl_code, item_name, quantity'))
                ->where('fiscal_year', $targetYear)
                ->orderBy('occurrence_count', 'DESC')
                ->limit(50)
                ->get();
      return $dates;
    }


    private function getCsvImportInsolvencyByBlcode($targetYear, $blCode) {
      $dates = DB::table('csv_import_insolvency_data')
                ->select(DB::raw('bl_code, quantity'))
                ->where([['fiscal_year', $targetYear], ['bl_code', $blCode]])
                ->get();
      return $dates;
    }


    private function makeDisplayList($dates, $yearMax, $yearMin) {
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
      $content .=<<< EOM
              <th class="w-50">{$fiscalYear}</th>
EOM;
            }
      $content .=<<< EOM
          </tr>
        </thead>
        <tbody>
EOM;
      foreach($dates as $key => $value) {
        $content .=<<< EOM
        <tr>
          <td>{$value->item_name}</td>
          <td>{$value->quantity}</td>
EOM;
        for( $n = 1; $n <= $yearDiff; ++$n ) {
          $fiscalYear = intval($yearMax) - $n;
          $dateTmp = $this->getCsvImportInsolvencyByBlcode($fiscalYear, $value->bl_code);
          $quantity = 0;
          $decreaseRate = 0;
          foreach($dateTmp as $keyTmp => $valueTmp) {
            if (empty($valueTmp->quantity)) {
              $quantity = 0;
              $decreaseRate = 0;
            } else {
              $quantity = $valueTmp->quantity;
              $decreaseRate = round((intval($value->quantity) / intval($quantity)) * 100);
            }
          }
          $content .=<<< EOM
            <td>{$quantity} / {$decreaseRate}%</td>
EOM;
        }
        $content .=<<< EOM
        </tr>
EOM;
      }
      $content .=<<< EOM
        </tbody>
      </table>
EOM;
      return $content;
    }


    private function makeDisplaySelectBox($yearMax, $yearMin, $id = "") {
      $yearDiff = intval($yearMax) - intval($yearMin);
      $list = "";
      for( $i = 0; $i <= $yearDiff; ++$i ) {
        $fiscalYear = intval($yearMax) - $i;
        $selected = '';
        if(old('select_master_year') !== null) {
          if(old('select_master_year') == $fiscalYear->id) {
            $selected = 'selected';
          }
        }
        $list .=<<< EOM
        <option value="{$fiscalYear}" {$selected}>{$fiscalYear}</option>
EOM;
      }
      return $list;
    }


    private function getImportDateByYear($fiscalYear) {
      $dates = DB::table('csv_import_insolvency_data')
                ->select(DB::raw('fiscal_year, bl_code, item_number, item_name, quantity, occurrence_count'))
                ->where('fiscal_year', $fiscalYear)
                ->orderBy('quantity', 'DESC')
                ->get();
      return $dates;
    }


    private function registCsvTableHead($dates) {
      $csvM = new CsvInsolvencyMasterHead;
      $insertId = $csvM->create($dates)->id;
      return $insertId;
    }


    private function registCsvTable($dates, $id, $dataYearMax) {
      $csvM = new CsvInsolvencyMaster;
      foreach($dates as $key => $value) {
        $decreaseRate = 0;
        foreach($dataYearMax as $keyYearMax => $valueYearMax) {
          if ($value->bl_code == $valueYearMax->bl_code) {
            if (intval($value->quantity) === 0) {
              $decreaseRate = 0;
            } else {
              $decreaseRate = round((intval($valueYearMax->quantity) / intval($value->quantity)), 2);
            }
            break;
          }
        }
        $csvM->create([
          'master_id' => $id,
          'bl_code' => $value->bl_code,
          'item_number' => $value->item_number,
          'item_name' => $value->item_name,
          'quantity' => $value->quantity,
          'occurrence_count' => $value->occurrence_count,
          'decrease_rate' => $decreaseRate,
          'created_user' => Auth::id(),
          'updated_user' => Auth::id(),
        ]);
      }
    }


    /**
     * マスタデータのデータの整形
     *
     * @param array $datas
     * @param string $yearMax
     * @param string $yearMin
     * @return void
     */
    private function makeExportListMaster($datas, $yearMax, $yearMin) {
      $xlsxData = [];
      $header = [];
      $body = [];
      // header
      $yearDiff = intval($yearMax) - intval($yearMin);
      $header[] = '品名';
      $header[] = '個数';
      for( $i = 1; $i <= $yearDiff; ++$i ) {
        $header[] = intval($yearMax) - $i;
      }
      $xlsxData['header'] = $header;
      // body
      foreach($datas as $key => $value) {
        $xlsxRow = [];
        $xlsxRow[] = $value->item_name;
        $xlsxRow[] = $value->quantity;
        for( $n = 1; $n <= $yearDiff; ++$n ) {
          $fiscalYear = intval($yearMax) - $n;
          $dateTmp = $this->getCsvImportInsolvencyByBlcode($fiscalYear, $value->bl_code);
          $quantity = 0;
          $decreaseRate = 0;
          foreach($dateTmp as $keyTmp => $valueTmp) {
            if (empty($valueTmp->quantity)) {
              $quantity = 0;
              $decreaseRate = 0;
            } else {
              $quantity = $valueTmp->quantity;
              $decreaseRate = round((intval($value->quantity) / intval($quantity)) * 100);
            }
          }
          $xlsxRow[] = $quantity . ' / ' . $decreaseRate . '%';
        }
        $body[] = $xlsxRow;
      }
      $xlsxData['body'] = $body;
      return $xlsxData;
    }




}
