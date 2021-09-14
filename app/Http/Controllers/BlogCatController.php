<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Requests\BlogCatRequest;
use App\BlogCat;
use Config;

class BlogCatController extends Controller
{

  public function __construct()
  {
    $this->middleware('auth');
  }

  public function index()
  {

    return view('admin.blog_cat');
  }

  public function store(BlogCatRequest $request) {
    $data = [
      'category_name' => $request->category,
      'is_deleted' => 0,
      'created_user' => Auth::id(),
      'updated_user' => Auth::id()
    ];
    BlogCat::create($data);
    $wording = Config::get('consts.wording.WORDING_REGISTRATION');
    return redirect()->route('admin.list_blog.index')->with('success', $wording);
  }
}
