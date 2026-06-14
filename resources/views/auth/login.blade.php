<!DOCTYPE HTML>
<html>
<head>
    <title>Logowanie — Plant Care Diary</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
    <link rel="stylesheet" href="{{ asset('assets/css/main.css') }}" />
    <style>
        body { margin: 0; }
        #page-wrapper { display: flex; flex-direction: column; min-height: 100vh; }
        #main { flex: 1 0 auto; }
        #footer { flex-shrink: 0; }
    </style>
</head>
<body class="index is-preload">
<div id="page-wrapper">

    <header id="header" class="alt">
        <h1 id="logo"><a href="/catalogue">Plant <span>Care Diary</span></a></h1>
        <nav id="nav">
            <ul>
                <li><a href="{{ route('catalogue') }}">Katalog roślin</a></li>
                <li><a href="{{ route('register') }}">Rejestracja</a></li>
            </ul>
        </nav>
    </header>

    <article id="main">
        <header class="special container">
            <span class="icon solid fa-lock"></span>
            <h2>Logowanie</h2>

            @if(!empty($errors))
                <div style="background:#ffdddd;border-left:4px solid #f44336;padding:1em;margin-bottom:1em;text-align:left;display:inline-block;min-width:300px;">
                    <ul style="margin:0;padding-left:1.2em;">
                        @foreach($errors as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(!empty($infos))
                <div style="background:#d4edda;border-left:4px solid #28a745;padding:1em;margin-bottom:1em;">
                    @foreach($infos as $info)
                        <p style="margin:0;color:#155724;">{{ $info }}</p>
                    @endforeach
                </div>
            @endif
        </header>

        <section class="wrapper style4 special container medium">
            <div class="content">
                <form action="{{ route('login.post') }}" method="POST">
                    @csrf
                    <div class="row gtr-50">
                        <div class="col-12">
                            <input type="email" name="email"
                                   placeholder="Adres email"
                                   value="{{ $form->email ?? '' }}" required />
                        </div>
                        <div class="col-12">
                            <input type="password" name="pass"
                                   placeholder="Hasło" required />
                        </div>
                        <div class="col-12">
                            <ul class="buttons">
                                <li><input type="submit" class="special" value="Zaloguj się" /></li>
                            </ul>
                        </div>
                        <div class="col-12" style="text-align:center;opacity:.75;font-size:.9em;">
                            Nie masz jeszcze konta?
                            <a href="{{ route('register') }}">Zarejestruj się tutaj.</a>
                        </div>
                    </div>
                </form>
            </div>
        </section>
    </article>

    <footer id="footer">
        <ul class="copyright">
            <li>&copy; Plant Care Diary</li>
            <li>Design: <a href="http://html5up.net">HTML5 UP</a></li>
        </ul>
    </footer>
</div>
</body>
</html>
