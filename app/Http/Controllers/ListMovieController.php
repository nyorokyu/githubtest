<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Movie;
use Config;

class ListMovieController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth');
  }

  public function index()
  {
    $list = Movie::latest('id')->get();
    return view('admin.list_movie', compact('list'));
  }

  public function destroy($id) {
    Movie::where('id', $id)->delete();

    $wording = Config::get('consts.wording.WORDING_DELETE');
    return redirect()->route('admin.list_movie.index')->with('success', $wording);
  }
}
