<?php

namespace App\Modulos\Identidad\Aplicacion\Acciones;

use App\Compartido\Infraestructura\Persistencia\GeneradorCodigo;
use App\Models\DestinatarioSolicitudDocumental;
use App\Models\Documento;
use App\Models\EntregaSolicitudDocumental;
use App\Models\Personal;
use App\Models\SolicitudDocumentalPersonal;
use App\Models\User;
use App\Modulos\Identidad\Aplicacion\Consultas\CapacidadDocumentalPersonal;
use App\Modulos\Identidad\Dominio\Reglas\DocumentosPersonalInstitucional;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

final class GestionarSolicitudesDocumentales
{
    public function __construct(private readonly AutorizarGestionPersonal $alcance, private readonly CapacidadDocumentalPersonal $capacidad) {}

    public function emitir(User $actor, array $entrada): SolicitudDocumentalPersonal
    {
        $actor = $this->alcance->autorizar($actor);
        abort_unless($actor->hasAnyRole(['GERENTE', 'SUPERADMINISTRADOR']), 403);
        $this->capacidad->exigir();
        $datos = Validator::make($entrada, [
            'rol' => ['required', Rule::in(AutorizarGestionPersonal::ROLES_TRABAJADOR)],
            'tipo_documento' => ['required', 'string', 'max:60'], 'nombre_documento' => ['required', 'string', 'max:160'],
            'indicaciones' => ['nullable', 'string', 'max:4000'], 'motivo' => ['required', 'string', 'min:5', 'max:4000'],
            'personas' => ['required', 'array', 'min:1', 'max:100'], 'personas.*' => ['required', 'string', 'distinct', 'exists:personal,cod_personal'],
            'origen_plazo' => ['required', 'in:ALTA,SOLICITUD_POSTERIOR'], 'fecha_limite' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:today'],
            'clave_solicitud' => ['required', 'uuid'],
        ])->validate();
        $tipo = collect(DocumentosPersonalInstitucional::paraRol($datos['rol']))->firstWhere('id', $datos['tipo_documento']);
        if (! $tipo || ($datos['origen_plazo'] === 'ALTA' && $tipo['obligatorio_inmediato'])) {
            throw ValidationException::withMessages(['tipo_documento' => 'Selecciona un respaldo compatible; el mínimo inmediato del alta no se posterga.']);
        }
        if ($datos['origen_plazo'] === 'SOLICITUD_POSTERIOR' && empty($datos['fecha_limite'])) {
            throw ValidationException::withMessages(['fecha_limite' => 'Indica una fecha límite prospectiva.']);
        }
        $personas = $datos['personas'];
        sort($personas);

        return app(GeneradorCodigo::class)->serializar('solicitudes_documentales_personal', function () use ($actor, $datos, $personas) {
            $this->alcance->autorizar($actor);
            $previa = SolicitudDocumentalPersonal::where('clave_solicitud', $datos['clave_solicitud'])->with('rol', 'destinatarios')->first();
            if ($previa) {
                $emitidos = $previa->destinatarios->pluck('cod_personal')->sort()->values()->all();
                $mismos = $previa->cod_usuario_solicitante === $actor->getKey() && $previa->rol->name === $datos['rol'] && $personas === $emitidos
                    && $previa->tipo_documento === $datos['tipo_documento'] && $previa->nombre_documento === trim($datos['nombre_documento'])
                    && $previa->motivo === trim($datos['motivo']) && $previa->indicaciones === ($datos['indicaciones'] ?? null)
                    && $previa->destinatarios->every(fn ($d) => $d->origen_plazo === $datos['origen_plazo'] && ($d->origen_plazo === 'ALTA' || $d->fecha_limite_original->toDateString() === $datos['fecha_limite']));
                abort_unless($mismos, 409, 'La solicitud ya fue confirmada con otro contenido.');

                return $previa;
            }
            $usuarios = Personal::whereIn('cod_personal', $personas)->pluck('cod_usuario')->filter()->all();
            User::whereIn('cod_usuario', $usuarios)->orderBy('cod_usuario')->lockForUpdate()->get();
            $targets = Personal::whereIn('cod_personal', $personas)->with('usuario.roles')->orderBy('cod_personal')->lockForUpdate()->get();
            if ($targets->count() !== count($personas)) {
                throw ValidationException::withMessages(['personas' => 'Actualiza las personas seleccionadas.']);
            }
            $limites = [];
            foreach ($targets as $personal) {
                $usuario = $personal->usuario ? User::with('roles')->lockForUpdate()->find($personal->cod_usuario) : null;
                abort_unless($usuario && $usuario->hasRole($datos['rol']), 403);
                $this->alcance->autorizar($actor, $usuario);
                $limite = $datos['origen_plazo'] === 'ALTA' ? $usuario->fecha_hora_alta?->copy()->addDays(7)->startOfDay() : Carbon::parse($datos['fecha_limite'])->startOfDay();
                if (! $limite || $limite->lt(today())) {
                    throw ValidationException::withMessages(['origen_plazo' => 'El alta no tiene fecha técnica conocida o su plazo ya pasó. Usa una solicitud posterior con fecha prospectiva.']);
                }
                $limites[$personal->getKey()] = $limite->toDateString();
            }
            $solicitud = SolicitudDocumentalPersonal::create(['rol_id' => Role::findByName($datos['rol'], 'web')->getKey(),
                'tipo_documento' => $datos['tipo_documento'], 'nombre_documento' => trim($datos['nombre_documento']),
                'indicaciones' => $datos['indicaciones'] ?? null, 'motivo' => trim($datos['motivo']),
                'cod_usuario_solicitante' => $actor->getKey(), 'fecha_hora_emision' => now(), 'clave_solicitud' => $datos['clave_solicitud']]);
            foreach ($targets as $personal) {
                DestinatarioSolicitudDocumental::create(['cod_solicitud_documental' => $solicitud->getKey(),
                    'cod_personal' => $personal->getKey(), 'origen_plazo' => $datos['origen_plazo'], 'fecha_limite_original' => $limites[$personal->getKey()]]);
            }
            activity('Personal')->causedBy($actor)->performedOn($solicitud)->event('solicitud_documental')->log('Emitió una solicitud documental con destinatarios explícitos.');

            return $solicitud->load('destinatarios.personal');
        });
    }

