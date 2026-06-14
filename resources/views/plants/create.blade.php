<!DOCTYPE HTML>
<html>
<head>
    <title>Dodaj roślinę — Plant Care Diary</title>
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
        .hint { font-size: .8em; opacity: .6; margin-top: .3em; }
        /* override Twenty: buttony skalują się do tekstu */
        input[type=submit].special,
        input[type=submit].special.small,
        .button.special,
        .button.small,
        .button {
            min-width: 0 !important; width: auto !important;
            padding: .55em 1.5em !important; white-space: nowrap;
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
            <span class="icon solid fa-plus"></span>
            <h2>Dodaj roślinę</h2>
            <p style="opacity:.65;">Nazwij ją jak chcesz — byleby można ją było znaleźć.</p>

            @if(!empty($errors))
                <div style="background:#ffdddd;border-left:4px solid #f44336;padding:1em;margin:1em auto;max-width:560px;text-align:left;border-radius:4px;">
                    <ul style="margin:0;padding-left:1.2em;">
                        @foreach($errors as $err)<li>{{ $err }}</li>@endforeach
                    </ul>
                </div>
            @endif
        </header>

        <section class="wrapper style4 special container medium">
            <div class="content">
                <form action="{{ route('plants.store') }}" method="POST">
                    @csrf
                    <div class="row gtr-50">
                        <div class="col-12">
                            <label style="display:block;font-size:.85em;opacity:.7;margin-bottom:.3em;">Nazwa własna rośliny *</label>
                            <input type="text" name="name"
                                   placeholder="np. Monstera przy oknie, Kaktus na parapecie…"
                                   value="{{ $form->name ?? '' }}"
                                   maxlength="100" required />
                            <p class="hint">Twoja prywatna nazwa — nikt inny jej nie widzi.</p>
                        </div>
                        <div class="col-12">
                            <label style="display:block;font-size:.85em;opacity:.7;margin-bottom:.3em;">Typ rośliny *</label>
                            <select name="plant_type_id" required
                                    style="padding:.75em;width:100%;background:none;border:solid 1px rgba(124,128,129,.2);">
                                <option value="">— wybierz typ —</option>
                                @foreach($plantTypes as $type)
                                    <option value="{{ $type['id'] }}">{{ $type['name'] }}</option>
                                @endforeach
                            </select>
                            <p class="hint">Typ określa zalecany rytm podlewania.</p>
                        </div>
                        <div class="col-12">
                            <label style="display:block;font-size:.85em;opacity:.7;margin-bottom:.3em;">Notatki (opcjonalnie)</label>
                            <textarea name="notes" rows="4"
                                      placeholder="Skąd pochodzi, gdzie stoi, co jej dolega…"
                                      style="width:100%;padding:.75em;background:none;border:solid 1px rgba(124,128,129,.2);border-radius:4px;font-family:inherit;resize:vertical;"></textarea>
                        </div>
                        <div class="col-8 col-12-mobile">
                            <ul class="buttons">
                                <li><input type="submit" class="special" value="Zapisz roślinę" /></li>
                                <li><a href="{{ route('plants.index') }}" class="button">Anuluj</a></li>
                            </ul>
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
