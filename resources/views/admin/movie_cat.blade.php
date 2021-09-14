@extends('admin.layouts.template')

@section('title', '動画カテゴリ登録')
@include('admin.layouts.head')

@section('h1', '動画カテゴリ登録')
@section('header_nav')
  <p><a href="{{ route('admin.home.index') }}" class="btn btn-back">TOPに戻る</a></p>
@endsection
@include('admin.layouts.header')

@section('content')
  <div class="vertical-t col-2 layout2">
    <section>
      <form action="{{route('admin.movie_cat.store')}}" method="POST" class="form">
        @csrf
        <div class="box-flex">
          <p>カテゴリ：</p>
          <p class="flex2"><input type="text" name="category" required></p>
        </div>

      @if($errors->has('category'))
        <p class="err-msg content-margin-ss">{{$errors->first('category')}}</p>
      @endif

        <div>
          <button type="submit" name="submit" class="btn-submit">登録</button>
        </div>
      </form>
    </section>
  </div>
@endsection
