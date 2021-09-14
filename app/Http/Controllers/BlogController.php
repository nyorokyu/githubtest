<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Requests\BlogRequest;
use App\Blog;
use App\BlogCat;
use Carbon\Carbon;
use Config;

class BlogController extends Controller
{

  public function __construct()
  {
    $this->middleware('auth');
  }

  public function index($id = null)
  {
    $blog = null;
    if(!is_null($id)) {
      $blog = Blog::find($id);
    }

    $blogCat = BlogCat::all();
    return view('admin.blog', compact('blog', 'blogCat'));
  }

  public function store(BlogRequest $request) {
    $displayAt = null;
    if($request->display == 1) {
        $now = Carbon::now();
        $displayAt = $now->format('Y-m-d H:i:s');
    }

    $data = [
      'title' => $request->title,
      'category_id' => $request->categories,
      'content' => $request->content,
      'is_display' => $request->display,
      'displayed_at' => $displayAt,
      'is_deleted' => 0,
      'created_user' => Auth::id(),
      'updated_user' => Auth::id()
    ];
    Blog::create($data);
    $wording = Config::get('consts.wording.WORDING_REGISTRATION');
    return redirect()->route('admin.list_blog.index')->with('success', $wording);
  }

  public function update(BlogRequest $request, $id) {
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
      'category_id' => $request->categories,
      'content' => $request->content,
      'is_display' => $request->display,
      'displayed_at' => $displayAt,
      'is_deleted' => 0,
      'updated_user' => Auth::id()
    ];
    Blog::where('id', $id)->update($data);
    $wording = Config::get('consts.wording.WORDING_UPDATE');
    return redirect()->route('admin.list_blog.index')->with('success', $wording);
  }
}
