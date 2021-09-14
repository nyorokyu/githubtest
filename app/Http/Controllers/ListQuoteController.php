<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\QuoteRequestTable;
use App\QuoteRequestMakeRelationTable;
use Session;
use Illuminate\Support\Facades\Auth;
use Config;

class ListQuoteController extends Controller
{

  public function __construct(QuoteRequestTable $qr, QuoteRequestMakeRelationTable $qrmr)
  {
    $this->middleware('auth');

    $this->qr = $qr;
    $this->qrmr = $qrmr;
  }


  public function index()
  {
    $qr = null;
    //-----------------------------------------------------
    // 管理者 role = 1 : SYSTEM_ADMIN
    //-----------------------------------------------------
    if (Auth::user()->can('SYSTEM_ADMIN')) {
      //全データ取得
      $qr = $this->qr->latest('id')->get();

    //-----------------------------------------------------
    // 見積作成者 role = 5 : QUOTE_MEMBER
    //-----------------------------------------------------
    } elseif(Auth::user()->can('QUOTE_MEMBER')) {
      //自身が承諾、作成したデータ及び、ステータスが依頼中のデータを取得
      $qr = $this->qr->where('quote_make_user', Auth::id())
              ->orWhereHas('quoteRequestMakeRelationTables', function($query) {
                $query->where('quote_status', Config::get('consts.quote.STATUS_REQUESTING'));
              })->latest('id')->get();

    //-----------------------------------------------------
    // 一般会員（見積依頼） role = 10 : GENERAL_MEMBER
    //-----------------------------------------------------
    } elseif( Auth::user()->can('GENERAL_MEMBER')) {
      //自身が依頼したデータ
      $qr = $this->qr->where('created_user', Auth::id())->latest('id')->get();
    }

    return view('admin.list_quote', compact('qr'));
  }

  public function update(Request $request, $id) {
    // $message = '';
    if($request->has('make_quote')) {
      //見積ステータスを更新
      $data = [
        'quote_status' => Config::get('consts.quote.STATUS_MAKING'),
        'updated_user' => Auth::id()
      ];
      $this->qrmr->where('quote_request_id', $id)->update($data);

      //見積作成者を更新
      $data = [
        'quote_make_user' => Auth::id(),
        'updated_user' => Auth::id()
      ];
      $this->qr->where('id', $id)->update($data);

      return redirect()->route('admin.make_quote.index', ['id' => $id]);
    } else if($request->has('allow_download')) {
      //見積ステータスを更新
      $data = [
        'quote_status' => Config::get('consts.quote.STATUS_PAID'),
        'updated_user' => Auth::id()
      ];
      $this->qrmr->where('quote_request_id', $id)->update($data);

      // $message = '依頼者が見積書をダウンロード可能になりました。';
    }

    $wording = Config::get('consts.wording.WORDING_QUOTE_PERMIT');
    return redirect()->route('admin.list_quote.index')->with('success', $wording);
    // return redirect()->route('admin.list_quote.index')->with('success', $message);
  }
}
