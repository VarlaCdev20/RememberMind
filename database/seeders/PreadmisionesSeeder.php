<?php

namespace Database\Seeders;

use App\Models\DocumentoPreadmision;
use App\Models\Preadmision;
use App\Models\User;
use Illuminate\Database\Seeder;

class PreadmisionesSeeder extends Seeder
{
    public function run(): void
    {
        $enfermero = User::whereHas('roles', fn($q) => $q->where('name', 'ENFERMEROS'))->first()
                  ?? User::first();
        $admin     = User::whereHas('roles', fn($q) => $q->where('name', 'SUPERADMINISTRADOR'))->first()
                  ?? User::first();

        $preadmisiones = [
            [
                'estado'              => 'PREADMISION_ASIGNADA',
                'prioridad'           => 'MEDIA',
                'nombres'             => 'HUGO ALBERTO',
                'ap_paterno'          => 'VILLAZON',
                'ap_materno'          => 'MEDRANO',
                'ci'                  => '6677881-PRE',
                'expedicion_ci'       => 'CB',
                'fecha_nac'           => '1943-08-14',
                'genero'              => 'MASCULINO',
                'estado_civil'        => 'VIUDO/A',
                'departamento_residencia' => 'COCHABAMBA',
                'ciudad_municipio'    => 'COCHABAMBA',
                'zona'                => 'Cala Cala',
                'calle'               => 'Av. Pando Nro. 789',
                'motivo_ingreso'      => 'Parkinson avanzado. Caídas frecuentes. Familia no puede dar cuidados continuos.',
                'procedencia_ingreso' => 'Derivación médica',
                'tipo_ingreso'        => 'DERIVADO',
                'permanencia'         => 'PERMANENTE',
                'descripcion_caso'    => 'Adulto mayor con Parkinson estadio III. Requiere cuidados de enfermería 24h.',
                'familiar_nombres'    => 'PATRICIA',
                'familiar_ap_paterno' => 'VILLAZON',
                'familiar_ap_materno' => 'GUTIERREZ',
                'familiar_ci'         => '8891122-PRE',
                'familiar_parentesco' => 'HIJA',
                'familiar_celular'    => '76112233',
                'familiar_correo'     => 'patricia.villazon@email.com',
                'familiar_direccion'  => 'Av. Pando Nro. 789, Cala Cala, Cbba',
                'dias_atras'          => 3,
            ],
            [
                'estado'              => 'EN_EVALUACION',
                'prioridad'           => 'ALTA',
                'nombres'             => 'ROSA ELVIRA',
                'ap_paterno'          => 'AGUILAR',
                'ap_materno'          => 'NINA',
                'ci'                  => '7788992-PRE',
                'expedicion_ci'       => 'LP',
                'fecha_nac'           => '1937-04-22',
                'genero'              => 'FEMENINO',
                'estado_civil'        => 'CASADO/A',
                'departamento_residencia' => 'LA PAZ',
                'ciudad_municipio'    => 'EL ALTO',
                'zona'                => 'Villa Adela',
                'calle'               => 'Calle 8 Nro. 200',
                'motivo_ingreso'      => 'Alzheimer estadio moderado. Episodios de deambulación nocturna. Riesgo de caída.',
                'procedencia_ingreso' => 'Solicitud familiar',
                'tipo_ingreso'        => 'FAMILIA',
                'permanencia'         => 'PERMANENTE',
                'descripcion_caso'    => 'Deterioro cognitivo severo. La familia no cuenta con condiciones para cuidado domiciliario.',
                'familiar_nombres'    => 'MARIO FERNANDO',
                'familiar_ap_paterno' => 'AGUILAR',
                'familiar_ap_materno' => 'TORREZ',
                'familiar_ci'         => '9902233-PRE',
                'familiar_parentesco' => 'HIJO',
                'familiar_celular'    => '77223344',
                'familiar_correo'     => 'mario.aguilar@email.com',
                'familiar_direccion'  => 'Calle 8 Nro. 200, Villa Adela, El Alto',
                'dias_atras'          => 7,
            ],
            [
                'estado'              => 'EN_EVALUACION',
                'prioridad'           => 'MEDIA',
                'nombres'             => 'GUILLERMO ERNESTO',
                'ap_paterno'          => 'ARZE',
                'ap_materno'          => 'CALDERON',
                'ci'                  => '8899003-PRE',
                'expedicion_ci'       => 'SC',
                'fecha_nac'           => '1947-12-03',
                'genero'              => 'MASCULINO',
                'estado_civil'        => 'DIVORCIADO/A',
                'departamento_residencia' => 'SANTA CRUZ',
                'ciudad_municipio'    => 'SANTA CRUZ',
                'zona'                => 'Equipetrol',
                'calle'               => 'Calle Los Cedros Nro. 45',
                'motivo_ingreso'      => 'Insuficiencia cardíaca controlada. Necesita supervisión médica continua.',
                'procedencia_ingreso' => 'Derivación médica — Cardiólogo particular',
                'tipo_ingreso'        => 'DERIVADO',
                'permanencia'         => 'PERMANENTE',
                'descripcion_caso'    => 'Cardiopatía con control ambulatorio insuficiente. Requiere seguimiento institucional.',
                'familiar_nombres'    => 'CLAUDIA',
                'familiar_ap_paterno' => 'ARZE',
                'familiar_ap_materno' => 'PARRA',
                'familiar_ci'         => '0013344-PRE',
                'familiar_parentesco' => 'HIJA',
                'familiar_celular'    => '78334455',
                'familiar_correo'     => 'claudia.arze@email.com',
                'familiar_direccion'  => 'Av. Roca y Coronado, Santa Cruz',
                'dias_atras'          => 12,
            ],
            [
                'estado'              => 'RECHAZADA',
                'prioridad'           => 'BAJA',
                'nombres'             => 'BEATRIZ AMPARO',
                'ap_paterno'          => 'LUNA',
                'ap_materno'          => 'BERRIOS',
                'ci'                  => '9900114-PRE',
                'expedicion_ci'       => 'OR',
                'fecha_nac'           => '1950-06-18',
                'genero'              => 'FEMENINO',
                'estado_civil'        => 'SOLTERO/A',
                'departamento_residencia' => 'ORURO',
                'ciudad_municipio'    => 'ORURO',
                'zona'                => 'Huajara',
                'calle'               => 'Calle Potosí Nro. 310',
                'motivo_ingreso'      => 'Solicitud de cuidados básicos. Sin condición clínica grave.',
                'procedencia_ingreso' => 'Solicitud propia',
                'tipo_ingreso'        => 'VOLUNTARIO',
                'permanencia'         => 'TEMPORAL',
                'descripcion_caso'    => 'Adulta mayor autónoma. Solicita ingreso por preferencia personal.',
                'familiar_nombres'    => 'CARLOS',
                'familiar_ap_paterno' => 'LUNA',
                'familiar_ap_materno' => 'MAMANI',
                'familiar_ci'         => '1114455-PRE',
                'familiar_parentesco' => 'SOBRINO',
                'familiar_celular'    => '79445566',
                'familiar_correo'     => null,
                'familiar_direccion'  => 'Calle Potosí Nro. 310, Oruro',
                'motivo_rechazo'      => 'CAPACIDAD MAXIMA OCUPADA',
                'observacion_rechazo' => 'La institución no cuenta con disponibilidad en este momento. Se sugiere reingresar solicitud en 3 meses.',
                'dias_atras'          => 20,
            ],
            [
                'estado'              => 'PREADMISION_ASIGNADA',
                'prioridad'           => 'CRITICA',
                'nombres'             => 'ESTEBAN FELIX',
                'ap_paterno'          => 'TORREZ',
                'ap_materno'          => 'MAMANI',
                'ci'                  => '0011225-PRE',
                'expedicion_ci'       => 'PT',
                'fecha_nac'           => '1930-02-10',
                'genero'              => 'MASCULINO',
                'estado_civil'        => 'VIUDO/A',
                'departamento_residencia' => 'POTOSÍ',
                'ciudad_municipio'    => 'POTOSÍ',
                'zona'                => 'Cantumarca',
                'calle'               => 'Calle Sucre Nro. 15',
                'motivo_ingreso'      => 'ACV reciente. Hemiplejia derecha. Dependencia total. Requiere cuidados intensivos.',
                'procedencia_ingreso' => 'Alta hospitalaria — Hospital Bracamonte Potosí',
                'tipo_ingreso'        => 'DERIVADO',
                'permanencia'         => 'PERMANENTE',
                'descripcion_caso'    => 'Post-ACV con secuelas severas. Alta dependencia. Requiere cuidados de enfermería especializados.',
                'familiar_nombres'    => 'SILVERIA',
                'familiar_ap_paterno' => 'TORREZ',
                'familiar_ap_materno' => 'FLORES',
                'familiar_ci'         => '2225566-PRE',
                'familiar_parentesco' => 'HIJA',
                'familiar_celular'    => '72556677',
                'familiar_correo'     => 'silveria.torrez@email.com',
                'familiar_direccion'  => 'Calle Sucre Nro. 15, Cantumarca, Potosí',
                'dias_atras'          => 1,
            ],
        ];

        foreach ($preadmisiones as $pData) {
            $pre = Preadmision::firstWhere('ci', $pData['ci']);
            if ($pre) continue;

            $fechaSolicitud = now()->subDays($pData['dias_atras'])->toDateString();

            $preData = [
                'estado'              => $pData['estado'],
                'prioridad'           => $pData['prioridad'],
                'fecha_solicitud'     => $fechaSolicitud,
                'fecha_asignacion'    => now()->subDays($pData['dias_atras'] - 1),
                'nombres'             => $pData['nombres'],
                'ap_paterno'          => $pData['ap_paterno'],
                'ap_materno'          => $pData['ap_materno'],
                'ci'                  => $pData['ci'],
                'expedicion_ci'       => $pData['expedicion_ci'],
                'fecha_nac'           => $pData['fecha_nac'],
                'genero'              => $pData['genero'],
                'estado_civil'        => $pData['estado_civil'],
                'departamento_residencia' => $pData['departamento_residencia'],
                'ciudad_municipio'    => $pData['ciudad_municipio'],
                'zona'                => $pData['zona'],
                'calle'               => $pData['calle'],
                'motivo_ingreso'      => $pData['motivo_ingreso'],
                'procedencia_ingreso' => $pData['procedencia_ingreso'],
                'tipo_ingreso'        => $pData['tipo_ingreso'],
                'permanencia'         => $pData['permanencia'],
                'descripcion_caso'    => $pData['descripcion_caso'],
                'familiar_nombres'    => $pData['familiar_nombres'],
                'familiar_ap_paterno' => $pData['familiar_ap_paterno'],
                'familiar_ap_materno' => $pData['familiar_ap_materno'],
                'familiar_ci'         => $pData['familiar_ci'],
                'familiar_parentesco' => $pData['familiar_parentesco'],
                'familiar_celular'    => $pData['familiar_celular'],
                'familiar_correo'     => $pData['familiar_correo'],
                'familiar_direccion'  => $pData['familiar_direccion'],
                'enfermero_asignado'  => $enfermero?->cod_usu,
                'creado_por'          => $admin?->cod_usu,
                'documentos_iniciales_completos'        => in_array($pData['estado'], ['EN_EVALUACION', 'RECHAZADA']),
                'documentos_institucionales_generados'  => $pData['estado'] === 'EN_EVALUACION',
            ];

            if ($pData['estado'] === 'RECHAZADA') {
                $preData['motivo_rechazo']      = $pData['motivo_rechazo'];
                $preData['observacion_rechazo'] = $pData['observacion_rechazo'];
                $preData['fecha_rechazo']       = now()->subDays($pData['dias_atras'] - 5);
                $preData['rechazado_por']        = $admin?->cod_usu;
            }

            $pre = Preadmision::create($preData);

            // Documentos básicos
            DocumentoPreadmision::create([
                'cod_pre'          => $pre->cod_pre,
                'nombre_documento' => 'Cédula de Identidad',
                'tipo_documento'   => 'IDENTIFICACION',
                'es_institucional'  => false,
                'estado'           => 'PENDIENTE',
                'archivo_path'     => null,
                'observaciones'    => 'Pendiente de entrega por familiar.',
            ]);

            DocumentoPreadmision::create([
                'cod_pre'          => $pre->cod_pre,
                'nombre_documento' => 'Certificado Médico',
                'tipo_documento'   => 'MEDICO',
                'es_institucional'  => false,
                'estado'           => in_array($pData['estado'], ['EN_EVALUACION', 'RECHAZADA']) ? 'ENTREGADO' : 'PENDIENTE',
                'archivo_path'     => in_array($pData['estado'], ['EN_EVALUACION', 'RECHAZADA']) ? 'demo/cert_medico_pre.pdf' : null,
                'observaciones'    => null,
            ]);
        }

        $this->command->info('[PreadmisionesSeeder] 5 preadmisiones creadas con distintos estados y documentos.');
    }
}
