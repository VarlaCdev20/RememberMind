<?php

if (getenv('APP_ENV') !== 'testing' || getenv('RM_GERENCIA_QA_RED_APOYO') !== '1') throw new RuntimeException('Fuera del ensayo.');

$actor->forceFill(['cambio_password_pendiente' => false])->save();
$contacto->update(['celular' => '77700001']);
foreach ([['Familiar de prueba', 'Identidad sintética', 'Hijo', 'ACTIVO', true], ['Persona de apoyo', 'Contacto sintético de nombre extenso', 'Amistad', 'INACTIVO', false]] as [$nombres, $apellido, $parentesco, $estado, $responsable]) {
    $personaRed = \App\Models\Contacto::create(['nombres' => $nombres, 'apellido_paterno' => $apellido, 'celular' => '77700002', 'correo' => 'red-apoyo@example.test', 'estado' => $estado]);
    \App\Models\ResidenteContacto::create(['cod_residente_contacto' => $responsable ? 'QA_RED_PRINCIPAL' : 'QA_RED_INACTIVO', 'cod_residente' => $residentes[0]->getKey(), 'cod_contacto' => $personaRed->getKey(),
        'parentesco' => $parentesco, 'responsable_principal' => $responsable, 'contacto_emergencia' => $responsable,
        'autoriza_informacion' => false, 'autoriza_salida' => false, 'estado' => $estado]);
}

\App\Models\ResidenteContacto::create(['cod_residente_contacto' => 'QA_RED_UNICO', 'cod_residente' => $residentes[2]->getKey(), 'cod_contacto' => $contacto->getKey(),
    'parentesco' => 'Hija', 'responsable_principal' => true, 'contacto_emergencia' => true,
    'autoriza_informacion' => true, 'autoriza_salida' => false, 'estado' => 'ACTIVO']);
