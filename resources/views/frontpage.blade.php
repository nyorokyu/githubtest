@extends('layouts.template')

@section('title', 'フロントページ')
@include('layouts.head')

@include('layouts.header')

@section('content')
<div id="area-quote" class="box-group">
  <ul class="area-slick">
    @foreach($quoteData as $data)
    <li>{{$data['maker']}}の{{$data['car_model']}}　{{$data['self_quote_amount']}}円の見積もりが<span class="txt-l underline blink">{{$data['amount']}}円</span>になりました。（<span class="underline blink"><span class="txt-l">{{$data['ratio']}}%</span>アップ！</span>）</li>
    @endforeach
  </ul>
</div>
<section id="area-blog">
  <h2 class="title">News <span class="txt-s">- お知らせ -</span></h2>
  @foreach($news as $data)
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
  <p class="content-margin-s"><a href="{{route('news.list')}}" class="btn">一覧画面へ<i class="fas fa-chevron-circle-right"></i></a></p>
</section>
<section id="area-movies">
  <h2 class="title">Movies <span class="txt-s">- オススメ動画 -</span></h2>
  @canany(['SYSTEM_ADMIN', 'GENERAL_MEMBER'])
    <!-- メッセージ出力なし -->
  @else
    <p class="align-c notes"><span class="txt-l">会員様のみログイン時に動画を閲覧することができます。</span></p>
  @endcanany
  <div class="box-flex col-3">
    @foreach($movie as $data)
      <div class="box-flex flex-col sp-flex">
        <h3 class="txt-l order2 content-margin-ss ellipsis">{{$data->title}}</h3>
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
  <p class="content-margin-s"><a href="{{route('movie.list')}}" class="btn">一覧画面へ<i class="fas fa-chevron-circle-right"></i></a></p>
</section>
<section id="area-information">
  <h2 class="title">Information <span class="txt-s">- M&A情報 -</span></h2>
  <div class="box-flex col-2">
    @foreach($info as $data)
      <div>
        <h3 class="txt-l ellipsis">{{$data->title}}</h3>
        <p class="align-r"><time datetime="{{$data->displayed_at->format('Y-m-d')}}">{{$data->displayed_at->format('Y-m-d')}}</time>
          <span class="area-category">
            @foreach(config('consts.pref.PREF') as $key => $value)
              @if($key == $data->pref_id)
                {{$value}}
              @endif
            @endforeach
          </span></p>
        <div class="ellipsis">
          {{html_entity_decode(strip_tags($data->content))}}
        </div>
        <div class="box-btn content-margin-s">
          <p class="align-r"><a href="{{route('info', $data->id)}}" class="btn">詳細を見る<i class="fas fa-chevron-circle-right"></i></a></p>
        </div>
      </div>
    @endforeach
  </div>
  <p class="content-margin-s"><a href="{{route('info.list')}}" class="btn">一覧画面へ<i class="fas fa-chevron-circle-right"></i></a></p>
</section>
@endsection
