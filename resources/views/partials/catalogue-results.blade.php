{{-- Fragment wynikow katalogu — uzywany i w pelnej stronie (catalogue.blade.php),
     i samodzielnie przy AJAX-ie (PlantTypeController@index zwraca sam ten widok). --}}

@if (empty($plantTypes))
    <div class="empty-state">
        <span class="icon solid fa-seedling"></span>
        <p>Brak roślin spełniających podane kryteria.</p>
    </div>
@else
    <table class="data-table">
        <thead>
            <tr>
                <th>Nazwa</th>
                <th>Opis</th>
                <th style="text-align:center;">Podlewanie</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($plantTypes as $pt)
                <tr>
                    <td>{{ $pt['name'] }}</td>
                    <td>{{ $pt['description'] ?? '' }}</td>
                    <td style="text-align:center;">
                        <span class="watering-badge">co {{ $pt['watering_interval_days'] }} dni</span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- paginacja: linki zachowuja aktualne filtry (search + watering) --}}
    @if ($lastPage > 1)
        @php
            $buildUrl = function ($p) use ($search, $wateringFilter) {
                $params = array_filter([
                    'search'   => $search,
                    'watering' => $wateringFilter,
                    'page'     => $p,
                ], fn ($v) => $v !== '' && $v !== null);
                return route('catalogue') . '?' . http_build_query($params);
            };
        @endphp

        <div style="display:flex;justify-content:center;align-items:center;gap:.5em;margin-top:1.6em;flex-wrap:wrap;">
            @if ($page > 1)
                <a href="{{ $buildUrl($page - 1) }}" class="filter-btn filter-btn-ghost">&laquo; Poprzednia</a>
            @endif

            @for ($p = 1; $p <= $lastPage; $p++)
                @if ($p === $page)
                    <span class="filter-btn filter-btn-primary">{{ $p }}</span>
                @else
                    <a href="{{ $buildUrl($p) }}" class="filter-btn filter-btn-ghost">{{ $p }}</a>
                @endif
            @endfor

            @if ($page < $lastPage)
                <a href="{{ $buildUrl($page + 1) }}" class="filter-btn filter-btn-ghost">Następna &raquo;</a>
            @endif
        </div>
    @endif
@endif