    public function prorrogar(User $actor, string $codigo, string $motivo): DestinatarioSolicitudDocumental
    {
        $actor = $this->alcance->autorizar($actor);
        $this->capacidad->exigir();
        abort_unless($actor->hasAnyRole(['GERENTE', 'SUPERADMINISTRADOR']), 403);
        Validator::make(compact('motivo'), ['motivo' => ['required', 'string', 'min:5', 'max:4000']])->validate();

        // Mismo coordinador que la emisión: antes de cualquier bloqueo de destinatarios.
        return app(GeneradorCodigo::class)->serializar('solicitudes_documentales_personal', function () use ($actor, $codigo, $motivo) {
            $destino = DestinatarioSolicitudDocumental::with('personal.usuario')->lockForUpdate()->findOrFail($codigo);
            $this->alcance->autorizar($actor, $destino->personal->usuario);
            if ($destino->origen_plazo !== 'ALTA' || $destino->fecha_limite_prorrogada !== null) {
                throw ValidationException::withMessages(['prorroga' => 'Solo se permite una prórroga del plazo de alta.']);
            }
            $concesiones = DestinatarioSolicitudDocumental::query()->where('cod_personal', $destino->cod_personal)
                ->where('origen_plazo', 'ALTA')->whereDate('fecha_limite_original', $destino->fecha_limite_original->toDateString())
                ->whereNotNull('fecha_limite_prorrogada')->lockForUpdate()->get();
            if ($concesiones->isNotEmpty()) {
                throw ValidationException::withMessages(['prorroga' => $concesiones->count() === 1
                    ? 'El expediente de esta alta ya tiene su única prórroga. Se aplica a todas sus solicitudes de alta.'
                    : 'Hay varias concesiones para esta alta. Revisa la inconsistencia antes de continuar.']);
            }
            $destino->update(['fecha_limite_prorrogada' => $destino->fecha_limite_original->copy()->addDays(7),
                'cod_usuario_prorroga' => $actor->getKey(), 'fecha_hora_prorroga' => now(), 'motivo_prorroga' => trim($motivo)]);
            activity('Personal')->causedBy($actor)->performedOn($destino)->event('prorroga_documental')->log('Concedió la única prórroga de siete días sobre el límite original.');

            return $destino;
        });
    }

    public function vincularEntrega(User $actor, string $destinatario, string $documento): EntregaSolicitudDocumental
    {
        $this->capacidad->exigir();

        return DB::transaction(function () use ($actor, $destinatario, $documento) {
            $destino = DestinatarioSolicitudDocumental::with('personal.usuario', 'solicitud')->lockForUpdate()->findOrFail($destinatario);
            $this->autorizarEntrega($actor, $destino);
            $archivo = Documento::query()->lockForUpdate()->findOrFail($documento);
            abort_unless($archivo->cod_usuario === $destino->personal->cod_usuario && $archivo->tipo_documento === $destino->solicitud->tipo_documento, 403);
            abort_if(in_array($archivo->estado, ['ANULADO', 'ANULADA', 'REEMPLAZADO'], true), 422, 'El documento ya no está vigente.');

            return EntregaSolicitudDocumental::firstOrCreate(['cod_destinatario_documental' => $destino->getKey(), 'cod_documento' => $archivo->getKey()],
                ['cod_usuario_registro' => $actor->getKey(), 'fecha_hora_registro' => now()]);
        });
    }

    public function autorizarEntrega(User $actor, DestinatarioSolicitudDocumental $destino): User
    {
        abort_unless($destino->personal?->cod_usuario && $destino->personal->usuario, 403);
        $actor = $actor->fresh();
        abort_unless($actor?->estado === 'ACTIVO' && ! $actor->hasRole('FAMILIAR'), 403);
        abort_if(app(RolePreviewService::class)->isActive($actor), 403);
        if ($destino->personal->cod_usuario !== $actor->getKey()) {
            $this->alcance->autorizar($actor,$destino->personal->usuario);
        }

        return $actor;
    }
}
