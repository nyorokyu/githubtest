@extends('admin.layouts.template')

@section('title', '優良顧客比較')
@include('admin.layouts.head')

@section('h1', '優良顧客比較')
@section('header_nav')
  <!-- <p><a href="{{ route('admin.excellent_comparison.export') }}" class="btn">Excel出力</a></p> -->
  <p><a href="{{ route('admin.list_excellent.index') }}" class="btn">マスタ編集</a></p>
  <p><a href="{{ route('admin.home.index') }}" class="btn btn-back">TOPに戻る</a></p>
@endsection
@include('admin.layouts.header')

@section('content')
  @if(session('success'))
    <div class="layout2">
      <div class="success">
        {{ session('success') }}
      </div>
    </div>
  @endif
  <div class="box-flex vertical-t col-3 layout2">
    <section>
      <h2 class="align-c">優良比較データの選択</h2>
      <form action="{{ route('admin.excellent_comparison.store') }}" method="POST" class="form" enctype="multipart/form-data">
        @csrf
        <div>
          <label class="selectbox">
            <select name="select_master" form="form_m_list" data-relation-btn="open-csv-1">
              <option value="0">選択してください</option>
              {!! @$selectBoxMaster !!}
            </select>
          </label>
        </div>
        <!-- @if($errors->has('select_master'))
          <p class="err-msg content-margin-ss">{{$errors->first('select_master')}}</p>
        @endif -->

        <div class="float-r box-float-r">
          <p class="toggle btn-submit no-margin" data-toggle="area-create-master">マスタを新規登録する</p>
        </div>
        <div id="area-create-master" class="content-margin-s {{$openAreaSection1 ?? ''}}">
          <div>
            <input type="file" name="csv_m">
          </div>
          @if($errors->has('csv_m'))
            <p class="err-msg content-margin-ss">{{$errors->first('csv_m')}}</p>
          @endif

          <div class="box-flex">
            <p>登録名：</p>
            <p class="flex2"><input type="text" name="name_m" value="{{old('name_m') ?? ''}}"></p>
          </div>
          @if($errors->has('name_m'))
            <p class="err-msg content-margin-ss">{{$errors->first('name_m')}}</p>
          @endif

          <div class="box-flex">
            <p>入庫台数：</p>
            <p class="flex2"><input type="text" name="cnt_m" value="{{old('cnt_m') ?? ''}}"></p>
          </div>
          @if($errors->has('cnt_m'))
            <p class="err-msg content-margin-ss">{{$errors->first('cnt_m')}}</p>
          @endif

          <div>
            <button type="submit" name="submit_m" class="btn-submit" data-relation-btn="open-csv-1">マスタ登録</button>
          </div>
        </div>
      </form>
    </section>

    <section class="area-section2 {{ $openAreaSection2 ?? '' }}">
      <h2 class="align-c">比較クライアントデータの選択</h2>
      <form action="" method="POST" class="form" enctype="multipart/form-data">
        @csrf
        <div>
          <div>
            <input type="file" name="csv_c">
          </div>
          @if($errors->has('csv_c'))
            <p class="err-msg content-margin-ss">{{$errors->first('csv_c')}}</p>
          @endif
          <div class="box-flex">
            <p>登録名：</p>
            <p class="flex2"><input type="text" name="name_c" value="{{old('name_c') ?? ''}}"></p>
          </div>
          @if($errors->has('name_c'))
            <p class="err-msg content-margin-ss">{{$errors->first('name_c')}}</p>
          @endif
          <div class="box-flex">
            <p>入庫台数：</p>
            <p class="flex2"><input type="text" name="cnt_c" value="{{old('cnt_c') ?? ''}}"></p>
          </div>
          @if($errors->has('cnt_c'))
            <p class="err-msg content-margin-ss">{{$errors->first('cnt_c')}}</p>
          @endif
          <div>
            <button type="submit" name="submit_c" class="btn-submit" data-relation-btn="open-csv-2">登録</button>
          </div>
        </div>
      </form>
    </section>
  </div>

  <!-- テーブルエリア -->
  <div class="layout2 content-margin">
    <div class="box-flex col-3 vertical-t">
      <div class="area-section1">
        <div>
          <div class="cf">
            <form id="form_m_list" action="" method="POST" class="float-r box-float-r form">
              @csrf
              <button type="submit" name="submit_m_list" class="btn open-csv-1" data-open-area="area-section2" disabled>表示</button>
            </form>
          </div>
          {!! @$contentHeadM !!}
        </div>
        {!! @$contentListM !!}
      </div>

      <div class="area-section2 {{ $openAreaSection2 ?? '' }}">
        <div>
          <div class="cf">
            <form action="" method="POST" class="float-r box-float-r form">
              @csrf

                @php
                  $disabled = 'disabled';
                  if($disableOpenAreaSection2 == 'enabled') {
                    $disabled = '';
                  }
                @endphp
                <button type="submit" name="submit_c_list" class="btn open-csv-2" {{$disabled}}>表示</button>
            </form>
          </div>
          {!! @$contentHeadC !!}
        </div>
        {!! @$contentListC !!}
      </div>

      <div class="area-section3  {{ $openAreaSection3 ?? '' }}">
        <div class="box-float-r cf">
          <div class="margin-lr area-section4  {{ $openAreaSection4 ?? '' }}">
            <form action="{{ route('admin.excellent_comparison.export') }}" method="GET">
              @csrf
              <button class="btn">Excel出力</button>
            </form>
          </div>
          <form action="" method="POST" class="form">
            @csrf
            @php
              $disabled = 'disabled';
              if($disableOpenAreaSection3 == 'enabled') {
                $disabled = '';
              }
            @endphp
            <button type="submit" name="submit_comparison" class="btn" {{$disabled}}>表示</button>
          </form>
        </div>
        <ul>
          <br>
          <br>
        </ul>
        {!! @$contentComparison !!}
      </div>

    </div>
  </div>
@endsection
