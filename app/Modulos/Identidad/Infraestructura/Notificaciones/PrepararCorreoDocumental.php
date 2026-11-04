<?php

namespace App\Modulos\Identidad\Infraestructura\Notificaciones;

use App\Compartido\Infraestructura\Documentos\DocumentosUsuarioService;
use App\Mail\UsuarioFichaAdjuntaMail;
use App\Mail\Usuarios\PaqueteDocumentalUsuarioMail;
use App\Models\User;
use App\Modulos\Identidad\Aplicacion\Acciones\DocumentacionUsuarioService;
use App\Modulos\Identidad\Aplicacion\Consultas\UsuarioFichaService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Mail\Mailable;

class PrepararCorreoDocumental
{
    public function preparar(User $usuario, User $actor, string $tipo): Mailable
    {
        if ($tipo === 'paquete') {
            $documentos = app(DocumentosUsuarioService::class);
            $datos = $documentos->prepararPaquete($usuario);
            $pdf = Pdf::loadView('pages.usuarios.documentos.paquete-documental', $datos)->setPaper('a4', 'portrait')->setWarnings(false);

            return new PaqueteDocumentalUsuarioMail($usuario, $pdf->output(), $documentos->nombreArchivoPaquete($usuario), $datos['requisitos']);
        }

        $expediente = app(UsuarioFichaService::class)->obtenerExpedienteCompleto($usuario);
        $datos = ['usuario' => $usuario, 'rol' => $expediente['rol'], 'nombre_rol' => $expediente['nombre_rol'],
            'area' => $expediente['area'], 'horarios' => $expediente['horarios'], 'avance_documental' => $expediente['avance_documental'],
            'checklist' => app(DocumentacionUsuarioService::class)->obtenerChecklistUsuario($usuario),
            'fecha_ingreso' => $expediente['fecha_ingreso'],
            'fecha' => now()->format('d/m/Y H:i'), 'usuario_solicitante' => $actor->name];
        $pdf = Pdf::loadView('pdf.exports.usuarios.expediente_ficha', $datos)->setPaper('a4', 'portrait')->setWarnings(false);

        $correo = new UsuarioFichaAdjuntaMail($usuario, $pdf->output(), 'ficha-institucional.pdf');
        if (filter_var($actor->correo, FILTER_VALIDATE_EMAIL)) {
            $correo->cc($actor->correo);
        }

        return $correo;
    }
}
