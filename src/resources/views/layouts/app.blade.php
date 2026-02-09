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
    </header>

    <main>
        @yield('content')
    </main>
</body>
</html>
