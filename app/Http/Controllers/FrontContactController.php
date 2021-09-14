<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Blog;
use App\Http\Requests\ContactRequest;
use App\Mail\MakeMail;
use App\User;
use Config;
use Mail;

class FrontContactController extends Controller
{
  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct()
  {

  }

  public function index($title = null)
  {
    if($title !== null) {
      $title .= 'について';
    }
    return view('contact.index', compact('title'));
  }

  public function sendmail(ContactRequest $request)
  {
    //システム管理者に一斉メール送信
    foreach(User::all() as $user) {
      if($user->role == Config::get('consts.role.SYSTEM_ADMIN')) {
        //システム管理者に一斉メール送信
        $data = [
          'subject' => 'webサイトからのお問い合わせ',
          'template' => 'emails.contactMailAdmin',
          'from' => $request->email,
          'request' => $request
        ];
        Mail::to($user->email)->send(new MakeMail($data));
      }
    }

    //お問い合わせユーザーにメール送信
    $data = [
      'subject' => 'お問い合わせありがとうございます',
      'template' => 'emails.contactMail',
      'request' => $request
    ];
    Mail::to($request->email)->send(new MakeMail($data));

    $wording = Config::get('consts.wording.WORDING_CONTACT_THANKS');
    return redirect()->route('contact.index')->with('success', $wording);
  }

}
