@extends('admin.layouts.template')

@section('title', 'マスタデータ一覧')
@include('admin.layouts.head')

@section('h1', 'マスタデータ一覧')
@section('header_nav')
  <p><a href="{{ route('admin.excellent_comparison.index') }}" class="btn btn-back">戻る</a></p>
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
          <th>登録名</th>
          <th>年度</th>
          <th>入庫台数</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @foreach($csvExcellentMasterHeads as $csvExcellentMasterHead)
          <tr>
            <td>{{ $csvExcellentMasterHead->company_name }}</td>
            <td class="align-r">{{ $csvExcellentMasterHead->fiscal_year }}</td>
            <td class="align-r">{{ $csvExcellentMasterHead->receiving_count }}</td>
            <td class="align-c">
              <form action="{{ route('admin.list_excellent.destroy', $csvExcellentMasterHead->id) }}" method="POST" class="form">
                @csrf
                @method('DELETE')
                <button type="submit" name="" class="btn-delete" onclick='return confirm("削除します。よろしいですか？");'>削除</button>
              </form>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
@endsection
