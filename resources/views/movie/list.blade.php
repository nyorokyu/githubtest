@extends('layouts.template')

@section('title', '動画一覧')
@include('layouts.head')

@include('layouts.header')

@section('content')
<section id="area-movies">
  <h2 class="title">Movies <span class="txt-s">- オススメ動画 -</span></h2>
  @canany(['SYSTEM_ADMIN', 'GENERAL_MEMBER'])
    <!-- メッセージ出力なし -->
  @else
    <p class="align-c notes"><span class="txt-l">会員様のみログイン時に動画を閲覧することができます。</span></p>
  @endcanany
  <div class="box-flex col-3">
    @foreach($result as $data)
      <div class="box-flex flex-col sp-flex">
        <h3 class="txt-l order2 content-margin-ss">{{$data->title}}</h3>
        <p class="order3"><time datetime="{{$data->displayed_at->format('Y-m-d')}}">{{$data->displayed_at->format('Y-m-d')}}</time> <span class="area-category">{{$data->movieCat->category_name}}</span></p>
        @canany(['SYSTEM_ADMIN', 'GENERAL_MEMBER'])
          <p class="order1">
            <video controls>
              <source src="{{URL::asset('storage/movies/'.$data->movie_file)}}">
            </video>
          </p>
        @endcanany
      </div>
    @endforeach
  </div>
  <p class="content-margin-s"><a href="{{route('frontpage')}}" class="btn"><i class="fas fa-chevron-circle-left"></i>トップに戻る</a></p>
</section>
@endsection
