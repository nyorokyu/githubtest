@extends('layouts.template')

@section('title', ' | 会社概要')
@include('layouts.head')

@include('layouts.header')

@section('content')
<section id="area-detail" class="area-company">
  <!-- <h2 class="txt-l align-c">『知らない』から『損をする』を解消します</h2>
  <div class="services txt-s">
    <ul>
      <li>
        <dl>
          <dt class="txt-l">デジタルコンテンツ配信</dt>
          <dd>カーオーナー向け整備提案</dd>
        </dl>
      </li>
      <li>
        <dl>
          <dt class="txt-l">ビジネスマッチング</dt>
          <dd>見積アドバイス</dd>
          <dd>M＆A</dd>
          <dd>スキルシェア</dd>
        </dl>
      </li>
      <li>
        <dl>
          <dt class="txt-l">業務改善</dt>
          <dd>RPA導入支援</dd>
          <dd>事業拡大支援</dd>
        </dl>
      </li>
    </ul>
  </div> -->
  <p><img src="{{asset('images/slide1.jpg')}}" alt=""></p>
  <p><img src="{{asset('images/slide2.jpg')}}" alt=""></p>
  <p><img src="{{asset('images/slide3.jpg')}}" alt=""></p>
  <p><img src="{{asset('images/slide4.jpg')}}" alt=""></p>
  <p><img src="{{asset('images/slide5.jpg')}}" alt=""></p>
  <p><img src="{{asset('images/slide6.jpg')}}" alt=""></p>
  <h2 class="title content-margin">Company <span class="txt-s">- 会社概要 -</span></h2>
  <table class="table">
    <tr>
      <th>会社名</th>
      <td>株式会社AmiT</td>
    </tr>
    <tr>
      <th>所在地</th>
      <td>福岡市西区室見が丘２－１－９</td>
    </tr>
    <tr>
      <td colspan="2">
        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2702.0517900445006!2d130.31476251577323!3d33.520757925071194!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x35419530cb637771%3A0xfdd646f9e6b9ad9!2z44CSODE5LTAwMzAg56aP5bKh55yM56aP5bKh5biC6KW_5Yy65a6k6KaL44GM5LiY77yS5LiB55uu77yR4oiS77yZ!5e0!3m2!1sja!2sjp!4v1606725819309!5m2!1sja!2sjp" frameborder="0" style="border:0;" allowfullscreen="" aria-hidden="false" tabindex="0" class="googlemap content-margin-s"></iframe>
      </td>
    </tr>
  </table>
  <p class="content-margin-s"><a href="{{route('frontpage')}}" class="btn"><i class="fas fa-chevron-circle-left"></i>トップに戻る</a></p>
</section>
@endsection
