<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class UsuarioFichaAdjuntaMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $usuario;
    public string $pdfContent;
    public string $filename;

    public function __construct(User $usuario, string $pdfContent, string $filename)
    {
        $this->usuario = $usuario;
        $this->pdfContent = $pdfContent;
        $this->filename = $filename;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'RememberMind - Ficha Institucional Oficial: ' . $this->usuario->nombres . ' ' . $this->usuario->ap_paterno,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.usuarios.ficha-adjunta',
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => $this->pdfContent, $this->filename)
                ->withMime('application/pdf'),
        ];
    }
}
