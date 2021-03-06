<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\QuoteMakeTable;
use App\QuoteRequestMakeRelationTable;
use App\QuoteRequestTable;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Mail\MakeMail;
use Session;
use Config;
use Mail;
use ZipArchive;



class MakeQuoteController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth');
  }


  public function index($id = 0)
  {
    $makeId = $this->getQuoteMakeId($id);
    Session::put('requestId', $id);
    Session::put('makeId', $makeId);

    $requestId = Session::get('requestId');
    $qr = QuoteRequestTable::find($requestId);

    $quoteStatus = $this->getQuoteStatus($id);
    $datas = $this->getQuoteRequestTable($id);
    // $targetPath = public_path('/storage/quote/images/');
    $targetPath = '/storage/quote/images/';
    $quoteType = '';
    $maker = '';
    $carModel = '';
    $selfQuoteAmount = '';
    $accidentDetail = '';
    $certificatePath = '';
    // $accidentPath = '';
    $cautionPlatePath = '';
    $requestQuotePath = '';

    $dispSelfQuoteAmount = 0;

    foreach($datas as $key => $value) {
      $quoteType = $value->quote_request_type;
      $maker = $value->maker;
      $carModel = $value->car_model;
      $selfQuoteAmount = $value->self_quote_amount;
      $accidentDetail = nl2br($value->accident_detail);
      $certificatePath = $targetPath . $value->vic_image_path;
      // $accidentPath = $targetPath . $value->accident_car_image_path;
      $cautionPlatePath = $targetPath . $value->caution_plate_image_path;
      $requestQuotePath = $targetPath . $value->quotation_image_path;

      $dispSelfQuoteAmount = number_format($selfQuoteAmount);
      break;
    }

    $totalPrice = 0;
    $dispTotalPrice = 0;
    $depositAmount = 0;

    if (empty($makeId)) {
      $wage = '';
      $parts = '';
      $paintingWages = '';
      $paintingParts = '';

    } else {
      $makeDatas = $this->getQuoteMakeTable($makeId);
      foreach($makeDatas as $makeKey => $makeValue) {
        $wage = $makeValue->wage;
        $parts = $makeValue->parts_price;
        $paintingWages = $makeValue->painting_wage;
        $paintingParts = $makeValue->painting_parts_price;

        $totalPrice = intval($wage) + intval($parts) + intval($paintingWages) + intval($paintingParts);
        $dispTotalPrice = number_format($totalPrice);
        break;
      }
    }

    // ????????????????????????
    // $fee = Config::get('consts.quote.MAKE_QUOTE_1');
    // if($qr->quote_request_type == 2) {
    //   $fee = Config::get('consts.quote.MAKE_QUOTE_2');
    // }

    if($totalPrice != 0) {
      if ($qr->quote_request_type == 1) {
        // ????????? = ??????????????????????????? * ????????????
        $depositAmount = floor($totalPrice * config('consts.quote.MAKE_QUOTE_1'));
      } elseif ($qr->quote_request_type == 2) {
      // ????????? = ??????????????????????????? - ??????????????????????????? * ????????????
        $depositAmount = floor(($totalPrice - $selfQuoteAmount) * config('consts.quote.MAKE_QUOTE_2'));
      }

      // $depositAmount = (intval($wage) + intval($parts) + intval($paintingWages) + intval($paintingParts)) * $fee;
      $depositAmount = number_format($depositAmount);
    }

    return view('admin.make_quote', ['id'=>$id],
              compact('maker', 'carModel', 'dispSelfQuoteAmount', 'accidentDetail', 'certificatePath', 'cautionPlatePath', 'requestQuotePath',
                      'wage', 'parts', 'paintingWages', 'paintingParts', 'quoteType', 'quoteStatus', 'dispTotalPrice', 'depositAmount'
              )
    );
  }


  public function store(Request $request)
  {
    $requestId = Session::get('requestId');
    $qr = QuoteRequestTable::find($requestId);

    // --------------------------------------------------------------------------
    // Download images
    // --------------------------------------------------------------------------
    $targetPath = storage_path('/app/public/quote/images/');
    // ?????????
    if (isset($_POST['dl_accident'])) {
      $files = $this->getQuoteRequestImageTable($requestId ,Config::get('consts.quote.IMAGE_TYPE_ACCIDENT'));
      $zip = new ZipArchive();
      $zipFileName = 'accident_' . time() . '.zip';
      $zip->open($targetPath . $zipFileName, ZipArchive::CREATE);
      foreach($files as $fileKey => $fileValue) {
        $fileName = $fileValue->image_path;
        $filePath = $targetPath . $fileName;
        $zip->addFile($filePath, $fileName);
      }
      $zip->close();
      return response()->download($targetPath . $zipFileName)->deleteFileAfterSend(true);
    }

    // --------------------------------------------------------------------------
    // ????????????
    // --------------------------------------------------------------------------
    if($request->has('make_quote')) {
      //??????????????????????????????
      $data = [
        'quote_status' => Config::get('consts.quote.STATUS_MAKING'),
        'updated_user' => Auth::id()
      ];
      QuoteRequestMakeRelationTable::where('quote_request_id', $requestId)->update($data);

      //????????????????????????
      $data = [
        'quote_make_user' => Auth::id(),
        'updated_user' => Auth::id()
      ];
      QuoteRequestTable::where('id', $requestId)->update($data);

      $wording = Config::get('consts.wording.WORDING_QUOTE_APPROVAL');
      return redirect()->route('admin.make_quote.index', ['id' => $requestId])->with('success', $wording);

    // --------------------------------------------------------------------------
    // ????????????
    // --------------------------------------------------------------------------
    } elseif (isset($_POST['submit'])) {
      // ?????????????????????
      $rules = [
          // 'wage'=>['required', 'integer', 'max:9999999999'],                // ??????
          // 'parts'=>['required', 'integer', 'max:9999999999'],               // ??????
          // 'painting_wages'=>['required', 'integer', 'max:9999999999'],      // ????????????
          // 'painting_parts'=>['required', 'integer', 'max:9999999999'],      // ????????????
          'wage'=>['required', 'max:13'],                // ??????
          'parts'=>['required', 'max:13'],               // ??????
          'painting_wages'=>['required', 'max:13'],      // ????????????
          'painting_parts'=>['required', 'max:13'],      // ????????????
          'quotation'=>['required', 'file', 'mimes:jpeg,jpg,pdf'],          // ?????????
      ];

      $messages = [
          'wage.required' => '??????????????????????????????',
          // 'wage.integer' => '????????????????????????????????????',
          'wage.max' => '?????????10???????????????????????????????????????',
          'parts.required' => '??????????????????????????????',
          // 'parts.integer' => '????????????????????????????????????',
          'parts.max' => '?????????10???????????????????????????????????????',
          'painting_wages.required' => '????????????????????????????????????',
          // 'painting_wages.integer' => '????????????????????????????????????',
          'painting_wages.max' => '???????????????10???????????????????????????????????????',
          'painting_parts.required' => '????????????????????????????????????',
          // 'painting_parts.integer' => '????????????????????????????????????',
          'painting_parts.max' => '???????????????10???????????????????????????????????????',
          'quotation.required' => Config::get('consts.wording.ERROR_REQUIRE_FILE'),
          'quotation.file' => Config::get('consts.wording.ERROR_REQUIRE_FILE'),
          'quotation.mimes' => '???????????? jpeg, jpg, pdf ???????????????????????????????????????',
      ];

      $validator = Validator::make($request->all(), $rules, $messages);
      if ($validator->fails()) {
          return redirect()->route('admin.make_quote.index', ['id'=>$requestId])
                      ->withErrors($validator)
                      ->withInput();
      }

      $relationId = $this->getRelationId($requestId);

      $wage  = str_replace(',', '', $request->input('wage'));
      $parts  = str_replace(',', '', $request->input('parts'));
      $paintingWages  = str_replace(',', '', $request->input('painting_wages'));
      $paintingParts  = str_replace(',', '', $request->input('painting_parts'));
      $targetPath = storage_path('/app/public/quote/images/');
      if ($file = $request->quotation) {
        $fileNameMakeQuote = 'make_quote_' . time() . '.' . $file->getClientOriginalExtension();
        $file->move($targetPath, $fileNameMakeQuote);
      }
      $datas = [
        'wage' => $wage,
        'parts_price' => $parts,
        'painting_wage' => $paintingWages,
        'painting_parts_price' => $paintingParts,
        'quotation_path' => $fileNameMakeQuote,
        'created_user' => Auth::id(),
        'updated_user' => Auth::id(),
      ];
      $insertId = $this->registQuoteMakeTable($datas);
      $this->updateQuoteRequestMakeRelationTable($relationId, $insertId);

      //???????????????
      foreach(User::all() as $user) {
        $data = [];
        if($user->role == Config::get('consts.role.SYSTEM_ADMIN')) {
          //?????????????????????????????????????????????
          $data = [
            'subject' => '?????????????????????????????????',
            'template' => 'emails.makeQuoteMailAdmin',
            'type' => $qr->quote_request_type,
            'id' => $qr->id
          ];
          Mail::to($user->email)->send(new MakeMail($data));
        }
      }

      //?????????????????????????????????
      $fee = Config::get('consts.quote.MAKE_QUOTE_1');
      if($qr->quote_request_type == 2) {
        $fee = Config::get('consts.quote.MAKE_QUOTE_2');
      }
      $depositAmount = (intval($wage) + intval($parts) + intval($paintingWages) + intval($paintingParts)) * $fee;
      $depositAmount = number_format($depositAmount);
      $user = User::find($qr->created_user);
      $data = [
        'subject' => '?????????????????????????????????',
        'template' => 'emails.makeQuoteMail',
        'deposit_amount' => $depositAmount,
        'type' => $qr->quote_request_type,
        'id' => $qr->id
      ];
      Mail::to($user->email)->send(new MakeMail($data));

      $wording = Config::get('consts.wording.WORDING_QUOTE_CONFIRM');
      return redirect()->route('admin.list_quote.index')->with('success', $wording);

    // --------------------------------------------------------------------------
    // ????????????????????????
    // --------------------------------------------------------------------------
    } elseif ($request->has('allow_download')) {
      $requestId = Session::get('requestId');
      $relationId = $this->getRelationId($requestId);

      //??????????????????????????????
      $data = [
        'quote_status' => Config::get('consts.quote.STATUS_PAID'),
        'updated_user' => Auth::id()
      ];
      QuoteRequestMakeRelationTable::where('id', $relationId)->update($data);

      //?????????????????????????????????
      $user = User::find($qr->created_user);
      $data = [
        'subject' => '??????????????????????????????????????????????????????',
        'template' => 'emails.allowDownloadMail',
        'type' => $qr->quote_request_type,
        'id' => $qr->id
      ];
      Mail::to($user->email)->send(new MakeMail($data));

      $wording = Config::get('consts.wording.WORDING_QUOTE_PERMIT');
      return redirect()->route('admin.list_quote.index')->with('success', $wording);
    }

  }









  /*
  |--------------------------------------------------------------------------
  | private function
  |--------------------------------------------------------------------------
  */
  private function getQuoteRequestId($makeId) {
    $quoteRequestId = DB::table('quote_request_make_relation_table')
              ->where([['is_deleted', 0], ['quote_make_id', $makeId]])
              ->value('quote_request_id');
    return $quoteRequestId;
  }


  private function getQuoteMakeId($requestId) {
    $quoteMakeId = DB::table('quote_request_make_relation_table')
              ->where([['is_deleted', 0], ['quote_request_id', $requestId]])
              ->value('quote_make_id');
    return $quoteMakeId;
  }


  private function getRelationId($requestId) {
    $relationId = DB::table('quote_request_make_relation_table')
              ->where([['is_deleted', 0], ['quote_request_id', $requestId]])
              ->value('id');
    return $relationId;
  }


  private function getQuoteStatus($requestId) {
    $quoteStatus = DB::table('quote_request_make_relation_table')
              ->where([['is_deleted', 0], ['quote_request_id', $requestId]])
              ->value('quote_status');
    return $quoteStatus;
  }


  private function getQuoteRequestTable($requestId) {
    $datas = DB::table('quote_request_table')
              ->select(DB::raw('quote_request_type, maker, car_model, accident_detail, self_quote_amount, vic_image_path, accident_car_image_path, caution_plate_image_path, quotation_image_path'))
              ->where([['is_deleted', 0], ['id', $requestId]])
              ->get();
    return $datas;
  }


  private function getQuoteMakeTable($makeId) {
    $datas = DB::table('quote_make_table')
              ->select(DB::raw('wage, parts_price, painting_wage, painting_parts_price, quotation_path'))
              ->where([['is_deleted', 0], ['id', $makeId]])
              ->get();
    return $datas;
  }


  private function registQuoteMakeTable($datas) {
    $table = new QuoteMakeTable;
    $insertId = $table->create($datas)->id;
    return $insertId;
  }


  private function updateQuoteRequestMakeRelationTable($relationId, $makeId) {
    $table = new QuoteRequestMakeRelationTable;
    $dates = $table::find($relationId);
    $dates->quote_status = '3'; // ????????????
    $dates->quote_make_id = $makeId;
    $dates->updated_user = Auth::id();
    $dates->save();
  }


  private function getQuoteRequestImageTable($requestId, $imageType) {
    $datas = DB::table('quote_request_image_table')
              ->select(DB::raw('id, image_path'))
              ->where([['is_deleted', 0], ['quote_request_id', $requestId], ['image_type', $imageType]])
              ->get();
    return $datas;
  }







}
