<?php

namespace App\Http\Controllers\Identidad;

use App\Backend\Modulos\Identidad\Servicios\RolePreviewService;
use App\Http\Controllers\Controller;
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
        ]);

        $preview->activate($request->user(), $validated['role']);

        return redirect()->route('dashboard');
    }

    public function destroy(Request $request, RolePreviewService $preview): RedirectResponse
    {
        abort_unless($request->user()?->hasRole('SUPERADMINISTRADOR'), 403);

        $preview->clear();

        return redirect()->route('dashboard');
    }
}
