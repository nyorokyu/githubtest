<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Blog;

class FrontCompanyController extends Controller
{
  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct()
  {

  }

  public function index()
  {
    return view('company.index');
  }

}
