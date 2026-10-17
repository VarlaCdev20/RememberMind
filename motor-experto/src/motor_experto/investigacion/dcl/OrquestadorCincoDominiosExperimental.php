<?php
declare(strict_types=1);

namespace MotorExperto\investigacion\dcl;

use DateTimeImmutable;
use DomainException;
use MotorExperto\adaptadores\entrada\AdaptadorHechosCOGMEM;
use MotorExperto\adaptadores\entrada\EjemploTecnicoCOGMEM;
use MotorExperto\aplicacion\casos_uso\MotorExperto;
use MotorExperto\dominio\inferencia\MemoriaTrabajo;
use MotorExperto\dominio\objetos_valor\ContextoEjecucion;
use MotorExperto\dominio\objetos_valor\FuenteBrutaCOGMEM;
use MotorExperto\investigacion\g09\CatalogoReglasG09Experimental;
use MotorExperto\investigacion\g09\EjecutorPerfilG09Experimental;

/**
 * Demostración sin uso clínico: mismo individuo/caso/corte para cinco dominios.
 * Memoria ejecuta D-131 sobre su versión VER_TEST; los demás G09-EXP mantienen
 * su versión propia. No se alteran paquetes/mapeos institucionales.
 * Petersen recibe U en los cinco dominios: jamás infiere A/P de resultados
 * descriptivos, de una regla que no cumplió o de una respuesta sintética.
 */
final class OrquestadorCincoDominiosExperimental
{
    public const VERSION_PERFIL = 'INTEGRA5-EXP-01';

    public function __construct(private readonly EjecutorPerfilG09Experimental $g09)
    {
    }

    public function ejecutar(array $contexto, array $tareas, string $literalMemoria): array
    {
        if (($contexto['uso'] ?? null) !== 'TEST_ONLY'
            || ($contexto['aislado'] ?? null) !== true
            || ($contexto['version'] ?? null) !== CatalogoReglasG09Experimental::VERSION) {
            throw new DomainException('Solo se admite el orquestador experimental aislado.');
        }
        $id = $contexto['evaluacion'] ?? null;
        $residente = $contexto['residente_ficticio'] ?? null;
        $corte = $contexto['fecha_corte'] ?? null;
        if (!is_string($id) || !str_starts_with($id, 'SIM-') || strlen($id) > 20
            || !is_string($residente) || !str_starts_with($residente, 'SINTETICO-')
            || strlen($residente) > 20 || !is_string($corte)
            || !preg_match('/^\d{4}-\d{2}-\d{2}$/D', $corte)
            || !in_array($literalMemoria, ['literal_A','literal_B','SIN_DATO'], true)) {
            throw new DomainException('Identidad, fecha o caso de Memoria TEST_ONLY inválidos.');
        }
        $fechaCorte = DateTimeImmutable::createFromFormat('!Y-m-d H:i:sP', $corte.' 23:59:59-04:00');
        if (!$fechaCorte || $fechaCorte->format('Y-m-d') !== $corte) {
            throw new DomainException('Fecha de corte inválida.');
        }

        $perfilG09 = $this->g09->evaluar($contexto, $tareas);
        $paqueteMem = EjemploTecnicoCOGMEM::paquete();
        $memoriaTrabajo = new MemoriaTrabajo(new ContextoEjecucion($id, $residente, $paqueteMem->version, $fechaCorte));

        if ($literalMemoria !== 'SIN_DATO') {
            $registro = 'SIM-MEM-APP';
            $fuente = new FuenteBrutaCOGMEM(
                'FU_INST','aplicaciones_instrumento','COMP_TEST',
                $registro, $residente,
                new DateTimeImmutable($corte.'T09:00:00-04:00'),
                $literalMemoria, $literalMemoria, 'SIM-PERSONAL',
                true, true,
                contexto: ['componente_instrumental_verificado' => true]
            );
            $e = (new AdaptadorHechosCOGMEM)->adaptar($paqueteMem, $memoriaTrabajo, $fuente, 'MAP_INS');
            $memoriaTrabajo->vincular('MEM', $e->id, 'INSTRUMENTAL');
        }
        $memoria = (new MotorExperto)->evaluarCOGMEM(
            $paqueteMem,$memoriaTrabajo,'MEM',EjemploTecnicoCOGMEM::contextoClinico()
        );
        if (($memoria['uso'] ?? null) !== 'PRUEBA_TECNICA') {
            throw new DomainException('Memoria no produjo salida técnica segregada.');
        }

        $dominios = [
            'COG-MEM' => [
                'motor' => 'D-131',
                'version' => $paqueteMem->version,
                'salida' => $memoria,
                'estado_dcl' => 'U',
                'razon_dcl' => 'MEMORIA_DESCRIPTIVA_NO_EQUIVALE_A_ESTADO_A_P',
            ],
        ];
        foreach (['COG-ATE','COG-EJE','COG-LEN','COG-VIS'] as $criterio) {
            if (!isset($perfilG09['perfil'][$criterio])) {
                throw new DomainException('Falta un criterio G09 en el perfil.');
            }
            $g = $perfilG09['perfil'][$criterio];
            if (($g['cotejo_nucleo_d130']['coincidencia'] ?? null) !== 'COINCIDE') {
                throw new DomainException('D-130 y G09 no concuerdan en '.$criterio);
            }
            $dominios[$criterio] = [
                'motor' => 'G09-EXP/D-130',
                'version' => CatalogoReglasG09Experimental::VERSION,
                'salida' => $g,
                'estado_dcl' => 'U',
                'razon_dcl' => 'G09_SINTETICO_NO_EQUIVALE_A_ESTADO_A_P',
            ];
        }
        $c01a05 = [];
        foreach (ClasificadorPetersenExperimental::CRITERIOS as $criterio) {
            $c01a05[$criterio] = ['estado' => 'U'];
        }
        $estadosDcl = [];
        foreach (ClasificadorPetersenExperimental::DOMINIOS as $criterio) {
            $estadosDcl[$criterio] = ['estado' => 'U'];
        }

        $petersen = (new ClasificadorPetersenExperimental)->clasificar([
            'uso' => 'TEST_ONLY',
            'aislado' => true,
            'id_evaluacion' => $id,
            'residente_ficticio' => $residente,
            'version' => ClasificadorPetersenExperimental::VERSION,
            'fecha_corte' => $corte,
            'criterios' => $c01a05,
            'dominios' => $estadosDcl,
        ]);
        if ($petersen['patron_candidato'] !== null
            || $petersen['estado'] !== 'ABSTENCION_INFORMACION_INSUFICIENTE') {
            throw new DomainException('El piloto inventó una clasificación sin referencias independientes.');
        }
        return [
            'uso' => 'PRUEBA_TECNICA_NO_CLINICA',
            'version_orquestacion' => self::VERSION_PERFIL,
            'evaluacion' => $id,
            'residente_ficticio' => $residente,
            'fecha_corte' => $corte,
            'versiones' => [
                'COG-MEM' => $paqueteMem->version,
                'G09' => CatalogoReglasG09Experimental::VERSION,
                'DCL' => ClasificadorPetersenExperimental::VERSION,
            ],
            'perfil' => $dominios,
            'clasificacion_dcl' => $petersen,
            'informe_dcl' => (new ConstructorInformePetersenExperimental)->construir($petersen),
            'limitacion' => 'Cinco dominios ejecutados en laboratorio; SIN mapeo A/P. Petersen se abstiene por diseño.',
        ];
    }
}
