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
    public string $pdfPath;
    public string $filename;

    public function __construct(User $usuario, string $pdfPath, string $filename)
    {
        $this->usuario = $usuario;
        $this->pdfPath = $pdfPath;
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
            Attachment::fromPath($this->pdfPath)
                ->as($this->filename)
                ->withMime('application/pdf'),
        ];
    }
}
