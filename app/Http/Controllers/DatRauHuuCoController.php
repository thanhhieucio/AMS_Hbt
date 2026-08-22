<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

/**
 * Placeholder for the "Đặt rau hữu cơ" module, linked from the portal
 * hub while the real module is being developed.
 */
class DatRauHuuCoController extends Controller
{
    public function index(): View
    {
        return view('dat-rau-huu-co.index');
    }
}
