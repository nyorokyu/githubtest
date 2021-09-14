@extends('layouts.template')

@section('title', ' | お問い合わせ')
@include('layouts.head')

@include('layouts.header')

@section('content')
<section id="area-detail">
  <h2 class="title">Contact <span class="txt-s">- お問い合わせ -</span></h2>
  @if(session('success'))
    <div class="success">
      {!! session('success') !!}
    </div>
  @endif
  <div class="layout1">
    <form action="{{route('contact.sendmail')}}" method="POST" class="form">
      @csrf
      <div>
        <label class="box-relative">氏名<span class="required">必須</span>
          <input type="text" name="name" value="" required>
        </label>
      </div>
      @if($errors->has('name'))
        <p class="err-msg content-margin-ss">{{$errors->first('name')}}</p>
      @endif

      <div>
        <label class="box-relative">メールアドレス<span class="required">必須</span>
          <input type="email" name="email" value="" required>
        </label>
      </div>
      @if($errors->has('email'))
        <p class="err-msg content-margin-ss">{{$errors->first('email')}}</p>
      @endif

      <div>
        <label class="box-relative">電話番号
          <input type="tel" name="tel" value="">
        </label>
      </div>
      @if($errors->has('tel'))
        <p class="err-msg content-margin-ss">{{$errors->first('tel')}}</p>
      @endif

      <div>
        <label class="box-relative">お問い合わせ内容<span class="required">必須</span>
          <textarea name="message" rows="5" required>{{$title ?? ''}}</textarea>
        </label>
      </div>
      @if($errors->has('message'))
        <p class="err-msg content-margin-ss">{{$errors->first('message')}}</p>
      @endif

      <div>
        <button type="submit" name="submit" class="btn-submit">送信する</button>
      </div>
    </form>
  </div>
  <p class="content-margin-s"><a href="{{route('frontpage')}}" class="btn"><i class="fas fa-chevron-circle-left"></i>トップに戻る</a></p>
</section>
@endsection
