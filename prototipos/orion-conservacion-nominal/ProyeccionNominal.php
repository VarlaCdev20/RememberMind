<?php

declare(strict_types=1);

namespace OrionNominal;

use DomainException;
use MotorExperto\soporte\conservacion\CodecInstantanea;
use PDO;

/** Escritor/lector del contrato cerrado. Ninguna fila contiene nombre de atributo/valor ni JSON clínico. */
final class ProyeccionNominal
{
    private array $filas = [];

    private array $consumidas = [];

    public function __construct(private PDO $db, private array $mapa) {}

    public function proyectar(array $entrada, string $evaluacion): array
    {
        $this->filas = [];
        $this->fila($this->mapa['raiz'], $entrada, $evaluacion, null, 0, null, null);

        return $this->filas;
    }

    private function fila(string $modelo, mixed $valor, string $evaluacion, ?string $padre, int $posicion_coleccion,
        string|int|null $clave, ?string $rol): void
    {
        $def = $this->mapa['modelos'][$modelo];
        $id = bin2hex(random_bytes(10));
        $fila = ['cod_fila' => $id, 'cod_evaluacion_experta' => $evaluacion];
        if ($padre !== null) {
            $fila += [$def['fk_propietario'] => $padre, 'posicion_coleccion' => $posicion_coleccion, 'rol_coleccion' => $rol,
                'clave_tipo' => is_int($clave) ? 'INT' : ($clave === null ? 'NULL' : 'STRING'),
                'clave_texto' => $clave === null ? null : (string) $clave];
        }
        $pendientes = [];
        $this->campos($def['estructura'], $valor, true, $modelo, '', $fila, $pendientes);
        $this->filas[] = ['tabla' => $def['tabla'], 'fila' => $fila];
        foreach ($pendientes as [$hijo, $elementos, $modo, $identidad]) {
            $posicion = 0;
            foreach ($elementos as $k => $v) {
                $this->fila($hijo, $v, $evaluacion, $id, $posicion++, $modo === 'mapa' ? $k : null, $identidad);
            }
        }
    }

    private function campos(array $nodo, mixed $valor, bool $presente, string $modelo, string $ruta,
        array &$fila, array &$pendientes): void
    {
        $col = $nodo['columna'] ?? ContratoNominal::columna($ruta, $modelo);
        if ($nodo['clase'] === 'escalar') {
            $tipos = $nodo['tipos'] ?? ContratoNominal::tipos($modelo, $ruta);
            $tipo = match (true) {
                $valor === null => 'NULL', is_string($valor) => 'STRING', is_int($valor) => 'INT',
                is_float($valor) => 'FLOAT', is_bool($valor) => 'BOOL', default => 'INVALIDO'
            };
            if ($tipo !== 'NULL' && ! in_array($tipo, $tipos, true)) {
                throw new DomainException("Tipo fuera del contrato nominal: $modelo$ruta ($tipo).");
            }
            if (($tipo === 'FLOAT' && ! is_finite($valor)) || ($tipo === 'STRING'
                && (preg_match('//u', $valor) !== 1 || str_contains($valor, "\0")))) {
                throw new DomainException("Valor no conservable: $modelo$ruta.");
            }
            $fila[$col.'__presente'] = $presente;
            if (count($tipos) === 1) {
                $fila[$col] = $valor;
                if ($tipos[0] === 'FLOAT') {
                    $fila[$col.'__cero_negativo'] = $tipo === 'FLOAT' && $valor === 0.0 && str_starts_with((string) $valor, '-');
                }
            } else {
                $fila[$col.'__tipo'] = $tipo;
                foreach ($tipos as $permitido) {
                    $fila[$col.'_'.EsquemaNominal::sufijo($permitido)] = $tipo === $permitido ? $valor : null;
                }
                if (in_array('FLOAT', $tipos, true)) {
                    $fila[$col.'__cero_negativo'] = $tipo === 'FLOAT' && $valor === 0.0 && str_starts_with((string) $valor, '-');
                }
            }

            return;
        }
        $fila[$col.'__estado'] = ! $presente ? 'AUSENTE' : ($valor === null ? 'NULL' : 'PRESENTE');
        if ($presente && $valor !== null && ! is_array($valor)) {
            throw new DomainException("Forma incompatible: $modelo$ruta.");
        }
        if ($nodo['clase'] === 'registro') {
            $pendientes[] = [$nodo['modelo'], $presente && $valor !== null ? [$valor] : [], 'lista', $nodo['identidad']];
        } elseif ($nodo['clase'] === 'coleccion') {
            $lista = $valor ?? [];
            if ($lista !== [] && array_is_list($lista) !== ($nodo['modo'] === 'lista')) {
                throw new DomainException("Forma de colección incompatible: $modelo$ruta.");
            }
            $fila[$col.'__cantidad'] = count($lista);
            $pendientes[] = [$nodo['modelo'], $lista, $nodo['modo'], $nodo['identidad']];
        } else {
            if (is_array($valor) && array_diff(array_keys($valor), array_keys($nodo['campos']))) {
                throw new DomainException("Campo no definido en $modelo$ruta.");
            }
            foreach ($nodo['campos'] as $k => $hijo) {
                $existe = $presente && is_array($valor) && array_key_exists($k, $valor);
                $this->campos($hijo, $existe ? $valor[$k] : null, $existe, $modelo, $ruta.'/'.$k, $fila, $pendientes);
            }
        }
    }

