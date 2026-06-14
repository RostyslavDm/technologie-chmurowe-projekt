<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Database;

class PlantTypeController extends Controller {

    // publiczny katalog typow roslin (gosc + zalogowani) — z wyszukiwaniem i filtrowaniem
    public function index(Request $request) {
        $db = Database::getInstance();

        $search = trim((string) $request->input('search', ''));
        $wateringFilter  = $request->input('watering');   // 'frequent' | 'weekly' | ''

        $where = ['is_active' => 1, 'ORDER' => ['name' => 'ASC']];
        if ($search) {
            $where['name[~]'] = $search;
        }
        if ($wateringFilter === 'frequent') {
            $where['watering_interval_days[<]'] = 7;
        }
        if ($wateringFilter === 'weekly') {
            $where['watering_interval_days[>=]'] = 7;
        }

        // paginacja
        // najpierw ile jest wszystkich pasujacych (dla ilosci stron)
        // potem LIMIT do $where
        $perPage  = 6;
        $page     = max(1, (int) $request->input('page', 1));   // numer strony z ?page=
        $total    = $db->count('plant_types', $where);
        $lastPage = max(1, (int) ceil($total / $perPage));
        $page     = min($page, $lastPage);                      // nie wyjdz poza ostatnia strone
        $where['LIMIT'] = [($page - 1) * $perPage, $perPage];   // Medoo: [offset, ile]

        $plantTypes = $db->select(
            'plant_types',
            ['id', 'name', 'description', 'watering_interval_days', 'is_active'],
            $where
        ) ?: [];

        $data = [
            'plantTypes' => $plantTypes,
            'user' => $this->currentUser(),
            'search' => $search,
            'wateringFilter' => $wateringFilter,
            'page' => $page,           // dane dla pagera
            'lastPage' => $lastPage,
            'infos' => (array) session('infos', []),
        ];

        // AJAX
        // oddaje SAM kawalek z wynikami, a nie caly HTML
        if ($request->ajax()) {
            return view('partials.catalogue-results', $data);
        }

        return view('catalogue', $data);
    }
}
