<!DOCTYPE HTML>
<html>
<head>
    <title>Plant Care Diary</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
    <link rel="stylesheet" href="{{ asset('assets/css/main.css') }}" />
    <style>
        /* przyklejony footer dla krotkich stron — bez tego ucieka do gory po reloadzie */
        body { margin: 0; }
        #page-wrapper { display: flex; flex-direction: column; min-height: 100vh; }
        #main         { flex: 1 0 auto; }
        #footer       { flex-shrink: 0; }

        /* czytelnosc na jasnym tle headera */
        #logo a { text-shadow: 0 2px 8px rgb(113, 128, 150); }
        #nav ul li a, #nav ul li button {
            color: rgba(160, 174, 192, 0.8) !important;
            text-shadow: 0 1px 4px rgb(213, 253, 250);
        }

        /* karty dashboardu */
        .dash-card {
            display: block; text-decoration: none; color: inherit;
            background: rgba(255,255,255,.07);
            border: 1px solid rgba(255,255,255,.14);
            border-radius: 10px; padding: 2em 1.5em;
            text-align: center; transition: background .2s, transform .2s;
            height: 100%; box-sizing: border-box;
        }
        .dash-card:hover { background: rgba(63,177,163,.2); transform: translateY(-4px); color: inherit; }
        .dash-card .card-icon { font-size: 2.4em; color: #3fb1a3; display: block; margin-bottom: .5em; }
        .dash-card h3 { margin: .4em 0 .5em; color: #3da99c; font-size: 1.05em; }
        .dash-card p  { font-size: .82em; opacity: .65; margin: 0; line-height: 1.5; }
        .admin-card .card-icon { color: #e6a817; }
        .admin-card:hover { background: rgba(230,168,23,.15); }
        .section-label {
            text-align: center; font-size: .75em; letter-spacing: .15em;
            text-transform: uppercase; opacity: .45; margin: 2em 0 1.5em;
        }
    </style>
</head>
<body class="index">
<div id="page-wrapper">

    <header id="header" class="alt">
        <h1 id="logo"><a href="/">Plant <span>Care Diary</span></a></h1>
        <nav id="nav">
            <ul>
                <li class="current"><a href="{{ route('home') }}">Panel</a></li>
                <li><a href="{{ route('catalogue') }}">Katalog roślin</a></li>
                <li><a href="{{ route('plants.index') }}">Moje rośliny</a></li>
                <li><a href="{{ route('diary.index') }}">Pamiętnik</a></li>
                @if($user->isAdmin())
                <li><a href="{{ route('admin.plant-types') }}">Admin</a></li>
                @endif
                <li>
                    <a href="{{ route('nickname.show') }}"
                       style="font-size:.85em;opacity:.8;">{{ $user->nickname }}</a>
                </li>
                <li>
                    <form action="{{ route('logout') }}" method="POST" style="display:inline;">
                        @csrf
                        <button type="submit"
                                style="background:none;border:none;color:inherit;cursor:pointer;font-size:inherit;">
                            Wyloguj
                        </button>
                    </form>
                </li>
            </ul>
        </nav>
    </header>

    <article id="main">
        <header class="special container">
            <span class="icon solid fa-seedling"></span>
            <h2>Witaj, <span style="color:#3fb1a3;">{{ $user->nickname }}</span>.</h2>
            <p style="opacity:.65;">Co dziś pielęgnujemy?</p>

            @if(!empty($infos))
                <div style="background:#d4edda;border-left:4px solid #28a745;padding:1em;margin:1em auto;max-width:560px;text-align:left;border-radius:4px;">
                    @foreach($infos as $info)
                        <p style="margin:0;color:#155724;">{{ $info }}</p>
                    @endforeach
                </div>
            @endif
        </header>

        {{-- główne karty --}}
        <section class="wrapper style4 special">
            <div class="container">
                <div class="row gtr-50">
                    <div class="col-4 col-12-mobile">
                        <a href="{{ route('catalogue') }}" class="dash-card">
                            <span class="icon solid fa-leaf card-icon"></span>
                            <h3>Katalog Roślin</h3>
                            <p>Przeglądaj typy roślin i ich wymagania pielęgnacyjne. Dostępne dla wszystkich.</p>
                        </a>
                    </div>
                    <div class="col-4 col-12-mobile">
                        <a href="{{ route('plants.index') }}" class="dash-card">
                            <span class="icon solid fa-seedling card-icon"></span>
                            <h3>Moje Rośliny</h3>
                            <p>Dodawaj własne rośliny, nazywaj je i przypisuj do typów z katalogu.</p>
                        </a>
                    </div>
                    <div class="col-4 col-12-mobile">
                        <a href="{{ route('diary.index') }}" class="dash-card">
                            <span class="icon solid fa-tint card-icon"></span>
                            <h3>Pamiętnik</h3>
                            <p>Zapisuj kiedy i co robiłaś — podlewanie, nawożenie, przesadzanie.</p>
                        </a>
                    </div>
                </div>

                {{-- karty admina, widoczne tylko dla administratora --}}
                @if($user->isAdmin())
                <p class="section-label">Panel administratora</p>
                <div class="row gtr-50">
                    <div class="col-6 col-12-mobile">
                        <a href="{{ route('admin.plant-types') }}" class="dash-card admin-card">
                            <span class="icon solid fa-cog card-icon"></span>
                            <h3>Typy Roślin</h3>
                            <p>Zarządzaj katalogiem — dodawaj, edytuj i dezaktywuj typy roślin.</p>
                        </a>
                    </div>
                    <div class="col-6 col-12-mobile">
                        <a href="{{ route('admin.users') }}" class="dash-card admin-card">
                            <span class="icon solid fa-users card-icon"></span>
                            <h3>Użytkownicy</h3>
                            <p>Przeglądaj konta i zarządzaj rolami użytkowników systemu.</p>
                        </a>
                    </div>
                </div>
                @endif
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
