@extends('admin.layouts.template')

@section('title', '倒産データマスタ一覧')
@include('admin.layouts.head')

@section('h1', '倒産データマスタ一覧')
@section('header_nav')
  <p><a href="{{ route('admin.insolvency_master_import.index') }}" class="btn">マスタ登録</a></p>
  <p><a href="{{ route('admin.home.index') }}" class="btn btn-back">TOPに戻る</a></p>
@endsection
@include('admin.layouts.header')

@section('content')
  <div class="layout2">
    @if(session('success'))
      <div class="success">
        {{ session('success') }}
      </div>
    @endif
    <div class="box-flex content-margin-s vertical-t col-3">
      @if (isset($arrayList['master_id']))
      <div>
        <div>
          <ul>
            <li>全マスタWランキング</li>
            <!-- <li>Wランキング</li> -->
          </ul>
          <div class="content-margin-ss w-80p">
            <a href="{{ route('admin.insolvency_analysis.index', 0) }}" class="btn">データ解析（全マスタ）</a>
          </div>
        </div>
        {!! @$contentListAll !!}
      </div>
      @endif
    </div>

    <div class="box-flex vertical-t col-4">
      @php
        $tempId = '';
        $tempCnt = 0;
        if (isset($arrayList['master_id'])) {
          $counts = array_count_values($arrayList['master_id']);
        } else {
          $counts = [];
        }
      @endphp

      @if (isset($arrayList['master_id']))
        @foreach($arrayList['master_id'] as $masterId)
          @php
            $cnt = $counts[$masterId];
            $index = $loop->index;
          @endphp

          @if ($masterId != $tempId)
          @php
            $tempCnt = 0;
          @endphp
          <div id="master_{{$masterId}}" class="margin-lr">
            <div>
              <ul>
                <li class="ellipsis">登録名：{{ $arrayList['registration_name'][$index] }}</li>
              </ul>
              <form action="{{ route('admin.list_insolvency_master.destroy') }}" method="POST" class="form padding-4 w-80p">
                @csrf
                <input type="hidden" name="area_name" value="master_{{$masterId}}">
                <div>
                  <a href="{{ route('admin.insolvency_analysis.index', $masterId) }}" class="btn content-margin-ss">データ解析（個別）</a>
                </div>
                <div class="box-flex content-margin-s">
                  <button type="submit" name="sort_asc" value="{{ $masterId }}" class="btn btn-back txt-s">↓減少率の低い順</button>
                  <button type="submit" name="sort_desc" value="{{ $masterId }}" class="btn btn-back txt-s">↑減少率の高い順</button>
                </div>
              </form>
            </div>
            <div class="margin-b20">
              <table class="table content-margin-ss">
                <thead>
                  <tr>
                    <th class="w-200">品名</th>
                    <th class="w-50">個数</th>
                    <th class="w-50">{{ $arrayList['fiscal_year'][$index] }}</th>
                  </tr>
                </thead>
                <tbody>
            @endif
            @php
              ++$tempCnt;
            @endphp
                  <tr>
                    <td>{{ $arrayList['item_name'][$index] }}</td>
                    <td>{{ $arrayList['quantity'][$index] }}</td>
                    <td>{{ $arrayList['decrease_rate'][$index] }}%</td>
                  </tr>
            @if ($cnt == $tempCnt)
                </tbody>
              </table>
            </div>
            <div>
              <p>メモ：{!! nl2br($arrayList['memo'][$index]) !!}</p>
            </div>
            <div class="content-margin-ss">
              <form action="{{ route('admin.list_insolvency_master.destroy') }}" method="POST" class="form w-80p">
                @csrf
                @method('DELETE')
                <button type="submit" name="delete" value="{{ $masterId }}" class="btn-delete" onclick='return confirm("削除します。よろしいですか？");'>削除</button>
              </form>
            </div>
          </div>
          @endif
          @php
            $tempId = $masterId;
          @endphp
        @endforeach
      @endif
    </div>
  </div>
@endsection
