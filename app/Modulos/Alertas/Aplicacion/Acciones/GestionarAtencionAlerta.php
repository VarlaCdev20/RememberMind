<?php
namespace App\Modulos\Alertas\Aplicacion\Acciones;
use App\Models\Alerta;
use App\Models\User;
final class GestionarAtencionAlerta
{
    public function atenderAlerta(User $usuario, string $id, string $texto): void
    {
        app(AlertasService::class)->registrarDesdePanel(Alerta::findOrFail($id), $texto, $usuario, atencion: true);
    }
    public function cerrarAlerta(User $usuario, string $id, string $texto): void
    {
        app(AlertasService::class)->cerrar(Alerta::findOrFail($id), $texto, $usuario, coordinacion: true);
    }
}
