<?php

namespace App\Modulos\Cuidados\Presentacion\Policies;

use App\Models\DocumentoClinico;
use App\Models\Herida;
use App\Models\Residente;
use App\Models\User;
use App\Modulos\Clinica\Aplicacion\Acciones\AutorizacionClinicaService;
use App\Modulos\Cuidados\Aplicacion\Consultas\TurnoEnfermeriaService;

final class DocumentoHeridaPolicy
{
    public function adjuntar(User $usuario, Residente $residente, string $permiso): bool
    {
        if ($usuario->estado !== 'ACTIVO' || ! $usuario->hasAnyRole(['MEDICO GENERAL/GERIATRA', 'ENFERMEROS'])
            || ! in_array($permiso, ['heridas.crear', 'curaciones_herida.crear'], true)) {
            return false;
        }
        app(AutorizacionClinicaService::class)->autorizarMutacion($usuario, $residente, $permiso,
            $permiso === 'heridas.crear' ? ['MEDICO GENERAL/GERIATRA', 'ENFERMEROS'] : ['ENFERMEROS']);

        return true;
    }

    public function consultar(User $usuario, DocumentoClinico $documento, Residente $residente): bool
    {
        if ($usuario->estado !== 'ACTIVO' || $documento->estado !== 'VIGENTE'
            || $documento->tipo_documento !== 'FOTO_HERIDA' || $documento->cod_residente !== $residente->getKey()
            || ! $usuario->can('heridas.ver') || ($documento->cod_curacion && ! $usuario->can('curaciones_herida.ver'))
            || ! $usuario->hasAnyRole(['SUPERADMINISTRADOR', 'MEDICO GENERAL/GERIATRA', 'ENFERMEROS'])) {
            return false;
        }
        $herida = $documento->cod_herida ? $documento->herida : $documento->curacionHerida?->herida;
        if (! $herida || $herida->cod_residente !== $residente->getKey() || ($documento->cod_herida && $documento->cod_curacion)) {
            return false;
        }

        return $this->consultarHerida($usuario, $herida);
    }

    public function consultarHerida(User $usuario, Herida $herida): bool
    {
        if ($usuario->estado !== 'ACTIVO' || ! $usuario->can('heridas.ver')
            || ! $usuario->hasAnyRole(['SUPERADMINISTRADOR', 'MEDICO GENERAL/GERIATRA', 'ENFERMEROS'])) {
            return false;
        }

        return app(TurnoEnfermeriaService::class)->obtenerPacientesAsignadosQuery($usuario)->whereKey($herida->cod_residente)->exists();
    }
}
