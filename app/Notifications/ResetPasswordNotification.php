<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use Laravel\Fortify\Fortify;

class ResetPasswordNotification extends ResetPassword
{
    /**
     * Construir el correo de recuperación.
     */
    public function toMail($notifiable): MailMessage
    {
        $url = $this->resetUrl(
            $notifiable
        );


        /*
        |--------------------------------------------------------------------------
        | Tiempo de expiración
        |--------------------------------------------------------------------------
        |
        | Se obtiene directamente de config/auth.php.
        |
        | Actualmente RememberMind tiene configurados 60 minutos.
        |
        */

        $minutes = (int) config(
            'auth.passwords.'
                . config('fortify.passwords', 'users')
                . '.expire',
            60
        );


        /*
        |--------------------------------------------------------------------------
        | Nombre del usuario
        |--------------------------------------------------------------------------
        */

        $nombre = trim(
            (string) (
                $notifiable->nombres
                ?? ''
            )
        );


        $saludo = $nombre !== ''
            ? "Hola, {$nombre}."
            : 'Hola.';


        /*
        |--------------------------------------------------------------------------
        | Correo
        |--------------------------------------------------------------------------
        */

        return (new MailMessage)
            ->subject(
                'Restablecimiento de contraseña | RememberMind'
            )

            ->greeting(
                $saludo
            )

            ->line(
                'Recibimos una solicitud para restablecer la contraseña de tu cuenta institucional en RememberMind.'
            )

            ->line(
                'Para crear una nueva contraseña, utiliza el siguiente botón.'
            )

            ->action(
                'Restablecer contraseña',
                $url
            )

            ->line(
                "Por seguridad, este enlace estará disponible durante {$minutes} minutos."
            )

            ->line(
                'El enlace de recuperación es personal y no debe compartirse con otras personas.'
            )

            ->line(
                'Si no solicitaste el restablecimiento de tu contraseña, puedes ignorar este mensaje. Tu contraseña actual no será modificada.'
            )

            ->salutation(
                'Centro Geriátrico Jardín de los Recuerdos'
            );
    }


    /**
     * Construir la URL de recuperación.
     *
     * RememberMind utiliza "correo" como identificador
     * de recuperación en lugar del "email" convencional.
     */
    protected function resetUrl($notifiable): string
    {
        return route(
            'password.reset',
            [
                'token' => $this->token,

                Fortify::email() =>
                $notifiable->getEmailForPasswordReset(),
            ]
        );
    }
}
