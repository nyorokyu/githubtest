@extends('layouts.template')

@section('title', 'お知らせ一覧')
@include('layouts.head')

@include('layouts.header')

@section('content')
<section id="area-list">
  <h2 class="title">News <span class="txt-s">- お知らせ -</span></h2>
  <div class="box-flex col-2">
    @foreach($result as $data)
      <div>
        <h3 class="txt-l ellipsis">{{$data->title}}</h3>
        <p class="align-r"><time datetime="{{$data->displayed_at->format('Y-m-d')}}">{{$data->displayed_at->format('Y-m-d')}}</time> <span class="area-category">{{$data->blogCat->category_name}}</span></p>
        <div class="ellipsis">
          {{html_entity_decode(strip_tags($data->content))}}
        </div>
        <div class="box-btn content-margin-s">
          <p class="align-r"><a href="{{route('news', $data->id)}}" class="btn">詳細を見る<i class="fas fa-chevron-circle-right"></i></a></p>
        </div>
      </div>
    @endforeach
  </div>
  <p class="content-margin-s"><a href="{{route('frontpage')}}" class="btn"><i class="fas fa-chevron-circle-left"></i>トップに戻る</a></p>
</section>
@endsection
