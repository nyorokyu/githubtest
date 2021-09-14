<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Blog;

class FrontNewsController extends Controller
{
  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct(Blog $blog)
  {
      $this->blog = $blog;
  }

  public function list()
  {
    $result = $this->blog->where('is_display', 1)->where('is_deleted', 0)->latest('displayed_at')->get();
    return view('news.list', compact('result'));
  }

  public function detail($id) {
    $result = $this->blog->find($id);
    return view('news.detail', compact('result'));
  }
}
