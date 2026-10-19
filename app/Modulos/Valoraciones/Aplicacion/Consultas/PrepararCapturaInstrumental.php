<?php

declare(strict_types=1);

namespace App\Modulos\Valoraciones\Aplicacion\Consultas;

use App\Models\DocumentoClinico;
use App\Models\Instrumento;
use App\Models\Residente;
use App\Models\User;
use App\Modulos\Clinica\Aplicacion\Acciones\AccesoClinicoTemporalService;
use App\Modulos\Clinica\Aplicacion\Acciones\AutorizacionClinicaService;
use App\Modulos\Clinica\Aplicacion\Consultas\ContextoClinicoService;
use App\Modulos\Cuidados\Aplicacion\Consultas\TurnoEnfermeriaService;
use App\Modulos\Valoraciones\Aplicacion\Acciones\AplicarInstrumento;
use App\Modulos\Valoraciones\Aplicacion\DTOs\CatalogoPreguntasInstrumento;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use LogicException;

/** Prepara fuentes de captura sin crear instrumentos, preguntas ni registros clínicos. */
final readonly class PrepararCapturaInstrumental
{
    public function usuario(?User $usuario): User
    {
        abort_unless($usuario, 403);
        $usuario = User::query()->findOrFail($usuario->getKey());
        abort_unless($usuario->estado === 'ACTIVO'
            && app(AccesoClinicoTemporalService::class)->tieneRol($usuario, AplicarInstrumento::ROLES_APLICADORES), 403);
        foreach (['aplicaciones_instrumento.crear', 'instrumentos.ver', 'preguntas_instrumento.ver', 'opciones_pregunta.ver'] as $permiso) {
            abort_unless($usuario->checkPermissionTo($permiso, 'web'), 403);
        }
        try {
            $personal = app(ContextoClinicoService::class)->personalActivo($usuario);
            if (! app(AccesoClinicoTemporalService::class)->sustituyeRol($usuario)) {
                app(ContextoClinicoService::class)->areaAtencion($personal);
            }
        } catch (LogicException $e) {
            abort(403, $e->getMessage());
        }

        return $usuario;
    }

    public function residentes(?User $usuario, string $busqueda = ''): Collection
    {
        $usuario = $this->usuario($usuario);

        return app(TurnoEnfermeriaService::class)->obtenerPacientesAsignadosQuery($usuario)
            ->whereIn('estado', ['ACTIVO', 'ADMITIDO'])
            ->when($busqueda !== '', fn ($q) => $q->where(function ($q) use ($busqueda): void {
                $q->where('nombres', 'like', '%'.$busqueda.'%')->orWhere('apellido_paterno', 'like', '%'.$busqueda.'%')
                    ->orWhere('apellido_materno', 'like', '%'.$busqueda.'%');
            }))->orderBy('nombres')->orderBy('cod_residente')->limit(50)
            ->get(['cod_residente', 'nombres', 'apellido_paterno', 'apellido_materno']);
    }

    public function contexto(?User $usuario, string $codigoResidente): array
    {
        $usuario = $this->usuario($usuario);
        $residente = Residente::query()->findOrFail($codigoResidente);
        $personal = app(AutorizacionClinicaService::class)->autorizarMutacion($usuario, $residente,
            'aplicaciones_instrumento.crear', AplicarInstrumento::ROLES_APLICADORES);

        return ['residente' => trim(implode(' ', [$residente->nombres, $residente->apellido_paterno, $residente->apellido_materno])),
            'profesional' => trim(implode(' ', [$personal->nombres, $personal->apellido_paterno, $personal->apellido_materno]))];
    }

    public function instrumentos(?User $usuario): Collection
    {
        $this->usuario($usuario);

        return Instrumento::query()->where('estado', 'ACTIVO')->orderBy('nombre')->get(['cod_instrumento', 'codigo', 'nombre', 'version']);
    }

    public function catalogo(?User $usuario, string $codigoResidente, string $codigoInstrumento): array
    {
        $this->contexto($usuario, $codigoResidente);
        $instrumento = Instrumento::query()->whereKey($codigoInstrumento)->where('estado', 'ACTIVO')->firstOrFail();

        $preguntas = $instrumento->preguntas()->where('estado', 'ACTIVA')->orderBy('orden')->orderBy('cod_pregunta')
            ->with(['opciones' => fn ($q) => $q->where('estado', 'ACTIVO')->orderBy('orden')->orderBy('cod_opcion')])
            ->get();

        return CatalogoPreguntasInstrumento::proyectar($instrumento, $preguntas);
    }

    /** El permiso específico permite metadatos de la fuente del residente; no concede descarga general de documentos. */
    public function fuentesTranscripcion(?User $usuario, string $codigoResidente): Collection
    {
        $usuario = $this->usuario($usuario);
        abort_unless($usuario->hasAnyRole(['MEDICO GENERAL/GERIATRA', 'PSICOLOGO/A'])
            && $usuario->checkPermissionTo('aplicaciones_instrumento.transcribir', 'web'), 403);
        $residente = Residente::query()->findOrFail($codigoResidente);
        $this->contexto($usuario, $codigoResidente);
        Gate::forUser($usuario)->authorize('view', $residente);

        return DocumentoClinico::query()->where('cod_residente', $codigoResidente)->where('estado', 'VIGENTE')
            ->orderByDesc('fecha_hora')->limit(100)->get(['cod_documento_clinico', 'titulo', 'fecha_hora']);
    }
}
