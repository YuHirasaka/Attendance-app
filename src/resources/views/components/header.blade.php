<header class="header">
    <div class="header__logo">
        <a href=""><img src="{{ asset('img/icon.png') }}" alt="ロゴ"></a>
    </div>
    @if( !in_array(Route::currentRouteName(), ['register', 'login', 'verification.notice']))
    <nav class="header__nav">
        <ul class="header__nav-list">
            @if(Auth::check() && Auth::user()->role === 'user')
            <li class="header__nav-item"><a href="/attendance">勤怠</a></li>
            <li class="header__nav-item"><a href="/attendance/list">勤怠一覧</a></li>
            <li class="header__nav-item"><a href="/stamp_correction_request/list">申請</a></li>
            <li>
                <form action="/logout" method="post">
                    @csrf
                    <button class="header__logout" type="submit">ログアウト</button>
                </form>
            </li>
            @elseif(Auth::check() && Auth::user()->role === 'admin')
            <li class="header__nav-item"><a href="/admin/attendance/list">勤怠一覧</a></li>
            <li class="header__nav-item"><a href="/admin/staff/list">スタッフ一覧</a></li>
            <li class="header__nav-item"><a href="stamp_correction_request/list">申請一覧</a></li>
            <li>
                <form action="/logout" method="post">
                    @csrf
                    <input type="hidden" name="guard" value="admin">
                    <button class="header__logout" type="submit">ログアウト</button>
                </form>
            </li>
            @endif
        </ul>
    </nav>
    @endif
</header>