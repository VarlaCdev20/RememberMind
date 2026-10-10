<?php

namespace App\Backend\Modulos\SistemaExperto\Conocimiento;

/** Propuesta de representación observacional; no acredita una validación clínica. */
final class PaqueteInvestigadoCOGMEM
{
    public const VERSION = 'VER_MEM_INV_20261008';

    public const CODIGO = 'MEM-INV-20261008';

    public const FUENTES = [
        'DETeCD-ADRD' => 'https://pmc.ncbi.nlm.nih.gov/articles/PMC11772712/',
        'Mini-Cog puntuación' => 'https://mini-cog.com/scoring-the-mini-cog/',
        'Mini-Cog derechos' => 'https://mini-cog.com/contact/',
        'Barthel' => 'https://www.sralab.org/rehabilitation-measures/barthel-index',
    ];

    public const MAPEOS_PROPUESTOS = [
        'CONSERVADA' => 'SIN_DIFICULTAD_OBSERVADA',
        'ALTERACION_LEVE' => 'DIFICULTAD_OBSERVADA',
        'ALTERADA' => 'DIFICULTAD_OBSERVADA',
    ];

    public const LIMITES = 'Propuesta investigada del 08/10/2026. D-119/D-120 requieren validación profesional de los mapeos y oportunidad real de observación. Sin instrumento de memoria autorizado, reglas completas ni activación clínica.';

    public function codigo(string $prefijo, string $semantico): string
    {
        return $prefijo.'_'.substr(hash('sha256', self::VERSION.'|'.$semantico), 0, 16);
    }

