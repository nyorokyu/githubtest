@extends('admin.layouts.template')

@section('title', '会員情報編集')
@include('admin.layouts.head')

@section('h1', '会員情報編集')
@section('header_nav')
  <!-- <p><a href="{{ route('admin.list_movie.index') }}" class="btn">動画一覧</a></p> -->
  <p><a href="{{ route('admin.list_member_info.index') }}" class="btn btn-back">戻る</a></p>
@endsection
@include('admin.layouts.header')

@section('content')
  <div class="vertical-t col-2 layout2">
    <section>
        <form action="{{route('admin.member_info.update', ['id' => $edit->id])}}" method="POST" class="form" enctype="multipart/form-data">
        @method('PUT')
        @csrf
        <div class="box-flex">
          <p>会員種別：</p>
          <p class="flex2">
            @if($edit->role == 1)
              管理者
            @elseif($edit->role == 5)
              見積作成者
            @else
              一般会員
            @endif
          </p>
        </div>
        <div class="box-flex">
          <p>氏名：</p>
          <p class="flex2"><input type="text" name="name" value="{{old('name') ?? $edit->name ?? ''}}"></p>
        </div>
        @if($errors->has('name'))
          <p class="err-msg content-margin-ss">{{$errors->first('name')}}</p>
        @endif
        <div class="box-flex">
          <p>メールアドレス：</p>
          <p class="flex2"><input type="text" name="email" value="{{old('email') ?? $edit->email ?? ''}}"></p>
        </div>
        @if($errors->has('email'))
          <p class="err-msg content-margin-ss">{{$errors->first('email')}}</p>
        @endif
        <div class="box-flex">
          <p>住所：</p>
          <p class="flex2"><input type="text" name="address" value="{{old('address') ?? $edit->address ?? ''}}"></p>
        </div>
        @if($errors->has('address'))
          <p class="err-msg content-margin-ss">{{$errors->first('address')}}</p>
        @endif
        <div class="box-flex">
          <p>電話番号：</p>
          <p class="flex2"><input type="text" name="tel" value="{{old('tel') ?? $edit->tel ?? ''}}"></p>
        </div>
        @if($errors->has('tel'))
          <p class="err-msg content-margin-ss">{{$errors->first('tel')}}</p>
        @endif
        <div>
          <button type="submit" name="submit" class="btn-submit">登録</button>
        </div>
      </form>
    </section>
  </div>
@endsection
