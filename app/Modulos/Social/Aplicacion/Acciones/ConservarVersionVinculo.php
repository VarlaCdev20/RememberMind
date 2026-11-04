<?php

namespace App\Modulos\Social\Aplicacion\Acciones;

use App\Compartido\Aplicacion\Contratos\CodigosEntidad;
use App\Models\ResidenteContacto;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class ConservarVersionVinculo
{
    public function exigir(): void
    {
        abort_unless($this->disponible(), 409, 'La historia nominal del vínculo todavía no está instalada. Puedes consultar o registrar relaciones nuevas; las correcciones y cambios de vigencia requieren conservar la versión anterior.');
    }

    public function disponible(): bool
    {
        return Schema::hasTable('versiones_vinculo_contacto');
    }

    public function conservar(ResidenteContacto $vinculo, ?User $actor, ?string $motivo): void
    {
        if (! $this->disponible()) {
            return;
        }
        $anterior = DB::table('versiones_vinculo_contacto')->where('cod_residente_contacto', $vinculo->getKey())->orderByDesc('numero_version')->first();
        $campos = $vinculo->only(['parentesco', 'responsable_principal', 'contacto_emergencia', 'autoriza_informacion', 'autoriza_salida', 'estado', 'observacion']);
        if ($anterior && collect($campos)->every(fn ($valor, $clave) => is_bool($valor) ? $valor === (bool) $anterior->$clave : $valor === $anterior->$clave)) {
            return;
        }
        DB::table('versiones_vinculo_contacto')->insert([
            'cod_version_vinculo' => app(CodigosEntidad::class)->siguiente('versiones_vinculo_contacto'),
            'cod_residente_contacto' => $vinculo->getKey(), 'numero_version' => ($anterior?->numero_version ?? 0) + 1,
            'cod_version_anterior' => $anterior?->cod_version_vinculo, ...$campos, 'cod_usuario_acto' => $actor?->getKey(),
            'fecha_hora_acto' => $actor ? now() : null, 'motivo' => $actor ? $motivo : null, 'fecha_hora_conservacion' => now(),
        ]);
    }
}
