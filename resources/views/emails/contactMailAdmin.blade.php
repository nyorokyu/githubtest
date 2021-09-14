<p>webサイトから、下記の内容でお問い合わせがありました。</p>
<p>内容をご確認の上、ご返信ください。</p>
<hr>
<p>氏名：{{$data['request']['name']}}</p>
<p>メールアドレス：{{$data['request']['email']}}</p>
<p>電話番号：{{$data['request']['tel']}}</p>
<p>お問い合わせ内容：{!! nl2br($data['request']['message']) !!}</p>
<hr>
<p>このメッセージはAmiT SYSTEMからの自動送信メールです。</p>
