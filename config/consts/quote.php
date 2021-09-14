<?php

return [

  // --------------------------------------------------------------------------
  // ステータス
  // --------------------------------------------------------------------------
  'STATUS_REQUESTING' => '1',
  'STATUS_MAKING' => '2',
  'STATUS_PAYMENT_WAITING' => '3',
  'STATUS_PAID' => '4',

  'STATUS_JAPANESE' => [
    '1' => '依頼中',
    '2' => '作成中',
    '3' => '入金待ち',
    '4' => '入金済',
  ],

  // --------------------------------------------------------------------------
  // 手数料率
  // --------------------------------------------------------------------------
  // TODO 正しい数値に変更
  'MAKE_QUOTE_1' => 0.07,    // 見積作成
  'MAKE_QUOTE_2' => 0.1,   // 見積添削

  // --------------------------------------------------------------------------
  // 画像タイプ
  // --------------------------------------------------------------------------
  'IMAGE_TYPE_CERTIFICATE' => '1',    // 車検証
  'IMAGE_TYPE_ACCIDENT' => '2',       // 事故車
  'IMAGE_TYPE_CAUTIONPLATE' => '3',   // コーションプレート
  'IMAGE_TYPE_REQUESTQUOTE' => '4',   // 見積書（依頼者作成）



];