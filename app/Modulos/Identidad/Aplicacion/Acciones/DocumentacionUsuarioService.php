<?php

namespace App\Modulos\Identidad\Aplicacion\Acciones;

use App\Compartido\Aplicacion\Contratos\ContextoEjecucion;
use App\Compartido\Aplicacion\Contratos\DocumentosUsuario;
use App\Models\Documento;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

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
        $tipo = collect($this->tiposDisponibles($usuario))->firstWhere('cod_tipo_doc', $codigo);
        abort_unless($tipo, 404, 'Tipo documental no válido.');

        return (object) $tipo;
    }

    public function obtenerChecklistUsuario(User $usuario): array
    {
        $documentos = Documento::query()->where('cod_usuario', $usuario->cod_usuario)
            ->whereNotIn('estado', ['ANULADO', 'ANULADA', 'REEMPLAZADO'])->orderByDesc('fecha_validacion')->get()->groupBy('tipo_documento');

        return collect($this->tiposDisponibles($usuario))->map(function (array $tipo) use ($documentos): array {
            $actual = $documentos->get($tipo['cod_tipo_doc'])?->first();

            return $tipo + ['requiere_validacion' => true, 'cargado' => $actual !== null, 'documento' => $actual, 'estado' => $actual?->estado ?? 'PENDIENTE'];
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
            'porcentaje_avance' => $requeridos->isEmpty() ? 100 : (int) round(($validadosRequeridos / $requeridos->count()) * 100),
            'completo' => $validadosRequeridos === $requeridos->count(),
        ];
    }

    public function subirDocumento(User $usuario, string $codTipoDoc, object $file, ?string $fechaEmision = null, ?string $fechaVencimiento = null, ?string $observaciones = null, ?User $subidoPor = null): Documento
    {
        $actor = $this->autorizar($subidoPor ?? $this->contexto->usuario(), ['documentos.subir', 'documentos.gestionar']);
        $usuario = User::query()->findOrFail($usuario->getKey());
        $tipo = $this->tipoDisponible($usuario, $codTipoDoc);
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
            return DB::transaction(function () use ($usuario, $codTipoDoc, $file, $fechaEmision, $fechaVencimiento, $observaciones, $actor, $tipo, &$archivo) {
                User::query()->lockForUpdate()->findOrFail($usuario->getKey());
                $previo = Documento::query()->where('cod_usuario', $usuario->getKey())->where('tipo_documento', $codTipoDoc)
                    ->whereIn('estado', ['PENDIENTE', 'CARGADO', 'VALIDADO', 'OBSERVADO', 'VENCIDO'])->lockForUpdate()->first();
                $archivo = $this->archivos->guardar($usuario->getKey(), $file);
                $detalle = array_filter([$observaciones, $fechaEmision ? "Fecha de emisión: {$fechaEmision}" : null,
                    "Nombre original: {$this->archivos->nombreOriginal($file)}", 'Subido por: '.$actor->getKey()]);
                $documento = Documento::create([
                    'cod_documento' => app(\App\Compartido\Aplicacion\Contratos\CodigosEntidad::class)->siguiente('documentos'), 'cod_usuario' => $usuario->getKey(),
                    'cod_documento_anterior' => $previo?->getKey(), 'tipo_documento' => $codTipoDoc, 'nombre' => $tipo->nombre,
                    ...$archivo, 'fecha_vencimiento' => $fechaVencimiento, 'estado' => 'CARGADO', 'observacion' => implode("\n", $detalle),
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

    public function validarDocumento(Documento $documento, ?User $validador, ?string $usuarioEsperado = null): void
    {
        $actor = $this->autorizar($validador, ['documentos.gestionar']);
        $this->transicionar($documento, $actor, $usuarioEsperado, 'VALIDADO', 'Validó');
    }

    public function observarDocumento(Documento $documento, string $motivo, ?User $validador, ?string $usuarioEsperado = null): void
    {
        $actor = $this->autorizar($validador, ['documentos.gestionar']);
        Validator::make(compact('motivo'), ['motivo' => ['required', 'string', 'min:5', 'max:255']])->validate();
        $this->transicionar($documento, $actor, $usuarioEsperado, 'OBSERVADO', 'Observó', $motivo);
    }

    public function anularDocumento(Documento $documento, string $motivo, ?User $validador, ?string $usuarioEsperado = null): void
    {
        $actor = $this->autorizar($validador, ['documentos.archivar', 'documentos.gestionar']);
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

    private function transicionar(Documento $documento, User $actor, ?string $usuarioEsperado, string $estado, string $accion, ?string $motivo = null): void
    {
        DB::transaction(function () use ($documento, $actor, $usuarioEsperado, $estado, $accion, $motivo) {
            $actual = Documento::query()->lockForUpdate()->findOrFail($documento->getKey());
            abort_unless($actual->cod_usuario && ($usuarioEsperado === null || $actual->cod_usuario === $usuarioEsperado), 404);
            abort_if(in_array($actual->estado, ['ANULADO', 'ANULADA', 'REEMPLAZADO'], true), 422, 'El documento no admite esta operación.');
            $datos = ['estado' => $estado, 'cod_usuario_validacion' => $actor->getKey()];
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
