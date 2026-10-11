<?php

namespace App\Modulos\Identidad\Infraestructura\Notificaciones;

use App\Compartido\Infraestructura\Documentos\DocumentosUsuarioService;
use App\Mail\Usuarios\PaqueteDocumentalUsuarioMail;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;

final class EnviarPaqueteDocumental
{
    public function __construct(private readonly DocumentosUsuarioService $docService) {}

    public function ejecutar(User $actor, User $user): void
    {
        abort_unless($actor->canAny(['usuarios.editar', 'usuarios.crear']), 403);
        $datos = $this->docService->prepararPaquete($user);
        $filename = $this->docService->nombreArchivoPaquete($user);

        $pdf = Pdf::loadView('pages.usuarios.documentos.paquete-documental', $datos)
            ->setPaper('a4', 'portrait')
            ->setWarnings(false);

        Mail::to($user->correo)->send(new PaqueteDocumentalUsuarioMail($user, $pdf->output(), $filename, $datos['requisitos']));

        if (function_exists('activity')) {
            activity('Usuarios')
                ->causedBy($actor)
                ->performedOn($user)
                ->event('reportes')
                ->log("Envió paquete documental institucional por correo al usuario: {$user->name}.");
        }

    }
}
