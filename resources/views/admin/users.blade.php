<!DOCTYPE HTML>
<html>
<head>
    <title>Użytkownicy [Admin] — Plant Care Diary</title>
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

        .role-badge {
            display: inline-block; border-radius: 20px; padding: .2em .75em;
            font-size: .78em; font-weight: 600; white-space: nowrap;
        }
        .role-admin { background: rgba(230,168,23,.2); color: #856404; border: 1px solid rgba(230,168,23,.4); }
        .role-user  { background: rgba(63,177,163,.15); color: #2a9d8f; border: 1px solid rgba(63,177,163,.3); }
        .role-guest { background: rgba(127,140,141,.12); color: #7f8c8d; border: 1px solid rgba(127,140,141,.3); }

        .filter-bar { display:flex; gap:1em; flex-wrap:wrap; align-items:flex-end; margin-bottom:1.5em; }
        .filter-bar > div { display:flex; flex-direction:column; gap:.3em; flex:1; min-width:160px; }
        .filter-bar label { font-size:.78em; text-transform:uppercase; letter-spacing:.1em; opacity:.6; }
        .filter-bar select, .filter-bar input { margin: 0; text-align: center; }

        .inline-form { display:inline-flex; gap:.4em; align-items:center; }
        .inline-form select {
            padding:.25em .55em; font-size:.82em; background:none;
            border:1px solid rgba(0,0,0,.2); border-radius:4px;
            margin:0; height:auto;
        }
        .inline-btn {
            background: #3fb1a3; color: #fff; border: none;
            border-radius: 4px; padding: .3em .9em;
            font-size: .82em; cursor: pointer; white-space: nowrap;
        }
        .inline-btn:hover { background: #2a9d8f; }
        .inline-btn.ghost {
            background: none; color: inherit; border: 1px solid rgba(0,0,0,.15);
            text-decoration: none; display: inline-block;
        }
        .inline-btn.ghost:hover { background: rgba(0,0,0,.04); }

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

        .me-badge {
            display: inline-block; background: rgba(63,177,163,.15);
            color: #2a9d8f; font-size: .7em; padding: .1em .55em;
            border-radius: 20px; margin-left: .5em; vertical-align: middle;
        }
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
                <li class="current"><a href="{{ route('admin.users') }}">Admin</a></li>
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
            <span class="icon solid fa-users"></span>
            <h2>Użytkownicy</h2>
            <p style="opacity:.65;">Zarządzanie kontami i rolami w systemie.</p>

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

                <div style="margin-bottom:1.5em;text-align:left;">
                    <a href="{{ route('admin.plant-types') }}" class="inline-btn ghost">
                        <span class="icon solid fa-cog"></span>&nbsp; Przejdź do Typów Roślin
                    </a>
                </div>

                <div class="admin-banner">
                    <span class="icon solid fa-exclamation-triangle"></span>
                    &nbsp; Panel administratora — zmiany ról są natychmiastowe.
                </div>

                {{-- filtry --}}
                <form method="GET" action="{{ route('admin.users') }}">
                <div class="filter-bar">
                    <div>
                        <label>Szukaj po nicku lub emailu</label>
                        <input type="text" name="search"
                               placeholder="np. maria…"
                               value="{{ $search ?? '' }}" />
                    </div>
                    <div style="flex:0;min-width:160px;">
                        <label>Filtruj po roli</label>
                        <select name="role_id" style="width:100%; text-align: center; text-align-last: center;">
                            <option value="">Wszystkie role</option>
                            @foreach($roles as $role)
                                <option value="{{ $role['id'] }}"
                                    {{ ($filterRoleId ?? '') == $role['id'] ? 'selected' : '' }}>
                                    {{ ucfirst($role['name']) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div style="flex:0;align-items:flex-end;gap:.4em;">
                        @if(!empty($search) || !empty($filterRoleId))
                            <a href="{{ route('admin.users') }}" class="filter-btn filter-btn-ghost">Wyczyść</a>
                        @endif
                        <button type="submit" class="filter-btn filter-btn-primary">Szukaj</button>
                    </div>
                </div>
                </form>

                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nick</th>
                            <th>Email</th>
                            <th>Rola</th>
                            <th>Zarejestrowano</th>
                            <th style="text-align:right;">Zmień rolę</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $u)
                        <tr>
                            <td style="opacity:.45;font-size:.8em;">{{ $u['id'] }}</td>
                            <td>
                                <strong>{{ $u['nickname'] }}</strong>
                                @if($u['id'] === $user->id)
                                    <span class="me-badge">to Ty</span>
                                @endif
                            </td>
                            <td style="opacity:.7;font-size:.88em;">{{ $u['email'] }}</td>
                            <td>
                                <span class="role-badge role-{{ $u['role'] }}">{{ $u['role'] }}</span>
                            </td>
                            <td style="opacity:.6;font-size:.82em;white-space:nowrap;">
                                {{ \Carbon\Carbon::parse($u['created_at'])->format('d.m.Y') }}
                            </td>
                            <td style="text-align:right;">
                                @if($u['id'] === $user->id)
                                    <span style="opacity:.4;font-size:.82em;">— nie możesz zmienić swojej roli —</span>
                                @else
                                    <form action="{{ route('admin.users.role') }}" method="POST" class="inline-form">
                                        @csrf
                                        <input type="hidden" name="user_id" value="{{ $u['id'] }}" />
                                        <select name="role_id">
                                            @foreach($roles as $role)
                                                <option value="{{ $role['id'] }}"
                                                    {{ $role['name'] === $u['role'] ? 'selected' : '' }}>
                                                    {{ ucfirst($role['name']) }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="inline-btn">Zapisz</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" style="text-align:center;opacity:.5;padding:2em;">
                                Brak użytkowników{{ ($search || $filterRoleId) ? ' pasujących do filtru' : '' }}.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

                <p style="text-align:right;opacity:.5;font-size:.8em;margin-top:.8em;">
                    Wyświetlono {{ count($users) }} {{ count($users) === 1 ? 'użytkownika' : 'użytkowników' }} na tej stronie.
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
