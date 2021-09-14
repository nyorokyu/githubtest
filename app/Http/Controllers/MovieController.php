<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Requests\MovieRequest;
use App\Movie;
use App\MovieCat;
use Carbon\Carbon;
use Config;

class MovieController extends Controller
{

  public function __construct()
  {
    $this->middleware('auth');
  }

  public function index($id = null)
  {
    $movie = null;
    if(!is_null($id)) {
      $movie = Movie::find($id);
    }

    $movieCat = MovieCat::all();
    return view('admin.movie', compact('movie', 'movieCat'));
  }

  public function store(MovieRequest $request) {
    //ファイルをstorageに保存
    $filename = null;
    if($request->file('movie_file') !== null) {
      $filename = $request->file('movie_file')->getClientOriginalName();
      $request->file('movie_file')->storeAs('public/movies', $filename);
    }

    $displayAt = null;
    if($request->display == 1) {
        $now = Carbon::now();
        $displayAt = $now->format('Y-m-d H:i:s');
    }

    $data = [
      'title' => $request->title,
      'category_id' => $request->category_id,
      'movie_file' => $filename,
      'is_display' => $request->display,
      'displayed_at' => $displayAt,
      'is_deleted' => 0,
      'created_user' => Auth::id(),
      'updated_user' => Auth::id()
    ];
    Movie::create($data);
    $wording = Config::get('consts.wording.WORDING_REGISTRATION');
    return redirect()->route('admin.list_movie.index')->with('success', $wording);
  }

  public function update(MovieRequest $request, $id) {
    $filename = null;
    if(isset($request->movie_file_name)) {
      $filename = $request->movie_file_name;
    } else {
      //ファイルをstorageに保存
      if($request->file('movie_file') !== null) {
        $filename = $request->file('movie_file')->getClientOriginalName();
        $request->file('movie_file')->storeAs('public/movies', $filename);
      }
    }

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
      'category_id' => $request->category_id,
      'movie_file' => $filename,
      'is_display' => $request->display,
      'displayed_at' => $displayAt,
      'is_deleted' => 0,
      'updated_user' => Auth::id()
    ];
    Movie::where('id', $id)->update($data);
    $wording = Config::get('consts.wording.WORDING_UPDATE');
    return redirect()->route('admin.list_movie.index')->with('success', $wording);
  }
}
