@extends('admin.layouts.template')

@section('title', '見積作成詳細')
@include('admin.layouts.head')

@section('h1', '見積作成詳細')
@section('header_nav')
  <p><a href="{{ route('admin.list_quote.index') }}" class="btn btn-back">戻る</a></p>
@endsection
@include('admin.layouts.header')

@section('content')
<div class="content-margin-s vertical-t layout2">
  @if(session('success'))
    <div class="success">
      {{ session('success') }}
    </div>
  @endif
  <form action="{{ route('admin.make_quote.store') }}" method="POST" class="box-flexvertical-b" enctype="multipart/form-data">
    @csrf
    <div>
      <div class="flex-none">
        <div class="box-flex p-margin">
          <p>メーカー：</p>
          <p>{{ $maker }}</p>
          <p>車種：</p>
          <p>{{ $carModel }}</p>
          @if ($quoteType == '2')
          <p>依頼者が算出した見積り金額：</p>
          <p>{{ $dispSelfQuoteAmount }} 円</p>
          @endif
        </div>
        <div class="box-flex content-margin-s p-margin">
          <p>車検証の写真：</p>
          <p><a href="{{ url($certificatePath) }}" download class="btn download-image">ダウンロード</a></p>

          <p>事故車の写真：</p>
          <p><button type="submit" name="dl_accident" class="btn download-image">ダウンロード</button></p>

          <p>コーションプレートの写真：</p>
          <p><a href="{{ url($cautionPlatePath) }}" download class="btn download-image">ダウンロード</a></p>
        </div>
        @if ($quoteType == '2')
        <div class="box-flex content-margin-s p-margin">
          <p>作成した見積書の写真：</p>
          <p><a href="{{ url($requestQuotePath) }}" download class="btn download-image">ダウンロード</a></p>
        </div>
        @endif
      </div>
      <div class="content-margin-s">
        <p>事故の状況：</p>
        <div class="frame">
          <!-- <p>依頼者が記入した事故状況が閲覧できます。</p> -->
          <p>{!! @$accidentDetail !!}</p>
        </div>
      </div>
      @if ($quoteStatus == '1')
      <div class="alignright w-25p">
        @can('QUOTE_MEMBER')
          <button type="submit" name="make_quote" class="btn">見積承諾</button>
        @endcan
      </div>
      @endif
    </div>
  </form>

  @if ($quoteStatus !== '1')
  <div class="content-margin-ss b-color frame ">
    <form action="{{ route('admin.make_quote.store') }}" method="POST" class="box-flex col-2 form w-inherit vertical-b" enctype="multipart/form-data">
      @csrf
      <div>
        <div class="box-flex">
          <p>工賃：</p>
          @if ($quoteStatus == config('consts.quote.STATUS_MAKING') and Auth::user()->can('QUOTE_MEMBER'))
          <p class="flex3"><input id="wage" class="txt-r number-separator" type="text" name="wage" value="{{old('wage') ?? $wage ?? ''}}" required pattern="^((([1-9]\d*)(,\d{3})*)|0)$"> 円</p>
          @else
            @empty($wage)
            <p class="flex3"><input id="wage" class="txt-r number-separator" type="text" name="wage" value="{{old('wage') ?? $wage ?? ''}}" disabled pattern="^((([1-9]\d*)(,\d{3})*)|0)$"> 円</p>
            @else
            <p class="flex3"><input id="wage" class="txt-r number-separator" type="text" name="wage" value="{{old('wage') ?? number_format($wage) ?? ''}}" disabled pattern="^((([1-9]\d*)(,\d{3})*)|0)$"> 円</p>
            @endempty
          @endif
        </div>
        @if($errors->has('wage'))
          <p class="err-msg content-margin-ss">{{ $errors->first('wage') }}</p>
        @endif

        <div class="box-flex">
          <p>部品：</p>
          @if ($quoteStatus == config('consts.quote.STATUS_MAKING') and Auth::user()->can('QUOTE_MEMBER'))
          <p class="flex3"><input id="parts" class="txt-r number-separator" type="text" name="parts" value="{{old('parts') ?? $parts ?? ''}}" required pattern="^((([1-9]\d*)(,\d{3})*)|0)$"> 円</p>
          @else
            @empty($parts)
            <p class="flex3"><input id="parts" class="txt-r number-separator" type="text" name="parts" value="{{old('parts') ?? $parts ?? ''}}" disabled pattern="^((([1-9]\d*)(,\d{3})*)|0)$"> 円</p>
            @else
            <p class="flex3"><input id="parts" class="txt-r number-separator" type="text" name="parts" value="{{old('parts') ?? number_format($parts) ?? ''}}" disabled pattern="^((([1-9]\d*)(,\d{3})*)|0)$"> 円</p>
            @endempty
          @endif
        </div>
        @if($errors->has('parts'))
          <p class="err-msg content-margin-ss">{{ $errors->first('parts') }}</p>
        @endif

        <div class="box-flex">
          <p>塗装工賃：</p>
          @if ($quoteStatus == config('consts.quote.STATUS_MAKING') and Auth::user()->can('QUOTE_MEMBER'))
          <p class="flex3"><input id="painting_wages" class="txt-r number-separator" type="text" name="painting_wages" value="{{old('painting_wages') ?? $paintingWages ?? ''}}" required pattern="^((([1-9]\d*)(,\d{3})*)|0)$"> 円</p>
          @else
            @empty($paintingWages)
            <p class="flex3"><input id="painting_wages" class="txt-r number-separator" type="text" name="painting_wages" value="{{old('painting_wages') ?? $paintingWages ?? ''}}" disabled pattern="^((([1-9]\d*)(,\d{3})*)|0)$"> 円</p>
            @else
            <p class="flex3"><input id="painting_wages" class="txt-r number-separator" type="text" name="painting_wages" value="{{old('painting_wages') ?? number_format($paintingWages) ?? ''}}" disabled pattern="^((([1-9]\d*)(,\d{3})*)|0)$"> 円</p>
            @endempty
          @endif
        </div>
        @if($errors->has('painting_wages'))
          <p class="err-msg content-margin-ss">{{ $errors->first('painting_wages') }}</p>
        @endif

        <div class="box-flex">
          <p>塗装部品：</p>
          @if ($quoteStatus == config('consts.quote.STATUS_MAKING') and Auth::user()->can('QUOTE_MEMBER'))
          <p class="flex3"><input id="painting_parts" class="txt-r number-separator" type="text" name="painting_parts" value="{{old('painting_parts') ?? $paintingParts ?? ''}}"  required pattern="^((([1-9]\d*)(,\d{3})*)|0)$"> 円</p>
          @else
            @empty($paintingParts)
            <p class="flex3"><input id="painting_parts" class="txt-r number-separator" type="text" name="painting_parts" value="{{old('painting_parts') ?? $paintingParts ?? ''}}" disabled pattern="^((([1-9]\d*)(,\d{3})*)|0)$"> 円</p>
            @else
            <p class="flex3"><input id="painting_parts" class="txt-r number-separator" type="text" name="painting_parts" value="{{old('painting_parts') ?? number_format($paintingParts) ?? ''}}" disabled pattern="^((([1-9]\d*)(,\d{3})*)|0)$"> 円</p>
            @endempty
          @endif
        </div>
        @if($errors->has('painting_parts'))
          <p class="err-msg content-margin-ss">{{ $errors->first('painting_parts') }}</p>
        @endif

        <div class="box-flex">
          <p>見積書：</p>
          @if ($quoteStatus == config('consts.quote.STATUS_MAKING') and Auth::user()->can('QUOTE_MEMBER'))
          <p class="flex3"><input type="file" name="quotation" required></p>
          @else
          <p class="flex3"><input type="file" name="quotation" disabled></p>
          @endif
        </div>
        @if($errors->has('quotation'))
          <p class="err-msg content-margin-ss">{{ $errors->first('quotation') }}</p>
        @endif

        <div>
          @if ($quoteStatus == config('consts.quote.STATUS_MAKING') and Auth::user()->can('QUOTE_MEMBER'))
          <button type="submit" name="submit" class="btn-submit">確定</button>
          @else
          <button type="submit" name="submit" class="btn-submit" disabled>確定</button>
          @endif
        </div>
      </div>

      @if($quoteStatus == config('consts.quote.STATUS_PAYMENT_WAITING') || $quoteStatus == config('consts.quote.STATUS_PAID'))

        @can('SYSTEM_ADMIN')
        <div>
          <div class="align-c">
            <p>見積総額：<span class="txt-l">{{$dispTotalPrice}}</span> 円</p>
            <p class="content-margin-ss">入金予定金額：<span class="txt-l">{{$depositAmount}}</span> 円</p>
          </div>
          <div class="content-margin-s w-inherit">
            @if($quoteStatus == config('consts.quote.STATUS_PAID'))
            <button type="submit" name="allow_download" class="btn" disabled>ダウンロード許可</button>
            @else
            <button type="submit" name="allow_download" class="btn">ダウンロード許可</button>
            @endif
          </div>
        </div>
        @endcan

      @endif
    </form>
  </div>
  @endif
</div>
@endsection
