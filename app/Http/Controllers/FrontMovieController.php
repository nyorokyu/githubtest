<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Movie;

class FrontMovieController extends Controller
{
  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct(Movie $movie)
  {
      $this->movie = $movie;
  }

  public function list()
  {
    $result = $this->movie->where('is_display', 1)->where('is_deleted', 0)->latest('displayed_at')->get();
    return view('movie.list', compact('result'));
  }
}
