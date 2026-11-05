<?php

namespace App\Modulos\Identidad\Presentacion\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class ReferenciasFormulariosController extends Controller
{
    public function __invoke(): View
    {
        abort_unless(app()->environment(['local', 'testing']), 404);

        return view('identidad::paginas.referencias-formularios');
    }
}
