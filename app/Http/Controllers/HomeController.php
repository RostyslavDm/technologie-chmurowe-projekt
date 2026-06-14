<?php
namespace App\Http\Controllers;

use App\Transfer\User;

class HomeController extends Controller {
    public function index() {
        $user  = unserialize(session('user'));
        $infos = (array) session('infos', []);
        return view('home', ['user' => $user, 'infos' => $infos]);
    }
}
