@extends('admin.layouts.template')

@section('title', '倒産データ解析')
@include('admin.layouts.head')

@section('h1', '倒産データ解析')
@section('header_nav')
  <p><a href="{{ route('admin.export_insolvency.export') }}" class="btn">Excel出力</a></p>
  <!-- <p><a href="{{ route('admin.list_insolvency_master.index') }}" class="btn">マスタ編集</a></p> -->
  <p><a href="{{ route('admin.list_insolvency_master.index') }}" class="btn btn-back">戻る</a></p>
@endsection
@include('admin.layouts.header')

@section('content')
  <div class="layout2 box-flex content-margin-s vertical-t">

    <!-- マスタ -->
    <div class="box-relative margin-lr">
      <div>
        {!! @$contentHeadM !!}
      </div>
      <div class="absolute-150">
        <form action="{{ route('admin.insolvency_analysis.store') }}" method="POST" class="form" enctype="multipart/form-data">
          @csrf
          @if(!empty($contentListC) && Session::get('masterId') != 0 )
          <div class="box-flex padding-4">
            <button type="submit" name="sort_asc" value="{{ Session::get('masterId') }}" class="btn btn-back txt-s">↓減少率の低い順</button>
            <button type="submit" name="sort_desc" value="{{ Session::get('masterId') }}" class="btn btn-back txt-s">↑減少率の高い順</button>
          </div>
          @endif
        </form>
      </div>
      <div class="absolute-200">
        {!! @$contentListM !!}
      </div>
    </div>

    <!-- クライアント -->
    <div class="box-relative flex2 margin-lr">
      <form action="{{ route('admin.insolvency_analysis.store') }}" method="POST" class="form" enctype="multipart/form-data">
        <div>
          @csrf
          <div class="flex-none">
            <div class="box-flex">
              <p>比較クライアントデータ</p>
              <p><input type="file" name="csv_c"></p>
            </div>
            @if($errors->has('csv_c'))
              <p class="err-msg content-margin-ss">{{ $errors->first('csv_c') }}</p>
            @endif
          </div>
          <div class="flex-none">
            <div class="box-flex">
              <p>登録名：</p>
              <p class="w-300"><input type="text" name="name_c" value=""></p>
              <p class="w-inherit"><button type="submit" name="import_c" class="btn-submit margin-t-8">表示</button></p>
            </div>
          </div>
        </div>
      </form>
      {!! @$contentHeadC !!}
      <div class="absolute-200">
        {!! @$contentListC !!}
      </div>
    </div>

  </div>

@endsection
