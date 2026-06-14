<!DOCTYPE HTML>
<html>
<head>
    <title>Katalog Roślin — Plant Care Diary</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
    <link rel="stylesheet" href="{{ asset('assets/css/main.css') }}" />
    <style>
        body { margin: 0; }
        #page-wrapper { display: flex; flex-direction: column; min-height: 100vh; }
        #main { flex: 1 0 auto; }
        #footer { flex-shrink: 0; }
        #logo a { text-shadow: 0 2px 8px rgba(0,0,0,.3); }
        #nav ul li a, #nav ul li button {
            color: rgba(160, 174, 192, 0.8) !important;
            text-shadow: 0 1px 4px rgb(213, 253, 250);
        }
        .data-table { width: 100%; border-collapse: collapse; margin-top: 1em; }
        .data-table th {
            background: #3fb1a3; color: #fff;
            padding: .7em 1.1em; text-align: left; font-weight: 600;
        }
        .data-table td { padding: .65em 1.1em; border-bottom: 1px solid rgba(0,0,0,.07); }
        .data-table tr:last-child td { border-bottom: none; }
        .data-table tr:hover td { background: rgba(63,177,163,.06); }
        .watering-badge {
            display: inline-block; background: rgba(63,177,163,.12);
            color: #2a9d8f; border: 1px solid rgba(63,177,163,.3);
            border-radius: 20px; padding: .2em .8em; font-size: .82em; font-weight: 600;
        }
        .filter-bar { display: flex; gap: 1em; flex-wrap: wrap; align-items: flex-end; margin-bottom: 1.5em; }
        .filter-bar > div { display: flex; flex-direction: column; gap: .3em; flex: 1; min-width: 180px; }
        .filter-bar label { font-size: .78em; text-transform: uppercase; letter-spacing: .1em; opacity: .6; }
        .filter-bar select, .filter-bar input[type=text] { margin: 0; text-align: center; }
        .empty-state { text-align: center; padding: 3em 1em; opacity: .5; }
        .empty-state .icon { font-size: 3em; display: block; margin-bottom: .4em; }
        /* buttony filtra — wlasny styl, omija syf Twenty */
        .filter-btn {
            display: inline-block !important;
            width: auto !important;
            min-width: 0 !important;
            padding: .65em 1.4em !important;
            border-radius: 4px; font-size: .85em; font-weight: 600;
            text-transform: uppercase; letter-spacing: .08em;
            text-align: center;
            cursor: pointer; white-space: nowrap;
            text-decoration: none; line-height: 1;
            border: 1px solid transparent;
            font-family: inherit;
        }
        .filter-btn-primary { background: #3fb1a3; color: #fff; border-color: #3fb1a3; }
        .filter-btn-primary:hover { background: #2a9d8f; }
        .filter-btn-ghost   { background: none; color: inherit; border-color: rgba(0,0,0,.18); }
        .filter-btn-ghost:hover { background: rgba(0,0,0,.04); }
    </style>
</head>
<body class="index">
<div id="page-wrapper">

    <header id="header" class="alt">
        <h1 id="logo"><a href="/catalogue">Plant <span>Care Diary</span></a></h1>
        <nav id="nav">
            <ul>
                @if($user)
                    <li><a href="{{ route('home') }}">Panel</a></li>
                @endif
                <li class="current"><a href="{{ route('catalogue') }}">Katalog roślin</a></li>
                @if($user)
                    <li><a href="{{ route('plants.index') }}">Moje rośliny</a></li>
                    <li><a href="{{ route('diary.index') }}">Pamiętnik</a></li>
                    @if($user->isAdmin())
                    <li><a href="{{ route('admin.plant-types') }}">Admin</a></li>
                    @endif
                    <li><a href="{{ route('nickname.show') }}" style="font-size:.85em;opacity:.8;">{{ $user->nickname }}</a></li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST" style="display:inline;">
                            @csrf
                            <button type="submit" style="background:none;border:none;color:inherit;cursor:pointer;font-size:inherit;">Wyloguj</button>
                        </form>
                    </li>
                @else
                    <li><a href="{{ route('login') }}">Logowanie</a></li>
                    <li><a href="{{ route('register') }}">Rejestracja</a></li>
                @endif
            </ul>
        </nav>
    </header>

    <article id="main">
        <header class="special container">
            <span class="icon solid fa-leaf"></span>
            <h2>Katalog Roślin</h2>
            <p style="opacity:.65;">Ogólne informacje o typach roślin i zaleceniach pielęgnacyjnych.</p>

            @if(!$user)
                <div style="background:rgba(63,177,163,.12);border:1px solid rgba(63,177,163,.3);border-radius:6px;padding:.9em 1.2em;display:inline-block;margin-top:5.5em;font-size:.9em;">
                    <span class="icon solid fa-info-circle" style="color:#3fb1a3;margin-right:.4em;margin-top:15em;"></span>
                    <a href="{{ route('register') }}">Zarejestruj się</a> lub
                    <a href="{{ route('login') }}">zaloguj się</a>, żeby dodawać własne rośliny i prowadzić pamiętnik.
                </div>
            @endif
        </header>

        <section class="wrapper style4 special container large">
            <div class="content">

                {{-- pasek filtrów --}}
                <form method="GET" action="{{ route('catalogue') }}" id="filter-form">
                <div class="filter-bar">
                    <div>
                        <label>Szukaj po nazwie</label>
                        <input type="text" name="search"
                               placeholder="np. Sukulenty..."
                               value="{{ $search ?? '' }}" />
                    </div>
                    <div style="flex:0;min-width:230px;">
                        <label>Podlewanie</label>
                        <select name="watering" style="width:100%; text-align: center; text-align-last: center;">
                            <option value="">Wszystkie</option>
                            <option value="frequent" {{ ($wateringFilter ?? '') === 'frequent' ? 'selected' : '' }}>
                                Częściej niż co tydzień
                            </option>
                            <option value="weekly" {{ ($wateringFilter ?? '') === 'weekly' ? 'selected' : '' }}>
                                Co tydzień lub rzadziej
                            </option>
                        </select>
                    </div>
                    <div style="flex:0;align-items:flex-end;gap:.4em;">
                        {{-- 'Wyczysc' zawsze widoczny: pasek filtra jest poza #ajax-results,
                             wiec przy AJAX-ie warunek i tak by sie nie odswiezyl --}}
                        <a href="{{ route('catalogue') }}" class="filter-btn filter-btn-ghost">Wyczyść</a>
                        <button type="submit" class="filter-btn filter-btn-primary">Szukaj</button>
                    </div>
                </div>
                </form>

                {{-- wyniki ladowane TEZ przez AJAX — JS podmienia tylko ten kontener --}}
                <div id="ajax-results">
                    @include('partials.catalogue-results')
                </div>

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

{{-- AJAX: filtr i paginacja bez przeladowania calej strony.
     Pobiera SAM fragment z wynikami i podmienia zawartosc #ajax-results. --}}
<script>
(function () {
    const form = document.getElementById('filter-form');
    const box  = document.getElementById('ajax-results');
    if (!form || !box) return;

    async function load(url) {
        try {
            const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            box.innerHTML = await res.text();
            window.history.pushState({}, '', url);
        } catch (err) {
            window.location = url;              // awaryjnie: zwykle przeladowanie
        }
    }

    // 1) szukanie / filtrowanie : przejmujemy wyslanie formularza
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const qs = new URLSearchParams(new FormData(form)).toString();
        load(window.location.pathname + '?' + qs);
    });

    // 2) klikniecia w paginacje : linki sa w srodku kontenera, wiec delegacja zdarzen
    box.addEventListener('click', function (e) {
        const link = e.target.closest('a[href]');
        if (!link) return;
        e.preventDefault();
        load(link.href);
    });
})();
</script>
</body>
</html>
