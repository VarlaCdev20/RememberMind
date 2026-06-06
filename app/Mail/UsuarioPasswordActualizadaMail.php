<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class UsuarioPasswordActualizadaMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $usuario;
    public ?string $passwordTemporal;
    public string $enlaceSistema;

    public function __construct(User $usuario, ?string $passwordTemporal = null)
    {
        $this->usuario = $usuario;
        $this->passwordTemporal = $passwordTemporal;
        $this->enlaceSistema = config('app.url', 'http://127.0.0.1:8000');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'RememberMind - Actualización de Contraseña de Acceso',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.usuarios.password-actualizada',
        );
    }
}
