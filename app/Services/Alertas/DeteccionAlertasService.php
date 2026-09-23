<?php

namespace App\Services\Alertas;

use App\Models\AdministracionMedicacion;
use App\Models\Alerta;
use App\Models\EjecucionCuidado;
use App\Models\EventoAlerta;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DeteccionAlertasService
{
    public function detectar(): int
    {
        $usuario = User::query()->where('estado', 'ACTIVO')->orderBy('cod_usuario')->first();
        if (! $usuario) { return 0; }

        $creadas = 0;
        AdministracionMedicacion::query()->where('resultado', 'OMITIDA')->where('estado', 'REGISTRADA')->chunk(100, function ($registros) use (&$creadas, $usuario): void {
            foreach ($registros as $registro) { $creadas += $this->crearSiNoExiste($registro, $usuario, 'MEDICACION', 'ALTA', 'Administración de medicación omitida', $registro->motivo_omision ?: 'Omisión sin motivo documentado.'); }
        });
        EjecucionCuidado::query()->where('resultado', 'OMITIDA')->where('estado', 'REGISTRADA')->chunk(100, function ($registros) use (&$creadas, $usuario): void {
            foreach ($registros as $registro) { $creadas += $this->crearSiNoExiste($registro, $usuario, 'CUIDADOS', 'MEDIA', 'Intervención de cuidado omitida', $registro->motivo_omision ?: 'Omisión sin motivo documentado.'); }
        });
        return $creadas;
    }

    private function crearSiNoExiste(Model $registro, User $usuario, string $modulo, string $prioridad, string $titulo, string $descripcion): int
    {
        if (Alerta::query()->where('modulo', $modulo)->where('cod_registro', (string) $registro->getKey())->exists()) { return 0; }
        DB::transaction(function () use ($registro, $usuario, $modulo, $prioridad, $titulo, $descripcion): void {
            $alerta = Alerta::query()->create(['cod_alerta' => $this->codigo('ALE'), 'cod_residente' => $registro->cod_residente, 'tipo' => 'OMISION', 'prioridad' => $prioridad, 'modulo' => $modulo, 'cod_registro' => (string) $registro->getKey(), 'titulo' => $titulo, 'descripcion' => $descripcion, 'fecha_hora' => now(), 'generacion' => 'AUTOMATICA', 'estado' => 'ABIERTA']);
            EventoAlerta::query()->create(['cod_evento_alerta' => $this->codigo('EAL'), 'cod_alerta' => $alerta->cod_alerta, 'cod_usuario' => $usuario->cod_usuario, 'tipo_evento' => 'CREADA', 'estado_nuevo' => 'ABIERTA', 'fecha_hora' => now(), 'descripcion' => 'Generada automáticamente por seguimiento operativo.']);
        });
        return 1;
    }

    private function codigo(string $prefijo): string { return $prefijo.'_'.Str::upper(Str::random(12)); }
}
