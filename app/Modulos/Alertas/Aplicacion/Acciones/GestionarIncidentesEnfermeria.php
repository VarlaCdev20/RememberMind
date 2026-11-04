<?php

namespace App\Modulos\Alertas\Aplicacion\Acciones;

use App\Compartido\Aplicacion\Contratos\CodigosEntidad;
use App\Models\Alerta;
use App\Models\Herida;
use App\Models\Incidente;
use App\Models\User;
use App\Modulos\Cuidados\Aplicacion\Consultas\MiTurnoService;
use App\Modulos\Cuidados\Aplicacion\Consultas\TurnoEnfermeriaService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

final class GestionarIncidentesEnfermeria
{
    public function guardarIncidente(User $usuario, array $entrada): void
    {
        $this->autorizarMutacion($usuario, $entrada['codResidente']);
        Validator::make($entrada, ['tipoIncidente' => 'required|in:CAIDA,GOLPE,ERROR_MEDICACION,LESION,CAMBIO_CLINICO,OTRO', 'lugarIncidente' => 'required|string|max:120', 'actividadPrevia' => 'nullable|string|max:500', 'testigo' => 'required_if:presenciado,true|nullable|string|max:120', 'descripcionIncidente' => 'required|string|min:10|max:2000', 'dolorIncidente' => 'nullable|integer|min:0|max:10', 'tipoLesion' => 'required_if:hayLesion,true|nullable|string|max:60', 'zonaLesion' => 'required_if:hayLesion,true|nullable|string|max:120'])->validate();
        $personal = $usuario?->personal;
        abort_unless($personal, 403, 'Personal clínico no vinculado.');
        $miTurnoService = app(MiTurnoService::class);
        $jornada = $miTurnoService->resolverJornadaActual($personal, now());
        abort_unless($jornada, 409, 'No existe una jornada profesional activa para firmar el incidente.');
        $medida = $entrada['movilidadPosterior'] ? 'Movilidad: '.$entrada['movilidadPosterior'] : null;
        $obs = [];
        if ($entrada['dolorIncidente'] !== null) {
            $obs[] = 'Dolor: '.$entrada['dolorIncidente'].'/10';
        }
        if ($entrada['hayLesion']) {
            $obs[] = 'Lesión evidente';
        }
        if ($entrada['presenciado'] && $entrada['testigo']) {
            $obs[] = 'Testigo: '.$entrada['testigo'];
        }
        if ($entrada['familiarInformado']) {
            $obs[] = 'Familiar informado';
        }
        $observacionTexto = ! empty($obs) ? implode('. ', $obs) : null;
        DB::transaction(function () use ($usuario, $entrada, $personal, $jornada, $medida, $observacionTexto): void {
            $incidente = Incidente::create(['cod_incidente' => app(CodigosEntidad::class)->siguiente('incidentes'), 'cod_residente' => $entrada['codResidente'], 'cod_personal' => $personal->cod_personal, 'cod_jornada' => $jornada->cod_jornada, 'tipo_incidente' => mb_strtoupper(trim($entrada['tipoIncidente'])), 'gravedad' => $entrada['dolorIncidente'] === null ? null : ($entrada['dolorIncidente'] >= 7 ? 'GRAVE' : ($entrada['dolorIncidente'] >= 4 ? 'MODERADA' : 'LEVE')), 'lugar' => $entrada['lugarIncidente'], 'fecha_hora' => now(), 'descripcion' => $entrada['descripcionIncidente'], 'medida_inmediata' => $medida, 'requiere_medico' => (bool) $entrada['medicoInformado'], 'requiere_derivacion' => false, 'estado' => $entrada['requiereSeguimiento'] ? 'EN_SEGUIMIENTO' : 'ABIERTO', 'observacion' => $observacionTexto]);
            if ($entrada['hayLesion'] && $entrada['tipoLesion']) {
                Herida::create(['cod_herida' => app(CodigosEntidad::class)->siguiente('heridas'), 'cod_residente' => $entrada['codResidente'], 'cod_personal' => $personal->cod_personal, 'tipo_herida' => mb_strtoupper(trim($entrada['tipoLesion'])), 'ubicacion' => $entrada['zonaLesion'], 'causa' => 'Incidente: '.mb_strtoupper(trim($entrada['tipoIncidente'])), 'fecha_hora_identificacion' => now(), 'estado' => 'ACTIVA']);
            }
            $alerta = Alerta::create(['cod_alerta' => app(CodigosEntidad::class)->siguiente('alertas'), 'cod_residente' => $entrada['codResidente'], 'cod_personal_responsable' => $personal->cod_personal, 'modulo' => 'INCIDENTE', 'cod_registro' => $incidente->getKey(), 'tipo' => mb_strtoupper(trim($entrada['tipoIncidente'])), 'generacion' => 'AUTOMATICA', 'prioridad' => mb_strtoupper(trim($entrada['tipoIncidente'])) === 'CAIDA' ? 'ALTA' : 'MEDIA', 'titulo' => 'Incidente reportado: '.mb_strtoupper(trim($entrada['tipoIncidente'])), 'descripcion' => $entrada['descripcionIncidente'], 'fecha_hora' => now(), 'estado' => 'ABIERTA']);
            app(AlertasService::class)->registrarEventoOrigen($alerta, $usuario);
        });
    }

    public function seguimientoIncidente(User $usuario, array $entrada): void
    {
        $this->autorizarMutacion($usuario, $entrada['codResidente']);
        Validator::make($entrada, ['incidenteId' => 'required|string|max:20', 'seguimientoIncidente' => 'required|string|min:10|max:2000'])->validate();
        $incidente = Incidente::where('cod_residente', $entrada['codResidente'])->whereIn('estado', ['ABIERTO', 'EN_SEGUIMIENTO'])->findOrFail($entrada['incidenteId']);
        $incidente->update(['estado' => 'EN_SEGUIMIENTO', 'observacion' => trim($incidente->observacion."\n".'['.now()->format('d/m/Y H:i').'] '.$entrada['seguimientoIncidente'])]);
    }

    public function cerrarIncidente(User $usuario, array $entrada): void
    {
        $this->autorizarMutacion($usuario, $entrada['codResidente']);
        Validator::make($entrada, ['incidenteId' => 'required|string|max:20', 'evaluacionFinalIncidente' => 'required|string|min:10|max:2000', 'resultadoIncidente' => 'required|string|min:5|max:1000'])->validate();
        $incidente = Incidente::where('cod_residente', $entrada['codResidente'])->whereIn('estado', ['ABIERTO', 'EN_SEGUIMIENTO'])->findOrFail($entrada['incidenteId']);
        $incidente->update(['estado' => 'CERRADO', 'observacion' => trim($incidente->observacion."\n[Cierre: ".$entrada['evaluacionFinalIncidente'].' - '.$entrada['resultadoIncidente'].']')]);
    }

    private function autorizarMutacion(User $usuario, string $codResidente): void
    {
        app(TurnoEnfermeriaService::class)->autorizarMutacionPaciente($codResidente, 'atenciones.crear', $usuario);
    }
}
