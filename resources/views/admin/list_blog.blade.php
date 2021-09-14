@extends('admin.layouts.template')

@section('title', 'その他の投稿一覧')
@include('admin.layouts.head')

@section('h1', 'その他の投稿一覧')
@section('header_nav')
  <p><a href="{{ route('admin.blog.index') }}" class="btn">新規追加</a></p>
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
    <table class="table content-margin-ss">
      <thead>
        <tr>
          <th>タイトル</th>
          <th>本文</th>
          <th>投稿日時</th>
          <th>操作</th>
        </tr>
      </thead>
      <tbody>
        @foreach($list as $data)
          <tr>
            @if($data->displayed_at == null)
              <td><span class="label-private">（非公開）</span> {{$data->title}}</td>
            @else
              <td>{{$data->title}}</td>
            @endif
            <td class="text-hidden">{{html_entity_decode(strip_tags($data->content))}}</td>
            <td>{{$data->displayed_at ?? $data->displayed_at_hidden}}</td>
            <td class="w-200">
              <form action="{{route('admin.list_blog.destroy', $data->id)}}" method="POST" class="form">
                @csrf
                @method('DELETE')
                <div class="box-flex">
                  <a href="{{route('admin.blog.index', $data->id)}}" class="btn">変更</a>
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
