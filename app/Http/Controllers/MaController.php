<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Requests\MaRequest;
use App\Ma;
use App\Prefecture;
use Carbon\Carbon;
use Config;

class MaController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth');
  }

  public function index($id = null)
  {
    $ma = null;
    if(!is_null($id)) {
      $ma = Ma::find($id);
    }

    $prefecture = Prefecture::all();
    return view('admin.ma', compact('ma', 'prefecture'));
  }

  public function store(MaRequest $request) {
    $displayAt = null;
    if($request->display == 1) {
        $now = Carbon::now();
        $displayAt = $now->format('Y-m-d H:i:s');
    }

    $data = [
      'title' => $request->title,
      'pref_id' => $request->prefectures,
      'content' => $request->content,
      'is_display' => $request->display,
      'displayed_at' => $displayAt,
      'is_deleted' => 0,
      'created_user' => Auth::id(),
      'updated_user' => Auth::id()
    ];
    Ma::create($data);
    $wording = Config::get('consts.wording.WORDING_REGISTRATION');
    return redirect()->route('admin.list_ma.index')->with('success', $wording);
  }

  public function update(MaRequest $request, $id) {
    if($request->displayed_at_hidden !== null) {
      if($request->display == 1) {
        $displayAt = $request->displayed_at_hidden;
      } else {
        $displayAt = null;
      }
    } else {
      if($request->display == 1) {
        $now = Carbon::now();
        $displayAt = $now->format('Y-m-d H:i:s');
      } else {
        $displayAt = null;
      }
    }

    $data = [
      'title' => $request->title,
      'pref_id' => $request->prefectures,
      'content' => $request->content,
      'is_display' => $request->display,
      'displayed_at' => $displayAt,
      'is_deleted' => 0,
      'updated_user' => Auth::id()
    ];
    Ma::where('id', $id)->update($data);
    $wording = Config::get('consts.wording.WORDING_UPDATE');
    return redirect()->route('admin.list_ma.index')->with('success', $wording);
  }
}
