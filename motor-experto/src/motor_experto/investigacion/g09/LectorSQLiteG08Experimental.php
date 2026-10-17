<?php
declare(strict_types=1);

namespace MotorExperto\investigacion\g09;

use DomainException;
use PDO;

/**
 * Lee capturas instrumentales de una base SQLite EN MEMORIA, exclusivamente
 * con identificadores SINTETICO-/SIM-. No permite fichero, PDO de Laravel,
 * conexiones asistenciales ni instrumentos no simulados.
 *
 * Proyecta nombres operativos reales: instrumentos, aplicaciones_instrumento,
 * preguntas_instrumento, respuestas_instrumento y opciones_pregunta.
 */
final class LectorSQLiteG08Experimental
{
    public function __construct(private readonly PDO $db)
    {
        if ($db->getAttribute(PDO::ATTR_DRIVER_NAME) !== 'sqlite') {
            throw new DomainException('G08: se exige SQLite descartable, nunca conexión asistencial.');
        }
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->verificarMemoria();
    }

    /** Revalidar en cada lectura también evita adjuntar después una BDD en disco. */
    private function verificarMemoria(): void
    {
        foreach ($this->db->query('PRAGMA database_list')->fetchAll(PDO::FETCH_ASSOC) as $base) {
            if ((string)($base['file'] ?? '') !== '') {
                throw new DomainException('G08: no se permite base SQLite guardada en disco.');
            }
        }
    }

    public function leer(string $codAplicacion, string $residente): array
    {
        $this->verificarMemoria();
        if (!str_starts_with($codAplicacion, 'SIM-')
            || !str_starts_with($residente, 'SINTETICO-')) {
            throw new DomainException('G08: identificadores fuera del conjunto ficticio.');
        }
        $app = $this->uno(
            'SELECT * FROM aplicaciones_instrumento WHERE cod_aplicacion = :codigo AND cod_residente = :residente',
            ['codigo' => $codAplicacion, 'residente' => $residente]
        );
        if ($app === null) {
            throw new DomainException('G08: aplicación ficticia no encontrada para este residente.');
        }
        $ins = $this->uno(
            'SELECT * FROM instrumentos WHERE cod_instrumento = :codigo',
            ['codigo' => $app['cod_instrumento']]
        );
        if ($ins === null || !str_starts_with((string)($ins['cod_instrumento'] ?? ''), 'SIM-')) {
            throw new DomainException('G08: instrumento simulado ausente o inválido.');
        }
        $preguntas = [];
        foreach ($this->varios(
            'SELECT * FROM preguntas_instrumento WHERE cod_instrumento = :codigo',
            ['codigo' => $ins['cod_instrumento']]
        ) as $fila) {
            $preguntas[$fila['cod_pregunta']] = $fila;
        }
        $opciones = [];
        foreach ($this->varios(
            'SELECT o.* FROM opciones_pregunta o
             JOIN preguntas_instrumento p ON p.cod_pregunta = o.cod_pregunta
             WHERE p.cod_instrumento = :codigo',
            ['codigo' => $ins['cod_instrumento']]
        ) as $fila) {
            $opciones[$fila['cod_opcion']] = $fila;
        }
        $respuestas = $this->varios(
            'SELECT * FROM respuestas_instrumento WHERE cod_aplicacion = :codigo',
            ['codigo' => $codAplicacion]
        );

        return [
            'fixture_sintetico' => true,
            'aplicacion' => $app,
            'instrumento' => $ins,
            'preguntas' => $preguntas,
            'opciones' => $opciones,
            'respuestas' => $respuestas,
        ];
    }

    private function uno(string $sql, array $parametros): ?array
    {
        $rows = $this->varios($sql, $parametros);
        if (count($rows) > 1) {
            throw new DomainException('G08: cardinalidad de fuente ambigua.');
        }
        return $rows[0] ?? null;
    }

    private function varios(string $sql, array $parametros): array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($parametros);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
