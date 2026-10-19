<?php

declare(strict_types=1);

namespace MotorExperto\dominio\petersen;

use DateTimeImmutable;
use DomainException;
use MotorExperto\soporte\conservacion\CapturaInstrumentalCanonica;

/**
 * COMPONENTE DEL SISTEMA EXPERTO: Procedencia original de Petersen institucional.
 * Identifica un registro clínico y su episodio explícito; nunca crea independencia por fecha.
 * Es un DTO en memoria, sin persistencia ni autorización para representar atributos en EAV.
 * La adquisición autorizada comprueba el registro, sus valores y el estado original antes de construirlo.
 */
final readonly class FuentePetersenInstitucional
{
    public function __construct(public string $tabla, public string $registro, public string $residente,
        public string $autor, public DateTimeImmutable $fecha, public ?string $episodio, public string $tipo,
        public bool $vigente, public array $datos = [])
    {
        if (! in_array($tabla, ['aplicaciones_instrumento', 'controles_cognitivos', 'controles_funcionales_iadl',
            'valoraciones_psicologicas', 'antecedentes_clinicos', 'diagnosticos', 'documentos_clinicos', 'valoraciones_petersen_originales'], true)) {
            throw new DomainException('La fuente Petersen debe identificar una tabla original admitida.');
        }
        foreach ([$registro, $residente, $autor] as $id) {
            if ($id === '' || strlen($id) > 20) {
                throw new DomainException('La fuente Petersen requiere identidad, residente y autor originales.');
            }
        }
        if (($episodio !== null && ($episodio === '' || strlen($episodio) > 40))
            || ! in_array($tipo, ['VALORACION_ORIGINAL', 'HISTORIA_PREVIA', 'CORROBORACION', 'MEDICION', 'IADL', 'DIFERENCIAL', 'CALIDAD'], true)) {
            throw new DomainException('Tipo o episodio original Petersen incompatible.');
        }
        $permitidos = match ($tipo) {
            'MEDICION' => ['dominio', 'captura_original'],
            'IADL' => ['actividad', 'oportunidad', 'desempeno', 'tipo_ayuda', 'causa_ayuda', 'nivel_previo', 'fuente'],
            'VALORACION_ORIGINAL' => [],
            default => ['descripcion'],
        };
        if (array_diff(array_keys($datos), $permitidos)) {
            throw new DomainException('La fuente incluye campos ajenos a su contrato original.');
        }
        foreach ($datos as $campo => $valor) {
            if ($tipo === 'MEDICION' && $campo === 'captura_original' && is_array($valor)) {
                CapturaInstrumentalCanonica::ordenar($valor);

                continue;
            }
            if ((! is_scalar($valor) && $valor !== null) || (is_float($valor) && ! is_finite($valor))) {
                throw new DomainException('Los valores originales de Petersen deben ser escalares finitos.');
            }
        }
    }

    public function identidad(): string
    {
        return $this->tabla.':'.$this->registro;
    }

    public function admisible(string $residente, DateTimeImmutable $corte): bool
    {
        return $this->residente === $residente && $this->vigente && $this->fecha <= $corte;
    }

    public function referencia(): array
    {
        return ['tabla' => $this->tabla, 'registro' => $this->registro, 'residente' => $this->residente, 'autor' => $this->autor,
            'fecha' => $this->fecha->format('Y-m-d\TH:i:s.uP'), 'episodio' => $this->episodio, 'tipo' => $this->tipo,
            'vigente' => $this->vigente, 'datos_originales' => $this->datos];
    }
}
