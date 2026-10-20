<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Conservacion;

use DomainException;
use MotorExperto\soporte\conservacion\ReleaseMotorInstrumental;

/** Retiene únicamente software verificado en un directorio privado y nunca datos clínicos. */
final readonly class AlmacenReleasesMotor
{
    public function __construct(private string $directorio) {}

    public function retener(string $origen, string $huella): string
    {
        $this->validarHuella($huella);
        $verificador = new ReleaseMotorInstrumental;
        $verificador->verificarEjecutorActual($origen, $huella);
        $directorio = $this->directorio(true);
        $candado = @fopen($directorio.'/.retencion.lock', 'c+b');
        if ($candado === false || ! flock($candado, LOCK_EX)) {
            if (is_resource($candado)) {
                fclose($candado);
            }
            throw new DomainException('No se pudo bloquear la retención del release.');
        }

        try {
            $destino = $directorio.'/'.$huella.'.zip';
            if (is_file($destino) && ! is_link($destino)) {
                $verificador->verificar($destino, $huella);

                return $destino;
            }
            if (file_exists($destino) || is_link($destino)) {
                throw new DomainException('El destino del release no es un archivo regular.');
            }
            $temporal = @tempnam($directorio, 'release-');
            if ($temporal === false) {
                throw new DomainException('No se pudo preparar la copia privada del release.');
            }
            try {
                if (! @copy($origen, $temporal)) {
                    throw new DomainException('No se pudo copiar el release verificado.');
                }
                $verificador->verificar($temporal, $huella);
                if (! @link($temporal, $destino)) {
                    throw new DomainException('No se pudo retener el release sin sobrescribirlo.');
                }
            } finally {
                unlink($temporal);
            }
            $verificador->verificar($destino, $huella);

            return $destino;
        } finally {
            flock($candado, LOCK_UN);
            fclose($candado);
        }
    }

    public function verificarRetenido(string $huella): array
    {
        $this->validarHuella($huella);
        $ruta = $this->directorio(false).'/'.$huella.'.zip';
        if (is_link($ruta)) {
            throw new DomainException('El release retenido no puede ser un enlace.');
        }

        return (new ReleaseMotorInstrumental)->verificar($ruta, $huella);
    }

    private function directorio(bool $crear): string
    {
        if (! is_dir($this->directorio) && (! $crear || (! @mkdir($this->directorio, 0700, true) && ! is_dir($this->directorio)))) {
            throw new DomainException('No se pudo crear el directorio privado de releases.');
        }
        if (is_link($this->directorio) || ($ruta = realpath($this->directorio)) === false) {
            throw new DomainException('El directorio privado de releases no es válido.');
        }

        return $ruta;
    }

    private function validarHuella(string $huella): void
    {
        if (! preg_match('/^[a-f0-9]{64}$/D', $huella)) {
            throw new DomainException('La huella del release es inválida.');
        }
    }
}
