@extends('admin.layouts.template')

@section('title', '見積依頼一覧')
@include('admin.layouts.head')

@section('h1', '見積依頼一覧')
@section('header_nav')
  @can('GENERAL_MEMBER')
  <p><a href="{{ route('admin.request_for_quote.index') }}" class="btn">新規作成</a></p>
  @endcan
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
          <th>依頼主</th>
          <th>依頼日時</th>
          <th>ステータス</th>
          <th>操作</th>
        </tr>
      </thead>
      <tbody>
        @foreach($qr as $data)
          <tr>
            <td>
              <?php $user = App\User::find($data->created_user); ?>
              {{$user->name}}
            </td>
            <td>{{$data->created_at}}</td>
            <td>
              @foreach(config('consts.quote.STATUS_JAPANESE') as $key => $val)
                @if($key == $data->quoteRequestMakeRelationTables->quote_status)
                  {{$val}}
                  @break
                @endif
              @endforeach
            </td>
            <td>
              <form action="{{route('admin.list_quote.update', ['id' => $data->id])}}" method="POST" class="form">
                @csrf
                @method('PUT')
                <div class="box-flex">
                  @can('GENERAL_MEMBER')
                    <a href="{{route('admin.detail_quote.index', [$data->quote_request_type, $data->id])}}" class="btn">詳細</a>
                  @else
                    @if($data->quoteRequestMakeRelationTables->quote_status != config('consts.quote.STATUS_MAKING') || Auth::user()->can('SYSTEM_ADMIN'))
                      <a href="{{route('admin.make_quote.index', [$data->quoteRequestMakeRelationTables->quote_request_id])}}" class="btn">詳細</a>
                    @endif
                  @endcan

                  <!-- ステータスが「作成中」の場合 -->
                  @if($data->quoteRequestMakeRelationTables->quote_status == config('consts.quote.STATUS_MAKING'))

                    @can('QUOTE_MEMBER')
                    <a href="{{route('admin.make_quote.index', [$data->id])}}" class="btn">見積作成</a>
                    @endcan
                  @endif

                  <!-- ステータスが「依頼中」の場合 -->
                  @if($data->quoteRequestMakeRelationTables->quote_status == config('consts.quote.STATUS_REQUESTING'))

                    @can('QUOTE_MEMBER')
                    <button type="submit" name="make_quote" class="btn">見積承諾</button>
                    @endcan

                  @endif

                  <!-- ステータスが「入金待ち」の場合 -->
                  @if($data->quoteRequestMakeRelationTables->quote_status == config('consts.quote.STATUS_PAYMENT_WAITING'))

                    @can('SYSTEM_ADMIN')
                    <button type="submit" name="allow_download" class="btn">ダウンロード許可</button>
                    @endcan

                  @endif
                </div>
              </form>
            </td>
          </tr>
          @endforeach
      </tbody>
    </table>
  </div>
@endsection
