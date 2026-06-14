<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Database;

class DiaryController extends Controller {

    // pamietnik pielegnacji — lista wpisow z filtrowaniem
    public function index(Request $request) {
        $user = $this->currentUser();
        $db = Database::getInstance();

        // rosliny usera do dropdownu (formularz + filtr)
        $plants = $db->select(
            'plants', ['id', 'name'],
            ['user_id' => $user->id, 'ORDER' => ['name' => 'ASC']]
        ) ?: [];

        // parametry filtrowania z URL (?plant_id=...&action=...&date_from=...&date_to=...)
        $filterPlant = $request->input('plant_id');
        $filterAction = $request->input('action');
        $filterFrom = $request->input('date_from');
        $filterTo = $request->input('date_to');

        $where = [
            'plants.user_id' => $user->id,
            'ORDER'          => ['care_logs.logged_at' => 'DESC'],
        ];
        if ($filterPlant) $where['care_logs.plant_id'] = (int) $filterPlant;
        if ($filterAction) $where['care_logs.action'] = $filterAction;
        if ($filterFrom) $where['logged_at[>=]'] = $filterFrom . ' 00:00:00';
        if ($filterTo) $where['logged_at[<=]'] = $filterTo . ' 23:59:59';

        // paginacja
        // count MUSI miec JOIN, bo filtruje po plants.user_id (kolumna z dolaczonej tabeli)
        $perPage  = 6;
        $page     = max(1, (int) $request->input('page', 1));
        $total    = $db->count('care_logs', ['[>]plants' => ['plant_id' => 'id']], 'care_logs.id', $where);
        $lastPage = max(1, (int) ceil($total / $perPage));
        $page     = min($page, $lastPage);
        $where['LIMIT'] = [($page - 1) * $perPage, $perPage];   // Medoo: [offset, ile]

        $logs = $db->select(
            'care_logs',
            ['[>]plants' => ['plant_id' => 'id']],
            [
                'care_logs.id', 'care_logs.plant_id', 'care_logs.action',
                'care_logs.notes', 'care_logs.logged_at',
                'plants.name(plant_name)',
            ],
            $where
        ) ?: [];

        return view('diary.index', [
            'user' => $user,
            'plants' => $plants,
            'logs' => $logs,
            'filterPlant' => $filterPlant,
            'filterAction' => $filterAction,
            'filterFrom' => $filterFrom,
            'filterTo' => $filterTo,
            'page' => $page,           // dane dla pagera
            'lastPage' => $lastPage,
            'infos' => (array) session('infos', []),
            'errors' => (array) session('diary_errors', []),
        ]);
    }

    // nowy wpis do pamiętnika (POST)
    public function store(Request $request) {
        $user = $this->currentUser();
        $db = Database::getInstance();
        $errors = [];

        $plantId = $request->input('plant_id');
        $action = $request->input('action');
        $notes = trim((string) $request->input('notes')) ?: null;
        $loggedAt = $request->input('logged_at');   // opcjonalnie — date picker

        $allowed = ['watering', 'fertilising', 'repotting', 'pruning', 'other'];

        // walidacja
        if (!$plantId && !$action) {
            $errors[] = 'Błędne wywołanie aplikacji!';
        } else {
            if (!$plantId) {
                $errors[] = 'Nie wybrano rośliny.';
            }
            if (!in_array($action, $allowed)) {
                $errors[] = 'Nieprawidłowy typ akcji.';
            }
            // walidacja kontekstowa — data nie moze byc w przyszlosci
            if ($loggedAt && $loggedAt > date('Y-m-d')) {
                $errors[] = 'Data wpisu nie może być w przyszłości.';
            }
        }

        if (empty($errors)) {
            // walidacja kontekstowa — roslina nalezy do tego usera (nie mozna wpisac obcego ID)
            if (!$db->has('plants', ['id' => (int) $plantId, 'user_id' => $user->id])) {
                $errors[] = 'Nie znaleziono rośliny w Twoim ogrodzie.';
            } else {
                $row = [
                    'plant_id' => (int) $plantId,
                    'action'   => $action,
                    'notes'    => $notes,
                ];
                if ($loggedAt) {
                    $row['logged_at'] = $loggedAt . ' 00:00:00';
                }

                $db->insert('care_logs', $row);
                return redirect()->route('diary.index')
                    ->with('infos', ['Wpis dodany.']);
            }
        }

        // z powrotem do pamiętnika z błędami
        return redirect()->route('diary.index')
            ->with('diary_errors', $errors);
    }
}
