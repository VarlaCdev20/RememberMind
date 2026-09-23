<?php

namespace App\Mail\Usuarios;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class PaqueteDocumentalUsuarioMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $usuario;
    public $requisitos;
    protected $pdfContent;
    protected $pdfFilename;

    public function __construct(User $usuario, string $pdfContent, string $pdfFilename, array $requisitos = [])
    {
        $this->usuario = $usuario;
        $this->pdfContent = $pdfContent;
        $this->pdfFilename = $pdfFilename;
        $this->requisitos = $requisitos;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Paquete Documental Institucional - CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.usuarios.paquete_documental',
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => $this->pdfContent, $this->pdfFilename)
                ->withMime('application/pdf'),
        ];
    }
}
