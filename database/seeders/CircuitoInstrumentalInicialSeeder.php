<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Instrumento;
use App\Models\PreguntaInstrumento;
use App\Modulos\ApoyoDecision\Aplicacion\Servicios\PerfilesInstrumentalesIniciales;
use DomainException;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use MotorExperto\instrumentos\CircuitoInstrumentalInicial;

/** Catálogo de referencia autorizado el04/11/2026. Idempotente; no altera instrumentos o aplicaciones anteriores. */
final class CircuitoInstrumentalInicialSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            foreach ((new CircuitoInstrumentalInicial)->definiciones() as $metodo => $d) {
                $instrumento = Instrumento::query()->where('codigo', $d['codigo'])->lockForUpdate()->first();
                if ($instrumento) {
                    if ($instrumento->estado !== 'ACTIVO') {
                        throw new DomainException('Un instrumento inicial existente está inactivo; no se reactiva silenciosamente.');
                    }
                    app(PerfilesInstrumentalesIniciales::class)->definicion($instrumento, $instrumento->preguntas()->with('opciones')->get());
                    continue;
                }
                $instrumento = Instrumento::create(['codigo' => $d['codigo'], 'nombre' => $d['nombre'], 'tipo' => $d['tipo'],
                    'version' => $d['version'], 'puntaje_maximo' => '30.00', 'estado' => 'ACTIVO',
                    'descripcion' => 'Captura profesional de puntuaciones por secciones. Fuente: '.$d['fuente'].'. No incluye estímulos ni clasificación automática de DCL.']);
                $orden = 0;
                foreach ($d['campos'] as $codigo => $campo) {
                    PreguntaInstrumento::create(['cod_instrumento' => $instrumento->getKey(), 'codigo' => $codigo,
                        'enunciado' => $campo['label'], 'dominio' => $d['tipo'] === 'FUNCIONAL' ? 'Registro funcional original' : 'Registro cognitivo original',
                        'tipo_respuesta' => $campo['tipo'] === 'LOGICO' ? 'LOGICO' : 'NUMERO',
                        'puntaje_maximo' => $campo['puntuable'] ? $campo['maximo'] : null, 'orden' => ++$orden, 'estado' => 'ACTIVA']);
                }
                activity('instrumentos')->performedOn($instrumento)->event('perfil_inicial_instalado')
                    ->withProperties(['metodo' => $metodo, 'version' => $d['version'], 'alcance' => 'CAPTURA_Y_PUNTUACION_SIN_CLASIFICACION'])
                    ->log('Instalación técnica del catálogo autorizado, sin autoría clínica ni datos de residentes.');
            }
        });
    }
}
