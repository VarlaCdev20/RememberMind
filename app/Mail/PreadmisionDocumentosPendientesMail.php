<?php

namespace App\Mail;

use App\Models\Preadmision;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PreadmisionDocumentosPendientesMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $nombreAdulto;
    public string $nombreFamiliar;
    public string $fechaLimite;
    public array $documentosPendientes;

    public function __construct(
        public Preadmision $preadmision,
        array $documentosPendientes,
        string $fechaLimite
    ) {
        $this->nombreAdulto     = trim("{$preadmision->nombres} {$preadmision->ap_paterno} {$preadmision->ap_materno}");
        $this->nombreFamiliar   = trim("{$preadmision->familiar_nombres} {$preadmision->familiar_ap_paterno}");
        $this->fechaLimite      = $fechaLimite;
        $this->documentosPendientes = $documentosPendientes;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Documentos pendientes — Preadmisión ' . $this->preadmision->cod_pre . ' | Casa Amandita',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.preadmision.documentos-pendientes',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
