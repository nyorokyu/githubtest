@section('header')
  <div class="box-flex layout2">
    <h1>@yield('h1')</h1>
    <div class="box-flex txt-s box-btn">
      @yield('header_nav')
      <ul>
        @guest
          <li>
            <a href="{{ route('login') }}">{{ __('ログイン') }}</a>
          </li>
        @else
          <li id="area-dropdown" class="box-relative">
            {{ Auth::user()->name }}<i class="fas fa-caret-down icon"></i>
            <ul class="hide">
              <li><a href="{{ route('frontpage') }}">webサイトを表示</a></li>
              <li>
                @can('SYSTEM_ADMIN')
                 <a href="{{ route('register') }}">会員登録</a>
                @endcan
              </li>
              <li>
                @can('SYSTEM_ADMIN')
                 <a href="{{ route('admin.list_member_info.index') }}">会員情報一覧</a>
                @endcan
              </li>
              <li>
                <a href="{{ route('logout') }}"
                   onclick="event.preventDefault();
                                 document.getElementById('logout-form').submit();">ログアウト</a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                  @csrf
                </form>
              </li>
            </ul>
          </li>
        @endguest
      </ul>
    </div>
  </div>
@endsection
