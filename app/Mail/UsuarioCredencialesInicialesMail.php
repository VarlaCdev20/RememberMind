<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class UsuarioCredencialesInicialesMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $usuario;
    public string $passwordTemporal;
    public string $enlaceSistema;

    public function __construct(User $usuario, string $passwordTemporal)
    {
        $this->usuario = $usuario;
        $this->passwordTemporal = $passwordTemporal;
        $this->enlaceSistema = config('app.url', 'http://127.0.0.1:8000');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'RememberMind - Credenciales de Acceso de Casa Amandita',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.usuarios.credenciales-iniciales',
        );
    }
}
