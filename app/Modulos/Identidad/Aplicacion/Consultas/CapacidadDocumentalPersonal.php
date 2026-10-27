<?php

namespace App\Modulos\Identidad\Aplicacion\Consultas;

use Illuminate\Support\Facades\Schema;

final class CapacidadDocumentalPersonal
{
    public function disponible(): bool
    {
        return Schema::hasColumns('usuarios', ['fecha_hora_alta', 'cambio_password_pendiente'])
            && Schema::hasColumns('documentos', ['cod_usuario_carga', 'fecha_hora_carga', 'fecha_emision', 'cod_usuario_anulacion', 'fecha_hora_anulacion', 'motivo_anulacion'])
            && Schema::hasTable('solicitudes_documentales_personal') && Schema::hasTable('destinatarios_solicitud_documental')
            && Schema::hasTable('entregas_solicitud_documental') && Schema::hasTable('movimientos_laborales_personal');
    }

    public function exigir(): void
    {
        abort_unless($this->disponible(), 409, 'La ampliación documental aprobada todavía no está instalada en esta base.');
    }
}
