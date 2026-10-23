<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class PlaceholderController extends Controller
{
    public function __invoke(Request $request): View
    {
        return view('placeholder', [
            'layout' => $request->routeIs('admin.*') ? 'admin' : 'site',
            'routeName' => $request->route()?->getName(),
        ]);
    }
}
