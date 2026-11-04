<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Aplicacion\Servicios;

use App\Compartido\Aplicacion\Contratos\ArchivosAplicacion;
use App\Models\DocumentoClinico;

/** Verifica bytes del original privado; una FK o título no prueban que el documento esté disponible. */
final readonly class VerificarOriginalDocumentalPetersen
{
    public function __construct(private ArchivosAplicacion $archivos) {}

    public function verificar(DocumentoClinico $documento): bool
    {
        if ($documento->estado !== 'VIGENTE' || ! $this->archivos->existe('local', $documento->ruta_archivo)) {
            return false;
        }
        $hash = $this->archivos->hash('local', $documento->ruta_archivo);

        return is_string($hash) && hash_equals($documento->hash_archivo, $hash);
    }
}
