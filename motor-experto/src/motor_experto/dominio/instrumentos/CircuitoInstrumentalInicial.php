<?php

declare(strict_types=1);

namespace MotorExperto\dominio\instrumentos;

use DomainException;

/** Captura propia de puntuaciones de ediciones identificadas. No contiene estímulos ni cortes diagnósticos. */
final class CircuitoInstrumentalInicial
{
    public const MOCA = 'MOCA81-ES-PUNTUACION-1';
    public const RUDAS = 'RUDASPE20-ES-PUNTUACION-1';
    public const FAQ = 'FAQB7S08-ES-PUNTUACION-1';

    public function definiciones(): array
    {
        $moca = [
            'alternancia' => $this->campo('Alternancia: puntuación', 1),
            'copia' => $this->campo('Copia: puntuación', 1),
            'reloj' => $this->campo('Reloj: puntuación conjunta', 3),
            'identificacion' => $this->campo('Identificación: puntuación', 3),
            'aprendizaje_1' => $this->campo('Primer ensayo: elementos aprendidos (sin puntos)', 5, false),
            'aprendizaje_2' => $this->campo('Segundo ensayo: elementos aprendidos (sin puntos)', 5, false),
            'digitos' => $this->campo('Dígitos: puntuación conjunta', 2),
            'vigilancia' => $this->campo('Vigilancia: puntuación', 1),
            'calculo' => $this->campo('Cálculo: puntuación', 3),
            'repeticion' => $this->campo('Repetición: puntuación conjunta', 2),
            'fluidez' => $this->campo('Fluidez: puntuación', 1),
            'abstraccion' => $this->campo('Abstracción: puntuación conjunta', 2),
            'recuerdo_libre' => $this->campo('Recuerdo diferido libre: aciertos', 5),
            'recuerdo_categoria' => $this->campo('Recuerdos adicionales con categoría (solo índice de memoria)', 5, false),
            'recuerdo_eleccion' => $this->campo('Recuerdos adicionales con elección (solo índice de memoria)', 5, false),
            'orientacion' => $this->campo('Orientación: puntuación', 6),
            'ajuste_educativo' => $this->logico('¿Corresponde el punto educativo según esta edición (12 años o menos)?'),
        ];
        $rudas = [
            'registro_aprendizaje' => $this->logico('¿Se completó el registro inicial conforme al protocolo?'),
            'orientacion' => $this->campo('Orientación visuoespacial: puntuación', 5),
            'praxias' => $this->campo('Praxias: puntuación', 2),
            'construccion' => $this->campo('Construcción: puntuación', 3),
            'juicio' => $this->campo('Juicio: puntuación conjunta', 4),
            'recuerdo' => $this->campo('Recuerdo no inducido: puntuación (0, 2, 4, 6 u 8)', 8) + ['permitidos' => [0, 2, 4, 6, 8]],
            'lenguaje' => $this->campo('Lenguaje: puntuación', 8),
        ];
        $faq = [];
        foreach (['Pagos y cuentas', 'Documentación económica', 'Compras', 'Actividades de interés', 'Uso de cocina',
            'Preparación de alimentos', 'Seguimiento de actualidad', 'Comprensión de contenidos', 'Compromisos y medicación', 'Desplazamientos'] as $i => $nombre) {
            $faq['actividad_'.($i + 1)] = $this->campo($nombre, 3) + ['permitidos' => [0, 1, 2, 3, 8],
                'opciones' => [0 => 'Sin dificultad', 1 => 'Con dificultad, conserva autonomía', 2 => 'Necesita ayuda', 3 => 'Dependencia', 8 => 'No aplica']];
        }
        $faq['informante_participo'] = $this->logico('¿La información procede de un informante que conoce estas actividades?');

        return [
            self::MOCA => ['familia' => 'I01', 'codigo' => 'MOCA81-ES', 'nombre' => 'MoCA Full 8.1 · captura de puntuaciones',
                'version' => '8.1-ES-ID061021', 'tipo' => 'COGNITIVO',
                'identidad' => ['instrumento' => 'MoCA Full', 'edicion' => '8.1-ID061021-2018', 'idioma' => 'es-ES'],
                'fuente' => 'https://championsforhealth.org/wp-content/uploads/2018/12/MOCA-8.1-Spanish.pdf', 'campos' => $moca],
            self::RUDAS => ['familia' => 'I02', 'codigo' => 'RUDASPE20-ES', 'nombre' => 'RUDAS-PE 2020 · captura de puntuaciones',
                'version' => 'PE-2020-ES', 'tipo' => 'COGNITIVO',
                'identidad' => ['instrumento' => 'RUDAS-PE', 'edicion' => 'Custodio-2020-Table2', 'idioma' => 'es-PE'],
                'fuente' => 'https://www.frontiersin.org/journals/neurology/articles/10.3389/fneur.2020.00374/full', 'campos' => $rudas],
            self::FAQ => ['familia' => 'I04', 'codigo' => 'FAQ-B7S08-ES', 'nombre' => 'FAQ/Pfeffer B7S · 10 actividades',
                'version' => 'UDS2-B7S-2008-ES', 'tipo' => 'FUNCIONAL',
                'identidad' => ['instrumento' => 'FAQ', 'edicion' => 'UDSv2-B7S-2008-02-10', 'idioma' => 'es'],
                'fuente' => 'https://files.alz.washington.edu/documentation/uds2-ivp-b7-sp.pdf', 'campos' => $faq],
        ];
    }

