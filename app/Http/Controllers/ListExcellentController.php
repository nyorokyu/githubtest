<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\CsvExcellentMasterHead;
use App\CsvExcellentMaster;
use Session;
use Config;


class ListExcellentController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth');
  }

  public function index()
  {
    // Session delete
    Session::forget('contentHeadM');
    Session::forget('contentListM');
    Session::forget('contentHeadC');
    Session::forget('contentListC');
    Session::forget('exportHeaderM');
    Session::forget('exportDateM');

    $csvExcellentMasterHeads = CsvExcellentMasterHead::latest('id')->get();
    return view('admin.list_excellent', compact('csvExcellentMasterHeads'));
  }

  public function destroy($id) {
    CsvExcellentMasterHead::where('id', $id)->delete();
    CsvExcellentMaster::where('master_id', $id)->delete();

    $wording = Config::get('consts.wording.WORDING_DELETE');
    return redirect()->route('admin.list_excellent.index')->with('success', $wording);
  }
}
