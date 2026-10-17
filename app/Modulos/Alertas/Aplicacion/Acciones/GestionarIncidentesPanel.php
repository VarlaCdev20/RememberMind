<?php

namespace App\Modulos\Alertas\Aplicacion\Acciones;

use App\Models\Alerta;
use App\Models\Derivacion;
use App\Models\EventoAlerta;
use App\Models\Incidente;
use App\Models\Jornada;
use App\Models\Personal;
use App\Models\User;
use App\Modulos\Cuidados\Aplicacion\Consultas\TurnoEnfermeriaService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class GestionarIncidentesPanel
{
    public function registrarIncidente(User $usuario, array $entrada): Incidente
    {
        $user = $usuario;
        $personal = Personal::query()
            ->where('cod_usuario', $user->cod_usuario)
            ->where('estado', 'ACTIVO')
            ->first();
        $codPersonal = $personal?->cod_personal;

        if (! $codPersonal) {
            throw ValidationException::withMessages(['error_general' => 'No se encontró un perfil de personal activo asociado.']);
        }

        // Validar tipo_incidente
        $tipoFinal = $entrada['tipo_incidente'];
        if ($entrada['tipo_incidente'] === 'OTRO') {
            $tipoFinal = trim($entrada['tipo_incidente_otro']);
        }

        // Validación frontend / backend
        Validator::make($entrada, [
            'cod_residente' => 'required|exists:residentes,cod_residente',
            'tipo_incidente' => 'required|string',
            'tipo_incidente_otro' => $entrada['tipo_incidente'] === 'OTRO' ? 'required|string|min:3|max:60' : 'nullable',
            'gravedad' => 'required|in:BAJA,MEDIA,ALTA,CRITICA',
            'lugar' => 'nullable|string|max:120',
            'fecha_incidente' => 'required|date|before_or_equal:today',
            'hora_incidente' => 'required',
            'descripcion' => 'required|string|min:10',
            'medida_inmediata' => 'nullable|string',
            'requiere_medico' => 'boolean',
            'requiere_derivacion' => 'boolean',
            'observacion' => 'nullable|string',
        ], [
            'cod_residente.required' => 'Debe seleccionar un residente.',
            'tipo_incidente.required' => 'Seleccione el tipo de incidente.',
            'tipo_incidente_otro.required' => 'Especifique el tipo de incidente.',
            'tipo_incidente_otro.max' => 'El tipo especificado no debe superar 60 caracteres.',
            'fecha_incidente.before_or_equal' => 'La fecha no puede ser futura.',
            'descripcion.required' => 'La descripción objetiva del evento es obligatoria.',
            'descripcion.min' => 'Describa el evento con al menos 10 caracteres.',
        ])->validate();
        // Validar que la fecha y hora no sean en el futuro
        $fechaHoraIncidente = Carbon::parse($entrada['fecha_incidente'].' '.$entrada['hora_incidente']);
        if ($fechaHoraIncidente->isFuture()) {
            throw ValidationException::withMessages(['hora_incidente' => 'La fecha y hora del incidente no pueden ser futuras.']);
        }

        $turnoActivo = app(TurnoEnfermeriaService::class)
            ->autorizarMutacionPaciente($entrada['cod_residente'], 'incidentes.crear', $user);
        $jornadaActiva = Jornada::where('cod_turno', $turnoActivo->cod_turno)
            ->whereIn('estado', ['ABIERTA', 'ACTIVA', 'EN_CURSO'])
            ->whereDate('fecha_jornada', Carbon::today())
            ->firstOrFail();
        $codJornada = $jornadaActiva->cod_jornada;

        // Normalización backend
        $tipoNormalizado = Str::upper(preg_replace('/\s+/', ' ', trim($tipoFinal)));
        $gravedadNormalizada = Str::upper(trim($entrada['gravedad']));

        DB::beginTransaction();
        try {
            $incidente = new Incidente;
            $incidente->cod_residente = $entrada['cod_residente'];
            $incidente->cod_personal = $codPersonal;
            $incidente->cod_jornada = $codJornada;
            $incidente->tipo_incidente = Str::substr($tipoNormalizado, 0, 60);
            $incidente->gravedad = $gravedadNormalizada;
            $incidente->lugar = $entrada['lugar'] ? Str::substr(trim($entrada['lugar']), 0, 120) : null;
            $incidente->fecha_hora = $fechaHoraIncidente;
            $incidente->descripcion = trim($entrada['descripcion']);
            $incidente->medida_inmediata = $entrada['medida_inmediata'] ? trim($entrada['medida_inmediata']) : null;
            $incidente->requiere_medico = (bool) $entrada['requiere_medico'];
            $incidente->requiere_derivacion = (bool) $entrada['requiere_derivacion'];
            $incidente->estado = 'ABIERTO';
            $incidente->observacion = $entrada['observacion'] ? trim($entrada['observacion']) : null;
            $incidente->save();

            DB::commit();

            // Log de actividad
            activity()
                ->performedOn($incidente)
                ->causedBy($user)
                ->withProperties([
                    'residente' => $entrada['cod_residente'],
                    'tipo' => $tipoNormalizado,
                    'gravedad' => $gravedadNormalizada,
                    'requiere_medico' => $entrada['requiere_medico'],
                    'requiere_derivacion' => $entrada['requiere_derivacion'],
                ])
                ->log('Registro de incidente de enfermería');

            return $incidente;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function actualizarEstado(User $usuario, array $entrada): bool
    {
        Validator::make($entrada, [
            'nuevo_estado' => 'required|in:ABIERTO,EN_SEGUIMIENTO,CERRADO,ANULADO',
            'nota_estado' => 'required|string|min:5|max:500',
        ], [
            'nuevo_estado.required' => 'Seleccione el nuevo estado.',
            'nota_estado.required' => 'Especifique la justificación clínica del cambio de estado.',
            'nota_estado.min' => 'La nota debe tener al menos 5 caracteres.',
        ])->validate();
        $incidente = Incidente::find($entrada['incidenteSeleccionadoId']);
        if (! $incidente) {
            return false;
        }
        $this->autorizarMutacionIncidente($usuario, $incidente);

        $estadoAnterior = $incidente->estado;
        $incidente->estado = $entrada['nuevo_estado'];

        $fechaActual = Carbon::now()->format('d/m/Y H:i');
        $usuarioNombre = $usuario->name ?? 'Personal';
        $anexo = "\n[{$fechaActual} - {$usuarioNombre}]: Estado cambiado de {$estadoAnterior} a {$entrada['nuevo_estado']}. Justificación: ".trim($entrada['nota_estado']);

        $incidente->observacion = ($incidente->observacion ?? '').$anexo;
        $incidente->save();

        activity()
            ->performedOn($incidente)
            ->causedBy($usuario)
            ->withProperties([
                'estado_anterior' => $estadoAnterior,
                'estado_nuevo' => $entrada['nuevo_estado'],
                'nota' => $entrada['nota_estado'],
            ])
            ->log('Actualización de estado de incidente');

        return true;
    }

    public function solicitarValoracionMedica(User $usuario, Incidente $incidente): Alerta
    {
        $this->autorizarMutacionIncidente($usuario, $incidente);
        $existente = Alerta::where('modulo', 'INCIDENTES')->where('cod_registro', $incidente->cod_incidente)->whereIn('estado', ['ACTIVA', 'PENDIENTE'])->first();
        if ($existente) {
            return $existente;
        }
        DB::beginTransaction();
        try {
            $user = $usuario;
            $codUsuario = $user->cod_usuario;
            $personal = Personal::query()
                ->where('cod_usuario', $codUsuario)
                ->where('estado', 'ACTIVO')
                ->first();
            if (! $personal) {
                throw new \LogicException('El usuario autenticado no tiene un perfil de personal activo.');
            }
            $codPersonal = $personal->cod_personal;

            // Prioridad según gravedad
            $prioridad = in_array($incidente->gravedad, ['ALTA', 'CRITICA']) ? 'ALTA' : 'MEDIA';
            if ($incidente->gravedad === 'CRITICA') {
                $prioridad = 'CRITICA';
            }

            $alerta = new Alerta;
            $alerta->cod_alerta = app(\App\Compartido\Aplicacion\Contratos\CodigosEntidad::class)->siguiente('alertas');
            $alerta->cod_residente = $incidente->cod_residente;
            $alerta->cod_personal_responsable = $codPersonal;
            $alerta->tipo = 'CLINICA';
            $alerta->prioridad = $prioridad;
            $alerta->modulo = 'INCIDENTES';
            $alerta->cod_registro = $incidente->cod_incidente;
            $alerta->titulo = "Valoración médica urgente: {$incidente->tipo_incidente}";
            $alerta->descripcion = "Incidente: {$incidente->tipo_incidente} (Gravedad: {$incidente->gravedad}). Detalle: {$incidente->descripcion}";
            $alerta->fecha_hora = Carbon::now();
            $alerta->fecha_hora_limite = Carbon::now()->addHours(2);
            $alerta->generacion = 'MANUAL';
            $alerta->estado = 'ACTIVA';
            $alerta->save();

            // Evento de alerta para trazabilidad
            $evento = new EventoAlerta;
            $evento->cod_evento_alerta = app(\App\Compartido\Aplicacion\Contratos\CodigosEntidad::class)->siguiente('eventos_alerta');
            $evento->cod_alerta = $alerta->cod_alerta;
            $evento->cod_usuario = $codUsuario;
            $evento->tipo_evento = 'CREACION';
            $evento->estado_anterior = null;
            $evento->estado_nuevo = 'ACTIVA';
            $evento->fecha_hora = Carbon::now();
            $evento->descripcion = "Alerta médica solicitada por enfermería a partir de la incidencia {$incidente->cod_incidente}.";
            $evento->save();

            // Marcar incidente como requiere_medico = true y en seguimiento si estaba abierto
            $incidente->requiere_medico = true;
            if ($incidente->estado === 'ABIERTO') {
                $incidente->estado = 'EN_SEGUIMIENTO';
            }
            $incidente->save();

            DB::commit();

            activity()
                ->performedOn($alerta)
                ->causedBy($user)
                ->withProperties(['incidente' => $incidente->cod_incidente])
                ->log('Generación de alerta médica para incidente');

            return $alerta;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function crearDerivacion(User $usuario, array $entrada, Incidente $incidente): Derivacion
    {
        $this->autorizarMutacionIncidente($usuario, $incidente);
        Validator::make($entrada, [
            'derivacion_area_receptora' => 'required|exists:areas,cod_area',
            'derivacion_prioridad' => 'required|in:BAJA,MEDIA,ALTA,URGENTE',
            'derivacion_motivo' => 'required|string|min:10|max:500',
        ], [
            'derivacion_area_receptora.required' => 'Seleccione el área receptora de la derivación.',
            'derivacion_motivo.required' => 'Indique el motivo clínico de la derivación.',
        ])->validate();
        $user = $usuario;
        $codUsuario = $user->cod_usuario;
        $personal = Personal::query()
            ->where('cod_usuario', $codUsuario)
            ->where('estado', 'ACTIVO')
            ->first();
        if (! $personal) {
            throw ValidationException::withMessages(['derivacion_area_receptora' => 'El usuario autenticado no tiene un perfil de personal activo.']);
        }
        $codPersonal = $personal->cod_personal;

        $codAreaSolicitante = $personal->asignaciones()
            ->whereIn('estado', ['ACTIVA', 'ACTIVO'])
            ->whereHas('jornada', fn ($query) => $query->whereDate('fecha_jornada', today()))
            ->latest('fecha_asignacion')
            ->value('cod_area');
        if (! $codAreaSolicitante) {
            throw ValidationException::withMessages(['derivacion_area_receptora' => 'El personal no tiene un área institucional activa en la jornada actual.']);
        }

        DB::beginTransaction();
        try {
            $derivacion = new Derivacion;
            $derivacion->cod_derivacion = app(\App\Compartido\Aplicacion\Contratos\CodigosEntidad::class)->siguiente('derivaciones');
            $derivacion->cod_residente = $incidente->cod_residente;
            $derivacion->cod_area_solicitante = $codAreaSolicitante;
            $derivacion->cod_area_receptora = $entrada['derivacion_area_receptora'];
            $derivacion->cod_personal_solicitante = $codPersonal;
            $derivacion->cod_personal_receptor = null;
            $derivacion->cod_atencion = null;
            $derivacion->motivo = trim($entrada['derivacion_motivo']);
            $derivacion->prioridad = $entrada['derivacion_prioridad'];
            $derivacion->fecha_hora = Carbon::now();
            $derivacion->respuesta = null;
            $derivacion->estado = 'SOLICITADA';
            $derivacion->save();

            // Actualizar flag en incidente y anexo trazable
            $incidente->requiere_derivacion = true;
            $fechaActual = Carbon::now()->format('d/m/Y H:i');
            $incidente->observacion = ($incidente->observacion ?? '')."\n[{$fechaActual}]: Derivación creada ({$derivacion->cod_derivacion}) hacia área receptora.";
            $incidente->save();

            DB::commit();

            activity()
                ->performedOn($derivacion)
                ->causedBy($user)
                ->withProperties(['incidente' => $incidente->cod_incidente])
                ->log('Creación de derivación desde incidente de enfermería');

            return $derivacion;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function autorizarMutacionIncidente(User $usuario, Incidente $incidente): void
    {
        if ($usuario?->hasRole('ENFERMEROS')) {
            app(TurnoEnfermeriaService::class)
                ->autorizarMutacionPaciente($incidente->cod_residente, 'incidentes.crear', $usuario);
        }
    }
}
