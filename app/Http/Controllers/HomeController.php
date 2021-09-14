<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Session;



class HomeController extends Controller
{
  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct()
  {
      $this->middleware('auth');
  }

  /**
   * Show the application dashboard.
   *
   * @return \Illuminate\Contracts\Support\Renderable
   */
  public function index()
  {
      // Session delete
      Session::forget('contentHeadM');
      Session::forget('contentListM');
      Session::forget('contentHeadC');
      Session::forget('contentListC');
      Session::forget('exportHeaderM');
      Session::forget('exportDateM');

      return view('admin.home');
  }
}
