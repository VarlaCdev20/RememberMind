<?php

namespace App\Compartido\Aplicacion\Contratos;

use App\Models\Preadmision;
use App\Models\User;

interface NotificacionesRegistro
{
    public function bienvenidaPersonal(User $usuario, string $rol, string $area, string $estado, array $pendientes, string $fechaLimite, array $adjuntos, ?string $clave, array $subidos): void;

    public function pendientesPreadmision(Preadmision $preadmision, array $pendientes, string $fechaLimite): void;

    public function claveActualizada(User $usuario, string $clave): void;
}
