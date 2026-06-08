<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class PersonalIngresanteConfirmacionMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $usuario;
    public string $rol_label;
    public string $area_nombre;
    public string $estado_general;
    public array $documentos_pendientes;
    public string $fecha_limite;
    public array $pdfPaths;

    public function __construct(User $usuario, string $rol_label, string $area_nombre, string $estado_general, array $documentos_pendientes, string $fecha_limite, array $pdfPaths = [])
    {
        $this->usuario = $usuario;
        $this->rol_label = $rol_label;
        $this->area_nombre = $area_nombre;
        $this->estado_general = $estado_general;
        $this->documentos_pendientes = $documentos_pendientes;
        $this->fecha_limite = $fecha_limite;
        $this->pdfPaths = $pdfPaths;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Confirmación de Registro e Incorporación - Geriátrico Jardín de los Recuerdos',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.usuarios.confirmacion-registro',
            with: [
                'nombre_completo' => trim($this->usuario->nombres . ' ' . ($this->usuario->ap_paterno ?? '') . ' ' . ($this->usuario->ap_materno ?? '')),
                'rol_label' => $this->rol_label,
                'area_nombre' => $this->area_nombre,
                'estado_general' => $this->estado_general,
                'documentos_pendientes' => $this->documentos_pendientes,
                'fecha_limite' => $this->fecha_limite,
            ]
        );
    }

    public function attachments(): array
    {
        $attachments = [];
        foreach ($this->pdfPaths as $name => $path) {
            if ($path && file_exists($path)) {
                $attachments[] = Attachment::fromPath($path)
                    ->as($name)
                    ->withMime('application/pdf');
            }
        }
        return $attachments;
    }
}
