<?php

declare(strict_types=1);

namespace App\SistemaExperto\Infrastructure\Adapters\FuentesOperativas;

use App\SistemaExperto\Infrastructure\Adapters\MapeoFuentes\ExtractorVariableMapeada;
use RuntimeException;

/**
 * TÍTULO:
 * Adaptador experimental de funcionalidad instrumental.
 *
 * CAPA:
 * Infrastructure - Adapters.
 *
 * ¿PARA QUÉ SIRVE?
 * Recupera datos brutos autorizados sin interpretación clínica.
 *
 * ¿QUÉ INFORMACIÓN RECIBE?
 * Conexión técnica segregada, sin asumir instalada la extensión propuesta.
 *
 * ¿QUÉ HACE?
 * Comprueba la presencia física de la tabla y declara el contrato no habilitado.
 *
 * ¿QUÉ DEVUELVE?
 * Disponibilidad falsa y motivo explícito; nunca evidencia o acreditación C03.
 *
 * ¿QUÉ NO DEBE HACER?
 * No crea tablas, modelos, actividades, valores sustitutos ni interpreta BADL como IADL.
 */
final readonly class AdaptadorIadlExperimental
{
    /**
     * Informa la indisponibilidad sin suponer un esquema aún no habilitado.
     *
     * @param  string  $conexion  Destino técnico explícito.
     * @return array{disponible: false, motivo: string} Ausencia física o contrato aún no habilitado.
     *
     * @throws RuntimeException Por entorno/base no autorizados o fallo de lectura del esquema.
     */
    public function disponibilidad(string $conexion): array
    {
        $destino = ExtractorVariableMapeada::conexionSegura($conexion);
        $existe = $destino->getSchemaBuilder()->hasTable('controles_funcionales_iadl');

        return ['disponible' => false, 'motivo' => $existe
            ? 'La tabla IADL existe, pero este contrato experimental no tiene un esquema aprobado y habilitado para leerla.'
            : 'La fuente controles_funcionales_iadl no está instalada en el destino experimental.'];
    }

    /**
     * Falla de forma explícita en lugar de devolver evidencia ficticia.
     *
     * @param  string  $conexion  Destino experimental explícito.
     * @return never No existe un contrato habilitado de extracción IADL.
     *
     * @throws RuntimeException Siempre, con la razón de indisponibilidad.
     */
    public function recuperar(string $conexion): never
    {
        throw new RuntimeException($this->disponibilidad($conexion)['motivo']);
    }
}
