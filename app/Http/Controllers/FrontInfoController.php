<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Ma;

class FrontInfoController extends Controller
{
  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct(Ma $ma)
  {
      $this->ma = $ma;
  }

  public function list()
  {
    $result = $this->ma->where('is_display', 1)->where('is_deleted', 0)->latest('displayed_at')->get();
    return view('info.list', compact('result'));
  }

  public function detail($id) {
    $result = $this->ma->find($id);
    return view('info.detail', compact('result'));
  }
}