    /** Filas normalizadas del esquema aprobado; ningún registro del residente forma parte del paquete. */
    public function tablas(string $autor, string $fecha): array
    {
        $t = array_fill_keys(array_keys(PaqueteConocimiento::CLAVES), []);
        $sello = ['estado' => 'INACTIVO', 'fecha_hora_creacion' => $fecha,
            'cod_usuario_creacion' => $autor, 'observacion' => self::LIMITES];
        $version = ['cod_version_modelo' => self::VERSION];
        $mem = $this->codigo('NS', 'COG-MEM');
        foreach (['COG-MEM' => ['Aprendizaje y memoria', 'CRITERIO'],
            'VAR-MEM-OBS-01' => ['Memoria reciente observada', 'VARIABLE'],
            'VAR-MEM-OBS-02' => ['Memoria remota observada', 'VARIABLE']] as $codigo => [$nombre, $tipo]) {
            $t['nodos_semanticos'][] = $sello + $version + ['cod_nodo_semantico' => $this->codigo('NS', $codigo),
                'codigo_semantico' => $codigo, 'nombre' => $nombre, 'tipo_nodo' => $tipo,
                'definicion' => $tipo === 'CRITERIO' ? 'Criterio COG-MEM de O.R.I.O.N.; observación distinta de resultado integrado.'
                    : 'Representación observacional de un control concreto, sin diagnóstico, severidad ni puntuación.'];
        }
        foreach (['DOM-MEM-OBS' => 'OBSERVACIONAL', 'DOM-RESULTADO-COG-MEM' => 'RESULTADO'] as $codigo => $tipo) {
            $t['dominios_valores_expertos'][] = $sello + $version + ['cod_dominio_valores' => $this->codigo('DV', $codigo),
                'codigo_dominio' => $codigo, 'nombre' => $codigo, 'tipo_dominio' => $tipo,
                'descripcion' => 'Separación observación/resultado de D-123/D-125; no habilita emisión de resultados.'];
        }
        $valores = ['SIN_DIFICULTAD_OBSERVADA' => ['DOM-MEM-OBS', 'No se documentó dificultad durante una oportunidad real de observación; no equivale a normalidad cognitiva.'],
            'DIFICULTAD_OBSERVADA' => ['DOM-MEM-OBS', 'Dificultad concreta observada; no determina severidad ni diagnóstico.'],
            'NO_DETERMINABLE' => ['DOM-MEM-OBS', 'Valoración intentada pero no resoluble; distinto de NULL. No se mapea automáticamente desde NO_VALORABLE.'],
            'DIFICULTAD_EVIDENCIADA' => ['DOM-RESULTADO-COG-MEM', 'Resultado integrado reservado a EV-CM-2 y reglas formalizadas.'],
            'SIN_DIFICULTAD_EVIDENCIADA' => ['DOM-RESULTADO-COG-MEM', 'Resultado integrado; no se deduce de una observación conservada aislada.'],
            'HALLAZGOS_MIXTOS' => ['DOM-RESULTADO-COG-MEM', 'Resultado reservado al resolvedor D-131 con independencia efectiva de soportes.']];
        foreach ($valores as $codigo => [$dominio, $definicion]) {
            $t['valores_semanticos'][] = $sello + ['cod_valor_semantico' => $this->codigo('VS', $codigo),
                'cod_dominio_valores' => $this->codigo('DV', $dominio), 'codigo_valor' => $codigo,
                'nombre' => $codigo, 'definicion_semantica' => $definicion, 'estado_aprobacion' => 'PENDIENTE'];
        }
        $fuente = $this->codigo('FD', 'SRC-CONTROL-COG');
        $t['fuentes_datos_expertas'][] = $sello + $version + ['cod_fuente_dato_experta' => $fuente,
            'codigo_fuente' => 'SRC-CONTROL-COG', 'nombre' => 'Control cognitivo vigente', 'tipo_fuente' => 'OPERATIVA',
            'tabla_raiz' => 'controles_cognitivos', 'campo_pk_raiz' => 'cod_control_cognitivo',
            'campo_residente' => 'cod_residente', 'campo_temporal' => 'fecha_hora', 'campo_personal' => 'cod_personal',
            'campo_estado' => 'estado', 'clave_adaptador' => 'COG_MEM_CONTROL'];
        foreach (['memoria_reciente' => 'VAR-MEM-OBS-01', 'memoria_remota' => 'VAR-MEM-OBS-02'] as $campo => $codigo) {
            $variable = $this->codigo('VE', $codigo);
            $mapa = $this->codigo('MV', $campo);
            $t['variables_expertas'][] = $sello + $version + ['cod_variable_experta' => $variable,
                'cod_nodo_semantico' => $this->codigo('NS', $codigo), 'cod_nodo_propietario_primario' => $mem,
                'cod_dominio_valores' => $this->codigo('DV', 'DOM-MEM-OBS'), 'tipo_semantico' => 'OBSERVACIONAL',
                'papel_inferencial' => $campo === 'memoria_reciente' ? 'PRIMARIO' : 'COMPLEMENTARIO'];
            $t['mapeos_variables_fuente'][] = $sello + $version + ['cod_mapeo_variable_fuente' => $mapa,
                'cod_variable_experta' => $variable, 'cod_fuente_dato_experta' => $fuente,
                'tipo_extraccion' => 'CAMPO_DIRECTO', 'campo_valor' => $campo, 'clave_selector' => null];
            foreach (self::MAPEOS_PROPUESTOS as $literal => $semantico) {
                $t['mapeos_valores_fuente'][] = $sello + ['cod_mapeo_valor_fuente' => $this->codigo('MF', $campo.'|'.$literal),
                    'cod_mapeo_variable_fuente' => $mapa, 'cod_valor_semantico' => $this->codigo('VS', $semantico),
                    'tipo_valor_fuente' => 'string', 'valor_fuente_exacto' => $literal,
                    'estado_aprobacion' => 'PENDIENTE', 'fecha_hora_vigencia_desde' => null, 'fecha_hora_vigencia_hasta' => null];
            }
        }
        $t['criterios_dominios_resultado'][] = $sello + ['cod_criterio_dominio_resultado' => $this->codigo('CD', 'COG-MEM'),
            'cod_nodo_criterio' => $mem, 'cod_dominio_valores' => $this->codigo('DV', 'DOM-RESULTADO-COG-MEM')];

        return $t;
    }

    public function paquete(string $autor, string $fecha): PaqueteConocimiento
    {
        return new PaqueteConocimiento(self::VERSION, $this->tablas($autor, $fecha), [],
            soloPruebasTecnicas: false, contratosExtraccion: ['CAMPO_DIRECTO' => 'CAMPO_DIRECTO']);
    }
}
