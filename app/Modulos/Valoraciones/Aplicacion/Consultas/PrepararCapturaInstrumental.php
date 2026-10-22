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
use MotorExperto\dominio\instrumentos\RegistroMetodosInstrumentales;

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
        Gate::forUser($usuario)->authorize('viewAny', Residente::class);
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
        Gate::forUser($usuario)->authorize('view', $residente);
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

        $catalogo = CatalogoPreguntasInstrumento::proyectar($instrumento, $preguntas);
        $catalogo['vinculo_metodo'] = $this->vinculoMetodo($instrumento, $catalogo['preguntas']);

        return $catalogo;
    }

    /** Presenta el contrato explícito; nunca identifica una prueba por semejanza de nombre. */
    private function vinculoMetodo(Instrumento $instrumento, array $preguntas): array
    {
        if ($instrumento->tipo === 'OBSERVACIONAL') {
            return ['estado' => 'OBSERVACIONAL'];
        }
        $perfiles = array_values(array_filter(config('sistema_experto.perfiles_instrumentales', []),
            static fn ($perfil): bool => is_array($perfil)
                && ($perfil['cod_instrumento'] ?? null) === $instrumento->getKey()
                && ($perfil['version_instrumento'] ?? null) === $instrumento->version));
        if (count($perfiles) !== 1) {
            return ['estado' => count($perfiles) > 1 ? 'VINCULO_AMBIGUO' : 'SIN_VINCULO'];
        }
        $perfil = $perfiles[0];
        $meta = (new RegistroMetodosInstrumentales)->metodos()[$perfil['metodo'] ?? ''] ?? null;
        $identidad = $perfil['identidad_instrumento'] ?? null;
        $identidadMetodo = $meta['identidad'] ?? null;
        if (is_array($identidad) && is_array($identidadMetodo)) {
            ksort($identidad);
            ksort($identidadMetodo);
        }
        if (! is_array($meta) || ! is_array($perfil['selectores'] ?? null)
            || ! is_string($perfil['id'] ?? null) || $perfil['id'] === ''
            || ($perfil['codigo_instrumento'] ?? null) !== $instrumento->codigo
            || ! is_array($identidad) || $identidad !== $identidadMetodo) {
            return ['estado' => 'VINCULO_INCOMPLETO'];
        }
        $porCodigo = array_column($preguntas, null, 'codigo');
        $entradas = [];
        $preguntasUsadas = [];
        foreach ($meta['entradas'] as $nombre => $tipo) {
            $selector = $perfil['selectores'][$nombre] ?? null;
            $pregunta = is_array($selector) ? ($porCodigo[$selector['cod_pregunta'] ?? ''] ?? null) : null;
            $campo = $tipo === 'LOGICO' ? 'valor_logico' : 'valor_numero';
            $codigo = $pregunta['codigo'] ?? null;
            $disponible = $pregunta !== null && $pregunta['campo'] === $campo && ($selector['campo'] ?? null) === $campo
                && ! isset($preguntasUsadas[$codigo]);
            if ($codigo !== null) {
                $preguntasUsadas[$codigo] = true;
            }
            $entradas[] = ['nombre' => $nombre, 'tipo' => $tipo, 'pregunta' => $codigo,
                'codigo_tarea' => $pregunta['codigo_catalogo'] ?? null, 'disponible' => $disponible];
        }

        $incompleto = array_diff(array_keys($perfil['selectores']), array_keys($meta['entradas'])) !== []
            || count(array_filter($entradas, static fn ($e) => ! $e['disponible'])) > 0;

        return ['estado' => $incompleto ? 'VINCULO_INCOMPLETO' : 'VINCULADO',
            'metodo' => $perfil['metodo'], 'componentes' => $meta['componentes'] ?? [], 'fuente' => $meta['fuente'] ?? null,
            'identidad' => $perfil['identidad_instrumento'] ?? [], 'entradas' => $entradas];
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
