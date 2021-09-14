<!DOCTYPE html>
<html lang="ja">
<head>
  @yield('head')
</head>
<body>
  <main>
    @yield('content')
  </main>
  <div id="area-main-nav">
    @yield('header')
  </div>
  <!-- <footer>
    <small class="align-c">&copy;2020 AmiT</small>
  </footer> -->
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.min.js"></script>
  <script>
    $('.area-slick').slick({
      arrows: false,
      autoplay: true,
      autoplaySpeed: 5000,
    });
  </script>
  <script src="{{asset('js/common.js')}}"></script>

</body>
</html>
