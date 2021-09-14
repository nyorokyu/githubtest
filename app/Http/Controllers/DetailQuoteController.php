<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\QuoteRequestTable;
use App\QuoteRequestMakeRelationTable;
use App\QuoteRequestImageTable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Mail\MakeMail;
use App\User;
use Session;
use Mail;
use Config;



class DetailQuoteController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth');
  }


  public function index($type, $id = 0)
  {
    Session::put('quoteType', $type);
    Session::put('requestId', $id);
    $quoteType = $type;

    $quoteStatus = $this->getQuoteStatus($id);
    $makeId = $this->getQuoteMakeId($id);
    $targetPath = '/storage/quote/images/';
    $makeQuotePath = '';
    $dispTotalPrice = '0';
    $dispFee = '0';

    // $accidentDetail = $this->getAccidentDetail($id);
    $requestDatas = $this->getQuoteRequestTable($id);
    $accidentDetail = '';
    $maker = '';
    $carModel = '';
    $selfQuoteAmount = '';
    foreach($requestDatas as $requestKey => $requestValue) {
      $accidentDetail = $requestValue->accident_detail;
      $maker = $requestValue->maker;
      $carModel = $requestValue->car_model;
      $selfQuoteAmount = $requestValue->self_quote_amount;
      break;
    }

    if (!empty($makeId)) {
      $datas = $this->getQuoteMakeTable($makeId);
      $wage = 0;
      $partsPrice = 0;
      $paintingWage = 0;
      $paintingPartsPrice = 0;
      $fee = 0;
      foreach($datas as $key => $value) {
        $wage = $value->wage;
        $partsPrice = $value->parts_price;
        $paintingWage = $value->painting_wage;
        $paintingPartsPrice = $value->painting_parts_price;
        $makeQuotePath = $targetPath . $value->quotation_path;
        break;
      }
      $totalPrice = intval($wage) + intval($partsPrice) + intval($paintingWage) + intval($paintingPartsPrice);
      $dispTotalPrice = number_format($totalPrice);

      if ($quoteType == 1) {
        // 手数料 = （工賃＋塗装工賃） * 手数料率
        $fee = floor($totalPrice * config('consts.quote.MAKE_QUOTE_1'));
      } elseif ($quoteType == 2) {
      // 手数料 = （作成者の見積総額 - 依頼者の見積総額） * 手数料率
        $fee = floor(($totalPrice - $selfQuoteAmount) * config('consts.quote.MAKE_QUOTE_2'));
      }
      $dispFee = number_format($fee);

    }

    $disabledFlg = 0;
    if ($id > 0) { $disabledFlg = 1; }

    return view('admin.detail_quote', ['type'=>$type, 'id'=>$id],
              compact('accidentDetail', 'maker', 'carModel', 'selfQuoteAmount', 'makeQuotePath', 'dispTotalPrice', 'dispFee', 'quoteType', 'quoteStatus', 'disabledFlg')
    );

  }


  public function store(Request $request)
  {
    // --------------------------------------------------------------------------
    // 見積依頼
    // --------------------------------------------------------------------------
    if (isset($_POST['submit'])) {

      $quoteType = Session::get('quoteType');

      // バリデーション
      // TODO：スマートな書き方ありそう
      if ($quoteType == Config::get('consts.quote.MAKE_QUOTE_2')) {
        $rules = [
            'accident_detail'=>['required', 'string', 'max:1000'],         // 事故状況
            'maker'=>['required', 'string', 'max:50'],                    // メーカー
            'car_model'=>['required', 'string', 'max:50'],                // 車種
            'self_quote_amount'=>['required'],
            'certificate'=>['required', 'file', 'mimes:jpeg,jpg,pdf'],    // 車検証の写真
            'accident.*'=>['required', 'file', 'mimes:jpeg,jpg,pdf'],     // 事故車の写真
            'caution_plate'=>['required', 'file', 'mimes:jpeg,jpg,pdf'],  // コーションプレートの写真
            'request_quote'=>['required', 'file', 'mimes:jpeg,jpg,pdf'],  // 作成した見積書の写真
        ];

        $messages = [
            'accident_detail.required' => '事故の状況を記入してください。',
            'accident_detail.string' => Config::get('consts.wording.ERROR_INPUT'),
            'accident_detail.max' => '事故の状況を1000文字以内で入力してください。',
            'maker.required' => 'メーカーを記入してください。',
            'maker.string' => Config::get('consts.wording.ERROR_INPUT'),
            'maker.max' => 'メーカーを50文字以内で入力してください。',
            'car_model.required' => '車種を記入してください。',
            'car_model.string' => Config::get('consts.wording.ERROR_INPUT'),
            'car_model.max' => '車種を50文字以内で入力してください。',
            'self_quote_amount.required' => '自身で算出した見積り金額は必須項目です。',
            'certificate.required' => Config::get('consts.wording.ERROR_REQUIRE_FILE'),
            'certificate.file' => Config::get('consts.wording.ERROR_REQUIRE_FILE'),
            'certificate.mimes' => Config::get('consts.wording.ERROR_MIMES'),
            'accident.*.required' => Config::get('consts.wording.ERROR_REQUIRE_FILE'),
            'accident.*.file' => Config::get('consts.wording.ERROR_REQUIRE_FILE'),
            'accident.*.mimes' => Config::get('consts.wording.ERROR_MIMES'),
            'caution_plate.required' => Config::get('consts.wording.ERROR_REQUIRE_FILE'),
            'caution_plate.file' => Config::get('consts.wording.ERROR_REQUIRE_FILE'),
            'caution_plate.mimes' => Config::get('consts.wording.ERROR_MIMES'),
            'request_quote.required' => Config::get('consts.wording.ERROR_REQUIRE_FILE'),
            'request_quote.file' => Config::get('consts.wording.ERROR_REQUIRE_FILE'),
            'request_quote.mimes' => Config::get('consts.wording.ERROR_MIMES'),
        ];

        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            return redirect('admin/detail_quote/2')
                        ->withErrors($validator)
                        ->withInput();
        }

      } else {

        $rules = [
            'accident_detail'=>['required', 'string', 'max:1000'],         // 事故状況
            'maker'=>['required', 'string', 'max:50'],                    // メーカー
            'car_model'=>['required', 'string', 'max:50'],                // 車種
            'certificate'=>['required', 'file', 'mimes:jpeg,jpg,pdf'],    // 車検証の写真
            'accident.*'=>['required', 'file', 'mimes:jpeg,jpg,pdf'],     // 事故車の写真
            'caution_plate'=>['required', 'file', 'mimes:jpeg,jpg,pdf'],  // コーションプレートの写真
        ];

        $messages = [
            'accident_detail.required' => '事故の状況を記入してください。',
            'accident_detail.string' => Config::get('consts.wording.ERROR_INPUT'),
            'accident_detail.max' => '事故の状況を1000文字以内で入力してください。',
            'maker.required' => 'メーカーを記入してください。',
            'maker.string' => Config::get('consts.wording.ERROR_INPUT'),
            'maker.max' => 'メーカーを50文字以内で入力してください。',
            'car_model.required' => '車種を記入してください。',
            'car_model.string' => Config::get('consts.wording.ERROR_INPUT'),
            'car_model.max' => '車種を50文字以内で入力してください。',
            'certificate.required' => Config::get('consts.wording.ERROR_REQUIRE_FILE'),
            'certificate.file' => Config::get('consts.wording.ERROR_REQUIRE_FILE'),
            'certificate.mimes' => Config::get('consts.wording.ERROR_MIMES'),
            'accident.*.required' => Config::get('consts.wording.ERROR_REQUIRE_FILE'),
            'accident.*.file' => Config::get('consts.wording.ERROR_REQUIRE_FILE'),
            'accident.*.mimes' => Config::get('consts.wording.ERROR_MIMES'),
            'caution_plate.required' => Config::get('consts.wording.ERROR_REQUIRE_FILE'),
            'caution_plate.file' => Config::get('consts.wording.ERROR_REQUIRE_FILE'),
            'caution_plate.mimes' => Config::get('consts.wording.ERROR_MIMES'),
        ];

        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            return redirect('admin/detail_quote/1')
                        ->withErrors($validator)
                        ->withInput();
        }
      }

      $targetPath = storage_path('/app/public/quote/images/');
      $fileNameCertificate = null;
      $fileNameAccident = null;
      $fileNameCautionPlate = null;
      $fileNameRequestQuote = null;
      if ($file = $request->certificate) {
        $fileNameCertificate = 'certificate_' . time() . '.' . $file->getClientOriginalExtension();
        $file->move($targetPath, $fileNameCertificate);
      }
      // if ($file = $request->accident) {
      //   $fileNameAccident = 'accident_' . time() . '.' . $file->getClientOriginalExtension();
      //   $file->move($targetPath, $fileNameAccident);
      // }
      $fileNameAccident = null;
      if ($file = $request->caution_plate) {
        $fileNameCautionPlate = 'caution_plate_' . time() . '.' . $file->getClientOriginalExtension();
        $file->move($targetPath, $fileNameCautionPlate);
      }
      if ($file = $request->request_quote) {
        $fileNameRequestQuote = 'request_quote_' . time() . '.' . $file->getClientOriginalExtension();
        $file->move($targetPath, $fileNameRequestQuote);
      }

      $accidentDetail  = $request->input('accident_detail');
      $maker  = $request->input('maker');
      $carModel  = $request->input('car_model');
      if (empty($request->input('self_quote_amount'))) {
        $selfQuoteAmount = NULL;
      } else {
        $selfQuoteAmount  = str_replace(',', '', $request->input('self_quote_amount'));
      }
      $datas = [
        'quote_request_type' => $quoteType,
        'maker' => $maker,                                    // メーカー
        'car_model' => $carModel,                             // 車種
        'accident_detail' => $accidentDetail,                 // 事故状況
        'self_quote_amount' => $selfQuoteAmount,              // 自身で算出した見積り金額
        'vic_image_path' => $fileNameCertificate,             // 車検証画像パス
        'accident_car_image_path' => $fileNameAccident,       // 事故車画像パス
        'caution_plate_image_path' => $fileNameCautionPlate,  // コーションプレート画像パス
        'quotation_image_path' => $fileNameRequestQuote,      // 見積書画像パス
        'created_user' => Auth::id(),
        'updated_user' => Auth::id(),
      ];
      $insertId = $this->registQuoteRequestTable($datas);
      if ($file = $request->accident) {
        for($i = 0; $i < count($request->accident); $i++ ) {
          $fileNameAccident = '';
          $fileNameAccident = 'accident_' . time() . '_' . $i . '.' . $file[$i]->getClientOriginalExtension();
          $file[$i]->move($targetPath, $fileNameAccident);
          $images = [
            'quote_request_id' => $insertId,
            'image_type' => '2',  // 2: 事故車
            'image_path' => $fileNameAccident,
            'created_user' => Auth::id(),
            'updated_user' => Auth::id(),
          ];
          $this->registQuoteRequestImageTable($images);
        }
      }
      $this->registQuoteRequestMakeRelationTable($insertId);

      //メール送信
      foreach(User::all() as $user) {
        $data = [];
        if($user->role == Config::get('consts.role.SYSTEM_ADMIN')) {
          //システム管理者に一斉メール送信
          $data = array_merge($data, [
            'subject' => '新しい見積作成依頼が届きました',
            'template' => 'emails.quoteRequestMailAdmin',
            'id' => $insertId
          ]);
        } else if($user->role == Config::get('consts.role.QUOTE_MEMBER')) {
          //見積作成者権限の会員に一斉メール送信
          $data = array_merge($data, [
            'subject' => '新しい見積作成依頼が届きました',
            'template' => 'emails.quoteRequestMail',
            'id' => $insertId
          ]);
        }

        if(!empty($data)) {
          Mail::to($user->email)->send(new MakeMail($data));
        }
      }

      $wording = Config::get('consts.wording.WORDING_QUOTE_REQUEST');
      return redirect()->route('admin.list_quote.index')->with('success', $wording);

    }

    return redirect()->route('admin.list_quote.index');

  }






  /*
  |--------------------------------------------------------------------------
  | private function
  |--------------------------------------------------------------------------
  */
  private function registQuoteRequestTable($datas) {
    $table = new QuoteRequestTable;
    $insertId = $table->create($datas)->id;
    return $insertId;
  }


  private function registQuoteRequestImageTable($datas) {
    $table = new QuoteRequestImageTable;
    $table->create($datas);
  }


  private function registQuoteRequestMakeRelationTable($requestId) {
    $table = new QuoteRequestMakeRelationTable;
    $table->create([
      'quote_status' => '1',  // 依頼中
      'quote_request_id' => $requestId,
      'quote_make_id' => NULL,
      'created_user' => Auth::id(),
      'updated_user' => Auth::id(),
    ]);
  }


  private function getQuoteMakeId($requestId) {
    $quoteMakeId = DB::table('quote_request_make_relation_table')
              ->where([['is_deleted', 0], ['quote_request_id', $requestId]])
              ->value('quote_make_id');
    return $quoteMakeId;
  }


  private function getQuotationPath($makeId) {
    $quotationPath = DB::table('quote_make_table')
              ->where([['is_deleted', 0], ['id', $makeId]])
              ->value('quotation_path');
    return $quotationPath;
  }


  private function getQuoteMakeTable($makeId) {
    $datas = DB::table('quote_make_table')
              ->select(DB::raw('wage, parts_price, painting_wage, painting_parts_price, quotation_path'))
              ->where([['is_deleted', 0], ['id', $makeId]])
              ->get();
    return $datas;
  }


  private function getQuoteRequestTable($requestId) {
    $datas = DB::table('quote_request_table')
              ->select(DB::raw('quote_request_type, maker, car_model, accident_detail, self_quote_amount, vic_image_path, accident_car_image_path, caution_plate_image_path, quotation_image_path'))
              ->where([['is_deleted', 0], ['id', $requestId]])
              ->get();
    return $datas;
  }


  private function getAccidentDetail($requestId) {
    $accidentDetail = DB::table('quote_request_table')
              ->where([['is_deleted', 0], ['id', $requestId]])
              ->value('accident_detail');
    return $accidentDetail;
  }


  private function getQuoteStatus($requestId) {
    $quoteStatus = DB::table('quote_request_make_relation_table')
              ->where([['is_deleted', 0], ['quote_request_id', $requestId]])
              ->value('quote_status');
    return $quoteStatus;
  }





}
