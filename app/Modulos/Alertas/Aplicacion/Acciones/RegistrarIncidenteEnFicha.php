<?php

namespace App\Modulos\Alertas\Aplicacion\Acciones;

use App\Models\Incidente;
use App\Models\User;
use App\Modulos\Cuidados\Aplicacion\Consultas\MiTurnoService;
use App\Modulos\Cuidados\Aplicacion\Consultas\TurnoEnfermeriaService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

final class RegistrarIncidenteEnFicha
{
    public function guardarNuevoEvento(User $usuario, array $entrada): mixed
    {

        $user = $usuario;
        abort_unless($user, 403, 'Acción no permitida: Usuario no autenticado.');
        abort_unless(strtoupper(trim((string) $user->estado)) === 'ACTIVO', 403, 'Acción no permitida: Cuenta de usuario no activa.');

        $personal = $user->personal;
        abort_unless($personal && strtoupper(trim((string) $personal->estado)) === 'ACTIVO', 403, 'Acción no permitida: Personal no vinculado o inactivo.');

        $codResidente = $entrada['adultoMayor']->cod_residente ?? $entrada['adultoMayor']->cod_residente;
        app(TurnoEnfermeriaService::class)->autorizarMutacionPaciente($codResidente, 'incidentes.crear', $user);

        Validator::make($entrada, [
            'nuevoEventoTipo' => 'required|in:CAIDA,LESION,INCIDENTE,COMPLICACION,OTRO',
            'nuevoEventoFechaHora' => 'required|date|before_or_equal:now',
            'nuevoEventoLugar' => 'required|string|max:120',
            'nuevoEventoSeveridad' => 'required|in:LEVE,MODERADA,GRAVE',
            'nuevoEventoDescripcion' => 'required|string|min:10|max:2000',
            'nuevoEventoDolor' => 'required|integer|min:0|max:10',
            'nuevoEventoTestigo' => 'required_if:nuevoEventoPresenciado,true|nullable|string|max:120',
        ])->validate();

        $miTurnoService = app(MiTurnoService::class);
        $jornada = $miTurnoService->resolverJornadaActual($personal, now());
        abort_unless($jornada, 409, 'No existe una jornada profesional activa para firmar el evento clínico.');

        $medida = $entrada['nuevoEventoMovilidad'] ? 'Movilidad: '.$entrada['nuevoEventoMovilidad'] : null;
        $obs = [];
        if ($entrada['nuevoEventoDolor'] !== null) {
            $obs[] = 'Dolor: '.$entrada['nuevoEventoDolor'].'/10';
        }
        if ($entrada['nuevoEventoLesion']) {
            $obs[] = 'Lesión evidente';
        }
        if ($entrada['nuevoEventoPresenciado'] && $entrada['nuevoEventoTestigo']) {
            $obs[] = 'Testigo: '.$entrada['nuevoEventoTestigo'];
        }
        if ($entrada['nuevoEventoFamiliarInformado']) {
            $obs[] = 'Familiar informado';
        }
        $observacionTexto = ! empty($obs) ? implode('. ', $obs) : null;

        $incidente = Incidente::create([
            'cod_incidente' => 'INC_'.strtoupper(Str::random(10)),
            'cod_residente' => $codResidente,
            'cod_personal' => $personal->cod_personal,
            'cod_jornada' => $jornada->cod_jornada,
            'tipo_incidente' => mb_strtoupper(trim($entrada['nuevoEventoTipo'])),
            'gravedad' => $entrada['nuevoEventoSeveridad'],
            'lugar' => trim($entrada['nuevoEventoLugar']),
            'fecha_hora' => Carbon::parse($entrada['nuevoEventoFechaHora']),
            'descripcion' => trim($entrada['nuevoEventoDescripcion']),
            'medida_inmediata' => $medida,
            'requiere_medico' => (bool) $entrada['nuevoEventoMedicoInformado'],
            'requiere_derivacion' => false,
            'estado' => $entrada['nuevoEventoRequiereSeguimiento'] ? 'EN_SEGUIMIENTO' : 'ABIERTO',
            'observacion' => $observacionTexto,
        ]);

        return $incidente;
    }
}
