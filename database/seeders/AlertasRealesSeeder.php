<?php

namespace Database\Seeders;

use App\Models\AccionAlerta;
use App\Models\AdministracionMedicacion;
use App\Models\AdultoMayor;
use App\Models\AlertaAdulto;
use App\Models\AsignacionAdultoMayor;
use App\Models\Cama;
use App\Models\FichaMedicaAdulto;
use App\Models\Habitacion;
use App\Models\MedicacionAdulto;
use App\Models\PlanCuidado;
use App\Models\SeguimientoDiario;
use App\Models\SignosVitalesAdulto;
use App\Models\TareaPlanCuidado;
use App\Models\TurnoEnfermeria;
use App\Models\User;
use App\Models\ValoracionFuncionalAdulto;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AlertasRealesSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Normalizar y eliminar todas las alertas simuladas o sinteticas
        DB::table('alertas_adulto')->where('nivel', 'CRITICA')->update(['nivel' => 'CRITICO']);
        DB::table('alertas_adulto')->where('nivel', 'MODERADA')->update(['nivel' => 'MEDIO']);
        DB::table('alertas_adulto')->where('nivel', 'LEVE')->update(['nivel' => 'BAJO']);
        DB::table('alertas_adulto')->where('estado', 'NUEVA')->update(['estado' => 'ABIERTA']);

        $simuladas = AlertaAdulto::where('motivo', 'like', '[%')
            ->orWhere('motivo', 'like', '%orientativa%')
            ->orWhere('motivo', 'like', '%demostracion%')
            ->orWhere('motivo', 'like', '%demostración%')
            ->orWhere('motivo', 'like', '%demo%')
            ->pluck('cod_alerta');

        if ($simuladas->isNotEmpty()) {
            AccionAlerta::whereIn('cod_alerta', $simuladas)->delete();
            AlertaAdulto::whereIn('cod_alerta', $simuladas)->delete();
        }

        // 2. Asignar habitacion y cama a todos los 15 residentes
        $adultos = AdultoMayor::orderBy('cod_am')->get();
        $camasLibres = Cama::where('estado', 'DISPONIBLE')->orderBy('cod_cama')->get();
        $cIdx = 0;
        foreach ($adultos as $ad) {
            if (!$ad->cod_habitacion || !$ad->cod_cama) {
                if ($cIdx < $camasLibres->count()) {
                    $cama = $camasLibres[$cIdx++];
                    $cama->update(['estado' => 'OCUPADA']);
                    AsignacionAdultoMayor::firstOrCreate([
                        'cod_am' => $ad->cod_am,
                        'cod_cama' => $cama->cod_cama,
                    ], [
                        'cod_habitacion' => $cama->cod_habitacion,
                        'fecha_asignacion' => Carbon::now()->subMonths(3)->toDateString(),
                        'hora_asignacion' => '08:00:00',
                        'estado' => 'ACTIVO',
                        'observaciones' => 'Asignacion permanente.',
                        'registrado_por' => 'USU_0001',
                    ]);
                    $ad->update([
                        'cod_habitacion' => $cama->cod_habitacion,
                        'cod_cama' => $cama->cod_cama,
                    ]);
                }
            }
        }

        $enfermeros = User::whereIn('cod_usu', ['USU_0002', 'USU_0003', 'USU_9988', 'USU_9999', 'USU_0009'])
            ->pluck('cod_usu')->toArray();
        if (empty($enfermeros)) $enfermeros = ['USU_0001'];
        $enfMendoza = User::where('correo', 'enfermera.mendoza@casaamandita.com')->value('cod_usu') ?? $enfermeros[0];
        $enfChoque  = User::where('correo', 'enfermera.choque@casaamandita.com')->value('cod_usu') ?? $enfermeros[0];
        $enfCarla   = User::where('correo', 'carla.patricia@jardinrecuerdos.bo')->value('cod_usu') ?? $enfermeros[0];
        $medSalinas = User::where('correo', 'medico.salinas@casaamandita.com')->value('cod_usu') ?? 'USU_0001';

        $turnos = TurnoEnfermeria::pluck('cod_turno')->toArray();
        $tManana = $turnos[0] ?? null;

        // 3. Fichas medicas, valoraciones y medicacion para todos los residentes
        $datasetResidentes = [
            ['cod' => 'AM_001', 'hta' => true, 'dm' => false, 'cardio' => false, 'acv' => false, 'park' => false, 'alz' => false, 'alergia' => 'Penicilina', 'dieta' => 'Hiposodica estricta', 'obs' => 'Hipertension arterial estadio II. Osteoartrosis bilateral de rodillas.', 'barthel' => 75, 'dep' => 'MODERADA', 'caida' => 'MEDIO', 'baston' => true, 'and' => false, 'silla' => false, 'plan' => 'Plan de Control Hemodinamico y Manejo del Dolor Articular', 'meds' => [['Enalapril 10mg', '10mg', 'CADA 12 HORAS', 'ORAL', 'Control PA'], ['Paracetamol 1g', '1g', 'CADA 8 HORAS', 'ORAL', 'Analgesia gonartrosis'], ['Zolpidem 5mg', '5mg', 'CADA 24 HORAS', 'ORAL', 'Inductor descanso nocturno']]],
            ['cod' => 'AM_002', 'hta' => true, 'dm' => false, 'cardio' => true, 'acv' => true, 'park' => false, 'alz' => false, 'alergia' => 'Sulfas y Ketorolaco', 'dieta' => 'Blanda con espesante', 'obs' => 'Secuela de ACV con hemiparesia derecha. Fibrilacion auricular.', 'barthel' => 55, 'dep' => 'SEVERA', 'caida' => 'ALTO', 'baston' => false, 'and' => true, 'silla' => false, 'plan' => 'Plan de Neurorehabilitacion y Prevencion Tromboembolica', 'meds' => [['Losartan 50mg', '50mg', 'CADA 12 HORAS', 'ORAL', 'Proteccion vascular'], ['Apixaban 2.5mg', '2.5mg', 'CADA 12 HORAS', 'ORAL', 'Anticoagulante FA'], ['Atorvastatina 20mg', '20mg', 'CADA 24 HORAS', 'ORAL', 'Cardioproteccion']]],
            ['cod' => 'AM_003', 'hta' => false, 'dm' => true, 'cardio' => false, 'acv' => false, 'park' => false, 'alz' => true, 'alergia' => 'Ninguna conocida', 'dieta' => 'Diabetica fraccionada', 'obs' => 'Demencia tipo Alzheimer moderada. Diabetes Mellitus tipo 2.', 'barthel' => 45, 'dep' => 'SEVERA', 'caida' => 'ALTO', 'baston' => false, 'and' => false, 'silla' => true, 'plan' => 'Plan de Estimulacion Cognitiva y Control Glucemico', 'meds' => [['Metformina 850mg', '850mg', 'CADA 12 HORAS', 'ORAL', 'Antidiabetico'], ['Donepezilo 5mg', '5mg', 'CADA 24 HORAS', 'ORAL', 'Cognitivo'], ['Quetiapina 25mg', '25mg', 'CADA 24 HORAS', 'ORAL', 'Control conductual vespertino']]],
            ['cod' => 'AM_004', 'hta' => true, 'dm' => false, 'cardio' => false, 'acv' => false, 'park' => false, 'alz' => false, 'alergia' => 'Penicilina', 'dieta' => 'Hiposodica', 'obs' => 'Hipertension y depresion reactiva compensada.', 'barthel' => 80, 'dep' => 'LEVE', 'caida' => 'BAJO', 'baston' => true, 'and' => false, 'silla' => false, 'plan' => 'Plan de Apoyo Emocional y Control Cardiovascular', 'meds' => [['Enalapril 10mg', '10mg', 'CADA 12 HORAS', 'ORAL', 'Antihipertensivo'], ['Sertralina 50mg', '50mg', 'CADA 24 HORAS', 'ORAL', 'Antidepresivo matutino']]],
            ['cod' => 'AM_005', 'hta' => true, 'dm' => true, 'cardio' => false, 'acv' => false, 'park' => false, 'alz' => false, 'alergia' => 'Ninguna conocida', 'dieta' => 'Diabetica e hiposodica', 'obs' => 'Diabetes tipo 2 e HTA primaria.', 'barthel' => 85, 'dep' => 'LEVE', 'caida' => 'BAJO', 'baston' => false, 'and' => false, 'silla' => false, 'plan' => 'Plan de Mantenimiento Metabolico y Autonomia Funcional', 'meds' => [['Metformina 850mg', '850mg', 'CADA 12 HORAS', 'ORAL', 'Control glicemico'], ['Amlodipino 5mg', '5mg', 'CADA 24 HORAS', 'ORAL', 'Control tensional']]],
            ['cod' => 'AM_006', 'hta' => true, 'dm' => false, 'cardio' => false, 'acv' => false, 'park' => false, 'alz' => false, 'alergia' => 'AINEs', 'dieta' => 'Rica en calcio', 'obs' => 'Osteoporosis severa y aplastamiento vertebral antiguo.', 'barthel' => 70, 'dep' => 'MODERADA', 'caida' => 'MEDIO', 'baston' => true, 'and' => false, 'silla' => false, 'plan' => 'Plan de Proteccion Osea y Prevencion de Fracturas', 'meds' => [['Calcio 500mg + Vitamina D3', '1 comp', 'CADA 12 HORAS', 'ORAL', 'Salud osea'], ['Losartan 50mg', '50mg', 'CADA 24 HORAS', 'ORAL', 'Antihipertensivo']]],
            ['cod' => 'AM_007', 'hta' => false, 'dm' => false, 'cardio' => false, 'acv' => false, 'park' => true, 'alz' => false, 'alergia' => 'Ninguna conocida', 'dieta' => 'Facil masticacion', 'obs' => 'Parkinson estadio II. Rigidez y temblor de reposo.', 'barthel' => 60, 'dep' => 'MODERADA', 'caida' => 'ALTO', 'baston' => false, 'and' => true, 'silla' => false, 'plan' => 'Plan de Terapia Motriz y Manejo de Bradicinesia', 'meds' => [['Levodopa / Carbidopa 250/25', '1/2 comp', 'CADA 8 HORAS', 'ORAL', 'Antiparkinsoniano'], ['Pramipexol 0.375mg', '0.375mg', 'CADA 24 HORAS', 'ORAL', 'Agonista dopaminergico']]],
            ['cod' => 'AM_008', 'hta' => true, 'dm' => false, 'cardio' => true, 'acv' => false, 'park' => false, 'alz' => false, 'alergia' => 'Ciprofloxacino', 'dieta' => 'Blanda baja en sal', 'obs' => 'Insuficiencia venosa cronica y ulcera maleolar derecha.', 'barthel' => 65, 'dep' => 'MODERADA', 'caida' => 'MEDIO', 'baston' => false, 'and' => false, 'silla' => true, 'plan' => 'Plan de Curacion de Heridas y Retorno Venoso', 'meds' => [['Diosmina / Hesperidina 500mg', '500mg', 'CADA 12 HORAS', 'ORAL', 'Flebotonico'], ['Enalapril 20mg', '20mg', 'CADA 24 HORAS', 'ORAL', 'Antihipertensivo']]],
            ['cod' => 'AM_009', 'hta' => false, 'dm' => false, 'cardio' => false, 'acv' => false, 'park' => false, 'alz' => false, 'alergia' => 'Ninguna conocida', 'dieta' => 'Normocalorica con suplemento', 'obs' => 'Trastorno depresivo y sindrome de fragilidad del anciano.', 'barthel' => 85, 'dep' => 'LEVE', 'caida' => 'BAJO', 'baston' => false, 'and' => false, 'silla' => false, 'plan' => 'Plan de Recuperacion Nutricional y Acompanamiento', 'meds' => [['Escitalopram 10mg', '10mg', 'CADA 24 HORAS', 'ORAL', 'Antidepresivo'], ['Ensure Plus 220ml', '1 envase', 'CADA 24 HORAS', 'ORAL', 'Suplemento nutricional']]],
            ['cod' => 'AM_010', 'hta' => true, 'dm' => false, 'cardio' => true, 'acv' => false, 'park' => false, 'alz' => false, 'alergia' => 'Aspirina', 'dieta' => 'Cardiosaludable', 'obs' => 'Cardiopatia isquemica cronica. Portador de marcapasos.', 'barthel' => 75, 'dep' => 'MODERADA', 'caida' => 'MEDIO', 'baston' => true, 'and' => false, 'silla' => false, 'plan' => 'Plan de Vigilancia Cardiaca y Acondicionamiento Fisico', 'meds' => [['Bisoprolol 2.5mg', '2.5mg', 'CADA 24 HORAS', 'ORAL', 'Betabloqueante'], ['Clopidogrel 75mg', '75mg', 'CADA 24 HORAS', 'ORAL', 'Antiagregante'], ['Atorvastatina 40mg', '40mg', 'CADA 24 HORAS', 'ORAL', 'Hipolipemiante']]],
            ['cod' => 'AM_011', 'hta' => true, 'dm' => false, 'cardio' => false, 'acv' => false, 'park' => false, 'alz' => false, 'alergia' => 'Ninguna conocida', 'dieta' => 'Rica en liquidos', 'obs' => 'Hiperplasia prostatica benigna. Portador de sonda Foley.', 'barthel' => 70, 'dep' => 'MODERADA', 'caida' => 'MEDIO', 'baston' => true, 'and' => false, 'silla' => false, 'plan' => 'Plan de Manejo y Cuidados de Cateter Urinario', 'meds' => [['Tamsulosina 0.4mg', '0.4mg', 'CADA 24 HORAS', 'ORAL', 'Alivio prostatico'], ['Finasterida 5mg', '5mg', 'CADA 24 HORAS', 'ORAL', 'Antiandrogenico'], ['Losartan 50mg', '50mg', 'CADA 24 HORAS', 'ORAL', 'Antihipertensivo']]],
            ['cod' => 'AM_012', 'hta' => true, 'dm' => true, 'cardio' => false, 'acv' => false, 'park' => false, 'alz' => false, 'alergia' => 'Metamizol', 'dieta' => 'Diabetica e hiposodica', 'obs' => 'Diabetes insulino-requiriente e HTA. Gonartrosis derecha.', 'barthel' => 70, 'dep' => 'MODERADA', 'caida' => 'MEDIO', 'baston' => true, 'and' => false, 'silla' => false, 'plan' => 'Plan de Terapia Insulinica y Cuidado Articular', 'meds' => [['Insulina NPH Humana', '14 UI', 'CADA 24 HORAS', 'SUBCUTANEA', 'Control glucemico matutino'], ['Enalapril 10mg', '10mg', 'CADA 12 HORAS', 'ORAL', 'Antihipertensivo'], ['Paracetamol 500mg', '500mg', 'CADA 8 HORAS', 'ORAL', 'Analgesico']]],
            ['cod' => 'AM_013', 'hta' => true, 'dm' => false, 'cardio' => true, 'acv' => false, 'park' => false, 'alz' => true, 'alergia' => 'Ninguna conocida', 'dieta' => 'Blanda triturada', 'obs' => 'Demencia mixta moderada-severa con sundowning.', 'barthel' => 50, 'dep' => 'SEVERA', 'caida' => 'ALTO', 'baston' => false, 'and' => true, 'silla' => false, 'plan' => 'Plan de Seguridad Integral y Soporte Neuroconductual', 'meds' => [['Memantina 10mg', '10mg', 'CADA 12 HORAS', 'ORAL', 'Neuroprotector'], ['Quetiapina 25mg', '25mg', 'CADA 24 HORAS', 'ORAL', 'Antipsicotico nocturno'], ['Amlodipino 5mg', '5mg', 'CADA 24 HORAS', 'ORAL', 'Antihipertensivo']]],
            ['cod' => 'AM_014', 'hta' => true, 'dm' => false, 'cardio' => false, 'acv' => true, 'park' => false, 'alz' => false, 'alergia' => 'Sulfametoxazol', 'dieta' => 'Blanda asistida', 'obs' => 'Secuela ACV frontal con disartria y disfagia leve a liquidos.', 'barthel' => 60, 'dep' => 'MODERADA', 'caida' => 'ALTO', 'baston' => false, 'and' => false, 'silla' => true, 'plan' => 'Plan de Terapia Fonoaudiologica y Neurorehabilitacion', 'meds' => [['Atorvastatina 20mg', '20mg', 'CADA 24 HORAS', 'ORAL', 'Prevencion secundaria'], ['Enalapril 10mg', '10mg', 'CADA 12 HORAS', 'ORAL', 'Control PA'], ['Aspirina 100mg', '100mg', 'CADA 24 HORAS', 'ORAL', 'Antiagregante']]],
            ['cod' => 'AM_015', 'hta' => false, 'dm' => false, 'cardio' => false, 'acv' => false, 'park' => false, 'alz' => false, 'alergia' => 'Diclofenaco', 'dieta' => 'Normal con fibra', 'obs' => 'Espondiloartrosis lumbar y lumbociatica cronica.', 'barthel' => 80, 'dep' => 'LEVE', 'caida' => 'MEDIO', 'baston' => true, 'and' => false, 'silla' => false, 'plan' => 'Plan de Fisioterapia Postural y Control Antalgico', 'meds' => [['Tramadol / Paracetamol 37.5/325', '1 comp', 'CADA 12 HORAS', 'ORAL', 'Analgesia moderada'], ['Complejo B Forte', '1 comp', 'CADA 24 HORAS', 'ORAL', 'Neuromodulador']]],
        ];

        foreach ($datasetResidentes as $idx => $r) {
            $cod = $r['cod'];
            $enf = $enfermeros[$idx % count($enfermeros)];

            FichaMedicaAdulto::updateOrCreate(['cod_am' => $cod], [
                'hipertension' => $r['hta'], 'diabetes' => $r['dm'], 'problemas_cardiacos' => $r['cardio'],
                'acv' => $r['acv'], 'parkinson' => $r['park'], 'epilepsia' => false, 'alzheimer_diagnosticado' => $r['alz'],
                'depresion' => false, 'ansiedad' => false, 'problemas_sueno' => false, 'problemas_visuales' => true,
                'problemas_auditivos' => false, 'dolor_cronico' => true, 'alergias' => $r['alergia'],
                'restricciones_alimentarias' => $r['dieta'], 'hospitalizaciones' => 'Controles geriatricos regulares.',
                'observacion_medica' => $r['obs'], 'registrado_por' => $medSalinas, 'estado' => 'ACTIVA'
            ]);

            ValoracionFuncionalAdulto::updateOrCreate(['cod_am' => $cod], [
                'fecha_valoracion' => Carbon::now()->subDays(10)->toDateString(),
                'come_solo' => $r['barthel'] >= 60, 'se_bana_solo' => $r['barthel'] >= 80,
                'se_viste_solo' => $r['barthel'] >= 75, 'va_bano_solo' => $r['barthel'] >= 70,
                'camina_solo' => $r['barthel'] >= 75, 'usa_baston' => $r['baston'],
                'usa_andador' => $r['and'], 'usa_silla_ruedas' => $r['silla'],
                'baja_vision' => true, 'baja_audicion' => false, 'dificultad_hablar' => $r['acv'] || $r['alz'],
                'molestia_luz' => false, 'molestia_ruido' => false, 'se_asusta_facil' => false,
                'necesita_supervision' => $r['dep'] !== 'LEVE', 'nivel_dependencia' => $r['dep'],
                'riesgo_caida' => $r['caida'], 'indice_barthel' => $r['barthel'],
                'observacion' => 'Valoracion funcional geriatrica multidisciplinaria.',
                'registrado_por' => $enfMendoza, 'estado' => 'VIGENTE'
            ]);

            // Signos vitales ultimos 3 dias
            foreach ([2, 1, 0] as $dOff) {
                SignosVitalesAdulto::firstOrCreate([
                    'cod_am' => $cod,
                    'fecha' => Carbon::now()->subDays($dOff)->toDateString(),
                    'hora' => '08:00:00',
                ], [
                    'presion_arterial' => (120 + ($idx % 8)) . '/' . (75 + ($idx % 5)),
                    'presion_sistolica' => 120 + ($idx % 8),
                    'presion_diastolica' => 75 + ($idx % 5),
                    'frecuencia_cardiaca' => 72 + ($idx % 6),
                    'frecuencia_respiratoria' => 17,
                    'temperatura' => 36.5,
                    'saturacion' => 96,
                    'glucosa' => 104,
                    'peso' => 62.0 + ($idx % 7),
                    'talla' => 1.60,
                    'imc' => 24.2,
                    'dolor' => 0,
                    'observacion' => 'Control hemodinamico regular.',
                    'registrado_por' => $enf,
                    'estado' => 'ACTIVO'
                ]);
            }

            // Medicacion y administraciones
            foreach ($r['meds'] as $m) {
                $med = MedicacionAdulto::firstOrCreate([
                    'cod_am' => $cod,
                    'nombre_medicamento' => $m[0],
                ], [
                    'dosis' => $m[1],
                    'frecuencia' => $m[2],
                    'via_administracion' => $m[3],
                    'hora_programada' => '08:00:00',
                    'fecha_inicio' => Carbon::now()->subMonths(2)->toDateString(),
                    'medico_indica' => 'Dr. Carlos Salinas (Geriatra)',
                    'estado' => 'ACTIVA',
                    'observacion' => $m[4],
                    'registrado_por' => $medSalinas
                ]);

                foreach ([1, 0] as $dOff) {
                    AdministracionMedicacion::firstOrCreate([
                        'cod_med_adulto' => $med->cod_med_adulto,
                        'cod_am' => $cod,
                        'fecha' => Carbon::now()->subDays($dOff)->toDateString(),
                        'hora_programada' => '08:00:00',
                    ], [
                        'hora_real' => '08:05:00',
                        'administrado' => true,
                        'efecto_observado' => 'Buena tolerancia sin eventos adversos.',
                        'observacion' => 'Administracion completada.',
                        'registrado_por' => $enfCarla
                    ]);
                }
            }

            // Plan y Tareas
            $plan = PlanCuidado::firstOrCreate(['cod_am' => $cod, 'estado' => 'ACTIVO'], [
                'tipo_plan' => 'CONTINUO', 'version' => 1, 'nivel_cuidado' => 'INTERMEDIO',
                'resumen' => $r['plan'], 'fecha_inicio' => Carbon::now()->subMonths(1)->toDateString(),
                'creado_por' => $enfMendoza, 'validado_por' => $medSalinas
            ]);

            TareaPlanCuidado::firstOrCreate([
                'cod_plan' => $plan->cod_plan,
                'cod_am' => $cod,
                'titulo' => 'Control de Signos Vitales y Cuidados Matutinos',
                'fecha_programada' => Carbon::today()->toDateString(),
                'hora_programada' => '08:00:00'
            ], [
                'cod_turno' => $tManana, 'responsable_id' => $enfChoque, 'area' => 'ENFERMERIA',
                'descripcion' => 'Monitorizacion hemodinamica matutina y asistencia en higiene.',
                'frecuencia' => 'DIARIA', 'prioridad' => 'ALTA', 'estado' => 'REALIZADA',
                'fecha_realizada' => Carbon::today()->toDateString(),
                'resultado' => 'Procedimiento completado sin incidencias.',
                'registrado_por' => $enfChoque
            ]);

            // Seguimiento diario
            SeguimientoDiario::firstOrCreate([
                'cod_am' => $cod,
                'fecha' => Carbon::today()->toDateString(),
                'hora_inicio' => '07:00:00'
            ], [
                'cod_turno' => $tManana, 'cod_plan' => $plan->cod_plan, 'registrado_por' => $enfCarla,
                'hora_fin' => '13:00:00', 'estado_general' => 'BUENO', 'alimentacion' => 'COMPLETA',
                'porcentaje_alimentacion' => 100, 'hidratacion' => 'ADECUADA',
                'movilidad' => $r['silla'] ? 'SILLA DE RUEDAS' : ($r['and'] ? 'CON ANDADOR' : 'AUTÓNOMO'),
                'intento_caminar_solo' => !$r['silla'], 'higiene' => 'COMPLETA', 'sueno' => 'BUENO',
                'orientacion' => $r['alz'] ? 'PARCIAL' : 'COMPLETA', 'repite_preguntas' => $r['alz'],
                'confusion_observable' => $r['alz'], 'conducta' => 'TRANQUILA', 'participacion' => 'ACTIVA',
                'incidente' => false, 'requiere_medico' => false,
                'observacion' => 'Turno matutino estable. Residente tranquilo y colaborador.'
            ]);
        }

        // 4. Poblar 32 Alertas Clinicas Reales con Acciones
        $alertasReales = [
            ['am' => 'AM_001', 'origen' => 'SIGNOS', 'tipo' => 'CRISIS_HIPERTENSIVA', 'nivel' => 'CRITICO', 'motivo' => 'Cifras tensionales elevadas de 185/110 mmHg con cefalea occipital pulsatil y acufenos. Residente orientada pero visiblemente inquieta.', 'estado' => 'EN_ATENCION', 'min' => 120, 'cierre' => null, 'accion' => 'Reposo en cama a 45 grados, control de PA cada 15 min y llamada a medico tratante.', 'hist' => [['Toma de presion arterial de verificacion: 185/110 mmHg.', 110], ['Administracion sublingual de Captopril 25 mg bajo indicacion medica.', 80], ['Control a los 30 min: 160/95 mmHg, cefalea disminuye a EVA 3/10.', 40]]],
            ['am' => 'AM_001', 'origen' => 'FICHA', 'tipo' => 'DOLOR_CRONICO', 'nivel' => 'MEDIO', 'motivo' => 'Reagudizacion de gonartrosis bilateral con dolor moderado a intenso (EVA 6/10) al ponerse de pie.', 'estado' => 'CERRADA', 'min' => 1440, 'cierre' => 600, 'accion' => 'Termoterapia local con compresas tibias y analgesico pautado.', 'hist' => [['Aplicacion de compresas tibias y Paracetamol 1g VO con agua tibia.', 1100]]],
            ['am' => 'AM_002', 'origen' => 'SIGNOS', 'tipo' => 'PICO_FEBRIL', 'nivel' => 'ALTO', 'motivo' => 'Temperatura axilar de 38.8 °C con escalofrios y taquicardia refleja (FC 104 lpm). Posible foco respiratorio.', 'estado' => 'ABIERTA', 'min' => 90, 'cierre' => null, 'accion' => null, 'hist' => []],
            ['am' => 'AM_002', 'origen' => 'MEDICACION', 'tipo' => 'VIGILANCIA_ANTICOAGULANTE', 'nivel' => 'MEDIO', 'motivo' => 'Aparicion de pequeno hematoma espontaneo en antebrazo derecho; requiere control de dosificacion de Apixaban.', 'estado' => 'EN_ATENCION', 'min' => 180, 'cierre' => null, 'accion' => 'Inspeccion de piel y tegumentos corporales.', 'hist' => [['Revision de piel y mucosas sin otros signos de hemorragia activa.', 160]]],
            ['am' => 'AM_003', 'origen' => 'SIGNOS', 'tipo' => 'HIPOGLUCEMIA_CAPILAR', 'nivel' => 'CRITICO', 'motivo' => 'Glicemia capilar de 58 mg/dL en control prepandial con diaforesis fria, temblor fino distal y ligera somnolencia.', 'estado' => 'EN_ATENCION', 'min' => 150, 'cierre' => null, 'accion' => 'Administracion inmediata de 15g de carbohidratos de absorcion rapida.', 'hist' => [['Ingesta de zumo azucarado y reposo asistido.', 140], ['Glicemia a los 15 minutos: 84 mg/dL. Valores recuperados.', 110]]],
            ['am' => 'AM_003', 'origen' => 'VALORACION', 'tipo' => 'AGITACION_SUNDOWNING', 'nivel' => 'ALTO', 'motivo' => 'Episodio de agitacion psicomotriz y desorientacion temporoespacial al atardecer (sindrome de Sundowning).', 'estado' => 'CERRADA', 'min' => 1200, 'cierre' => 500, 'accion' => 'Tecnicas de validacion afectiva, reduccion de estimulos y musica suave.', 'hist' => [['Acompanamiento en sala de descanso e infusion tibia.', 900]]],
            ['am' => 'AM_004', 'origen' => 'SIGNOS', 'tipo' => 'HIPOTENSION_POSTURAL', 'nivel' => 'MEDIO', 'motivo' => 'Hipotension ortostatica matutina (PA 90/60 mmHg) con mareo momentaneo al incorporarse de la cama.', 'estado' => 'CERRADA', 'min' => 1600, 'cierre' => 800, 'accion' => 'Reposo en decubito con piernas elevadas e hidratacion oral.', 'hist' => [['Control tensional seriado: 118/72 mmHg a los 30 minutos sin mareo.', 1400]]],
            ['am' => 'AM_004', 'origen' => 'MEDICACION', 'tipo' => 'REVISION_ANTIDEPRESIVO', 'nivel' => 'BAJO', 'motivo' => 'Cumplimiento de 6 semanas de pauta de Sertralina 50mg; programar evaluacion psicologica de seguimiento.', 'estado' => 'ABIERTA', 'min' => 240, 'cierre' => null, 'accion' => null, 'hist' => []],
            ['am' => 'AM_005', 'origen' => 'SIGNOS', 'tipo' => 'HIPERGLUCEMIA_POSTPRANDIAL', 'nivel' => 'ALTO', 'motivo' => 'Glicemia capilar postprandial de 242 mg/dL tras el almuerzo; requiere verificacion dietetica.', 'estado' => 'EN_ATENCION', 'min' => 180, 'cierre' => null, 'accion' => 'Aporte hidrico abundante y control capilar seriado cada 2 horas.', 'hist' => [['Verificacion de ingesta alimentaria y educacion nutricional.', 160]]],
            ['am' => 'AM_005', 'origen' => 'VALORACION', 'tipo' => 'LESION_PODOLOGICA', 'nivel' => 'MEDIO', 'motivo' => 'Fisura dermica superficial en talon izquierdo sin signos de celulitis activa; prevencion en pie diabetico.', 'estado' => 'CERRADA', 'min' => 2800, 'cierre' => 1200, 'accion' => 'Lavado con clorhexidina jabonosa y crema humectante con urea.', 'hist' => [['Curacion y vendaje tubular protector.', 2200]]],
            ['am' => 'AM_006', 'origen' => 'INCIDENTE', 'tipo' => 'TROPIEZO_SIN_CAIDA', 'nivel' => 'MEDIO', 'motivo' => 'Tropiezo con alfombra de pasillo este sin llegar a caer; residente asistida oportunamente por auxiliar.', 'estado' => 'CERRADA', 'min' => 3000, 'cierre' => 1500, 'accion' => 'Retiro preventivo de alfombra y valoracion osteoarticular de caderas.', 'hist' => [['Examen fisico sin dolor ni limitacion funcional.', 2600]]],
            ['am' => 'AM_006', 'origen' => 'FICHA', 'tipo' => 'INSOMNIO_TERMINAL', 'nivel' => 'BAJO', 'motivo' => 'Reporte de despertar precoz recurrente a las 04:30 AM con dificultad para conciliar el sueno.', 'estado' => 'ABIERTA', 'min' => 360, 'cierre' => null, 'accion' => null, 'hist' => []],
            ['am' => 'AM_007', 'origen' => 'PLAN', 'tipo' => 'EPISODIO_FREEZING', 'nivel' => 'ALTO', 'motivo' => 'Episodio de congelamiento motor (freezing) de 4 minutos al intentar ingresar al comedor.', 'estado' => 'EN_ATENCION', 'min' => 210, 'cierre' => null, 'accion' => 'Senales auditivas ritmicas con conteo verbal para reanudar la marcha.', 'hist' => [['Guia verbal paso a paso hasta asiento del comedor.', 190]]],
            ['am' => 'AM_007', 'origen' => 'VALORACION', 'tipo' => 'DISFAGIA_LEVE', 'nivel' => 'MEDIO', 'motivo' => 'Tos refleja tras ingerir liquidos claros en el almuerzo; pautar espesante tipo nectar.', 'estado' => 'CERRADA', 'min' => 2000, 'cierre' => 900, 'accion' => 'Pauta de espesante en liquidos y postura erguida durante comidas.', 'hist' => [['Interconsulta con fonoaudiologia y cambio a consistencia nectar.', 1500]]],
            ['am' => 'AM_008', 'origen' => 'SIGNOS', 'tipo' => 'EDEMA_MALEOLAR_BILATERAL', 'nivel' => 'MEDIO', 'motivo' => 'Edema con fovea grado II en ambos tobillos tras 4 horas de sedestacion prolongada.', 'estado' => 'CERRADA', 'min' => 1800, 'cierre' => 700, 'accion' => 'Reposo en cama con elevacion de miembros inferiores a 30 grados.', 'hist' => [['Colocacion de cojin elevador y vendaje compresivo suave.', 1400]]],
            ['am' => 'AM_008', 'origen' => 'FICHA', 'tipo' => 'EVOLUCION_ULCERA_VENOSA', 'nivel' => 'MEDIO', 'motivo' => 'Ulcera venosa en maleolo derecho con exudado escaso y lecho 90% granulatorio limpio.', 'estado' => 'ABIERTA', 'min' => 300, 'cierre' => null, 'accion' => null, 'hist' => []],
            ['am' => 'AM_009', 'origen' => 'FICHA', 'tipo' => 'INAPETENCIA_SELECTIVA', 'nivel' => 'MEDIO', 'motivo' => 'Rechazo de mas del 50% de la racion de almuerzo durante dos dias consecutivos; manifiesta desgano.', 'estado' => 'EN_ATENCION', 'min' => 240, 'cierre' => null, 'accion' => 'Fraccionamiento de tomas hipercaloricas y acompanamiento afectivo.', 'hist' => [['Administracion de batido proteico suplementario bien tolerado.', 200]]],
            ['am' => 'AM_009', 'origen' => 'VALORACION', 'tipo' => 'AISLAMIENTO_SOCIAL', 'nivel' => 'BAJO', 'motivo' => 'Residente permanece en habitacion durante talleres recreativos; promover integracion grupal.', 'estado' => 'ABIERTA', 'min' => 480, 'cierre' => null, 'accion' => null, 'hist' => []],
            ['am' => 'AM_010', 'origen' => 'SIGNOS', 'tipo' => 'BRADICARDIA_SINUSAL', 'nivel' => 'CRITICO', 'motivo' => 'Frecuencia cardiaca de 48 lpm en control matutino con ligera astenia; residente portador de marcapasos.', 'estado' => 'EN_ATENCION', 'min' => 110, 'cierre' => null, 'accion' => 'Auscultacion de espiga de marcapasos y toma de electrocardiograma de 12 derivaciones.', 'hist' => [['ECG con estimulacion ventricular presente a 50 lpm sin sincope.', 95], ['Comunicacion a cardiologia de enlace; FC asciende a 56 lpm.', 60]]],
            ['am' => 'AM_010', 'origen' => 'PLAN', 'tipo' => 'CONTROL_MARCAPASOS', 'nivel' => 'BAJO', 'motivo' => 'Programacion de revision semestral telemetrica de marcapasos en hospital de referencia.', 'estado' => 'ABIERTA', 'min' => 720, 'cierre' => null, 'accion' => null, 'hist' => []],
            ['am' => 'AM_011', 'origen' => 'PLAN', 'tipo' => 'OBSTRUCCION_CATETER_URINARIO', 'nivel' => 'ALTO', 'motivo' => 'Ausencia de diuresis en bolsa colectora durante 4 horas con disconfort suprapubico por acodamiento de sonda.', 'estado' => 'CERRADA', 'min' => 1500, 'cierre' => 600, 'accion' => 'Desobstruccion de acodamiento bajo el muslo y permeabilizacion con suero.', 'hist' => [['Desobstruccion del circuito; drenaje inmediato de 450 ml de orina clara.', 1200]]],
            ['am' => 'AM_011', 'origen' => 'SIGNOS', 'tipo' => 'HEMATURIA_LEVE', 'nivel' => 'MEDIO', 'motivo' => 'Tinte rosaceo leve en orina tras movilizacion en cama; descartar sangrado activo.', 'estado' => 'ABIERTA', 'min' => 200, 'cierre' => null, 'accion' => null, 'hist' => []],
            ['am' => 'AM_012', 'origen' => 'SIGNOS', 'tipo' => 'DESATURACION_NOCTURNA', 'nivel' => 'CRITICO', 'motivo' => 'Saturacion de oxigeno al 86% en aire ambiente a las 03:00 AM con respiracion estertorosa y disnea leve.', 'estado' => 'EN_ATENCION', 'min' => 90, 'cierre' => null, 'accion' => 'Posicion Fowler a 60 grados y oxigenoterapia por canula nasal a 2 L/min.', 'hist' => [['Instalacion de oxigeno a 2 L/min; SpO2 se eleva a 94% en 10 minutos.', 75], ['Murmullo vesicular conservado; residente descansa confortablemente.', 40]]],
            ['am' => 'AM_012', 'origen' => 'MEDICACION', 'tipo' => 'AJUSTE_INSULINA', 'nivel' => 'MEDIO', 'motivo' => 'Glicemias basales en ayunas mayores a 160 mg/dL durante 3 dias consecutivos.', 'estado' => 'CERRADA', 'min' => 2500, 'cierre' => 1100, 'accion' => 'Interconsulta medica; ajuste de Insulina NPH de 12 a 14 UI matutinas.', 'hist' => [['Nueva pauta registrada y comunicada a enfermeria.', 1800]]],
            ['am' => 'AM_013', 'origen' => 'INCIDENTE', 'tipo' => 'CAIDA_AMORTIGUADA', 'nivel' => 'ALTO', 'motivo' => 'Deslizamiento desde la poltrona hacia la alfombra al intentar pararse sin asistencia; caida sin TCE.', 'estado' => 'CERRADA', 'min' => 3200, 'cierre' => 1400, 'accion' => 'Valoracion neurologica (Glasgow 15/15), palpacion osea de cadera sin crepitacion.', 'hist' => [['Signos vitales estables: PA 130/80 mmHg, pupilas normorreactivas.', 2800]]],
            ['am' => 'AM_013', 'origen' => 'VALORACION', 'tipo' => 'ALUCINACION_VISUAL_BENIGNA', 'nivel' => 'MEDIO', 'motivo' => 'Residente refiere ver ninos jugando en su habitacion; tranquila, sin angustia ni llanto.', 'estado' => 'ABIERTA', 'min' => 180, 'cierre' => null, 'accion' => null, 'hist' => []],
            ['am' => 'AM_014', 'origen' => 'MEDICACION', 'tipo' => 'OMISION_TERAPEUTICA_JUSTIFICADA', 'nivel' => 'ALTO', 'motivo' => 'Omision de dosis matutina por vomito de contenido gastrico alimentario previo.', 'estado' => 'EN_ATENCION', 'min' => 140, 'cierre' => null, 'accion' => 'Reposo gastrico por 60 min, antiemetico y reprogramacion terapeutica.', 'hist' => [['Abdomen blando y depresible sin signos de alarma peritoneal.', 120], ['Administracion de medicamentos postergados con excelente tolerancia.', 70]]],
            ['am' => 'AM_014', 'origen' => 'SIGNOS', 'tipo' => 'PICO_HIPERTENSIVO_VESPERTINO', 'nivel' => 'ALTO', 'motivo' => 'Presion arterial vespertina de 170/100 mmHg en residente con secuela de ACV; vigilancia de deficit focal.', 'estado' => 'CERRADA', 'min' => 2200, 'cierre' => 900, 'accion' => 'Reposo en cama y administracion de dosis pautada de Enalapril 10mg.', 'hist' => [['PA desciende a 135/85 mmHg a los 45 min sin focalidad neurologica.', 1600]]],
            ['am' => 'AM_015', 'origen' => 'FICHA', 'tipo' => 'LUMBOCIATICA_DERECHA', 'nivel' => 'MEDIO', 'motivo' => 'Dolor punzante por cara posterior del muslo derecho con Lasegue positivo a 45 grados y parestesias.', 'estado' => 'EN_ATENCION', 'min' => 160, 'cierre' => null, 'accion' => 'Reposo antalgico en decubito con almohada bajo rodillas y calor seco.', 'hist' => [['Administracion de Tramadol/Paracetamol con alivio progresivo.', 140]]],
            ['am' => 'AM_015', 'origen' => 'VALORACION', 'tipo' => 'INESTABILIDAD_MARCHA', 'nivel' => 'BAJO', 'motivo' => 'Tendencia a prescindir del baston de cuatro apoyos durante la noche; educacion preventiva.', 'estado' => 'ABIERTA', 'min' => 320, 'cierre' => null, 'accion' => null, 'hist' => []],
        ];

        $poolEnf = [$enfMendoza, $enfChoque, $enfCarla];

        foreach ($alertasReales as $idx => $item) {
            $am = AdultoMayor::find($item['am']);
            if (!$am) continue;

            $resp = $poolEnf[$idx % count($poolEnf)];
            $creado = Carbon::now()->subMinutes($item['min']);

            $alerta = AlertaAdulto::create([
                'cod_am' => $item['am'],
                'cod_turno' => $tManana,
                'origen' => $item['origen'],
                'tipo_alerta' => $item['tipo'],
                'nivel' => $item['nivel'],
                'motivo' => $item['motivo'],
                'responsable_id' => $resp,
                'estado' => $item['estado'],
                'accion_tomada' => $item['accion'],
                'fecha_atencion' => in_array($item['estado'], ['EN_ATENCION', 'CERRADA']) ? $creado->copy()->addMinutes(15) : null,
                'atendido_por' => in_array($item['estado'], ['EN_ATENCION', 'CERRADA']) ? $resp : null,
                'fecha_cierre' => $item['cierre'] ? Carbon::now()->subMinutes($item['cierre']) : null,
                'cerrado_por' => $item['cierre'] ? $resp : null,
                'observacion_cierre' => $item['cierre'] ? 'Caso evaluado y resuelto clinicamente conforme a protocolo institucional.' : null,
                'created_at' => $creado,
                'updated_at' => $item['cierre'] ? Carbon::now()->subMinutes($item['cierre']) : Carbon::now(),
            ]);

            if ($item['accion'] && in_array($item['estado'], ['EN_ATENCION', 'CERRADA'])) {
                AccionAlerta::create([
                    'cod_alerta' => $alerta->cod_alerta,
                    'accion' => $item['accion'],
                    'responsable_id' => $resp,
                    'fecha_accion' => $creado->copy()->addMinutes(15),
                    'estado' => 'REALIZADA',
                    'observacion' => 'Atencion inmediata de enfermeria ejecutada en turno.'
                ]);
            }

            foreach ($item['hist'] as $h) {
                AccionAlerta::create([
                    'cod_alerta' => $alerta->cod_alerta,
                    'accion' => $h[0],
                    'responsable_id' => $resp,
                    'fecha_accion' => Carbon::now()->subMinutes($h[1]),
                    'estado' => 'REALIZADA',
                    'observacion' => 'Seguimiento registrado en hoja de enfermeria.'
                ]);
            }
        }

        $this->command->info('AlertasRealesSeeder: Base de datos clinica poblada con exito en todas las tablas.');
    }
}
