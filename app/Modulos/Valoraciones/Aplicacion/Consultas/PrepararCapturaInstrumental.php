<?php

declare(strict_types=1);

namespace App\Modulos\Valoraciones\Aplicacion\Consultas;

use App\Models\Atencion;
use App\Models\DocumentoClinico;
use App\Models\Instrumento;
use App\Models\Residente;
use App\Models\User;
use App\Modulos\ApoyoDecision\Aplicacion\Consultas\FuentesValoracionOperativa;
use App\Modulos\Clinica\Aplicacion\Acciones\AccesoClinicoTemporalService;
use App\Modulos\Clinica\Aplicacion\Acciones\AutorizacionClinicaService;
use App\Modulos\Clinica\Aplicacion\Consultas\ContextoClinicoService;
use App\Modulos\Cuidados\Aplicacion\Consultas\TurnoEnfermeriaService;
use App\Modulos\Valoraciones\Aplicacion\Acciones\AplicarInstrumento;
use App\Modulos\Valoraciones\Aplicacion\DTOs\CatalogoPreguntasInstrumento;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use LogicException;
use MotorExperto\instrumentos\RegistroMetodosInstrumentales;

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
        $residente = FuentesValoracionOperativa::residentes(Residente::query())->findOrFail($codigoResidente);
        Gate::forUser($usuario)->authorize('view', $residente);
        $personal = app(AutorizacionClinicaService::class)->autorizarMutacion($usuario, $residente,
            'aplicaciones_instrumento.crear', AplicarInstrumento::ROLES_APLICADORES);

        return ['residente' => trim(implode(' ', [$residente->nombres, $residente->apellido_paterno, $residente->apellido_materno])),
            'profesional' => trim(implode(' ', [$personal->nombres, $personal->apellido_paterno, $personal->apellido_materno])),
            'nivel_educativo' => $residente->nivel_educativo];
    }

    /** Metadatos de atención ya autorizados; nunca selecciona la última por defecto. */
    public function atenciones(?User $usuario, string $codigoResidente, string $fecha = ''): Collection
    {
        Validator::make(['fechaAtencion' => $fecha], ['fechaAtencion' => ['nullable', 'date_format:Y-m-d']])->validate();

        return $this->consultaAtenciones($usuario, $codigoResidente)
            ->when($fecha !== '', fn ($q) => $q->whereDate('fecha_hora', $fecha))
            ->orderByDesc('fecha_hora')->orderBy('cod_atencion')->limit(100)->get();
    }

    public function atencion(?User $usuario, string $codigoResidente, string $codigoAtencion): ?Atencion
    {
        return $this->consultaAtenciones($usuario, $codigoResidente)->whereKey($codigoAtencion)->first();
    }

    private function consultaAtenciones(?User $usuario, string $codigoResidente): Builder
    {
        $usuario = $this->usuario($usuario);
        abort_unless($usuario->checkPermissionTo('atenciones.ver', 'web'), 403);
        $this->contexto($usuario, $codigoResidente);

        $puedeVerInformante = app(ContextoAtencionCognitiva::class)->puedeVerInformantes($usuario);

        return Atencion::query()->where('cod_residente', $codigoResidente)
            ->with('personal:cod_personal,nombres,apellido_paterno,apellido_materno')
            ->when($puedeVerInformante, fn ($q) => $q->with('informantePrincipal.contacto:cod_contacto,nombres,apellido_paterno,apellido_materno'))
            ->select(['cod_atencion', 'cod_personal', 'fecha_hora', 'tipo_atencion', 'estado', 'escolaridad_anios', 'puede_leer', 'puede_escribir', 'idioma_principal_evaluacion',
                ...($puedeVerInformante ? ['cod_residente_contacto_informante', 'informante_conoce_nivel_previo'] : [])]);
    }

    public function instrumentos(?User $usuario): Collection
    {
        $this->usuario($usuario);

        return FuentesValoracionOperativa::instrumentoInstitucional(Instrumento::query())->where('estado', 'ACTIVO')->orderBy('nombre')->get(['cod_instrumento', 'codigo', 'nombre', 'version']);
    }

    public function catalogo(?User $usuario, string $codigoResidente, string $codigoInstrumento): array
    {
        $this->contexto($usuario, $codigoResidente);
        $instrumento = FuentesValoracionOperativa::instrumentoInstitucional(Instrumento::query())->whereKey($codigoInstrumento)->where('estado', 'ACTIVO')->firstOrFail();

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
        $perfiles = array_values(array_filter(app(\App\Modulos\ApoyoDecision\Aplicacion\Servicios\PerfilesInstrumentalesIniciales::class)
            ->resolver(new \Illuminate\Database\Eloquent\Collection([$instrumento])),
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

        $inicial = app(\App\Modulos\ApoyoDecision\Aplicacion\Servicios\PerfilesInstrumentalesIniciales::class)
            ->definicion($instrumento, $instrumento->preguntas()->with('opciones')->get());

        return ['estado' => $incompleto ? 'VINCULO_INCOMPLETO' : 'VINCULADO', 'inicial' => $inicial,
            'metodo' => $perfil['metodo'], 'componentes' => $meta['componentes'] ?? [], 'fuente' => $meta['fuente'] ?? null,
            'identidad' => $perfil['identidad_instrumento'] ?? [], 'entradas' => $entradas];
    }

    /** El permiso específico permite metadatos de la fuente del residente; no concede descarga general de documentos. */
    public function fuentesTranscripcion(?User $usuario, string $codigoResidente): Collection
    {
        $usuario = $this->usuario($usuario);
        abort_unless($usuario->hasAnyRole(['MEDICO GENERAL/GERIATRA', 'PSICOLOGO/A'])
            && $usuario->checkPermissionTo('aplicaciones_instrumento.transcribir', 'web'), 403);
        $residente = FuentesValoracionOperativa::residentes(Residente::query())->findOrFail($codigoResidente);
        $this->contexto($usuario, $codigoResidente);
        Gate::forUser($usuario)->authorize('view', $residente);

        return DocumentoClinico::query()->where('cod_residente', $codigoResidente)->where('estado', 'VIGENTE')
            ->orderByDesc('fecha_hora')->limit(100)->get(['cod_documento_clinico', 'titulo', 'fecha_hora']);
    }
}
