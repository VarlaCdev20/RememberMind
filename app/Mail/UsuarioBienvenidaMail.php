<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class UsuarioBienvenidaMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $usuario;
    public string $rolDisplay;
    public string $areaDisplay;
    public string $enlaceSistema;

    public function __construct(User $usuario, string $rolDisplay, string $areaDisplay)
    {
        $this->usuario = $usuario;
        $this->rolDisplay = $rolDisplay;
        $this->areaDisplay = $areaDisplay;
        $this->enlaceSistema = config('app.url', 'http://127.0.0.1:8000');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '¡Te damos la Bienvenida a RememberMind - Casa Amandita!',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.usuarios.bienvenida',
        );
    }
}
