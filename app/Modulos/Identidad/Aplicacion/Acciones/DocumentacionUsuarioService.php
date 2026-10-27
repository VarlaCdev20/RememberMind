<?php

namespace App\Modulos\Identidad\Aplicacion\Acciones;

use App\Compartido\Aplicacion\Contratos\CodigosEntidad;
use App\Compartido\Aplicacion\Contratos\ContextoEjecucion;
use App\Compartido\Aplicacion\Contratos\DocumentosUsuario;
use App\Models\Documento;
use App\Models\User;
use App\Modulos\Identidad\Aplicacion\Consultas\CapacidadDocumentalPersonal;
use App\Modulos\Identidad\Presentacion\Policies\DocumentoLaboralPolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class DocumentacionUsuarioService
{
    public function __construct(private readonly ContextoEjecucion $contexto, private readonly DocumentosUsuario $archivos) {}

    public function tiposDisponibles(User $usuario): array
    {
        $tipos = [
            ['cod_tipo_doc' => 'CI', 'nombre' => 'Cédula de Identidad', 'descripcion' => 'Anverso y reverso legibles.', 'obligatorio' => true, 'requiere_vencimiento' => false],
            ['cod_tipo_doc' => 'FOTO', 'nombre' => 'Fotografía actual', 'descripcion' => 'Fotografía formal vigente.', 'obligatorio' => true, 'requiere_vencimiento' => false],
            ['cod_tipo_doc' => 'CV', 'nombre' => 'Hoja de Vida / CV', 'descripcion' => 'Currículum actualizado y documentado.', 'obligatorio' => true, 'requiere_vencimiento' => false],
            ['cod_tipo_doc' => 'CONTRATO', 'nombre' => 'Contrato o prestación profesional', 'descripcion' => 'Relación laboral o de servicios.', 'obligatorio' => false, 'requiere_vencimiento' => true],
            ['cod_tipo_doc' => 'CONFIDENCIALIDAD', 'nombre' => 'Declaración de confidencialidad', 'descripcion' => 'Compromiso de manejo de información sensible.', 'obligatorio' => false, 'requiere_vencimiento' => false],
        ];

        if ($usuario->hasAnyRole(['ENFERMEROS', 'MEDICO GENERAL/GERIATRA', 'PSICOLOGO/A', 'NUTRICIONISTA', 'FISIOTERAPEUTA', 'PEDAGOGO'])) {
            $tipos[] = ['cod_tipo_doc' => 'TITULO', 'nombre' => 'Título o certificado de formación', 'descripcion' => 'Respaldo académico o técnico.', 'obligatorio' => true, 'requiere_vencimiento' => false];
            $tipos[] = ['cod_tipo_doc' => 'MATRICULA', 'nombre' => 'Matrícula profesional', 'descripcion' => 'Registro profesional vigente.', 'obligatorio' => $usuario->hasRole('MEDICO GENERAL/GERIATRA'), 'requiere_vencimiento' => true];
        }

        return $tipos;
    }

    public function tipoDisponible(User $usuario, string $codigo): object
    {
        // FOTO_DOC ya es la clave del alta institucional; FOTO identifica el mismo
        // requisito en la ficha anterior. Solo se comparte la definición de captura,
        // conservando la clave original y la cadena al sustituir un archivo.
        $tipo = collect($this->tiposDisponibles($usuario))->firstWhere('cod_tipo_doc', $codigo === 'FOTO_DOC' ? 'FOTO' : $codigo);
        abort_unless($tipo, 404, 'Tipo documental no válido.');

        return (object) [...$tipo, 'cod_tipo_doc' => $codigo];
    }

    public function obtenerChecklistUsuario(User $usuario): array
    {
        $documentos = Documento::query()->where('cod_usuario', $usuario->cod_usuario)
            ->whereNull('cod_residente')->whereNull('cod_contacto')->whereNull('cod_preadmision')
            ->whereNotIn('estado', ['ANULADO', 'ANULADA', 'REEMPLAZADO'])->orderByDesc('fecha_validacion')->get()->groupBy('tipo_documento');

        return collect($this->tiposDisponibles($usuario))->map(function (array $tipo) use ($documentos): array {
            // Compatibilidad exclusivamente de consulta: el alta conserva FOTO_DOC y
            // los archivos anteriores conservan FOTO. No se renombra ni se reasigna SQL.
            $codigos = $tipo['cod_tipo_doc'] === 'FOTO' ? ['FOTO', 'FOTO_DOC'] : [$tipo['cod_tipo_doc']];
            $candidatos = collect($codigos)->flatMap(fn ($codigo) => $documentos->get($codigo, collect()))->values();
            $conflicto = $candidatos->count() > 1;
            $actual = $candidatos->count() === 1 ? $candidatos->first() : null;

            return $tipo + ['requiere_validacion' => true, 'cargado' => $candidatos->isNotEmpty(),
                'documento' => $actual, 'estado' => $actual?->estado ?? 'PENDIENTE',
                'conflicto' => $conflicto, 'documentos_candidatos' => $candidatos,
                'advertencia' => $conflicto ? 'Hay varios archivos vigentes para este requisito. Revisa su historia antes de determinar cuál corresponde.' : null];
        })->all();
    }

    public function calcularAvanceDocumental(User $usuario, ?array $checklist = null): array
    {
        $items = collect($checklist ?? $this->obtenerChecklistUsuario($usuario));
        $requeridos = $items->where('obligatorio', true);
        $validadosRequeridos = $requeridos->where('estado', 'VALIDADO')->count();

        return [
            'total_requeridos' => $requeridos->count(), 'total_opcionales' => $items->where('obligatorio', false)->count(),
            'cargados' => $items->where('cargado', true)->count(), 'validados' => $items->where('estado', 'VALIDADO')->count(),
            'pendientes_validacion' => $items->whereIn('estado', ['PENDIENTE', 'CARGADO'])->count(),
            'observados' => $items->where('estado', 'OBSERVADO')->count(), 'vencidos' => $items->where('estado', 'VENCIDO')->count(),
            'conflictos' => $items->where('conflicto', true)->count(),
            'porcentaje_avance' => $requeridos->isEmpty() ? 100 : (int) round(($validadosRequeridos / $requeridos->count()) * 100),
            'completo' => $validadosRequeridos === $requeridos->count(),
        ];
    }

    /** @return array{cod_usuario: string, tipo_documento: string, cod_documento: ?string} */
    public function referenciaVigente(User $usuario, string $codTipoDoc, ?User $actor = null): array
    {
        $actor = $this->autorizar($actor ?? $this->contexto->usuario(), ['documentos.subir', 'documentos.gestionar']);
        $usuario = User::findOrFail($usuario->getKey());
        $this->autorizarCargaLaboral($actor, $usuario);
        $this->tipoDisponible($usuario, $codTipoDoc);
        $actual = $this->originalUnico($this->vigentes($usuario->getKey(), $codTipoDoc)->get(), $codTipoDoc);

        return ['cod_usuario' => $usuario->getKey(), 'tipo_documento' => $codTipoDoc, 'cod_documento' => $actual?->getKey()];
    }

    public function subirDocumento(User $usuario, string $codTipoDoc, object $file, ?string $fechaEmision = null, ?string $fechaVencimiento = null, ?string $observaciones = null, ?User $subidoPor = null, ?array $referenciaEsperada = null): Documento
    {
        $actor = $this->autorizar($subidoPor ?? $this->contexto->usuario(), ['documentos.subir', 'documentos.gestionar']);
        $usuario = User::query()->findOrFail($usuario->getKey());
        $this->autorizarCargaLaboral($actor, $usuario);
        $tipo = $this->tipoDisponible($usuario, $codTipoDoc);
        $referencia = $referenciaEsperada ?? $this->referenciaVigente($usuario, $codTipoDoc, $actor);
        if (($referencia['cod_usuario'] ?? null) !== $usuario->getKey() || ($referencia['tipo_documento'] ?? null) !== $codTipoDoc
            || ! array_key_exists('cod_documento', $referencia) || (! is_null($referencia['cod_documento']) && ! is_string($referencia['cod_documento']))) {
            throw ValidationException::withMessages(['file' => 'Vuelve a abrir la carga desde el documento de esta persona.']);
        }
        $vencimiento = [$tipo->requiere_vencimiento ? 'required' : 'nullable', 'date'];
        if ($fechaEmision) {
            $vencimiento[] = 'after:fechaEmision';
        }
        Validator::make(compact('file', 'fechaEmision', 'fechaVencimiento', 'observaciones'), [
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'fechaEmision' => ['nullable', 'date', 'before_or_equal:today'],
            'fechaVencimiento' => $vencimiento, 'observaciones' => ['nullable', 'string', 'max:1000'],
        ])->validate();
        $archivo = null;
        try {
            return DB::transaction(function () use ($usuario, $codTipoDoc, $file, $fechaEmision, $fechaVencimiento, $observaciones, $actor, $tipo, $referencia, &$archivo) {
                $titular = User::query()->lockForUpdate()->findOrFail($usuario->getKey());
                $actor = $this->autorizar($actor, ['documentos.subir', 'documentos.gestionar']);
                $this->autorizarCargaLaboral($actor, $titular);
                $previo = $this->originalUnico($this->vigentes($titular->getKey(), $codTipoDoc)->lockForUpdate()->get(), $codTipoDoc);
                if ($referencia['cod_documento'] !== $previo?->getKey()) {
                    throw ValidationException::withMessages(['file' => 'El documento cambió mientras estaba abierto este formulario. Revisa la versión vigente y vuelve a abrir la carga.']);
                }
                $capacidad = app(CapacidadDocumentalPersonal::class)->disponible();
                $privado = str_starts_with($previo?->ruta_archivo ?? '', 'documentos_personal_privados/') || $capacidad;
                $archivo = $this->archivos->guardar($usuario->getKey(), $file, $privado ? 'documentos_personal_privados' : 'documentos/usuarios');
                $procedencia = $capacidad ? ['cod_usuario_carga' => $actor->getKey(), 'fecha_hora_carga' => now(), 'fecha_emision' => $fechaEmision] : [];
                $detalle = array_filter([$observaciones, ! $capacidad && $fechaEmision ? "Fecha de emisión: {$fechaEmision}" : null,
                    "Nombre original: {$this->archivos->nombreOriginal($file)}", $capacidad ? null : 'Subido por: '.$actor->getKey()]);
                $documento = Documento::create([
                    'cod_documento' => app(CodigosEntidad::class)->siguiente('documentos'), 'cod_usuario' => $usuario->getKey(),
                    'cod_documento_anterior' => $previo?->getKey(), 'tipo_documento' => $codTipoDoc, 'nombre' => $tipo->nombre,
                    ...$archivo, ...$procedencia, 'fecha_vencimiento' => $fechaVencimiento, 'estado' => 'CARGADO', 'observacion' => implode("\n", $detalle),
                ]);
                $previo?->update(['estado' => 'REEMPLAZADO']);
                activity('Usuarios')->causedBy($actor)->performedOn($usuario)->event('documentacion')->log(($previo ? 'Reemplazó' : 'Subió')." el documento '{$tipo->nombre}'.");

                return $documento;
            });
        } catch (\Throwable $error) {
            if ($archivo !== null) {
                $this->archivos->compensar($archivo['ruta_archivo']);
            }
            throw $error;
        }
    }

    private function vigentes(string $usuario, string $tipo): Builder
    {
        return Documento::query()->where('cod_usuario', $usuario)
            ->whereIn('tipo_documento', in_array($tipo, ['FOTO', 'FOTO_DOC'], true) ? ['FOTO', 'FOTO_DOC'] : [$tipo])
            ->whereNull('cod_residente')->whereNull('cod_contacto')->whereNull('cod_preadmision')
            ->whereIn('estado', ['PENDIENTE', 'CARGADO', 'VALIDADO', 'OBSERVADO', 'VENCIDO']);
    }

    private function originalUnico(\Illuminate\Support\Collection $previos, string $tipo): ?Documento
    {
        if ($previos->count() > 1) {
            throw ValidationException::withMessages(['file' => 'Hay varios archivos vigentes de este tipo. Revisa su historia antes de sustituirlos.']);
        }
        $previo = $previos->first();
        if ($previo && $previo->tipo_documento !== $tipo) {
            throw ValidationException::withMessages(['file' => 'La fotografía ya tiene un registro de origen. Actualiza la ficha y sustituye ese archivo conservando su tipo.']);
        }

        return $previo;
    }

    public function validarDocumento(Documento $documento, ?User $validador, ?string $usuarioEsperado = null): void
    {
        $actor = $this->autorizarRevisionLaboral($documento, $validador, ['documentos.gestionar']);
        $this->transicionar($documento, $actor, $usuarioEsperado, 'VALIDADO', 'Validó');
    }

    public function observarDocumento(Documento $documento, string $motivo, ?User $validador, ?string $usuarioEsperado = null): void
    {
        $actor = $this->autorizarRevisionLaboral($documento, $validador, ['documentos.gestionar']);
        Validator::make(compact('motivo'), ['motivo' => ['required', 'string', 'min:5', 'max:255']])->validate();
        $this->transicionar($documento, $actor, $usuarioEsperado, 'OBSERVADO', 'Observó', $motivo);
    }

    public function anularDocumento(Documento $documento, string $motivo, ?User $validador, ?string $usuarioEsperado = null): void
    {
        $actor = $this->autorizarRevisionLaboral($documento, $validador, ['documentos.archivar', 'documentos.gestionar']);
        if ($actor->hasRole('GERENTE') && ! $actor->hasRole('SUPERADMINISTRADOR')) {
            app(CapacidadDocumentalPersonal::class)->exigir();
        }
        Validator::make(compact('motivo'), ['motivo' => ['required', 'string', 'min:5', 'max:255']])->validate();
        $this->transicionar($documento, $actor, $usuarioEsperado, 'ANULADO', 'Anuló', $motivo);
    }

    private function autorizar(?User $actor, array $permisos): User
    {
        $actor = $actor ? User::query()->find($actor->getKey()) : null;
        abort_unless($actor?->estado === 'ACTIVO' && collect($permisos)->contains(fn ($permiso) => $actor->can($permiso)), 403);
        abort_if(app(RolePreviewService::class)->isActive($actor), 403);

        return $actor;
    }

    private function autorizarCargaLaboral(User $actor, User $titular): void
    {
        if ($titular->personal) {
            $contexto = new Documento(['cod_usuario' => $titular->getKey()]);
            abort_unless(app(DocumentoLaboralPolicy::class)->view($actor, $contexto), 403);
        }
    }

    private function autorizarRevisionLaboral(Documento $documento, ?User $actor, array $permisos): User
    {
        $actor = $actor?->fresh();
        abort_unless($actor?->estado === 'ACTIVO', 403);
        if (($actor?->hasRole('GERENTE') && ! $actor->hasRole('SUPERADMINISTRADOR'))
            || ($documento->cod_usuario && $documento->usuario?->personal)) {
            abort_unless(app(DocumentoLaboralPolicy::class)->review($actor, $documento->fresh()), 403);

            return $actor;
        }

        return $this->autorizar($actor, $permisos);
    }

    private function transicionar(Documento $documento, User $actor, ?string $usuarioEsperado, string $estado, string $accion, ?string $motivo = null): void
    {
        DB::transaction(function () use ($documento, $actor, $usuarioEsperado, $estado, $accion, $motivo) {
            $actual = Documento::query()->lockForUpdate()->findOrFail($documento->getKey());
            abort_unless($actual->cod_usuario && ($usuarioEsperado === null || $actual->cod_usuario === $usuarioEsperado), 404);
            abort_if(in_array($actual->estado, ['ANULADO', 'ANULADA', 'REEMPLAZADO'], true), 422, 'El documento no admite esta operación.');
            $this->autorizarRevisionLaboral($actual, $actor, $estado === 'ANULADO' ? ['documentos.archivar', 'documentos.gestionar'] : ['documentos.gestionar']);
            $datos = ['estado' => $estado];
            if ($estado !== 'ANULADO') {
                $datos['cod_usuario_validacion'] = $actor->getKey();
            }
            if ($estado === 'ANULADO' && app(CapacidadDocumentalPersonal::class)->disponible()) {
                $datos += ['cod_usuario_anulacion' => $actor->getKey(), 'fecha_hora_anulacion' => now(), 'motivo_anulacion' => $motivo];
            }
            if ($estado === 'VALIDADO') {
                $datos['fecha_validacion'] = now();
            }
            if ($motivo !== null) {
                $datos['observacion'] = trim($actual->observacion."\n{$estado}: {$motivo}");
            }
            $actual->update($datos);
            $this->registrarActividad($actual, $actor, $accion);
        });
    }

    private function registrarActividad(Documento $documento, User $usuario, string $accion): void
    {
        activity('Usuarios')->causedBy($usuario)->performedOn($documento->usuario)->event('documentacion')->log("{$accion} el documento '{$documento->nombre}'.");
    }
}
