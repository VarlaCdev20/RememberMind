<?php

namespace App\Backend\Modulos\SistemaExperto\Conocimiento;

use DomainException;

/** Instantánea por versión; las claves son las columnas reales D-123/D-137. */
final readonly class PaqueteConocimiento
{
    public const CLAVES = [
        'nodos_semanticos' => 'cod_nodo_semantico', 'relaciones_semanticas' => 'cod_relacion_semantica',
        'dominios_valores_expertos' => 'cod_dominio_valores', 'valores_semanticos' => 'cod_valor_semantico',
        'variables_expertas' => 'cod_variable_experta', 'fuentes_datos_expertas' => 'cod_fuente_dato_experta',
        'mapeos_variables_fuente' => 'cod_mapeo_variable_fuente', 'mapeos_valores_fuente' => 'cod_mapeo_valor_fuente',
        'criterios_dominios_resultado' => 'cod_criterio_dominio_resultado', 'reglas_expertas' => 'cod_regla_experta',
        'condiciones_regla_experta' => 'cod_condicion_regla', 'consecuencias_regla_experta' => 'cod_consecuencia_regla',
    ];

    private array $filas;

    public RedSemantica $red;

    public function __construct(public string $version, array $tablas, public array $contratosRelaciones, public bool $soloPruebasTecnicas, public ?string $tipoReglaResultado = null, public array $contratosExtraccion = [])
    {
        $filas = [];
        foreach (self::CLAVES as $tabla => $pk) {
            foreach ($tablas[$tabla] ?? [] as $fila) {
                if (! isset($fila[$pk]) || isset($filas[$tabla][$fila[$pk]])) {
                    throw new DomainException('Identidad ausente o duplicada en el paquete: '.$tabla);
                }
                $filas[$tabla][$fila[$pk]] = $fila;
            }
        }
        if (array_diff(array_keys($tablas), array_keys(self::CLAVES))) {
            throw new DomainException('Tabla no reconocida en el paquete de conocimiento.');
        }
        $this->filas = $filas;
        $this->red = new RedSemantica($version, $this->tabla('nodos_semanticos'), $this->tabla('relaciones_semanticas'), $contratosRelaciones);
        (new ValidadorPaqueteConocimiento)->validar($this);
    }

    public function tabla(string $tabla): array
    {
        return array_values($this->filas[$tabla] ?? []);
    }

    public function fila(string $tabla, string $id): array
    {
        return $this->filas[$tabla][$id] ?? throw new DomainException('Referencia ausente del paquete: '.$tabla);
    }

    public function versionValor(string $id): string
    {
        return $this->fila('dominios_valores_expertos', $this->fila('valores_semanticos', $id)['cod_dominio_valores'])['cod_version_modelo'];
    }

    public function dominioCriterio(string $criterio): array
    {
        $filas = array_values(array_filter($this->tabla('criterios_dominios_resultado'), fn ($r) => $r['cod_nodo_criterio'] === $criterio));
        if (count($filas) !== 1) {
            throw new DomainException('El criterio requiere un único dominio de resultado.');
        }

        return $filas[0];
    }

    public function variableParticipa(string $variable, string $criterio): bool
    {
        $nodo = $this->fila('variables_expertas', $variable)['cod_nodo_semantico'];
        foreach ($this->red->salientes($nodo) as $r) {
            if ($r['cod_nodo_destino'] !== $criterio || $r['estado'] !== 'ACTIVO') {
                continue;
            }
            foreach ($this->contratosRelaciones as $contrato) {
                if ($contrato['tipo_relacion'] === $r['tipo_relacion']
                    && $contrato['tipo_origen'] === $this->red->nodo($nodo)['tipo_nodo']
                    && $contrato['tipo_destino'] === $this->red->nodo($criterio)['tipo_nodo']
                    && in_array($r['modalidad_relacion'] ?? null, $contrato['modalidades'], true)
                    && ($contrato['habilita_participacion'] ?? false) === true) {
                    return true;
                }
            }
        }

        return false;
    }
}
