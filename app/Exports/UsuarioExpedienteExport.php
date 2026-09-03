<?php

namespace App\Exports;

use App\Models\User;
use App\Services\Usuarios\UsuarioFichaService;
use App\Services\Usuarios\DocumentacionUsuarioService;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class UsuarioExpedienteExport implements FromView, ShouldAutoSize
{
    protected $usuario;

    public function __construct(User $usuario)
    {
        $this->usuario = $usuario;
    }

    public function view(): View
    {
        $fichaService = app(UsuarioFichaService::class);
        $docService = app(DocumentacionUsuarioService::class);

        $expediente = $fichaService->obtenerExpedienteCompleto($this->usuario);
        $checklist = $docService->obtenerChecklistUsuario($this->usuario);
        $actividades = $fichaService->obtenerHistorialActividad($this->usuario)->items();

        return view('pdf.exports.usuarios.excel_ficha', [
            'usuario' => $this->usuario,
            'rol' => $expediente['rol'],
            'nombre_rol' => $expediente['nombre_rol'],
            'area' => $expediente['area'],
            'horarios' => $expediente['horarios'],
            'avance_documental' => $expediente['avance_documental'],
            'checklist' => $checklist,
            'actividades' => $actividades,
            'fecha' => now()->format('d/m/Y H:i'),
        ]);
    }
}

