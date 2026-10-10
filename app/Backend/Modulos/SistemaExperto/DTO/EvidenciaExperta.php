<?php

namespace App\Backend\Modulos\SistemaExperto\DTO;

final readonly class EvidenciaExperta
{
    public function __construct(
        public string $id,
        public ContextoEjecucion $ejecucion,
        public FuenteBrutaCOGMEM $fuente,
        public string $mapeoVariable,
        public ?string $mapeoValor,
        public string $variable,
        public ?string $valor,
        public string $representacion,
        public string $admisibilidad,
        public array $motivos,
    ) {}

    public function utilizable(): bool
    {
        return $this->valor !== null && $this->representacion === 'MAPEADO'
            && in_array($this->admisibilidad, ['ADMISIBLE', 'ADMISIBLE_CON_ADVERTENCIA'], true);
    }

    /** Identidad D-137: no depende del número de criterios donde participa. */
    public function claveAtomica(): string
    {
        return serialize([$this->ejecucion->evaluacion, $this->mapeoVariable, $this->fuente->registro]);
    }
}
