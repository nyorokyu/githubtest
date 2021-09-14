@extends('admin.layouts.template')

@section('title', '見積作成依頼')
@include('admin.layouts.head')

@section('h1', '見積作成依頼')
@section('header_nav')
  <p><a href="{{ route('admin.home.index') }}" class="btn btn-back">TOPに戻る</a></p>
@endsection
@include('admin.layouts.header')

@section('content')
  <div class="content-margin-s layout2">
      <p class="color-r align-c txt-b">このサービスは有料となります。ご請求金額は依頼する下記メニューごとに異なります。</p>
    <div class="box-flex vertical-t col-2">
      <section>
        <form action="" method="POST" class="form" enctype="multipart/form-data">
          <div  class="box-group-inner padding-a w-80p">
            <div>
              <!-- <button type="submit" name="submit_m" class="btn">見積作成依頼</button> -->
              <a href="{{ route('admin.detail_quote.index', 1) }}" class="btn btn-submit">見積作成依頼</a>
            </div>
            <div class="content-margin-s">
              <p>情報や写真をアップロードすることで、プロの見積もり作成者が、より高品質な見積書を作成します。</p>
              <p class="content-margin-ss">ご請求額は<span class="color-r txt-l">見積総額の{{ config('consts.quote.MAKE_QUOTE_1')*100 }}%</span>となります。</p>
            </div>
          </div>
        </form>
      </section>

      <section>
        <form action=""method="POST" class="form" enctype="multipart/form-data">
          <div  class="box-group-inner padding-a w-80p">
            <div>
              <!-- <button type="submit" name="submit_m" class="btn">見積添削依頼</button> -->
              <a href="{{ route('admin.detail_quote.index', 2) }}" class="btn btn-submit">見積添削依頼</a>
            </div>
            <div class="content-margin-s">
              <p>作成した見積書をアップロードすることで、プロの見積もり作成者が添削し、より高品質な見積書を作成します。</p>
              <p class="content-margin-ss">ご請求額は<span class="color-r txt-l">見積差額の{{ config('consts.quote.MAKE_QUOTE_2')*100 }}%</span>となります。</p>
            </div>
          </div>
        </form>
      </section>
    </div>
  </div>
@endsection
