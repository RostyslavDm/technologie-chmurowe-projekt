<!DOCTYPE HTML>
<html>
<head>
    <title>Pamiętnik — Plant Care Diary</title>
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
        .data-table td { padding: .65em 1.1em; border-bottom: 1px solid rgba(0,0,0,.07); vertical-align: top; }
        .data-table tr:last-child td { border-bottom: none; }
        .data-table tr:hover td { background: rgba(63,177,163,.06); }

        .action-badge {
            display: inline-block; border-radius: 20px; padding: .2em .8em;
            font-size: .8em; font-weight: 600;
        }
        .action-watering    { background: rgba(52,152,219,.12); color: #2980b9; border: 1px solid rgba(52,152,219,.3); }
        .action-fertilising { background: rgba(39,174,96,.12);  color: #27ae60; border: 1px solid rgba(39,174,96,.3); }
        .action-repotting   { background: rgba(230,126,34,.12); color: #e67e22; border: 1px solid rgba(230,126,34,.3); }
        .action-pruning     { background: rgba(155,89,182,.12); color: #8e44ad; border: 1px solid rgba(155,89,182,.3); }
        .action-other       { background: rgba(127,140,141,.12);color: #7f8c8d; border: 1px solid rgba(127,140,141,.3); }

        .filter-bar { display:flex; gap:1em; flex-wrap:wrap; align-items:flex-end; margin-bottom:1.5em; }
        .filter-bar > div { display:flex; flex-direction:column; gap:.3em; flex:1; min-width:150px; }
        .filter-bar label { font-size:.78em; text-transform:uppercase; letter-spacing:.1em; opacity:.6; }
        .filter-bar select, .filter-bar input { margin: 0; text-align: center; }

        .new-entry-box {
            background: rgba(63,177,163,.07); border: 1px solid rgba(63,177,163,.2);
            border-radius: 8px; padding: 1.4em 1.6em; margin-bottom: 1.8em;
        }
        .new-entry-box h4 {
            margin: 0 0 1em; color: #3fb1a3; font-size: .95em;
            text-transform: uppercase; letter-spacing: .08em;
        }

        .empty-state { text-align:center; padding:4em 1em; opacity:.5; }
        .empty-state .icon { font-size:3.5em; display:block; margin-bottom:.5em; }
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
                <li><a href="{{ route('plants.index') }}">Moje rośliny</a></li>
                <li class="current"><a href="{{ route('diary.index') }}">Pamiętnik</a></li>
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
            <span class="icon solid fa-tint"></span>
            <h2>Pamiętnik pielęgnacji</h2>
            <p style="opacity:.65;">Historia każdego podlewania, nawożenia i przesadzenia.</p>

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

                {{-- ── FORMULARZ NOWEGO WPISU ─────────────────────────────────── --}}
                @if(!empty($plants))
                <div class="new-entry-box">
                    <h4><span class="icon solid fa-plus"></span>&nbsp; Nowy wpis</h4>
                    <form action="{{ route('diary.store') }}" method="POST">
                        @csrf
                        <div class="row gtr-50">
                            <div class="col-4 col-12-mobile">
                                <select name="plant_id" required
                                        style="padding:.65em;width:100%;background:none;border:solid 1px rgba(124,128,129,.2);">
                                    <option value="">— wybierz roślinę —</option>
                                    @foreach($plants as $p)
                                        <option value="{{ $p['id'] }}">{{ $p['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-3 col-12-mobile">
                                <select name="action" required
                                        style="padding:.65em;width:100%;background:none;border:solid 1px rgba(124,128,129,.2);">
                                    <option value="">— typ akcji —</option>
                                    <option value="watering">Podlewanie</option>
                                    <option value="fertilising">Nawożenie</option>
                                    <option value="repotting">Przesadzanie</option>
                                    <option value="pruning">Przycinanie</option>
                                    <option value="other">Inne</option>
                                </select>
                            </div>
                            {{-- max= zapobiega wyborowi przyszlej daty (walidacja kontekstowa) --}}
                            <div class="col-2 col-12-mobile">
                                <input type="date" name="logged_at"
                                       value="{{ date('Y-m-d') }}"
                                       max="{{ date('Y-m-d') }}"
                                       style="padding:.65em;width:100%;background:none;border:solid 1px rgba(124,128,129,.2);" />
                            </div>
                            <div class="col-3 col-12-mobile">
                                <input type="text" name="notes"
                                       placeholder="Notatka (opcjonalnie)"
                                       maxlength="500"
                                       style="margin:0;" />
                            </div>
                            <div class="col-12">
                                <ul class="buttons" style="margin:.4em 0 0;">
                                    <li><input type="submit" class="special small" value="Dodaj wpis" /></li>
                                </ul>
                            </div>
                        </div>
                    </form>
                </div>
                @else
                    <div style="background:rgba(230,126,34,.08);border:1px solid rgba(230,126,34,.2);border-radius:6px;padding:.9em 1.2em;margin-bottom:1.5em;font-size:.9em;">
                        <span class="icon solid fa-info-circle" style="color:#e67e22;margin-right:.4em;"></span>
                        Nie masz jeszcze żadnych roślin.
                        <a href="{{ route('plants.create') }}">Dodaj pierwszą roślinę</a>, żeby zacząć prowadzić pamiętnik.
                    </div>
                @endif

                {{-- ── FILTRY ──────────────────────────────────────────────────── --}}
                <form method="GET" action="{{ route('diary.index') }}">
                    <div class="filter-bar">
                        <div>
                            <label>Roślina</label>
                            <select name="plant_id">
                                <option value="">Wszystkie rośliny</option>
                                @foreach($plants as $plant)
                                    <option value="{{ $plant['id'] }}"
                                        {{ $filterPlant == $plant['id'] ? 'selected' : '' }}>
                                        {{ $plant['name'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label>Typ akcji</label>
                            <select name="action">
                                <option value="">Wszystkie akcje</option>
                                @foreach([
                                    'watering'    => 'Podlewanie',
                                    'fertilising' => 'Nawożenie',
                                    'repotting'   => 'Przesadzanie',
                                    'pruning'     => 'Przycinanie',
                                    'other'       => 'Inne',
                                ] as $val => $label)
                                    <option value="{{ $val }}" {{ $filterAction === $val ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label>Data od</label>
                            <input type="date" name="date_from" value="{{ $filterFrom ?? '' }}" />
                        </div>
                        <div>
                            <label>Data do</label>
                            <input type="date" name="date_to" value="{{ $filterTo ?? '' }}" />
                        </div>
                        <div style="flex:0;align-items:flex-end;gap:.4em;">
                            @if($filterPlant || $filterAction || $filterFrom || $filterTo)
                                <a href="{{ route('diary.index') }}" class="filter-btn filter-btn-ghost">Wyczyść</a>
                            @endif
                            <button type="submit" class="filter-btn filter-btn-primary">Filtruj</button>
                        </div>
                    </div>
                </form>

                {{-- ============= TABELA WPISÓW ============== --}}
                @if(empty($logs))
                    <div class="empty-state">
                        <span class="icon solid fa-tint"></span>
                        <p><strong>Brak wpisów{{ ($filterPlant || $filterAction || $filterFrom || $filterTo) ? ' dla wybranych filtrów' : '' }}.</strong></p>
                    </div>
                @else
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Roślina</th>
                                <th>Akcja</th>
                                <th>Notatka</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($logs as $log)
                            <tr>
                                <td style="white-space:nowrap;font-size:.85em;opacity:.75;">
                                    {{ \Carbon\Carbon::parse($log['logged_at'])->format('d.m.Y') }}
                                </td>
                                <td><strong>{{ $log['plant_name'] }}</strong></td>
                                <td>
                                    <span class="action-badge action-{{ $log['action'] }}">
                                        {{ match($log['action']) {
                                            'watering'    => 'Podlewanie',
                                            'fertilising' => 'Nawożenie',
                                            'repotting'   => 'Przesadzanie',
                                            'pruning'     => 'Przycinanie',
                                            default       => 'Inne',
                                        } }}
                                    </span>
                                </td>
                                <td style="opacity:.7;font-size:.85em;">{{ $log['notes'] ?? '—' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <p style="text-align:right;opacity:.5;font-size:.8em;margin-top:.8em;">
                        Wyświetlono {{ count($logs) }} {{ count($logs) === 1 ? 'wpis' : 'wpisów' }} na tej stronie.
                    </p>

                    {{-- PAGINACJA --}}
                    @if($lastPage > 1)
                        <nav style="display:flex;gap:1.2em;justify-content:center;align-items:center;margin-top:1.5em;">
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
