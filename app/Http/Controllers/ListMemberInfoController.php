<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\UserRequest;
use App\User;
use Config;

class ListMemberInfoController extends Controller
{
  public function __construct(User $user)
  {
    $this->middleware('auth');

    $this->user = $user;
  }

  public function index()
  {
    $list = $this->user->orderByRaw('role asc, id desc')->get();
    return view('admin.list_member_info', compact('list'));
  }

  // 編集
  public function edit($id)
  {
    $edit = $this->user->find($id);

    return view('admin.member_info', compact('edit'));
  }
  public function update(UserRequest $request, $id) {
    $data = [
      'name' => $request->name,
      'email' => $request->email,
      'address' => $request->address,
      'tel' => $request->tel,
    ];
    $this->user->where('id', $id)->update($data);
    $wording = Config::get('consts.wording.WORDING_UPDATE');
    return redirect()->route('admin.list_member_info.index')->with('success', $wording);
  }

  // 削除
  public function destroy($id)
  {
    $destroy = $this->user->find($id);
    $destroy->delete();

    $wording = Config::get('consts.wording.WORDING_DELETE');
    return redirect()->route('admin.list_member_info.index')->with('success', $wording);
  }
}
