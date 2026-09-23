<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class UsuarioRequisitosDocumentalesMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $usuario;
    public string $nombre_completo;
    public string $rol_legible;
    public array $documentos;
    public string $fecha_limite;
    public ?string $pdfPath;
    public ?string $pdfFilename;

    public function __construct(User $usuario, string $rol_legible, array $documentos, string $fecha_limite, ?string $pdfPath = null, ?string $pdfFilename = null)
    {
        $this->usuario = $usuario;
        $this->nombre_completo = trim($usuario->nombres . ' ' . ($usuario->ap_paterno ?? '') . ' ' . ($usuario->ap_materno ?? ''));
        $this->rol_legible = $rol_legible;
        $this->documentos = $documentos;
        $this->fecha_limite = $fecha_limite;
        $this->pdfPath = $pdfPath;
        $this->pdfFilename = $pdfFilename;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Documentación requerida para completar su registro institucional',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.usuarios.solicitud-documental',
            with: [
                'nombre_completo' => $this->nombre_completo,
                'rol_legible' => $this->rol_legible,
                'documentos' => $this->documentos,
                'fecha_limite' => $this->fecha_limite,
            ]
        );
    }

    public function attachments(): array
    {
        if ($this->pdfPath && file_exists($this->pdfPath)) {
            return [
                Attachment::fromPath($this->pdfPath)
                    ->as($this->pdfFilename ?? 'solicitud_documentacion.pdf')
                    ->withMime('application/pdf'),
            ];
        }
        return [];
    }
}
