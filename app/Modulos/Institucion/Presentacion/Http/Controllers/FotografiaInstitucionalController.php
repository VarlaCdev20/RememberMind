<?php

namespace App\Modulos\Institucion\Presentacion\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\FotografiaInstitucional;
use App\Models\User;
use App\Modulos\Institucion\Infraestructura\Fotografias\AlmacenamientoFotografias;
use App\Modulos\Institucion\Presentacion\Policies\FotografiaInstitucionalPolicy;
use Illuminate\Support\Facades\Storage;

class FotografiaInstitucionalController extends Controller
{
    public function __invoke(string $fotografia, string $variante, FotografiaInstitucionalPolicy $policy, AlmacenamientoFotografias $archivos)
    {
        $actor = auth()->user() ? User::find(auth()->id()) : null;
        abort_unless($policy->viewAny($actor), 403);
        $foto = FotografiaInstitucional::findOrFail($fotografia);
        abort_unless($policy->view($actor, $foto), 403);
        abort_unless(in_array($variante, ['imagen', 'miniatura'], true), 404);
        $ruta = $variante === 'imagen' ? $foto->ruta_archivo : $foto->ruta_miniatura;
        $archivos->validarRuta($ruta);
        abort_unless(Storage::disk('local')->exists($ruta), 404);
        return response()->file(Storage::disk('local')->path($ruta), ['Content-Type' => 'image/webp',
            'Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff']);
    }
}