    public function insertar(array $filas): void
    {
        foreach ($filas as $item) {
            $fila = $item['fila'];
            $q = $this->db->prepare('INSERT INTO '.$item['tabla'].'('.implode(',', array_keys($fila)).') VALUES ('.implode(',', array_fill(0, count($fila), '?')).')');
            foreach (array_values($fila) as $i => $valor) {
                if (is_float($valor)) {
                    $q->bindValue($i + 1, str_replace(',', '.', sprintf('%.17g', $valor)), PDO::PARAM_STR);
                } else {
                    $q->bindValue($i + 1, $valor, is_bool($valor) ? PDO::PARAM_BOOL : ($valor === null ? PDO::PARAM_NULL : (is_int($valor) ? PDO::PARAM_INT : PDO::PARAM_STR)));
                }
            }
            $q->execute();
        }
    }

    public function recuperar(string $evaluacion): array
    {
        $this->filas = [];
        $this->consumidas = [];
        foreach (array_unique(array_column($this->mapa['modelos'], 'tabla')) as $tabla) {
            $posicion_coleccion = $tabla === $this->mapa['modelos'][$this->mapa['raiz']]['tabla'] ? '' : ' ORDER BY posicion_coleccion,cod_fila';
            $q = $this->db->prepare("SELECT * FROM $tabla WHERE cod_evaluacion_experta=?$posicion_coleccion");
            $q->execute([$evaluacion]);
            $this->filas[$tabla] = $q->fetchAll(PDO::FETCH_ASSOC);
        }
        $raices = $this->filas[$this->mapa['modelos'][$this->mapa['raiz']]['tabla']];
        if (count($raices) !== 1) {
            throw new DomainException('La evaluación nominal no tiene una raíz íntegra.');
        }
        $entrada = $this->leerFila($raices[0], $this->mapa['raiz']);
        foreach ($this->filas as $tabla => $filas) {
            foreach ($filas as $fila) {
                if (! isset($this->consumidas[$tabla][$fila['cod_fila']])) {
                    throw new DomainException('La evaluación conserva una fila ajena a sus colecciones: '.$tabla);
                }
            }
        }

        return $entrada;
    }

    private function leerFila(array $fila, string $modelo): mixed
    {
        $def = $this->mapa['modelos'][$modelo];
        $valor = $this->leer($def['estructura'], $fila, $modelo, '');
        $esperada = $pendientes = [];
        $this->campos($def['estructura'], $valor, true, $modelo, '', $esperada, $pendientes);
        foreach ($esperada as $col => $dato) {
            $original = $fila[$col];
            if (is_bool($dato)) {
                $original = CodecInstantanea::booleano($original);
            } elseif (is_int($dato) && filter_var($original, FILTER_VALIDATE_INT) !== false) {
                $original = (int) $original;
            } elseif (is_float($dato)) {
                $original = (float) $original;
                if ($dato === 0.0 && $original === 0.0) {
                    // El signo se verifica en la columna explícita correspondiente.
                    $original = $dato;
                }
            }
            if ($original !== $dato) {
                throw new DomainException('Columna oculta, ausente o mal tipada en '.$def['tabla'].'.'.$col);
            }
        }
        $this->consumidas[$def['tabla']][$fila['cod_fila']] = true;

        return $valor;
    }

