<header class="header">
    <div class="header__logo">
        <a href=""><img src="{{ asset('img/icon.png') }}" alt="ロゴ"></a>
    </div>
    @if( !in_array(Route::currentRouteName(), ['register', 'login', 'verification.notice']))
    <nav class="header__nav">
        <ul class="header__nav-list">
            @if(Auth::check() && Auth::user()->role === 'user')
            <li class="header__nav-item"><a href="/">勤怠</a></li>
            <li class="header__nav-item"><a href="">勤怠一覧</a></li>
            <li class="header__nav-item"><a href="">申請</a></li>
            @elseif(Auth::check() && Auth::user()->role === 'admin')
            <li class="header__nav-item"><a href="">勤怠一覧</a></li>
            <li class="header__nav-item"><a href="">スタッフ一覧</a></li>
            <li class="header__nav-item"><a href="">申請一覧</a></li>
            @endif
            <li>
                <form action="/logout" method="post">
                    @csrf
                    <button class="header__logout" type="submit">ログアウト</button>
                </form>
            </li>
        </ul>
    </nav>
    @endif
</header>