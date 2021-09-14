<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Blog;
use Config;

class ListBlogController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth');
  }

  public function index()
  {
    $list = Blog::latest('id')->get();
    return view('admin.list_blog', compact('list'));
  }

  public function destroy($id) {
    Blog::where('id', $id)->delete();

    $wording = Config::get('consts.wording.WORDING_DELETE');
    return redirect()->route('admin.list_blog.index')->with('success', $wording);
  }
}
