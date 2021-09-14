<!DOCTYPE html>
<html lang="ja">
<head>
  @yield('head')
</head>
<body>
  <header>
    @yield('header')
  </header>
  <main>
    @yield('content')
  </main>
  <div id="area-progress" class="hide">
    <div class="rotating progress" id="progress"></div>
  </div>
  <!-- <footer>
    <small class="align-c">&copy;2020 AmiT</small>
  </footer> -->
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
  <script src="https://rawgit.com/kimmobrunfeldt/progressbar.js/master/dist/progressbar.min.js"></script>
  <script src="{{asset('js/common.js')}}"></script>
  <script src="{{asset('js/tinymce/tinymce.min.js')}}"></script>
  <script>
    tinymce.init({
          selector: "#text-editor",
  				language: "ja",
  				height: 400,
  				plugins: "textcolor image link jbimages",
  				toolbar: [
              // 戻る 進む | フォーマット | 太字 斜体 | 左寄せ 中央寄せ 右寄せ 均等割付 | 箇条書き 段落番号 インデントを減らす インデント
              "undo redo | formatselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent",
              // 文字サイズ 文字色 画像 リンク
              "forecolor link jbimages"
          ],
  				block_formats: "Paragraph=p;Header 1=h4;Header 2=h5;Header 3=h6;",
  				menubar: false,
          statusbar: false,
  				relative_urls : false
      });
  </script>

@if(Request::is('admin/list_insolvency_master') && isset($areaName))
  <script>
    $(window).on('load', function() {
      var param = JSON.parse('<?php echo $areaName; ?>');
      var speed = 500;
      var target = $('#' + param);
      var position = target.offset().top;
      $("html, body").animate({scrollTop:position}, speed, "swing");
      return false;
    });
  </script>
@endif
</body>
</html>
