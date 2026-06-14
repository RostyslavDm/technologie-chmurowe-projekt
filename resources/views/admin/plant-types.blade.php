<!DOCTYPE HTML>
<html>
<head>
    <title>Typy Roślin [Admin] — Plant Care Diary</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
    <link rel="stylesheet" href="{{ asset('assets/css/main.css') }}" />
    <style>
        body { margin: 0; }
        #page-wrapper { display: flex; flex-direction: column; min-height: 100vh; }
        #main { flex: 1 0 auto; }
        #footer { flex-shrink: 0; }
        #logo a { text-shadow: 0 2px 8px rgba(0,0,0,.3); }
        #nav ul li a, #nav ul li button { color: rgba(160, 174, 192, 0.8) !important; text-shadow: 0 1px 4px rgb(213, 253, 250); }

        .admin-banner {
            background: rgba(230,168,23,.15); border: 1px solid rgba(230,168,23,.4);
            border-radius: 6px; padding: .7em 1.2em; margin-bottom: 1.5em;
            font-size: .88em; color: #856404;
        }
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th { background: #3fb1a3; color: #fff; padding: .7em 1.1em; text-align: left; }
        .data-table td { padding: .65em 1.1em; border-bottom: 1px solid rgba(0,0,0,.07); vertical-align: middle; }
        .data-table tr:hover td { background: rgba(63,177,163,.05); }
        .badge-active   { display:inline-block;background:rgba(39,174,96,.15);color:#27ae60;border:1px solid rgba(39,174,96,.3);border-radius:20px;padding:.15em .7em;font-size:.78em;white-space:nowrap; }
        .badge-inactive { display:inline-block;background:rgba(231,76,60,.12);color:#c0392b;border:1px solid rgba(231,76,60,.3);border-radius:20px;padding:.15em .7em;font-size:.78em;white-space:nowrap; }
        .section-divider { border: none; border-top: 1px solid rgba(0,0,0,.1); margin: 2em 0; }

        /* override: buttony Twenty maja sztywna szerokosc — pozwalamy by sie skalowaly do tekstu */
        .row .buttons input[type=submit].special.small,
        .row .buttons .button.small {
            min-width: 0; width: auto; padding-left: 1.4em; padding-right: 1.4em;
            white-space: nowrap;
        }
        .inline-btn {
            background: none; border: 1px solid rgba(0,0,0,.15);
            border-radius: 4px; padding: .25em .75em;
            font-size: .82em; cursor: pointer; color: inherit;
            text-decoration: none; display: inline-block;
            white-space: nowrap;
        }
        .inline-btn:hover { background: rgba(0,0,0,.04); }
        .inline-btn-warn  { color: #c0392b; border-color: rgba(192,57,43,.3); }
        .inline-btn-warn:hover { background: rgba(231,76,60,.06); }
        .inline-btn-ok    { color: #27ae60; border-color: rgba(39,174,96,.3); }
        .inline-btn-ok:hover { background: rgba(39,174,96,.06); }
    </style>
</head>
<body class="index">
<div id="page-wrapper">

    <header id="header" class="alt">
        <h1 id="logo"><a href="/">Plant <span>Care Diary</span></a></h1>
        <nav id="nav">
            <ul>
                <li><a href="{{ route('home') }}">Panel</a></li>
                <li><a href="{{ route('catalogue') }}">Katalog roślin</a></li>
                <li><a href="{{ route('plants.index') }}">Moje rośliny</a></li>
                <li><a href="{{ route('diary.index') }}">Pamiętnik</a></li>
                <li class="current"><a href="{{ route('admin.plant-types') }}">Admin</a></li>
                <li><a href="{{ route('nickname.show') }}" style="font-size:.85em;opacity:.8;">{{ $user->nickname }}</a></li>
                <li>
                    <form action="{{ route('logout') }}" method="POST" style="display:inline;">
                        @csrf
                        <button type="submit" style="background:none;border:none;color:inherit;cursor:pointer;font-size:inherit;">Wyloguj</button>
                    </form>
                </li>
            </ul>
        </nav>
    </header>

    <article id="main">
        <header class="special container">
            <span class="icon solid fa-cog"></span>
            <h2>Typy Roślin</h2>
            <p style="opacity:.65;">Zarządzanie katalogiem — widocznym dla wszystkich użytkowników.</p>

            @if(!empty($infos))
                <div style="background:#d4edda;border-left:4px solid #28a745;padding:1em;margin:1em auto;max-width:600px;text-align:left;border-radius:4px;">
                    @foreach($infos as $info)<p style="margin:0;color:#155724;">{{ $info }}</p>@endforeach
                </div>
            @endif

            @if(!empty($errors))
                <div style="background:#ffdddd;border-left:4px solid #f44336;padding:1em;margin:1em auto;max-width:600px;text-align:left;border-radius:4px;">
                    <ul style="margin:0;padding-left:1.2em;">
                        @foreach($errors as $err)<li>{{ $err }}</li>@endforeach
                    </ul>
                </div>
            @endif
        </header>

        <section class="wrapper style4 special container large">
            <div class="content">

                {{-- sekz nawigacyjny do drugiej strony admina --}}
                <div style="margin-bottom:1.5em;text-align:left;">
                    <a href="{{ route('admin.users') }}" class="inline-btn">
                        <span class="icon solid fa-users"></span>&nbsp; Przejdź do Użytkowników
                    </a>
                </div>

                <div class="admin-banner">
                    <span class="icon solid fa-exclamation-triangle"></span>
                    &nbsp; Panel administratora — zmiany są widoczne dla wszystkich użytkowników.
                </div>

                {{-- formularz dodania nowego typu --}}
                <h3 style="margin-bottom:1em;">Dodaj nowy typ rośliny</h3>
                <form action="{{ route('admin.plant-types.store') }}" method="POST">
                    @csrf
                    <div class="row gtr-50">
                        <div class="col-6 col-12-mobile">
                            <label style="display:block;font-size:.78em;text-transform:uppercase;letter-spacing:.08em;opacity:.6;margin-bottom:.3em;">Nazwa typu</label>
                            <input type="text" name="name"
                                   placeholder="np. Bambus"
                                   maxlength="100" required
                                   value="{{ $form->name ?? '' }}" />
                        </div>
                        <div class="col-6 col-12-mobile">
                            <label style="display:block;font-size:.78em;text-transform:uppercase;letter-spacing:.08em;opacity:.6;margin-bottom:.3em;">Podlewanie co (dni)</label>
                            <input type="number" name="watering_interval_days"
                                   placeholder="np. 7"
                                   min="1" max="365" required
                                   value="{{ $form->watering_interval_days ?? '' }}" />
                        </div>
                        <div class="col-12">
                            <textarea name="description" rows="2"
                                      placeholder="Krótki opis (opcjonalny)…"
                                      maxlength="500"
                                      style="width:100%;padding:.75em;background:none;border:solid 1px rgba(124,128,129,.2);border-radius:4px;font-family:inherit;resize:vertical;">{{ $form->description ?? '' }}</textarea>
                        </div>
                        <div class="col-12">
                            <ul class="buttons">
                                <li><input type="submit" class="special small" value="Dodaj typ rośliny" /></li>
                            </ul>
                        </div>
                    </div>
                </form>

                <hr class="section-divider">

                {{-- lista istniejących typów --}}
                <h3 style="margin-bottom:1em;">Istniejące typy</h3>

                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Nazwa</th>
                            <th>Opis</th>
                            <th style="white-space:nowrap;">Podlewanie co</th>
                            <th>Status</th>
                            <th style="text-align:right;">Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($plantTypes as $pt)
                        <tr style="{{ $pt['is_active'] ? '' : 'opacity:.55;' }}">
                            <td><strong>{{ $pt['name'] }}</strong></td>
                            <td style="opacity:.7;font-size:.85em;">{{ $pt['description'] ?? '—' }}</td>
                            <td style="text-align:center;white-space:nowrap;">{{ $pt['watering_interval_days'] }} dni</td>
                            <td>
                                @if($pt['is_active'])
                                    <span class="badge-active">Aktywny</span>
                                @else
                                    <span class="badge-inactive">Nieaktywny</span>
                                @endif
                            </td>
                            <td style="text-align:right;white-space:nowrap;">
                                <form action="{{ route('admin.plant-types.toggle') }}" method="POST" style="display:inline;">
                                    @csrf
                                    <input type="hidden" name="id" value="{{ $pt['id'] }}" />
                                    @if($pt['is_active'])
                                        <button type="submit" class="inline-btn inline-btn-warn">Dezaktywuj</button>
                                    @else
                                        <button type="submit" class="inline-btn inline-btn-ok">Aktywuj</button>
                                    @endif
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" style="text-align:center;opacity:.5;padding:2em;">
                                Brak typów roślin w bazie.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

                {{-- PAGINACJA --}}
                @if($lastPage > 1)
                    <nav style="display:flex;gap:1.2em;justify-content:center;align-items:center;margin-top:2em;">
                        @if($page > 1)
                            <a href="{{ request()->fullUrlWithQuery(['page' => $page - 1]) }}"
                               style="padding:.5em 1.1em;border:1px solid rgba(63,177,163,.5);border-radius:4px;color:#3fb1a3;text-decoration:none;font-size:.85em;">← Poprzednia</a>
                        @else
                            <span style="padding:.5em 1.1em;border:1px solid rgba(0,0,0,.1);border-radius:4px;color:rgba(0,0,0,.25);font-size:.85em;">← Poprzednia</span>
                        @endif

                        <span style="opacity:.6;font-size:.85em;">Strona {{ $page }} z {{ $lastPage }}</span>

                        @if($page < $lastPage)
                            <a href="{{ request()->fullUrlWithQuery(['page' => $page + 1]) }}"
                               style="padding:.5em 1.1em;border:1px solid rgba(63,177,163,.5);border-radius:4px;color:#3fb1a3;text-decoration:none;font-size:.85em;">Następna →</a>
                        @else
                            <span style="padding:.5em 1.1em;border:1px solid rgba(0,0,0,.1);border-radius:4px;color:rgba(0,0,0,.25);font-size:.85em;">Następna →</span>
                        @endif
                    </nav>
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
