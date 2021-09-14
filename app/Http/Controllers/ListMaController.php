<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Ma;
use Config;

class ListMaController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth');
  }

  public function index()
  {
    $list = Ma::latest('id')->get();
    return view('admin.list_ma', compact('list'));
  }

  public function destroy($id) {
    Ma::where('id', $id)->delete();

    $wording = Config::get('consts.wording.WORDING_DELETE');
    return redirect()->route('admin.list_ma.index')->with('success', $wording);
  }
}
