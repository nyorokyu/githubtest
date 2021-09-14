@extends('admin.layouts.template')

@section('title', '動画投稿詳細')
@include('admin.layouts.head')

@section('h1', '動画投稿詳細')
@section('header_nav')
  <p><a href="{{ route('admin.list_movie.index') }}" class="btn">動画一覧</a></p>
  <p><a href="{{ route('admin.home.index') }}" class="btn btn-back">TOPに戻る</a></p>
@endsection
@include('admin.layouts.header')

@section('content')
  <div class="vertical-t col-2 layout2">
    <section>
        @isset($movie->id)
          <form action="{{route('admin.movie.update', ['id' => $movie->id])}}" method="POST" class="form" enctype="multipart/form-data">
          @method('PUT')
        @else
          <form action="{{route('admin.movie.store')}}" method="POST" class="form" enctype="multipart/form-data">
        @endisset

        @csrf
        <div class="box-flex">
          <p>タイトル：</p>
          <p class="flex2"><input type="text" name="title" value="{{old('title') ?? $movie->title ?? ''}}" required></p>
        </div>
        @if($errors->has('title'))
          <p class="err-msg content-margin-ss">{{$errors->first('title')}}</p>
        @endif

        <div class="box-flex">
          <p>カテゴリ：</p>
          <p class="flex2">
            <label class="selectbox">
              <select name="category_id">
                <option value="0">選択してください</option>
                @foreach($movieCat as $category)
                  <?php $selected = ''; ?>
                  @if(old('category_id') !== null)
                    @if(old('category_id') == $category->id)
                      <?php $selected = 'selected'; ?>
                    @endif
                  @else
                    @if(($movie !== null) && ($category->id == $movie->category_id))
                      <?php $selected = 'selected'; ?>
                    @endif
                  @endif

                  <option value="{{$category->id}}" {{$selected}}>{{$category->category_name}}</option>

                @endforeach
              </select>
            </label>
          </p>
        </div>
        @if($errors->has('category_id'))
          <p class="err-msg content-margin-ss">{{$errors->first('category_id')}}</p>
        @endif

        <!-- <div class="box-btn box-flex txt-s">
          <a href="{{route('admin.movie_cat.index')}}" class="btn">カテゴリ追加</a>
        </div> -->

        <div class="area-change-content">
          @isset($movie->movie_file)
          <div class="box-flex vertical-t">
            <p>動画ファイル：</p>
            <div class="flex2">
              {{$movie->movie_file}}
              <div class="box-flex box-btn">
                <button type="button" name="del_movie" class="txt-s btn-delete" onclick='return confirm("削除します。よろしいですか？");'>動画を削除</button>
              </div>
            </div>
          </div>
          <div>
            <video controls>
              <source src="{{URL::asset('storage/movies/'.$movie->movie_file)}}">
            </video>
          </div>
          <input type="hidden" name="movie_file_name" value="{{$movie->movie_file ?? ''}}">
          @else
          <div class="box-flex">
            <p>動画ファイル：</p>
            <p class="flex2"><input type="file" name="movie_file"></p>
          </div>
          @endisset
        </div>
        @if($errors->has('movie_file'))
          <p class="err-msg content-margin-ss">{{$errors->first('movie_file')}}</p>
        @endif

        <div class="flex-none">
          <div class="box-flex w-inherit">
            <label>
              <input type="radio" name="display" value="1" required {{isset($movie) && $movie->is_display == 1 ? 'checked' : ''}}> 公開
            </label>
            <label>
              <input type="radio" name="display" value="0" required {{isset($movie) && $movie->is_display == 0 ? 'checked' : ''}}> 保存
            </label>
          </div>
        </div>
        @if($errors->has('display'))
          <p class="err-msg content-margin-ss">{{$errors->first('display')}}</p>
        @endif

        <input type="hidden" name="displayed_at_hidden" value="{{$movie->displayed_at ?? ''}}">

        <div>
          <button type="submit" name="submit" class="btn-submit">登録</button>
        </div>
      </form>
    </section>
  </div>
@endsection
