<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Models\AsignacionResidenteJornada;
use App\Models\Cama;
use App\Models\Contacto;
use App\Models\ControlCognitivo;
use App\Models\Habitacion;
use App\Models\Jornada;
use App\Models\Personal;
use App\Models\Preadmision;
use App\Models\Turno;
use App\Models\User;
use App\Modulos\Admisiones\Aplicacion\Acciones\FormalizarAdmision;
use App\Modulos\ApoyoDecision\Infraestructura\Simulacion\ContextoSimulacionLocal;
use Database\Seeders\RolesAndPermissionsSeeder;

/** Cohorte mínima, admitida mediante la acción institucional y sin credenciales compartidas. */
trait CohorteSimulacionLocal
{
    private function prepararCohorte(): array
    {
        $this->travelTo(now()->setDate(2026, 10, 17)->setTime(12, 0));
        $this->seed(RolesAndPermissionsSeeder::class);
        config(['sistema_experto.simulacion_local.habilitada' => true]);
        $autor = User::factory()->create(['estado' => 'ACTIVO']);
        $autor->assignRole('SUPERADMINISTRADOR');
        $admin = User::factory()->create(['estado' => 'ACTIVO']);
        $admin->assignRole('ADMINISTRADOR');
        $admin->givePermissionTo('admisiones.formalizar');
        $nurse = User::factory()->create(['estado' => 'ACTIVO']);
        $nurse->assignRole('ENFERMEROS');
        $personal = Personal::create(['cod_usuario' => $nurse->getKey(), 'nombres' => 'Enfermera', 'apellido_paterno' => 'Sintética',
            'numero_documento' => 'SIM-COHORTE-ENF', 'profesion' => 'ENFERMERIA', 'estado' => 'ACTIVO']);
        $turno = Turno::create(['nombre' => 'Simulación', 'hora_inicio' => '00:00:00', 'hora_cierre' => '23:59:59', 'orden' => 1, 'estado' => 'ACTIVO']);
        $jornada = Jornada::create(['cod_turno' => $turno->getKey(), 'fecha_jornada' => now()->toDateString(), 'estado' => 'ABIERTA']);
        $hab = Habitacion::create(['codigo' => 'SIM-COHORTE', 'capacidad' => 5, 'estado' => 'ACTIVA']);
        $residentes = [];
        foreach (ContextoSimulacionLocal::CASOS as $documento => $caso) {
            $contacto = Contacto::create(['nombres' => 'Contacto', 'apellido_paterno' => 'Sintético', 'estado' => 'ACTIVO']);
            $cama = Cama::create(['cod_habitacion' => $hab->getKey(), 'codigo' => 'SIM-'.$documento, 'estado' => 'ACTIVA']);
            $pre = Preadmision::create(['cod_contacto' => $contacto->getKey(), 'cod_usuario_registro' => $admin->getKey(),
                'nombres' => 'Caso '.$caso['caso'], 'apellido_paterno' => 'Sintético', 'numero_documento' => (string) $documento,
                'fecha_nacimiento' => '1945-05-05', 'motivo_ingreso' => 'Cuidados ficticios', 'fecha_solicitud' => '2026-01-01', 'estado' => 'APROBADA']);
            $r = app(FormalizarAdmision::class)->ejecutar($pre, ['cod_cama' => $cama->getKey(), 'cod_contacto' => $contacto->getKey(),
                'fecha_hora_admision' => '2026-01-02 10:00:00'], $admin);
            AsignacionResidenteJornada::create(['cod_residente' => $r->getKey(), 'cod_personal' => $personal->getKey(),
                'cod_jornada' => $jornada->getKey(), 'nivel_supervision' => 'HABITUAL', 'fecha_hora' => now(), 'estado' => 'ACTIVA']);
            ControlCognitivo::create(['cod_residente' => $r->getKey(), 'cod_personal' => $personal->getKey(), 'cod_jornada' => $jornada->getKey(),
                'fecha_hora' => '2026-10-16 09:40:00', 'estado' => 'VIGENTE',
                'memoria_reciente' => match ($caso['caso']) {
                    'CAS-01', 'CAS-02' => 'ALTERACION_LEVE', 'CAS-08' => 'ALTERADA', 'CAS-20' => null, default => 'CONSERVADA'
                },
                'repite_preguntas' => $caso['caso'] !== 'CAS-10', 'olvida_indicaciones' => $caso['caso'] !== 'CAS-10',
                'atencion' => $caso['caso'] === 'CAS-08' ? 'DISMINUIDA' : 'CONSERVADA',
                'comprension' => 'CONSERVADA', 'lenguaje' => 'CONSERVADO',
                'observacion' => '[SIM-SE-20261017-V1:fixture] Antecedente ficticio independiente del patrón declarado en 03A.']);
            $residentes[$caso['caso']] = $r;
        }

        return compact('autor', 'nurse', 'personal', 'residentes');
    }
}
