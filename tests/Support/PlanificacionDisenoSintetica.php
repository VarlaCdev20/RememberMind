<?php
// Exclusivo del servidor sintético desechable, después de sus controles de entorno y ruta.
if (getenv('APP_ENV') !== 'testing' || getenv('RM_GERENCIA_QA_PLANIFICACION') !== '1') throw new RuntimeException('Fuera del ensayo.');
$areaAgenda = \App\Models\Area::where('nombre','Acompañamiento y cuidados')->sole();
$personaAgenda = \App\Models\User::where('correo','equipo1@example.test')->sole();
foreach ([['Tarde','16:00','23:00',3], ['Noche','23:00','07:00',1]] as [$nombreAgenda,$inicioAgenda,$finAgenda,$diaAgenda]) {
    $turnoAgenda = \App\Models\Turno::create(['nombre'=>$nombreAgenda,'hora_inicio'=>$inicioAgenda,'hora_cierre'=>$finAgenda,'estado'=>'ACTIVO']);
    $jornadaAgenda = \App\Models\Jornada::create(['cod_turno'=>$turnoAgenda->getKey(),'fecha_jornada'=>today()->startOfWeek()->addDays($diaAgenda),'estado'=>'ABIERTA']);
    \App\Models\AsignacionPersonal::create(['cod_personal'=>$personaAgenda->personal->getKey(),'cod_jornada'=>$jornadaAgenda->getKey(),'cod_area'=>$areaAgenda->getKey(),'estado'=>'ACTIVA']);
}

foreach ([1=>['MARTA','RÍOS'], 2=>['DIEGO','LEÓN'], 3=>['EVA','COSTA']] as $numeroAgenda=>$nombreAgenda) {
    \App\Models\User::where('correo','equipo'.$numeroAgenda.'@example.test')->sole()->personal
        ->update(['nombres'=>$nombreAgenda[0],'apellido_paterno'=>$nombreAgenda[1],'apellido_materno'=>null]);
}

\App\Models\User::where('cod_usuario','QA_GERENCIA')->sole()->forceFill(['cambio_password_pendiente'=>false])->save();
