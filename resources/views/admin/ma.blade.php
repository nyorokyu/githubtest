@extends('admin.layouts.template')

@section('title', 'M&A投稿詳細')
@include('admin.layouts.head')

@section('h1', 'M&A投稿詳細')
@section('header_nav')
  <p><a href="{{ route('admin.list_ma.index') }}" class="btn">投稿一覧</a></p>
  <p><a href="{{ route('admin.home.index') }}" class="btn btn-back">TOPに戻る</a></p>
@endsection
@include('admin.layouts.header')

@section('content')
  <div class="content-margin-s layout2">
    @isset($ma->id)
      <form action="{{ route('admin.ma.update', ['id' => $ma->id]) }}" method="POST" class="form form2" enctype="multipart/form-data">
      @method('PUT')
    @else
      <form action="{{ route('admin.ma.store') }}" method="POST" class="form form2" enctype="multipart/form-data">
    @endisset

      @csrf
      <div class="box-flex vertical-t">
        <div>
          <div>
            <p>タイトル：</p>
            <p><input type="text" name="title" value="{{old('title') ?? $ma->title ?? ''}}" required></p>
          </div>
          @if($errors->has('title'))
            <p class="err-msg content-margin-ss">{{$errors->first('title')}}</p>
          @endif

          <div>
            <p>都道府県：</p>
            <p>
              <label class="selectbox">
                <select name="prefectures">
                  <option value="0">選択してください</option>
                  @foreach(config('consts.pref.PREF') as $key => $value)
                    <?php $selected = ''; ?>
                    @if(old('prefectures') !== null)
                      @if(old('prefectures') == $key)
                        <?php $selected = 'selected'; ?>
                      @endif
                    @else
                      @if(($ma !== null) && ($key == $ma->pref_id))
                        <?php $selected = 'selected'; ?>
                      @endif
                    @endif

                    <option value="{{$key}}" {{$selected}}>{{$value}}</option>

                  @endforeach
                </select>
              </label>
            </p>
          </div>
          @if($errors->has('prefectures'))
            <p class="err-msg content-margin-ss">{{$errors->first('prefectures')}}</p>
          @endif

        </div>

        <div class="flex2">
          <div>
            <p>本文：</p>
            <p><textarea id="text-editor" name="content">{{old('content') ?? $ma->content ?? ''}}</textarea></p>
          </div>
          @if($errors->has('content'))
            <p class="err-msg content-margin-ss">{{$errors->first('content')}}</p>
          @endif

          <div class="flex-none">
            <div class="box-flex w-inherit">
              <?php
                $display1 = "";
                $display0 = "";

                if(old('display') !== null) {
                  // 入力チェック
                  if(old('display') == 1) {
                    $display1 = "checked";
                  } elseif(old('display') == 0) {
                    $display0 = "checked";
                  }
                } else {
                  // 変更を開いた時
                  if(isset($ma)) {
                    if($ma->is_display == 1) {
                      $display1 = "checked";
                    } elseif($ma->is_display == 0) {
                      $display0 = "checked";
                    }
                  }
                }
              ?>

              <label>
                <input type="radio" name="display" value="1" {{$display1}}> 公開
              </label>
              <label>
                <input type="radio" name="display" value="0" {{$display0}}> 保存
              </label>
            </div>
          </div>
          @if($errors->has('display'))
            <p class="err-msg content-margin-ss">{{$errors->first('display')}}</p>
          @endif

          <input type="hidden" name="displayed_at_hidden" value="{{$ma->displayed_at ?? ''}}">

          <div>
            <button type="submit" name="submit" class="btn-submit w-30p">登録</button>
          </div>
        </div>
      </div>
    </form>
  </div>
@endsection
