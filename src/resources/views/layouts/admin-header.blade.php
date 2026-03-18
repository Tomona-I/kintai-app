<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>coachtech 勤怠管理アプリ</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @yield('css')
</head>
<body>
    <header class="header">
        <div class="header__logo">
            <img src="{{ asset('img/COACHTECH_logo.png') }}" alt="COACHTECH">
        </div>
        <nav class="header__nav">
            <a href="{{ url('/admin/attendance/list') }}" class="nav-link">勤怠一覧</a>
            <a href="{{ url('/admin/staff/list') }}" class="nav-link">スタッフ一覧</a>
            <a href="{{ url('/admin/application-list') }}" class="nav-link">申請一覧</a>
            <form method="POST" action="{{ route('logout') }}" class="logout-form">
                @csrf
                <input type="hidden" name="is_admin" value="1">
                <button type="submit" class="nav-link nav-link--logout">ログアウト</button>
            </form>
        </nav>
    </header>

    <main>
        @yield('content')
    </main>
</body>
</html>
