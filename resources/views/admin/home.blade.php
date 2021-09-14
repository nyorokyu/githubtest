@extends('admin.layouts.template')

@section('title', 'ダッシュボード')
@include('admin.layouts.head')

@section('h1', 'ダッシュボード')
@include('admin.layouts.header')

@section('content')
  <div id="area-admin-home" class="layout2">
    <section>
      <div>
        <h2 class="txt-b">見積書</h2>
        <ul class="box-flex col-4">
          <li><a href="{{ route('admin.list_quote.index') }}" class="btn">見積一覧</a></li>
          @can('GENERAL_MEMBER')
            <li><a href="{{ route('admin.request_for_quote.index') }}" class="btn">見積作成依頼</a></li>
          @endcan
        </ul>
      </div>

      @can('SYSTEM_ADMIN')
        <div class="content-margin-s">
          <h2 class="txt-b">データ解析</h2>
          <ul class="box-flex col-4">
            <li><a href="{{ route('admin.excellent_comparison.index') }}" class="btn">優良顧客比較</a></li>
            <li><a href="{{ route('admin.list_insolvency_master.index') }}" class="btn">倒産データ解析</a></li>
          </ul>
        </div>
        <div class="content-margin-s">
          <h2 class="txt-b">動画</h2>
          <ul class="box-flex col-4">
            <li><a href="{{ route('admin.list_movie.index') }}" class="btn">動画一覧</a></li>
            <li><a href="{{ route('admin.movie.index') }}" class="btn">動画投稿</a></li>
            <li><a href="{{ route('admin.movie_cat.index') }}" class="btn">動画カテゴリ登録</a></li>
          </ul>
        </div>
        <div class="content-margin-s">
          <h2 class="txt-b">M&A</h2>
          <ul class="box-flex col-4">
            <li><a href="{{ route('admin.list_ma.index') }}" class="btn">M&A一覧</a></li>
            <li><a href="{{ route('admin.ma.index') }}" class="btn">M&A投稿</a></li>
          </ul>
        </div>
        <div class="content-margin-s">
          <h2 class="txt-b">その他</h2>
          <ul class="box-flex col-4">
            <li><a href="{{ route('admin.list_blog.index') }}" class="btn">その他一覧</a></li>
            <li><a href="{{ route('admin.blog.index') }}" class="btn">その他投稿</a></li>
            <li><a href="{{ route('admin.blog_cat.index') }}" class="btn">その他カテゴリ登録</a></li>
          </ul>
        </div>
      @endcan
    </section>
  </div>
@endsection
