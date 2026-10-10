<?php

namespace App\Backend\Modulos\SistemaExperto\DTO;

use DateTimeImmutable;

/** Bruto y tipado separados. Procedencia verificada por el adaptador, nunca por HTTP. */
final readonly class FuenteBrutaCOGMEM
{
    public string $tipoOriginal;

    public function __construct(
        public string $fuente,
        public string $tabla,
        public string $campo,
        public string $registro,
        public string $residente,
        public ?DateTimeImmutable $fecha,
        public mixed $valorOriginal,
        public mixed $valorTipado,
        public ?string $personal,
        public bool $procedenciaVerificada = false,
        public bool $estadoUtilizable = false,
        public ?string $atencion = null,
        public ?string $jornada = null,
        public array $contexto = [],
    ) {
        $this->tipoOriginal = get_debug_type($valorOriginal);
    }
}
