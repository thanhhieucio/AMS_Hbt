<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

/**
 * Landing hub shown right after login, letting the user pick which
 * module to enter (HSB-IT asset management, or a future module such
 * as the organic vegetable ordering placeholder).
 */
class PortalController extends Controller
{
    public function index(): View
    {
        return view('portal');
    }
}
