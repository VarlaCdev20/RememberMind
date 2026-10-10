<?php

namespace App\Models;

use App\Backend\Modulos\Enfermeria\Servicios\CuidadosEnfermeriaService as Cuidados;

class RegistroMovilidad extends ModeloOperativo
{
    protected $table = 'registros_movilidad';

    protected $primaryKey = 'cod_movilidad';

    public const CAMPOS_CAPTURA = ['motivo_registro', 'actividad_realizada', 'marcha', 'traslado', 'tipo_apoyo',
        'dispositivo', 'distancia_metros', 'equilibrio', 'fatiga', 'tolerancia_movilidad', 'dolor_movilidad',
        'mareo', 'disnea', 'debilidad', 'riesgo_caida', 'cambio_habitual', 'observacion'];

    public const LABELS = ['motivo_registro' => 'Motivo del registro', 'actividad_realizada' => 'Actividad realizada',
        'marcha' => 'Movilidad observada', 'traslado' => 'Traslado', 'tipo_apoyo' => 'Apoyo requerido',
        'dispositivo' => 'Dispositivo', 'distancia_metros' => 'Distancia recorrida', 'equilibrio' => 'Equilibrio',
        'fatiga' => 'Fatiga', 'tolerancia_movilidad' => 'Tolerancia', 'dolor_movilidad' => 'Dolor durante movilización',
        'mareo' => 'Mareo', 'disnea' => 'Disnea', 'debilidad' => 'Debilidad', 'riesgo_caida' => 'Riesgo de caída',
        'cambio_habitual' => 'Cambio respecto al habitual'];

    public const TEXTOS = ['CONTROL_DIARIO' => 'Control diario', 'CAMBIO_FUNCIONAL' => 'Cambio funcional',
        'POST_CAIDA' => 'Post caída', 'TRAS_FISIOTERAPIA' => 'Tras fisioterapia', 'ANTES_TRASLADO' => 'Antes de traslado',
        'OTRO' => 'Otro', 'CAMINAR_HABITACION' => 'Caminar en habitación', 'CAMINAR_PASILLO' => 'Caminar en pasillo',
        'LEVANTARSE_CAMA' => 'Levantarse de la cama', 'TRANSFERENCIA_CAMA_SILLON' => 'Transferencia cama–sillón',
        'CAMBIO_POSTURAL' => 'Cambio postural', 'SEDESTACION' => 'Sedestación', 'BIPEDESTACION' => 'Bipedestación',
        'INDEPENDIENTE' => 'Independiente', 'ASISTIDA' => 'Asistida', 'SILLA_RUEDAS' => 'Silla de ruedas',
        'ENCAMADO' => 'Encamado', 'SUPERVISION' => 'Supervisión', 'AYUDA_UNA_PERSONA' => 'Ayuda de 1 persona',
        'AYUDA_DOS_PERSONAS' => 'Ayuda de 2 personas', 'GRUA' => 'Grúa', 'PARCIAL' => 'Parcial', 'COMPLETA' => 'Completa',
        'UNA_PERSONA' => '1 persona', 'DOS_PERSONAS' => '2 personas', 'NINGUNO' => 'Ninguno', 'BASTON' => 'Bastón',
        'ANDADOR' => 'Andador', 'BARANDILLA' => 'Barandilla', 'ESTABLE' => 'Estable', 'INESTABLE' => 'Inestable',
        'NO_VALORABLE' => 'No valorable', 'SIN_FATIGA' => 'Sin fatiga', 'LEVE' => 'Leve', 'MODERADA' => 'Moderada',
        'SEVERA' => 'Severa', 'BUENA' => 'Buena', 'MALA' => 'Mala', 'BAJO' => 'Bajo', 'MEDIO' => 'Medio',
        'ALTO' => 'Alto', 'SIN_CAMBIOS' => 'Sin cambios', 'MEJOR' => 'Mejor', 'PEOR' => 'Peor'];

    protected function casts(): array
    {
        return ['fecha_hora' => 'datetime', 'distancia_metros' => 'decimal:2', 'dolor_movilidad' => 'boolean',
            'mareo' => 'boolean', 'disnea' => 'boolean', 'debilidad' => 'boolean'];
    }

    public static function catalogos(): array
    {
        return ['motivo_registro' => Cuidados::MOTIVOS_MOVILIDAD, 'actividad_realizada' => Cuidados::ACTIVIDADES_MOVILIDAD,
            'marcha' => Cuidados::MOVILIDAD_OBSERVADA, 'traslado' => Cuidados::TRASLADOS, 'tipo_apoyo' => Cuidados::NIVELES_AYUDA,
            'dispositivo' => Cuidados::DISPOSITIVOS_MOVILIDAD, 'equilibrio' => Cuidados::EQUILIBRIOS, 'fatiga' => Cuidados::FATIGAS,
            'tolerancia_movilidad' => Cuidados::TOLERANCIAS_MOVILIDAD, 'riesgo_caida' => Cuidados::RIESGOS_CAIDA,
            'cambio_habitual' => Cuidados::CAMBIOS_HABITUALES];
    }

    public function resumenOperacional(): array
    {
        $campos = [];
        foreach (self::LABELS as $campo => $label) {
            $valor = $this->{$campo};
            if ($valor === null || $valor === '') {
                continue;
            }
            $texto = match ($campo) {
                'distancia_metros' => $valor.' m',
                'dolor_movilidad', 'mareo', 'disnea', 'debilidad' => $valor ? 'Sí' : 'No',
                default => self::TEXTOS[$valor] ?? (string) $valor,
            };
            $campos[] = ['nombre' => $label, 'valor' => $texto];
        }

        return ['codigo' => $this->cod_movilidad, 'fecha' => $this->fecha_hora->format('d/m/Y'),
            'hora' => $this->fecha_hora->format('H:i'), 'grupo' => $this->fecha_hora->isToday() ? 'Hoy' : ($this->fecha_hora->isYesterday() ? 'Ayer' : $this->fecha_hora->format('d/m/Y')),
            'actividad' => self::TEXTOS[$this->actividad_realizada] ?? 'Movilidad registrada',
            'categoria' => in_array($this->actividad_realizada, Cuidados::ACTIVIDADES_DEAMBULACION, true) ? 'DEAMBULACION'
                : (in_array($this->actividad_realizada, ['LEVANTARSE_CAMA', 'TRANSFERENCIA_CAMA_SILLON'], true) ? 'TRANSFERENCIAS' : 'OTROS'),
            'campos' => $campos, 'observacion' => $this->observacion];
    }
}