    private function leer(array $nodo, array $fila, string $modelo, string $ruta): mixed
    {
        $col = $nodo['columna'] ?? ContratoNominal::columna($ruta, $modelo);
        if ($nodo['clase'] === 'escalar') {
            $tipos = $nodo['tipos'] ?? ContratoNominal::tipos($modelo, $ruta);
            $tipo = count($tipos) === 1 ? ($fila[$col] === null ? 'NULL' : $tipos[0]) : $fila[$col.'__tipo'];
            if ($tipo !== 'NULL' && ! in_array($tipo, $tipos, true)) {
                throw new DomainException('Discriminador nominal incompatible.');
            }
            $valor = $tipo === 'NULL' ? null : $fila[count($tipos) === 1 ? $col : $col.'_'.EsquemaNominal::sufijo($tipo)];

            return match ($tipo) {
                'NULL' => null,
                'STRING' => is_string($valor) ? $valor : throw new DomainException('Texto nominal incompatible.'),
                'BOOL' => CodecInstantanea::booleano($valor),
                'INT' => filter_var($valor, FILTER_VALIDATE_INT) !== false ? (int) $valor : throw new DomainException('Entero nominal incompatible.'),
                'FLOAT' => CodecInstantanea::booleano($fila[$col.'__cero_negativo']) ? -0.0 : (float) $valor,
            };
        }
        if ($fila[$col.'__estado'] !== 'PRESENTE') {
            return null;
        }
        if (in_array($nodo['clase'], ['registro', 'coleccion'], true)) {
            $hijo = $this->mapa['modelos'][$nodo['modelo']];
            $hijas = array_values(array_filter($this->filas[$hijo['tabla']], static fn ($r) => $r[$hijo['fk_propietario']] === $fila['cod_fila'] && $r['rol_coleccion'] === $nodo['identidad']));
            if (count($hijas) !== ($nodo['clase'] === 'registro' ? 1 : (int) $fila[$col.'__cantidad'])) {
                throw new DomainException("Colección nominal incompleta: $modelo$ruta.");
            }
            $salida = [];
            foreach ($hijas as $i => $hija) {
                if ((int) $hija['posicion_coleccion'] !== $i) {
                    throw new DomainException('Orden nominal discontinuo.');
                }
                $valor = $this->leerFila($hija, $nodo['modelo']);
                if ($nodo['clase'] === 'coleccion' && $nodo['modo'] === 'mapa') {
                    if (! in_array($hija['clave_tipo'], ['INT', 'STRING'], true)
                        || ($hija['clave_tipo'] === 'INT' && (filter_var($hija['clave_texto'], FILTER_VALIDATE_INT) === false
                            || (string) (int) $hija['clave_texto'] !== $hija['clave_texto']))
                        || ($hija['clave_tipo'] === 'STRING' && ! is_string(array_key_first([$hija['clave_texto'] => true])))) {
                        throw new DomainException('Tipo o representación de clave nominal incompatible.');
                    }
                    $clave = $hija['clave_tipo'] === 'INT' ? (int) $hija['clave_texto'] : $hija['clave_texto'];
                    if (array_key_exists($clave, $salida)) {
                        throw new DomainException('Clave nominal duplicada.');
                    }
                    $salida[$clave] = $valor;
                } else {
                    if ($hija['clave_tipo'] !== 'NULL' || $hija['clave_texto'] !== null) {
                        throw new DomainException('Una lista no puede ocultar una clave de mapa.');
                    }
                    $salida[] = $valor;
                }
            }

            return $nodo['clase'] === 'registro' ? $salida[0] : $salida;
        }
        $salida = [];
        foreach ($nodo['campos'] as $k => $hijo) {
            $campo = $hijo['columna'] ?? ContratoNominal::columna($ruta.'/'.$k, $modelo);
            $existe = $hijo['clase'] === 'escalar' ? CodecInstantanea::booleano($fila[$campo.'__presente']) : $fila[$campo.'__estado'] !== 'AUSENTE';
            if ($existe) {
                $salida[$k] = $this->leer($hijo, $fila, $modelo, $ruta.'/'.$k);
            }
        }

        return $salida;
    }
}
