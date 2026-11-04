<?php

namespace App\Modulos\Identidad\Presentacion\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modulos\Identidad\Aplicacion\Acciones\RolePreviewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RolePreviewController extends Controller
{
    public function store(Request $request, RolePreviewService $preview): RedirectResponse
    {
        abort_unless($request->user()?->hasRole('SUPERADMINISTRADOR'), 403);

        $validated = $request->validate([
            'role' => ['required', 'string', Rule::in(array_keys($preview->availableRoles()))],
            'destino' => ['nullable', Rule::in(['alertas'])],
        ]);

        $preview->activate($request->user(), $validated['role']);

        if (($validated['destino'] ?? null) === 'alertas' && $validated['role'] !== 'FAMILIAR') {
            return redirect()->route('admin.alertas-clinicas.index');
        }

        return redirect()->route('dashboard');
    }

    public function destroy(Request $request, RolePreviewService $preview): RedirectResponse
    {
        abort_unless($request->user()?->hasRole('SUPERADMINISTRADOR'), 403);

        $preview->clear();

        return redirect()->route('dashboard');
    }
}
