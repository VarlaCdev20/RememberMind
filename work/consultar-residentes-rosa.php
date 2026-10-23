<?php
require dirname(__DIR__).'/vendor/autoload.php';
$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$actor = App\Models\User::where('correo', 'admincasaamandita@gmail.com')->sole();
$rosa = App\Models\Personal::where('cod_usuario', App\Models\User::where('correo', 'enfermeria@remembermind.com')->sole()->getKey())->sole();
$asignaciones = App\Models\AsignacionResidenteJornada::with(['jornada.turno', 'residente'])
    ->where('cod_personal', $rosa->getKey())->orderByDesc('fecha_hora')->limit(80)->get();
echo json_encode(['actor_activo' => $actor->estado, 'permiso_asignar' => $actor->can('turnos.asignar'),
    'rosa' => $rosa->getKey(), 'historial' => $asignaciones->map(fn ($a) => [
        'id' => $a->getKey(), 'residente' => $a->cod_residente, 'nombre' => $a->residente?->nombre_completo,
        'estado_residente' => $a->residente?->estado, 'jornada' => $a->cod_jornada,
        'fecha' => $a->jornada?->fecha_jornada?->format('Y-m-d'), 'turno' => $a->jornada?->turno?->nombre,
        'fecha_registro' => $a->fecha_hora?->toIso8601String(), 'estado' => $a->estado,
        'supervision' => $a->nivel_supervision,
    ])], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), PHP_EOL;
