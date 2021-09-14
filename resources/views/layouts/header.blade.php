@section('header')
  <nav id="main-nav" class="box-flex flex-col align-c">
    <h1 class="order2"><a href="{{ route('frontpage') }}"><img src="{{asset('images/logo.png')}}" alt="ロゴ"></a></h1>
    <ul id="area-auth" class="box-float-r cf txt-s order1">
      @guest
        <li>
          <a href="{{ route('login') }}">{{ __('ログイン') }}</a>
        </li>
      @else
        <li id="area-dropdown" class="box-relative">
          {{ Auth::user()->name }}<i class="fas fa-caret-down icon"></i>
          <ul class="hide">
            <li class="p-inherit">
              <a href="{{ route('admin.home.index') }}">管理画面</a>
            </li>
            <li class="p-inherit">
              <a href="{{ route('logout') }}"
                 onclick="event.preventDefault();
                               document.getElementById('logout-form').submit();">
                {{ __('ログアウト') }}
              </a>

              <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                @csrf
              </form>
            </li>
          </ul>
        </li>
      @endguest
    </ul>
    <ul id="header-menu" class="txt-l order3">
      <li><a href="{{route('company.index')}}"><i class="fas fa-building"></i> Company</a></li>
      <li><a href="{{route('news.list')}}"><i class="fas fa-info-circle"></i> News</a></li>
      <li><a href="{{route('movie.list')}}"><i class="fas fa-video"></i> Movies</a></li>
      <li><a href="{{route('info.list')}}"><i class="fas fa-chart-line"></i> M&A</a></li>
      <li><a href="{{route('contact.index')}}"><i class="fas fa-envelope-open-text"></i> Contact</a></li>
    </ul>
  </nav>
  <p id="area-copy" class="txt-s align-c"><small>&copy;2020 AmiT</small></p>
@endsection
