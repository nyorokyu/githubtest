@extends('admin.layouts.template')

@section('title', 'その他投稿詳細')
@include('admin.layouts.head')

@section('h1', 'その他投稿詳細')
@section('header_nav')
  <p><a href="{{ route('admin.list_blog.index') }}" class="btn">その他投稿一覧</a></p>
  <p><a href="{{ route('admin.home.index') }}" class="btn btn-back">TOPに戻る</a></p>
@endsection
@include('admin.layouts.header')

@section('content')
  <div class="content-margin-s layout2">
    @isset($blog->id)
      <form action="{{ route('admin.blog.update', ['id' => $blog->id]) }}" method="POST" class="form form2" enctype="multipart/form-data">
      @method('PUT')
    @else
      <form action="{{ route('admin.blog.store') }}" method="POST" class="form form2" enctype="multipart/form-data">
    @endisset

      @csrf
      <div class="box-flex vertical-t">
        <div>
          <div>
            <p>タイトル：</p>
            <p><input type="text" name="title" value="{{old('title') ?? $blog->title ?? ''}}" required></p>
          </div>
          @if($errors->has('title'))
            <p class="err-msg content-margin-ss">{{$errors->first('title')}}</p>
          @endif

          <div>
            <p>カテゴリ：</p>
            <p>
              <label class="selectbox">
                <select name="categories">
                  <option value="0">選択してください</option>
                  @foreach($blogCat as $category)
                    <?php $selected = ''; ?>
                    @if(old('categories') !== null)
                      @if(old('categories') == $category->id)
                        <?php $selected = 'selected'; ?>
                      @endif
                    @else
                      @if(($blog !== null) && ($category->id == $blog->category_id))
                        <?php $selected = 'selected'; ?>
                      @endif
                    @endif

                    <option value="{{$category->id}}" {{$selected}}>{{$category->category_name}}</option>

                  @endforeach
                </select>
              </label>
            </p>
          </div>
          @if($errors->has('categories'))
            <p class="err-msg content-margin-ss">{{$errors->first('categories')}}</p>
          @endif

          <!-- <div class="box-btn box-flex txt-s">
            <a href="{{route('admin.blog_cat.index')}}" class="btn">カテゴリ追加</a>
          </div> -->
        </div>

        <div class="flex2">
          <div>
            <p>本文：</p>
            <p><textarea id="text-editor" name="content">{{old('content') ?? $blog->content ?? ''}}</textarea></p>
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
                  if(isset($blog)) {
                    if($blog->is_display == 1) {
                      $display1 = "checked";
                    } elseif($blog->is_display == 0) {
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

          <input type="hidden" name="displayed_at_hidden" value="{{$blog->displayed_at ?? ''}}">

          <div>
            <button type="submit" name="submit" class="btn-submit w-30p">登録</button>
          </div>
        </div>
      </div>
    </form>
  </div>
@endsection