    public function metodos(): array
    {
        $metodos = [];
        foreach ($this->definiciones() as $metodo => $d) {
            $metodos[$metodo] = ['instrumento' => $d['identidad']['instrumento'], 'tipo' => 'PUNTUACION_INICIAL_SIN_CLASIFICACION',
                'identidad' => $d['identidad'], 'fuente' => $d['fuente'], 'componentes' => [],
                'entradas' => array_map(static fn ($c) => $c['tipo'], $d['campos']),
                'reproduce_reactivos' => false, 'revision_profesional' => 'PENDIENTE'];
        }

        return $metodos;
    }

    /** NULL nunca es cero; las condiciones de aplicación no se presuponen verdaderas. */
    public function calcular(string $metodo, array $captura): array
    {
        $d = $this->definiciones()[$metodo] ?? throw new DomainException('Perfil inicial no reconocido.');
        $ausentes = $invalidos = $noAplicables = $originales = [];
        $suma = 0;
        $puntuablesCompletos = true;
        foreach ($d['identidad'] + ['protocolo' => $metodo] as $campo => $esperado) {
            if (($captura[$campo] ?? null) !== $esperado) {
                $invalidos[] = 'IDENTIDAD:'.$campo;
            }
        }
        foreach ($d['campos'] as $campo => $meta) {
            $v = $captura[$campo] ?? null;
            $originales[$campo] = $v;
            if ($v === null) {
                $ausentes[] = $campo;
                $puntuablesCompletos = $puntuablesCompletos && ! $meta['puntuable'];
                continue;
            }
            $valido = $meta['tipo'] === 'LOGICO' ? is_bool($v)
                : (is_int($v) && (isset($meta['permitidos']) ? in_array($v, $meta['permitidos'], true) : ($v >= 0 && $v <= $meta['maximo'])));
            if (! $valido) {
                $invalidos[] = $campo;
                $puntuablesCompletos = false;
            } elseif ($metodo === self::FAQ && $v === 8) {
                $noAplicables[] = $campo;
                $puntuablesCompletos = false;
            } elseif ($meta['puntuable']) {
                $suma += $v;
            }
        }
        $bruto = $puntuablesCompletos && $invalidos === [] ? $suma : null;
        $total = $bruto;
        $ajuste = $mis = null;
        $motivos = [];
        if ($metodo === self::MOCA) {
            $aplica = $captura['ajuste_educativo'] ?? null;
            $ajuste = is_bool($aplica) ? (int) $aplica : null;
            $total = $bruto !== null && $ajuste !== null ? min(30, $bruto + $ajuste) : null;
            if ($ajuste === null) {
                $motivos[] = 'AJUSTE_EDUCATIVO_SIN_CONFIRMAR';
            }
            $memoria = array_intersect_key($originales, array_flip(['recuerdo_libre', 'recuerdo_categoria', 'recuerdo_eleccion']));
            if (! in_array(null, $memoria, true) && array_filter($memoria, static fn ($v) => ! is_int($v) || $v < 0 || $v > 5) === []) {
                if (array_sum($memoria) > 5) {
                    $invalidos[] = 'RECUERDOS_SOLAPADOS';
                } else {
                    $mis = 3 * $memoria['recuerdo_libre'] + 2 * $memoria['recuerdo_categoria'] + $memoria['recuerdo_eleccion'];
                }
            }
        } elseif (($captura[$metodo === self::RUDAS ? 'registro_aprendizaje' : 'informante_participo'] ?? null) !== true) {
            $total = null;
            $motivos[] = $metodo === self::RUDAS ? 'REGISTRO_INICIAL_NO_CONFIRMADO' : 'INFORMANTE_NO_CONFIRMADO';
        }
        if ($invalidos !== []) {
            $total = null;
        }
        if ($noAplicables !== []) {
            $motivos[] = 'ACTIVIDADES_NO_APLICABLES_SIN_IMPUTACION';
        }
        $avisos = array_map(static fn ($m) => match ($m) {
            'AJUSTE_EDUCATIVO_SIN_CONFIRMAR' => 'Falta confirmar el ajuste educativo. El bruto se conserva por separado.',
            'REGISTRO_INICIAL_NO_CONFIRMADO' => 'Falta confirmar el registro inicial para emitir el total.',
            'INFORMANTE_NO_CONFIRMADO' => 'Falta confirmar la participación del informante para emitir el total.',
            'ACTIVIDADES_NO_APLICABLES_SIN_IMPUTACION' => 'Hay actividades no aplicables. No se convierten en cero ni se calcula un total parcial.',
        }, $motivos);
        if ($ausentes !== []) {
            $avisos[] = count($ausentes).' campos sin registrar. Las ausencias se conservan; no se convierten en cero.';
        }
        if ($invalidos !== []) {
            $avisos[] = 'Existen puntuaciones fuera de rango o incoherentes. Revísalas antes de utilizar el resultado.';
        }

        return ['metodo' => $metodo, 'referencia' => $d['fuente'], 'bruto' => $originales, 'contexto_aplicacion' => $captura,
            'componentes' => [], 'resumen_inicial' => ['familia' => $d['familia'], 'nombre' => $d['nombre'], 'edicion' => $d['version'],
                'estado' => $invalidos !== [] ? 'DATOS_INVALIDOS' : ($total === null ? 'PUNTUACION_INCOMPLETA' : 'PUNTUACION_DISPONIBLE'),
                'total_bruto' => $bruto, 'total' => $total, 'maximo' => 30, 'ajuste_educativo' => $ajuste, 'indice_memoria' => $mis,
                'ausentes' => $ausentes, 'invalidos' => $invalidos, 'no_aplicables' => $noAplicables, 'motivos' => $motivos, 'avisos' => $avisos],
            'diagnostico' => null, 'revision_profesional' => 'PENDIENTE',
            'limite' => 'Puntuación del protocolo identificado. No es una norma poblacional, un estado de dominio, C02, C03 ni un diagnóstico de DCL.'];
    }

    private function campo(string $label, int $maximo, bool $puntuable = true): array
    {
        return ['label' => $label, 'tipo' => 'ENTERO', 'maximo' => $maximo, 'puntuable' => $puntuable];
    }

    private function logico(string $label): array
    {
        return ['label' => $label, 'tipo' => 'LOGICO', 'maximo' => null, 'puntuable' => false];
    }
}
