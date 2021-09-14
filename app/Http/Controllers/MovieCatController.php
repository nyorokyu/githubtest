<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Requests\MovieCatRequest;
use App\MovieCat;
use Config;

class MovieCatController extends Controller
{

  public function __construct()
  {
    $this->middleware('auth');
  }

  public function index()
  {

    return view('admin.movie_cat');
  }

  public function store(MovieCatRequest $request) {
    $data = [
      'category_name' => $request->category,
      'is_deleted' => 0,
      'created_user' => Auth::id(),
      'updated_user' => Auth::id()
    ];
    MovieCat::create($data);
    $wording = Config::get('consts.wording.WORDING_REGISTRATION');
    return redirect()->route('admin.list_movie.index')->with('success', $wording);
  }
}
