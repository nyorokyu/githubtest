@extends('admin.layouts.template')

@section('title', '見積作成依頼詳細')
@include('admin.layouts.head')

@section('h1', '見積作成依頼詳細')
@section('header_nav')
  <p><a href="{{ route('admin.list_quote.index') }}" class="btn btn-back">戻る</a></p>
@endsection
@include('admin.layouts.header')

@section('content')
  <div class="content-margin-s vertical-t layout2">
    <div class="frame">
      <form action="{{ route('admin.detail_quote.store') }}" method="POST" class="form form2 w-inherit" enctype="multipart/form-data">
        @csrf
        <div class="margin-0">
          <div class="box-flex margin-0">
            <p>メーカー：</p>
            @if ($disabledFlg == 0)
            <p class="flex3"><input type="text" name="maker" value="{{ old('maker') ?? $maker ?? '' }}" required></p>
            @else
            <p class="flex3"><input type="text" name="maker" value="{{ $maker }}" disabled></p>
            @endif
          </div>
          @if($errors->has('maker'))
            <p class="err-msg content-margin-ss">{{ $errors->first('maker') }}</p>
          @endif
          <div class="box-flex">
            <p>車種：</p>
            @if ($disabledFlg == 0)
            <p class="flex3"><input type="text" name="car_model" value="{{ old('car_model') ?? $carModel ?? '' }}" required></p>
            @else
            <p class="flex3"><input type="text" name="car_model" value="{{ $carModel }}" disabled></p>
            @endif
          </div>
          @if($errors->has('car_model'))
            <p class="err-msg content-margin-ss">{{ $errors->first('car_model') }}</p>
          @endif
          @if ($quoteType == '2')
          <div class="box-flex">
            <p>自身で算出した見積り金額：</p>
            @if ($disabledFlg == 0)
            <p class="flex3"><input type="text" class="txt-r number-separator" name="self_quote_amount" value="{{ old('self_quote_amount') ?? $selfQuoteAmount ?? '' }}" required> 円</p>
            @else
            <p class="flex3"><input type="text" class="txt-r" name="self_quote_amount" value="{{ number_format($selfQuoteAmount) }}" disabled> 円</p>
            @endif
          </div>
          @if($errors->has('self_quote_amount'))
            <p class="err-msg content-margin-ss">{{ $errors->first('self_quote_amount') }}</p>
          @endif
          @endif
        </div>
        <div>
          <p class="color-r txt-b">個人情報保護のため、車検証などの個人情報が分からないように考慮し、アップロードしてください。</p>
        </div>
        <div class="box-flex">
          <p>車検証の写真：</p>
          @if ($disabledFlg == 0)
          <p class="flex3"><input type="file" name="certificate" required></p>
          @else
          <p class="flex3"><input type="file" name="certificate" disabled></p>
          @endif
        </div>
        @if($errors->has('certificate'))
          <p class="err-msg content-margin-ss">{{ $errors->first('certificate') }}</p>
        @endif
        <div class="box-flex">
          <p>事故車の写真：</p>
          @if ($disabledFlg == 0)
          <p class="flex3"><input type="file" name="accident[]" multiple required></p>
          @else
          <p class="flex3"><input type="file" name="accident" disabled></p>
          @endif
        </div>
        <p class="margin-t txt-s color-r">※ドラッグ&amp;ドロップで複数の画像をまとめてアップロードできます</p>
        @if($errors->has('accident.*'))
          <p class="err-msg content-margin-ss">{{ $errors->first('accident.*') }}</p>
        @endif

        <div class="box-flex">
          <p>コーションプレートの写真：</p>
          @if ($disabledFlg == 0)
          <p class="flex3"><input type="file" name="caution_plate" required></p>
          @else
          <p class="flex3"><input type="file" name="caution_plate" disabled></p>
          @endif
        </div>
        @if($errors->has('caution_plate'))
          <p class="err-msg content-margin-ss">{{ $errors->first('caution_plate') }}</p>
        @endif

        @if ($quoteType == '2')
        <div class="box-flex">
          <p>作成した見積書の写真：</p>
          @if ($disabledFlg == 0)
          <p class="flex3"><input type="file" name="request_quote" required></p>
          @else
          <p class="flex3"><input type="file" name="request_quote" disabled></p>
          @endif
        </div>
        @if($errors->has('request_quote'))
          <p class="err-msg content-margin-ss">{{ $errors->first('request_quote') }}</p>
        @endif
        @endif

        <div>
          <p>事故の状況を記入</p>
          <p>（損傷の入力方向、相手車輌の有無、衝突時の速度、天候　など）：</p>
          @if ($disabledFlg == 0)
          <p class="flex3"><textarea name="accident_detail" rows="5" required>{{old('accident_detail') ?? $accidentDetail ?? ''}}</textarea></p>
          @else
          <p class="flex3"><textarea name="accident_detail" rows="5" disabled>{{old('accident_detail') ?? $accidentDetail ?? ''}}</textarea></p>
          @endif
        </div>
        @if($errors->has('accident_detail'))
          <p class="err-msg content-margin-ss">{{ $errors->first('accident_detail') }}</p>
        @endif

        <div>
          @php
            $disabled = '';
            if($disabledFlg == 1) {
              $disabled = 'disabled';
            }

            $fee = 100;
            $str = '見積総額';
            if($quoteType == 1) {
              $fee = config('consts.quote.MAKE_QUOTE_1') * 100;
            } else if($quoteType == 2) {
              $fee = config('consts.quote.MAKE_QUOTE_2') * 100;
              $str = '見積差額';
            }

          @endphp
          <p class="color-r"><label><input type="checkbox" name="agree" {{$disabled}}> このサービスは有料となります。{{$str}}の<span class="txt-l">{{$fee}}%</span>を見積作成者に支払うことに同意します。</label></p>
        </div>
        <div>
          <button type="submit" name="submit" class="btn-submit" disabled>見積依頼</button>
        </div>
      </form>
    </div>
    @if ($quoteStatus == '3' || $quoteStatus == '4')
    <div class="content-margin-ss b-color frame">
      <form action="{{ route('admin.detail_quote.store') }}" method="POST" class="form form2" enctype="multipart/form-data">
        @csrf
        <div class="box-flex">
          <p>見積総額：</p>
          <p class="flex3"><span class="txt-l">{{ $dispTotalPrice }}</span> 円</p>
        </div>
        <div>
          <p class="color-r">問題なければ下記口座に <span class="txt-l">{{ $dispFee }}円</span> をご入金ください。ご入金確認後、正式な見積書をダウンロード可能になります。</p>
          <p class="color-r">【振込先金融機関】</p>
          <p class="color-r">西日本シティ銀行　姪浜駅前支店　普通　3206584　カ）アミティ</p>
          <p class="color-r">振込金額： {{ $dispFee }}円</p>
        </div>
        <div class="box-flex">
          <p>お見積書：</p>
          @if ($quoteStatus == '4')
          <p class="flex3"><a href="{{ url($makeQuotePath) }}" download class="btn">ダウンロード</a></p>
          @else
          <p class="flex3"><button type="" name="" class="btn" disabled>ダウンロード</button></p>
          @endif
        </div>
      </form>
    </div>
    @endif
  </div>
@endsection
