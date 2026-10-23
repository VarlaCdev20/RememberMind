<?php

declare(strict_types=1);

namespace OrionFase02;

use DomainException;
use MotorExperto\soporte\conservacion\CodecInstantanea;
use PDO;

/** Intérprete de un mapa NOMINADO cerrado y versionado. No genera esquema durante guardar/leer. */
final class ProyeccionTipada
{
    private array $filas = [];

    public function __construct(private PDO $db, private array $mapa, private string $prefijoTabla = 'orion_f02.')
    {
        if ($prefijoTabla !== '' && ! preg_match('/^[a-z_][a-z0-9_]*\.$/D', $prefijoTabla)) {
            throw new DomainException('Esquema SQL de ensayo inválido.');
        }
    }

    public function proyectar(array $entrada, string $evaluacion): array
    {
        $this->filas = [];
        $this->objeto($this->mapa['raiz'], $entrada, $evaluacion, $evaluacion, null, 0, null);

        return $this->filas;
    }

    private function objeto(string $modelo, mixed $valor, string $evaluacion, string $id, ?string $padre, int $orden, string|int|null $clave, ?string $coleccion = null): void
    {
        $m = $this->mapa['modelos'][$modelo];
        $fila = ['cod_fila' => $id, 'cod_evaluacion_experta' => $evaluacion];
        if ($padre !== null) {
            $fila += ['cod_propietario' => $padre, 'orden' => $orden, 'coleccion' => $coleccion,
                'clave_tipo' => is_int($clave) ? 'INT' : ($clave === null ? 'NULL' : 'STRING'), 'clave_texto' => $clave === null ? null : (string) $clave];
        }
        $pendientes = [];
        $this->campos($m['estructura'], $valor, true, '', $fila, $pendientes);
        $this->filas[] = ['tabla' => $m['tabla'], 'fila' => $fila];
        foreach ($pendientes as [$hijo, $lista, $tipo, $identidad]) {
            $posicion = 0;
            foreach ($lista as $k => $v) {
                $this->objeto($hijo, $v, $evaluacion, bin2hex(random_bytes(10)), $id, $posicion++, $tipo === 'mapa' ? $k : null, $identidad);
            }
        }
    }

    private function campos(array $nodo, mixed $v, bool $presente, string $prefijo, array &$fila, array &$pendientes): void
    {
        $p = $this->mapa['columnas'][$prefijo];
        if ($nodo['clase'] === 'escalar') {
            if ($presente && $v !== null && ! is_scalar($v)) {
                throw new DomainException('Tipo no representable en campo nominal: '.$prefijo);
            }
            $fila[$p.'_presente'] = $presente;
            $fila += CodecInstantanea::escalar($p, $presente ? $v : null);

            return;
        }
        $fila[$p.'_estado'] = ! $presente ? 'AUSENTE' : ($v === null ? 'NULL' : 'PRESENTE');
        if ($presente && $v !== null && ! is_array($v)) {
            throw new DomainException('Forma no representable: '.$prefijo);
        }
        if ($nodo['clase'] === 'coleccion') {
            $lista = $presente && $v !== null ? $v : [];
            if ($lista !== [] && ($nodo['modo'] === 'lista') !== array_is_list($lista)) {
                throw new DomainException('Lista/mapa incompatible: '.$prefijo);
            }
            $fila[$p.'_cantidad'] = count($lista);
            $pendientes[] = [$nodo['modelo'], $lista, $nodo['modo'], $nodo['identidad']];

            return;
        }
        if (is_array($v) && array_diff(array_keys($v), array_keys($nodo['campos']))) {
            throw new DomainException('Campo no representable: '.$prefijo.':'.implode(',', array_diff(array_keys($v), array_keys($nodo['campos']))));
        }
        foreach ($nodo['campos'] as $campo => $hijo) {
            $existe = $presente && is_array($v) && array_key_exists($campo, $v);
            $this->campos($hijo, $existe ? $v[$campo] : null, $existe, $prefijo.'/'.$campo, $fila, $pendientes);
        }
    }

    public function insertar(array $filas): void
    {
        foreach ($filas as $item) {
            $f = $item['fila'];
            $columnas = array_keys($f);
            $s = $this->db->prepare('INSERT INTO '.$this->prefijoTabla.$item['tabla'].' ('.implode(',', $columnas).') VALUES ('.implode(',', array_fill(0, count($f), '?')).')');
            foreach (array_values($f) as $n => $valor) {
                $s->bindValue($n + 1, $valor, is_bool($valor) ? PDO::PARAM_BOOL : ($valor === null ? PDO::PARAM_NULL : (is_int($valor) ? PDO::PARAM_INT : PDO::PARAM_STR)));
            }
            $s->execute();
        }
    }

    public function recuperar(string $evaluacion): array
    {
        $this->filas = [];
        foreach ($this->mapa['modelos'] as $id => $m) {
            $orden = $id === $this->mapa['raiz'] ? '' : ' ORDER BY orden,cod_fila';
            $s = $this->db->prepare('SELECT * FROM '.$this->prefijoTabla.$m['tabla'].' WHERE cod_evaluacion_experta=?'.$orden);
            $s->execute([$evaluacion]);
            $this->filas[$id] = $s->fetchAll(PDO::FETCH_ASSOC);
        }
        if (count($this->filas[$this->mapa['raiz']]) !== 1) {
            throw new DomainException('Cabecera de evaluación inexistente o duplicada.');
        }

        return $this->leer($this->mapa['modelos'][$this->mapa['raiz']]['estructura'], $this->filas[$this->mapa['raiz']][0], '');
    }

    private function leer(array $nodo, array $fila, string $prefijo): mixed
    {
        $p = $this->mapa['columnas'][$prefijo];
        if ($nodo['clase'] === 'escalar') {
            return CodecInstantanea::leerEscalar($p, (object) $fila);
        }
        if ($fila[$p.'_estado'] !== 'PRESENTE') {
            return null;
        }
        if ($nodo['clase'] === 'coleccion') {
            $hijas = array_values(array_filter($this->filas[$nodo['modelo']], fn ($r) => $r['cod_propietario'] === $fila['cod_fila'] && $r['coleccion'] === $nodo['identidad']));
            if (count($hijas) !== (int) $fila[$p.'_cantidad']) {
                throw new DomainException('Colección incompleta: '.$prefijo);
            }
            $r = [];
            foreach ($hijas as $i => $hija) {
                if ((int) $hija['orden'] !== $i) {
                    throw new DomainException('Orden de colección discontinuo.');
                }
                $valor = $this->leer($this->mapa['modelos'][$nodo['modelo']]['estructura'], $hija, '');
                if ($nodo['modo'] === 'lista') {
                    $r[] = $valor;
                } else {
                    $clave = $hija['clave_tipo'] === 'INT' ? (int) $hija['clave_texto'] : $hija['clave_texto'];
                    if (array_key_exists($clave, $r)) {
                        throw new DomainException('Clave repetida en mapa.');
                    }
                    $r[$clave] = $valor;
                }
            }

            return $r;
        }
        $r = [];
        foreach ($nodo['campos'] as $campo => $hijo) {
            $col = $this->mapa['columnas'][$prefijo.'/'.$campo];
            $existe = $hijo['clase'] === 'escalar' ? CodecInstantanea::booleano($fila[$col.'_presente']) : $fila[$col.'_estado'] !== 'AUSENTE';
            if ($existe) {
                $r[$campo] = $this->leer($hijo, $fila, $prefijo.'/'.$campo);
            }
        }

        return $r;
    }
}
