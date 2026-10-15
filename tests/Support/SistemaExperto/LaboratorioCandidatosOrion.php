<?php

namespace Tests\Support\SistemaExperto;

use DomainException;

/** SOLO autoload-dev: sin BDD, rutas, residentes, instrumentos o efectos laterales. */
final class LaboratorioCandidatosOrion
{
    public const VERSION = 'LAB-G09-20261015';

    private const CANDIDATOS = [
        'ATE-S01'=>['COG-ATE','ATE-R-SOSTENIMIENTO'],
        'ATE-S02'=>['COG-ATE','ATE-R-SELECCION'],
        'ATE-S03'=>['COG-ATE','ATE-R-DISTRIBUCION'],
        'EJE-S01'=>['COG-EJE','EJE-R-INHIBICION'],
        'EJE-S02'=>['COG-EJE','EJE-R-FLEXIBILIDAD'],
        'EJE-S03'=>['COG-EJE','EJE-R-ACTUALIZACION'],
        'EJE-S04'=>['COG-EJE','EJE-R-PLANIFICACION'],
        'EJE-S05'=>['COG-EJE','EJE-R-RAZONAMIENTO'],
        'LEN-S01'=>['COG-LEN','LEN-R-DENOMINACION'],
        'LEN-S02'=>['COG-LEN','LEN-R-FLUIDEZ'],
        'LEN-S03'=>['COG-LEN','LEN-R-COMPRENSION'],
        'LEN-S04'=>['COG-LEN','LEN-R-REPETICION'],
        'LEN-S05'=>['COG-LEN','LEN-R-DISCURSO'],
        'VIS-S01'=>['COG-VIS','VIS-R-PERCEPCION'],
        'VIS-S02'=>['COG-VIS','VIS-R-CONSTRUCCION'],
    ];

    public function inventario(): array
    {
        $items = [];
        foreach (self::CANDIDATOS as $sub => [$dominio,$etiqueta]) {
            $items[] = [
                'criterio'=>$dominio,'subcomponente'=>$sub,
                'referencia_g09_no_ejecutable'=>$etiqueta,
                'antecedentes_sinteticos_AND'=>['LAB-CTX-VALIDO','LAB-COMP-'.$sub],
                'estado'=>'SOLO_PRUEBA_SINTETICA',
            ];
        }
        return $items;
    }

    public function ejecutar(array $caso): array
    {
        $this->campos($caso, ['id_caso','actor_sintetico','origen',
            'version_laboratorio','subcomponente','compuerta_simulada','hechos']);
        if ($caso['origen'] !== 'SINTETICO' || $caso['version_laboratorio'] !== self::VERSION
            || ! $this->id($caso['id_caso'],'LAB-CASO-')
            || ! $this->id($caso['actor_sintetico'],'SYN-ACTOR-')) {
            throw new DomainException('Caso no sintético o versión ajena.');
        }
        $sub = $caso['subcomponente'];
        if (! is_string($sub) || ! isset(self::CANDIDATOS[$sub])) {
            throw new DomainException('Subcomponente no declarado.');
        }
        if (! in_array($caso['compuerta_simulada'],['LAB-EV-0','LAB-EV-1','LAB-EV-2'],true)) {
            throw new DomainException('Compuerta no simulada.');
        }
        if (! is_array($caso['hechos']) || count($caso['hechos']) > 64) {
            throw new DomainException('Hechos fuera del contrato de laboratorio.');
        }
        $esperados=['LAB-CTX-VALIDO','LAB-COMP-'.$sub];
        $porCodigo=[]; $ids=[]; $episodios=[];
        foreach ($caso['hechos'] as $hecho) {
            if (! is_array($hecho)) {
                throw new DomainException('Hecho no estructurado.');
            }
            $this->campos($hecho,['id','codigo','fuente','episodio']);
            if (! is_string($hecho['codigo']) || ! in_array($hecho['codigo'],$esperados,true)
                || ! $this->id($hecho['id'],'SYN-EV-')
                || ! $this->id($hecho['fuente'],'SYN-SRC-')
                || ! $this->id($hecho['episodio'],'SYN-EP-')) {
                throw new DomainException('Procedencia no sintética.');
            }
            $clave=$hecho['codigo'].'|'.$hecho['fuente'].'|'.$hecho['episodio'];
            if (isset($ids[$hecho['id']]) || isset($episodios[$clave])) {
                throw new DomainException('Evidencia o episodio duplicados.');
            }
            $ids[$hecho['id']]=true;
            $episodios[$clave]=true;
            $porCodigo[$hecho['codigo']][]=[
                'id'=>$hecho['id'],'fuente'=>$hecho['fuente'],'episodio'=>$hecho['episodio'],
            ];
        }
        $condiciones=[];$soportes=[];
        foreach ($esperados as $codigo) {
            $filas=$porCodigo[$codigo] ?? [];
            usort($filas,static fn (array $a,array $b)=>strcmp($a['id'],$b['id']));
            $condiciones[]=[
                'codigo'=>$codigo,'cumple_presencia_sintetica'=>(bool)$filas,
                'fuentes_y_episodios'=>$filas,
            ];
            $soportes[$codigo]=array_column($filas,'id');
        }
        $apta=$caso['compuerta_simulada']==='LAB-EV-2';
        $ok=$apta && ! in_array(false,array_column($condiciones,'cumple_presencia_sintetica'),true);
        $estado=! $apta ? 'NO_EJECUTADO_POR_COMPUERTA_SINTETICA'
            : ($ok ? 'COINCIDENCIA_TECNICA_SINTETICA' : 'SIN_COINCIDENCIA_TECNICA');
        [$dominio,$etiqueta]=self::CANDIDATOS[$sub];
        return [
            'uso'=>'PRUEBA_SINTETICA_EXCLUSIVA','estado_tecnico'=>$estado,
            'resultado_clinico'=>null,'activacion_clinica'=>false,'persistencia'=>'NINGUNA',
            'traza'=>[
                'id_caso'=>$caso['id_caso'],'actor_sintetico'=>$caso['actor_sintetico'],
                'version_laboratorio'=>self::VERSION,'criterio'=>$dominio,
                'subcomponente'=>$sub,'referencia_g09_no_ejecutable'=>$etiqueta,
                'compuerta_simulada'=>$caso['compuerta_simulada'],
                'condiciones_AND'=>$condiciones,'soportes_por_condicion'=>$soportes,
            ],
        ];
    }

    private function campos(array $dato,array $claves): void
    {
        $actual=array_keys($dato);sort($actual);sort($claves);
        if ($actual!==$claves) {
            throw new DomainException('Campos ajenos a las pruebas sintéticas.');
        }
    }

    private function id(mixed $valor,string $prefijo): bool
    {
        return is_string($valor) && strlen($valor)<=80
            && preg_match('/^'.preg_quote($prefijo,'/').'[A-Z0-9][A-Z0-9-]*$/D',$valor)===1;
    }
}
