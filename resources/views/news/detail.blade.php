@extends('layouts.template')

@section('title', 'お知らせ一覧')
@include('layouts.head')

@include('layouts.header')

@section('content')
<section id="area-detail">
  <h2 class="title">{{$result->title}}</h2>
  <p class="align-r"><time datetime="{{$result->displayed_at->format('Y-m-d')}}">{{$result->displayed_at->format('Y-m-d')}}</time> <span class="area-category">{{$result->blogCat->category_name}}</span></p>
  <div id="area-content">
    {!! $result->content !!}
  </div>
  <p class="content-margin-s"><a href="{{route('news.list')}}" class="btn history-back"><i class="fas fa-chevron-circle-left"></i>戻る</a></p>
</section>
@endsection
