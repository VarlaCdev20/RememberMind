<?php

namespace Database\Seeders;

use App\Models\AdultoMayor;
use App\Models\AsignacionAdultoMayor;
use App\Models\Cama;
use App\Models\DocumentoAdultoMayor;
use App\Models\EstadoAdulto;
use App\Models\Familiar;
use App\Models\HistorialEstadoAdulto;
use App\Models\User;
use Illuminate\Database\Seeder;

class ResidentesSeeder extends Seeder
{
    public function run(): void
    {
        $estadoActivo    = EstadoAdulto::where('estado', 'ACTIVO')->first();
        $estadoPendiente = EstadoAdulto::where('estado', 'PENDIENTE_VALORACION_INICIAL')->first()
                       ?? $estadoActivo;
        $admin           = User::whereHas('roles', fn($q) => $q->where('name', 'SUPERADMINISTRADOR'))->first()
                       ?? User::first();

        $residentes = [
            [
                'adulto' => [
                    'nombres'                        => 'CARMEN AURORA',
                    'ap_paterno'                     => 'MAMANI',
                    'ap_materno'                     => 'FLORES',
                    'ci'                             => '1122334-DEMO',
                    'expedicion_ci'                  => 'LP',
                    'fecha_nac'                      => '1940-03-15',
                    'genero'                         => 'FEMENINO',
                    'estado_civil'                   => 'VIUDO/A',
                    'telefono'                       => '22345678',
                    'tipo_ing'                       => 'FAMILIA',
                    'permanencia'                    => 'PERMANENTE',
                    'nivel_educat'                   => 'PRIMARIA',
                    'grupo_sanguineo'                => 'O',
                    'factor_rh'                      => '+',
                    'departamento_residencia'        => 'LA PAZ',
                    'ciudad_municipio'               => 'LA PAZ',
                    'zona'                           => 'San Pedro',
                    'calle'                          => 'Calle Murillo Nro. 245',
                    'motivo_ingreso'                 => 'Dependencia funcional parcial. Vive sola sin red de apoyo.',
                    'procedencia_ingreso'            => 'Derivación familiar',
                    'contacto_emergencia_nombre'     => 'JUAN CARLOS MAMANI',
                    'contacto_emergencia_parentesco' => 'HIJO',
                    'contacto_emergencia_celular'    => '71234567',
                    'observaciones'                  => 'Residente tranquila y colaboradora. Necesita ayuda para movilizarse.',
                    'alergias'                       => 'Penicilina',
                    'seguro_salud'                   => 'CAJA NACIONAL DE SALUD',
                ],
                'familiar' => [
                    'nombres'            => 'JUAN CARLOS',
                    'ap_paterno'         => 'MAMANI',
                    'ap_materno'         => 'VARGAS',
                    'ci'                 => '7788990-DEMO',
                    'parentesco_vinculo' => 'HIJO',
                    'celular'            => '71234567',
                    'correo'             => 'juan.mamani.demo@email.com',
                    'es_responsable'     => true,
                ],
                'cama_codigo' => 'CAM-A01-01',
                'dias_atras'  => 60,
            ],
            [
                'adulto' => [
                    'nombres'                        => 'PEDRO ANTONIO',
                    'ap_paterno'                     => 'QUISPE',
                    'ap_materno'                     => 'VARGAS',
                    'ci'                             => '2233445-DEMO',
                    'expedicion_ci'                  => 'OR',
                    'fecha_nac'                      => '1935-07-22',
                    'genero'                         => 'MASCULINO',
                    'estado_civil'                   => 'CASADO/A',
                    'telefono'                       => '33456789',
                    'tipo_ing'                       => 'VOLUNTARIO',
                    'permanencia'                    => 'PERMANENTE',
                    'nivel_educat'                   => 'SECUNDARIA',
                    'grupo_sanguineo'                => 'A',
                    'factor_rh'                      => '+',
                    'departamento_residencia'        => 'ORURO',
                    'ciudad_municipio'               => 'ORURO',
                    'zona'                           => 'Centro',
                    'calle'                          => 'Calle Bolivia Nro. 120',
                    'motivo_ingreso'                 => 'Deterioro cognitivo leve. La familia no puede brindar los cuidados requeridos.',
                    'procedencia_ingreso'            => 'Solicitud propia con consentimiento familiar',
                    'contacto_emergencia_nombre'     => 'MARIA LAURA QUISPE',
                    'contacto_emergencia_parentesco' => 'CÓNYUGE',
                    'contacto_emergencia_celular'    => '72345678',
                    'observaciones'                  => 'Sociable y activo. Disfruta de las actividades grupales y musicales.',
                    'alergias'                       => 'Ninguna conocida',
                    'seguro_salud'                   => 'SEGURO SOCIAL UNIVERSITARIO',
                ],
                'familiar' => [
                    'nombres'            => 'MARIA LAURA',
                    'ap_paterno'         => 'QUISPE',
                    'ap_materno'         => 'CONDORI',
                    'ci'                 => '8899001-DEMO',
                    'parentesco_vinculo' => 'CÓNYUGE',
                    'celular'            => '72345678',
                    'correo'             => 'maria.quispe.demo@email.com',
                    'es_responsable'     => true,
                ],
                'cama_codigo' => 'CAM-A02-01',
                'dias_atras'  => 45,
            ],
            [
                'adulto' => [
                    'nombres'                        => 'ELSA VICTORIA',
                    'ap_paterno'                     => 'CHOQUE',
                    'ap_materno'                     => 'MIRANDA',
                    'ci'                             => '3344556-DEMO',
                    'expedicion_ci'                  => 'CB',
                    'fecha_nac'                      => '1942-11-10',
                    'genero'                         => 'FEMENINO',
                    'estado_civil'                   => 'SOLTERO/A',
                    'telefono'                       => null,
                    'tipo_ing'                       => 'DERIVADO',
                    'permanencia'                    => 'TEMPORAL',
                    'nivel_educat'                   => 'UNIVERSITARIA',
                    'grupo_sanguineo'                => 'B',
                    'factor_rh'                      => '-',
                    'departamento_residencia'        => 'COCHABAMBA',
                    'ciudad_municipio'               => 'COCHABAMBA',
                    'zona'                           => 'Queru Queru',
                    'calle'                          => 'Av. Blanco Galindo Km 5',
                    'motivo_ingreso'                 => 'Post-operatorio de cadera. Requiere recuperación y rehabilitación de 3 meses.',
                    'procedencia_ingreso'            => 'Derivación médica — Hospital Viedma Cochabamba',
                    'contacto_emergencia_nombre'     => 'ROBERTO CHOQUE MIRANDA',
                    'contacto_emergencia_parentesco' => 'HERMANO',
                    'contacto_emergencia_celular'    => '73456789',
                    'observaciones'                  => 'En proceso de recuperación post-operatoria. Muy colaboradora con el personal.',
                    'alergias'                       => 'Aspirina — AAS',
                    'seguro_salud'                   => 'NINGUNO',
                ],
                'familiar' => [
                    'nombres'            => 'ROBERTO',
                    'ap_paterno'         => 'CHOQUE',
                    'ap_materno'         => 'MIRANDA',
                    'ci'                 => '9900112-DEMO',
                    'parentesco_vinculo' => 'HERMANO',
                    'celular'            => '73456789',
                    'correo'             => 'roberto.choque.demo@email.com',
                    'es_responsable'     => true,
                ],
                'cama_codigo' => 'CAM-B01-01',
                'dias_atras'  => 30,
            ],
            [
                'adulto' => [
                    'nombres'                        => 'RAFAEL HUGO',
                    'ap_paterno'                     => 'TORRICO',
                    'ap_materno'                     => 'SALINAS',
                    'ci'                             => '4455667-DEMO',
                    'expedicion_ci'                  => 'SC',
                    'fecha_nac'                      => '1938-05-03',
                    'genero'                         => 'MASCULINO',
                    'estado_civil'                   => 'DIVORCIADO/A',
                    'telefono'                       => '44567890',
                    'tipo_ing'                       => 'FAMILIA',
                    'permanencia'                    => 'PERMANENTE',
                    'nivel_educat'                   => 'PRIMARIA',
                    'grupo_sanguineo'                => 'AB',
                    'factor_rh'                      => '+',
                    'departamento_residencia'        => 'SANTA CRUZ',
                    'ciudad_municipio'               => 'SANTA CRUZ',
                    'zona'                           => 'Plan 3000',
                    'calle'                          => 'Calle 4 Nro. 85',
                    'motivo_ingreso'                 => 'Diabetes mellitus tipo 2 descontrolada y dependencia funcional moderada.',
                    'procedencia_ingreso'            => 'Derivación familiar',
                    'contacto_emergencia_nombre'     => 'ANA LUCIA TORRICO',
                    'contacto_emergencia_parentesco' => 'HIJA',
                    'contacto_emergencia_celular'    => '74567890',
                    'observaciones'                  => 'Requiere control glucémico estricto diario. Dieta especial para diabéticos.',
                    'alergias'                       => 'Sulfonamidas',
                    'seguro_salud'                   => 'CAJA NACIONAL DE SALUD',
                ],
                'familiar' => [
                    'nombres'            => 'ANA LUCIA',
                    'ap_paterno'         => 'TORRICO',
                    'ap_materno'         => 'FLORES',
                    'ci'                 => '0011223-DEMO',
                    'parentesco_vinculo' => 'HIJA',
                    'celular'            => '74567890',
                    'correo'             => 'ana.torrico.demo@email.com',
                    'es_responsable'     => true,
                ],
                'cama_codigo' => 'CAM-B02-01',
                'dias_atras'  => 20,
            ],
            [
                'adulto' => [
                    'nombres'                        => 'JOSEFINA AURORA',
                    'ap_paterno'                     => 'CONDORI',
                    'ap_materno'                     => 'ARCE',
                    'ci'                             => '5566778-DEMO',
                    'expedicion_ci'                  => 'PT',
                    'fecha_nac'                      => '1945-01-28',
                    'genero'                         => 'FEMENINO',
                    'estado_civil'                   => 'VIUDO/A',
                    'telefono'                       => null,
                    'tipo_ing'                       => 'VOLUNTARIO',
                    'permanencia'                    => 'PERMANENTE',
                    'nivel_educat'                   => 'SIN INSTRUCCION',
                    'grupo_sanguineo'                => 'O',
                    'factor_rh'                      => '+',
                    'departamento_residencia'        => 'POTOSÍ',
                    'ciudad_municipio'               => 'POTOSÍ',
                    'zona'                           => 'Ferroviaria',
                    'calle'                          => 'Calle Junín Nro. 10',
                    'motivo_ingreso'                 => 'Sin familiares a cargo. Situación de vulnerabilidad extrema. Necesidades básicas insatisfechas.',
                    'procedencia_ingreso'            => 'Derivación asistencia social municipal Potosí',
                    'contacto_emergencia_nombre'     => 'UNIDAD ASISTENCIA SOCIAL — ALCALDÍA POTOSÍ',
                    'contacto_emergencia_parentesco' => 'TUTOR INSTITUCIONAL',
                    'contacto_emergencia_celular'    => '622234567',
                    'observaciones'                  => 'Requiere atención integral. Alta vulnerabilidad social. Sin red de apoyo familiar.',
                    'alergias'                       => 'Ninguna conocida',
                    'seguro_salud'                   => 'SEGURO SOLIDARIO DE SALUD',
                ],
                'familiar' => [
                    'nombres'            => 'TRABAJADORA SOCIAL',
                    'ap_paterno'         => 'ALCALDIA',
                    'ap_materno'         => 'POTOSI',
                    'ci'                 => '1100229-DEMO',
                    'parentesco_vinculo' => 'TUTOR INSTITUCIONAL',
                    'celular'            => '622234567',
                    'correo'             => null,
                    'es_responsable'     => true,
                ],
                'cama_codigo' => 'CAM-C01-01',
                'dias_atras'  => 15,
            ],
        ];

        foreach ($residentes as $datos) {
            // --- AdultoMayor ---
            $adulto = AdultoMayor::firstWhere('ci', $datos['adulto']['ci']);
            if (! $adulto) {
                $adulto = AdultoMayor::create(array_merge($datos['adulto'], [
                    'fecha_ing'                     => now()->subDays($datos['dias_atras'])->toDateString(),
                    'hora_ing'                      => '09:00:00',
                    'tiene_celular'                 => (bool) $datos['adulto']['telefono'],
                    'sabe_usar_whatsapp'            => false,
                    'autorizado_informacion_medica' => true,
                    'consentimiento_datos'          => true,
                    'responsable_principal'         => $datos['familiar']['nombres'] . ' ' . $datos['familiar']['ap_paterno'],
                    'contacto_emergencia_direccion' => $datos['adulto']['calle'],
                    'cod_est_adul'                  => $estadoActivo->cod_est_adul,
                ]));
            }

            // --- Familiar ---
            $familiar = Familiar::firstWhere('ci', $datos['familiar']['ci']);
            if (! $familiar) {
                $familiar = Familiar::create([
                    'nombres'            => $datos['familiar']['nombres'],
                    'ap_paterno'         => $datos['familiar']['ap_paterno'],
                    'ap_materno'         => $datos['familiar']['ap_materno'],
                    'ci'                 => $datos['familiar']['ci'],
                    'parentesco_vinculo' => $datos['familiar']['parentesco_vinculo'],
                    'celular'            => $datos['familiar']['celular'],
                    'correo'             => $datos['familiar']['correo'],
                    'es_responsable'     => $datos['familiar']['es_responsable'],
                    'estado'             => 'ACTIVO',
                ]);
            }

            if (! $adulto->familiares()->where('familiares.cod_fam', $familiar->cod_fam)->exists()) {
                $adulto->familiares()->attach($familiar->cod_fam, [
                    'parentesco_vinculo' => $datos['familiar']['parentesco_vinculo'],
                    'es_responsable'     => true,
                    'estado'             => 'ACTIVO',
                    'observaciones'      => 'Vínculo registrado por seeder de datos demo.',
                ]);
            }

            // --- Historial de estado ---
            if (HistorialEstadoAdulto::where('cod_am', $adulto->cod_am)->doesntExist()) {
                HistorialEstadoAdulto::create([
                    'cod_am'          => $adulto->cod_am,
                    'estado_anterior' => null,
                    'estado_nuevo'    => $estadoPendiente->cod_est_adul,
                    'fecha_cambio'    => now()->subDays($datos['dias_atras']),
                    'motivo'          => 'Ingreso institucional. Pendiente valoración inicial.',
                    'cambiado_por'    => $admin?->cod_usu,
                    'observacion'     => 'Registro inicial por seeder de datos demo.',
                ]);

                HistorialEstadoAdulto::create([
                    'cod_am'          => $adulto->cod_am,
                    'estado_anterior' => $estadoPendiente->cod_est_adul,
                    'estado_nuevo'    => $estadoActivo->cod_est_adul,
                    'fecha_cambio'    => now()->subDays($datos['dias_atras'] - 3),
                    'motivo'          => 'Valoración inicial completada. Alta como residente activo.',
                    'cambiado_por'    => $admin?->cod_usu,
                    'observacion'     => 'Aprobado por el equipo clínico.',
                ]);
            }

            // --- DocumentosAdultoMayor ---
            DocumentoAdultoMayor::firstOrCreate(
                ['cod_am' => $adulto->cod_am, 'nombre' => 'Cédula de Identidad'],
                [
                    'tipo_documento' => 'IDENTIFICACION',
                    'ruta_archivo'   => 'demo/ci_' . strtolower($adulto->ap_paterno) . '.pdf',
                    'fecha_subida'   => now()->subDays($datos['dias_atras'])->toDateString(),
                    'estado'         => 'VIGENTE',
                    'modulo_ref'     => 'ADMISION',
                    'observaciones'  => 'Documento cargado al ingreso.',
                ]
            );

            DocumentoAdultoMayor::firstOrCreate(
                ['cod_am' => $adulto->cod_am, 'nombre' => 'Certificado Médico de Ingreso'],
                [
                    'tipo_documento' => 'MEDICO',
                    'ruta_archivo'   => 'demo/cert_ing_' . strtolower($adulto->ap_paterno) . '.pdf',
                    'fecha_subida'   => now()->subDays($datos['dias_atras'])->toDateString(),
                    'estado'         => 'VIGENTE',
                    'modulo_ref'     => 'ADMISION',
                    'observaciones'  => 'Certificado médico presentado al ingreso.',
                ]
            );

            // --- AsignacionAdultoMayor ---
            $cama = Cama::where('codigo', $datos['cama_codigo'])->first();
            if ($cama && AsignacionAdultoMayor::where('cod_am', $adulto->cod_am)->where('estado', 'ACTIVO')->doesntExist()) {
                AsignacionAdultoMayor::create([
                    'cod_am'           => $adulto->cod_am,
                    'cod_habitacion'   => $cama->cod_habitacion,
                    'cod_cama'         => $cama->cod_cama,
                    'fecha_asignacion' => now()->subDays($datos['dias_atras'] - 1)->toDateString(),
                    'hora_asignacion'  => '09:30:00',
                    'estado'           => 'ACTIVO',
                    'observaciones'    => 'Asignación inicial registrada por seeder demo.',
                    'registrado_por'   => $admin?->cod_usu,
                ]);
            }
        }

        $this->command->info('[ResidentesSeeder] 5 residentes con familiares, historial, documentos y asignaciones de habitación creados.');
    }
}
