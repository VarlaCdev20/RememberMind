<?php

namespace App\Modulos\Identidad\Infraestructura\Notificaciones;

use App\Compartido\Infraestructura\Documentos\DocumentosUsuarioService;
use App\Mail\UsuarioRequisitosDocumentalesMail;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Spatie\LaravelPdf\Facades\Pdf;

final class EnviarRequisitosDocumentales
{
    public function ejecutar(?User $actor, User $usuario): void
    {
        $rol = $usuario->roles->first()?->name;
        $documentos = app(DocumentosUsuarioService::class)->documentosRequeridosPorRol($rol);
        $rolLegible = $this->obtenerNombreRolLegible($rol);
        $fechaLimite = $usuario->created_at ? $usuario->created_at->addHours(48)->format('d/m/Y H:i') : now()->addHours(48)->format('d/m/Y H:i');
        $fechaRegistro = $usuario->created_at ? $usuario->created_at->format('d/m/Y H:i') : now()->format('d/m/Y H:i');

        // Generar PDF para adjuntarlo
        $viewData = [
            'usuario_reg' => $usuario,
            'rol_legible' => $rolLegible,
            'fecha_registro' => $fechaRegistro,
            'fecha_limite' => $fechaLimite,
            'documentos' => $documentos,
            'fecha' => now()->format('d/m/Y H:i'),
            'usuario' => $actor?->name ?? 'Sistema',
        ];

        $pdfContent = null;
        try {
            $pdfContent = Pdf::view('pdf.exports.usuarios.solicitud_documental_pdf', $viewData)->output();
        } catch (\Throwable $e) {
            Log::warning("Spatie PDF falló al generar adjunto en panel: {$e->getMessage()}. Usando DomPDF.");
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.exports.usuarios.solicitud_documental_pdf', $viewData)
                ->setPaper('a4', 'portrait')
                ->setWarnings(false);
            $pdfContent = $pdf->output();
        }

        $filename = 'solicitud_documental_'.$usuario->cod_usuario.'.pdf';
        $tempPath = 'temp/'.uniqid().'_'.$filename;
        Storage::disk('public')->put($tempPath, $pdfContent);
        $fullPath = storage_path('app/public/'.$tempPath);

        Mail::to($usuario->correo)
            ->send(new UsuarioRequisitosDocumentalesMail($usuario, $rolLegible, $documentos, $fechaLimite, $fullPath, $filename));

        if (file_exists($fullPath)) {
            unlink($fullPath);
        }

    }

    private function obtenerNombreRolLegible($rol): string
    {
        switch ($rol) {
            case 'SUPERADMINISTRADOR':
            case 'ADMINISTRADOR':
                return 'Personal Administrativo';
            case 'ENFERMEROS':
            case 'MEDICO GENERAL/GERIATRA':
            case 'PSICOLOGO/A':
            case 'PEDAGOGO':
            case 'NUTRICIONISTA':
            case 'FISIOTERAPEUTA':
                return 'Personal de Salud';
            case 'FAMILIAR':
                return 'Familiar';
            case 'admin':
                return 'Administrador';
            case 'super_admin':
                return 'Super Administrador';
            default:
                return ucfirst(str_replace('_', ' ', $rol));
        }
    }
}
