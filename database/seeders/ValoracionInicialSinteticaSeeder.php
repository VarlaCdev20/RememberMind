<?php

namespace Database\Seeders;

use App\Models\Preadmision;
use App\Models\User;
use App\Models\ValoracionEnfermeriaPreadmision;
use App\Modulos\Valoraciones\Aplicacion\Acciones\RegistrarValoracionInicial;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

/** Carga local de valoraciones para las solicitudes sintéticas del seeder LaPaz. */
final class ValoracionInicialSinteticaSeeder extends Seeder
{
    private const CASOS = [
        'LP_SOL_1' => ['documento' => '89000100', 'con_andador' => false, 'peso' => 61.2, 'talla' => 154],
        'LP_SOL_4' => ['documento' => '89000103', 'con_andador' => true, 'peso' => 68.4, 'talla' => 166],
    ];

    public function run(): void
    {
        $this->command?->info(json_encode($this->ejecutar(false), JSON_THROW_ON_ERROR));
    }

    public function ejecutar(bool $guardar): array
    {
        ComplementarCensoSinteticoSeeder::comprobarDestino();

        if (! $guardar) {
            DB::beginTransaction();
        }

        try {
            return DB::transaction(function (): array {
                DB::select('select pg_advisory_xact_lock(?)', [2026111303]);
                $actor = User::query()->where('estado', 'ACTIVO')->role('ENFERMEROS')
                    ->whereHas('personal', fn ($q) => $q->whereKey('COD_PER_000003')->where('estado', 'ACTIVO'))
                    ->sole();
                $registrar = app(RegistrarValoracionInicial::class);
                $nuevas = 0;

                foreach (self::CASOS as $codigo => $caso) {
                    $preadmision = Preadmision::query()->whereKey($codigo)
                        ->where('numero_documento', $caso['documento'])
                        ->where('cod_usuario_registro', 'LP_USU_ADMIN')->lockForUpdate()->sole();

                    if ($preadmision->valoracionEnfermeriaRegistro() !== null) {
                        continue;
                    }

                    Gate::forUser($actor)->authorize('create', [ValoracionEnfermeriaPreadmision::class, $preadmision]);
                    $registrar->ejecutar($actor, $this->datos($preadmision, $caso));
                    $nuevas++;
                }

                return ['nuevas' => $nuevas];
            });
        } finally {
            if (! $guardar) {
                DB::rollBack();
            }
        }
    }

    private function datos(Preadmision $preadmision, array $caso): array
    {
        $andador = $caso['con_andador'];

        return [
            'preadmision' => $preadmision,
            'estado_general' => 'ESTABLE',
            'nivel_conciencia' => 'ALERTA',
            'orientacion_persona' => 'ORIENTADO',
            'orientacion_tiempo' => 'ORIENTADO',
            'orientacion_espacio' => 'ORIENTADO',
            'comunicacion' => 'VERBAL',
            'hay_dolor' => false,
            'movilidad' => $andador ? 'CON_ANDADOR' : 'INDEPENDIENTE',
            'apoyo_movilidad' => $andador ? 'Andador' : 'Sin ayuda técnica',
            'riesgo_caida' => $andador ? 'MEDIO' : 'BAJO',
            'piel_estado' => 'INTEGRA',
            'hay_heridas' => false,
            'higiene_ingreso' => 'ADECUADA',
            'continencia_basica' => 'CONTINENTE',
            'alimentacion_aparente' => 'ADECUADA',
            'pa_sistolica' => $andador ? 126 : 118,
            'pa_diastolica' => $andador ? 78 : 72,
            'frecuencia_cardiaca' => $andador ? 74 : 70,
            'frecuencia_respiratoria' => 16,
            'temperatura' => 36.5,
            'saturacion_oxigeno' => 96,
            'peso' => $caso['peso'],
            'talla' => $caso['talla'],
            'antecedentes_relevantes' => null,
            'medicacion_referida' => null,
            'alergias_referidas' => null,
            'dependencia_funcional' => $andador ? 'DEPENDENCIA_LEVE' : 'INDEPENDIENTE',
            'riesgo_nutricional' => 'SIN RIESGO',
            'riesgo_cognitivo' => 'SIN DETERIORO',
            'necesidad_apoyo_inmediato' => $andador ? 'Acompañamiento durante los traslados' : null,
            'prioridad_sugerida' => $preadmision->prioridad,
            'confirmacion_documentacion' => true,
            'comentarios_adicionales' => 'Valoración de una solicitud sintética de desarrollo.',
            'recomendacion_enfermeria' => $andador
                ? 'Registrar el apoyo con andador en la revisión de ingreso.'
                : 'Continuar con la revisión de preadmisión.',
        ];
    }
}
