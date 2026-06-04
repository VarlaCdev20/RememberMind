<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class DocumentoVencidoMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $usuario;
    public string $nombre_completo;
    public array $documentos;

    public function __construct(User $usuario, array $documentos)
    {
        $this->usuario = $usuario;
        $this->nombre_completo = trim($usuario->nombres . ' ' . ($usuario->ap_paterno ?? '') . ' ' . ($usuario->ap_materno ?? ''));
        $this->documentos = $documentos;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'ALERTA: Plazo de presentación de documentación vencido - RememberMind',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.usuarios.documento-vencido',
            with: [
                'nombre_completo' => $this->nombre_completo,
                'documentos' => $this->documentos,
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
