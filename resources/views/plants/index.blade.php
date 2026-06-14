<!DOCTYPE HTML>
<html>
<head>
    <title>Moje Rośliny — Plant Care Diary</title>
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
        .data-table { width: 100%; border-collapse: collapse; margin-top: 1em; }
        .data-table th { background: #3fb1a3; color: #fff; padding: .7em 1.1em; text-align: left; }
        .data-table td { padding: .65em 1.1em; border-bottom: 1px solid rgba(0,0,0,.07); }
        .data-table tr:hover td { background: rgba(63,177,163,.06); }
        .btn-small { display:inline-block;padding:.3em .9em;border-radius:4px;font-size:.8em;text-decoration:none; }
        .btn-primary { background:#3fb1a3;color:#fff; }
        .btn-ghost   { background:transparent;border:1px solid rgba(0,0,0,.2);color:inherit; }
        .filter-bar { display:flex;gap:1em;flex-wrap:wrap;align-items:flex-end;margin-bottom:1.5em; }
        .filter-bar > div { display:flex;flex-direction:column;gap:.3em;flex:1;min-width:160px; }
        .filter-bar label { font-size:.78em;text-transform:uppercase;letter-spacing:.1em;opacity:.6; }
        .filter-bar select, .filter-bar input[type=text] { margin: 0; text-align: center; }
        .empty-state { text-align:center;padding:4em 1em;opacity:.5; }
        .empty-state .icon { font-size:3.5em;display:block;margin-bottom:.5em; }
        .page-actions { display:flex;justify-content:flex-end;margin-bottom:1em; }
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
        <h1 id="logo"><a href="/">Plant <span>Care Diary</span></a></h1>
        <nav id="nav">
            <ul>
                <li><a href="{{ route('home') }}">Panel</a></li>
                <li><a href="{{ route('catalogue') }}">Katalog roślin</a></li>
                <li class="current"><a href="{{ route('plants.index') }}">Moje rośliny</a></li>
                <li><a href="{{ route('diary.index') }}">Pamiętnik</a></li>
                @if($user->isAdmin())<li><a href="{{ route('admin.plant-types') }}">Admin</a></li>@endif
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
            <span class="icon solid fa-seedling"></span>
            <h2>Moje Rośliny</h2>
            <p style="opacity:.65;">Twój prywatny ogród, posortowany i opisany.</p>

            @if(!empty($infos))
                <div style="background:#d4edda;border-left:4px solid #28a745;padding:1em;margin:1em auto;max-width:560px;text-align:left;border-radius:4px;">
                    @foreach($infos as $info)<p style="margin:0;color:#155724;">{{ $info }}</p>@endforeach
                </div>
            @endif
        </header>

        <section class="wrapper style4 special container large">
            <div class="content">

                <div class="page-actions">
                    <a href="{{ route('plants.create') }}" class="button special small">
                        <span class="icon solid fa-plus"></span>&nbsp; Dodaj roślinę
                    </a>
                </div>

                {{-- pasek filtrów --}}
                <form method="GET" action="{{ route('plants.index') }}">
                <div class="filter-bar">
                    <div>
                        <label>Szukaj po nazwie</label>
                        <input type="text" name="search"
                               placeholder="np. Monstera przy oknie..."
                               value="{{ $search ?? '' }}" />
                    </div>
                    <div style="flex:0;min-width:160px;">
                        <label>Typ rośliny</label>
                        <select name="plant_type_id" style="width:100%; text-align: center; text-align-last: center;">
                            <option value="">Wszystkie typy</option>
                            @foreach($plantTypes as $type)
                                <option value="{{ $type['id'] }}"
                                    {{ ($typeId ?? '') == $type['id'] ? 'selected' : '' }}>
                                    {{ $type['name'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div style="flex:0;align-items:flex-end;gap:.4em;">
                        @if(!empty($search) || !empty($typeId))
                            <a href="{{ route('plants.index') }}" class="filter-btn filter-btn-ghost">Wyczyść</a>
                        @endif
                        <button type="submit" class="filter-btn filter-btn-primary">Filtruj</button>
                    </div>
                </div>
                </form>

                @if(empty($plants))
                    <div class="empty-state">
                        <span class="icon solid fa-seedling"></span>
                        <p><strong>Nie masz jeszcze żadnych roślin.</strong><br>
                           Dodaj pierwszą, klikając przycisk powyżej.</p>
                        <br>
                        <a href="{{ route('plants.create') }}" class="button special">Dodaj pierwszą roślinę</a>
                    </div>
                @else
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Nazwa</th>
                                <th>Typ</th>
                                <th>Dodano</th>
                                <th>Akcje</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($plants as $plant)
                            <tr>
                                <td><strong>{{ $plant['name'] }}</strong></td>
                                <td style="opacity:.7;">{{ $plant['plant_type_name'] ?? '—' }}</td>
                                <td style="opacity:.7;font-size:.85em;">{{ $plant['created_at'] }}</td>
                                <td>
                                    <a href="{{ route('diary.index', ['plant_id' => $plant['id']]) }}" class="btn-small btn-primary">Pamiętnik</a>&nbsp;
                                    <a href="#" class="btn-small btn-ghost">Edytuj</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    {{-- PAGINACJA --}}
                    @if($lastPage > 1)
                        <nav style="display:flex;gap:1.2em;justify-content:center;align-items:center;margin-top:2.5em;">
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
