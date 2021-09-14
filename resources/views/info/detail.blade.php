@extends('layouts.template')

@section('title', ' | M&A情報')
@include('layouts.head')

@include('layouts.header')

@section('content')
<section id="area-detail">
    <h2 class="title">{{$result->title}}</h2>
      <p class="align-r"><time datetime="{{$result->displayed_at->format('Y-m-d')}}">{{$result->displayed_at->format('Y-m-d')}}</time>
        <span class="area-category">
          @foreach(config('consts.pref.PREF') as $key => $value)
            @if($key == $result->pref_id)
              {{$value}}
            @endif
          @endforeach
        </span></p>
      <div id="area-content">
        <p>{!! $result->content !!}</p>
      </div>
  <p class="content-margin-s"><a href="{{route('contact.index', $result->title)}}" class="btn"><i class="fas fa-envelope-open-text"></i>お問い合わせ</a></p>
  <p class="content-margin-s"><a href="{{route('info.list')}}" class="btn history-back"><i class="fas fa-chevron-circle-left"></i>戻る</a></p>
</section>
@endsection
