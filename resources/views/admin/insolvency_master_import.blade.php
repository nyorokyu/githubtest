@extends('admin.layouts.template')

@section('title', '倒産データマスタ取込')
@include('admin.layouts.head')

@section('h1', '倒産データマスタ取込')
@section('header_nav')
  <p><a href="{{ route('admin.export_insolvency_master.export') }}" class="btn">Excel出力</a></p>
  <!-- <p><a href="{{ route('admin.list_insolvency_master.index') }}" class="btn">マスタ編集</a></p> -->
  <p><a href="{{ route('admin.list_insolvency_master.index') }}" class="btn btn-back">戻る</a></p>
@endsection
@include('admin.layouts.header')

@section('content')
  <section>
    <!-- <h2 class="align-c">優良比較データの選択</h2> -->
    <div class="layout2">
      <form action="{{ route('admin.insolvency_master_import.store') }}" method="POST" class="form" enctype="multipart/form-data">
        <div class="box-flex vertical-t col-3">
          @csrf
          <!-- <div>
            <label class="selectbox">
              <select name="select_master" form="form_m_list">
                <option value="0">選択してください</option>
                {!! @$selectBoxMaster !!}
              </select>
            </label>
          </div>
          <div class="float-r box-float-r">
            <p class="toggle btn-submit no-margin" data-toggle="area-create-master">マスタを新規登録する</p>
          </div> -->
          <div>
            <div>
              <input type="file" name="csv_m">
            </div>
            <!-- @if($errors->has('csv_m'))
              <p class="err-msg content-margin-ss">{{$errors->first('csv_m')}}</p>
            @endif -->

            <div>
              <button type="submit" name="import_m" class="btn-submit">マスタ取込</button>
            </div>
          </div>
          <div id="area-create-master" class="area-section1 {{ $openAreaSection1 ?? '' }}">
            <div>
              <label class="selectbox">
                <select name="select_master_year">
                  <option value="0">選択してください</option>
                  {!! @$selectBoxMasterYear !!}
                </select>
              </label>
            </div>
            <p class="content-margin-ss">登録名：</p>
            <p><input type="text" name="name_m" value="{{old('name_m') ?? ''}}"></p>
            @if($errors->has('name_m'))
              <p class="err-msg content-margin-ss">{{$errors->first('name_m')}}</p>
            @endif

            <p class="content-margin-ss">メモ：</p>
            <p><textarea name="memo_m" rows="5">{{old('memo_m') ?? ''}}</textarea></p>
            @if($errors->has('memo_m'))
              <p class="err-msg content-margin-ss">{{$errors->first('memo_m')}}</p>
            @endif

            <div>
              <button type="submit" name="submit_m" class="btn-submit">マスタ登録</button>
            </div>
          </div>
        </div>
      </form>
    </div>
  </section>


  <!-- テーブルエリア -->
  <div class="content-margin layout2">
    <div>
      <div>
        {!! @$contentList !!}
      </div>
    </div>
  </div>
@endsection
