@extends('admin.layouts.template')

@section('title', '会員情報一覧')
@include('admin.layouts.head')

@section('h1', '会員情報一覧')
@section('header_nav')
  <p><a href="#" disabled="disabled" class="btn" onclick="window.print();"/>印刷</a></p>
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
    <table class="table table2 content-margin-ss">
      <thead>
        <tr>
          <th>会員種別</th>
          <th>氏名</th>
          <th>メールアドレス</th>
          <th>住所</th>
          <th>電話番号</th>
          <th>登録日時</th>
          <th>操作</th>
        </tr>
      </thead>
      <tbody>
        @foreach($list as $data)
          <tr>
            @if($data->role == 1)
              <td>管理者</td>
            @elseif($data->role == 5)
              <td>見積作成者</td>
            @else
              <td>一般会員</td>
            @endif
            <td>{{$data->name}}</td>
            <td>{{$data->email}}</td>
            <td>{{$data->address}}</td>
            <td>{{$data->tel}}</td>
            <td>{{$data->created_at}}</td>
            <td class="w-200">
              <form action="{{route('admin.member_info.destroy', $data->id)}}" method="POST" class="form">
                @csrf
                @method('DELETE')
                <div class="box-flex">
                  <a href="{{route('admin.member_info.edit', $data->id)}}" class="btn">変更</a>
                  <button type="submit" name="" class="btn-delete" onclick='return confirm("削除します。よろしいですか？");'>削除</button>
                </div>
              </form>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
@endsection
