<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use App\Services\Usuarios\UsuarioFichaService;
use App\Services\Usuarios\DocumentacionUsuarioService;
use App\Services\Reports\ReportFileNameService;
use App\Mail\UsuarioFichaAdjuntaMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class EnviarFichaUsuarioJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected User $usuario;
    protected User $admin;

    public function __construct(User $usuario, User $admin)
    {
        $this->usuario = $usuario;
        $this->admin = $admin;
    }

    public function handle(): void
    {
        $usuario = $this->usuario;
        $admin = $this->admin;

        try {
            $fichaService = app(UsuarioFichaService::class);
            $docService = app(DocumentacionUsuarioService::class);

            $expediente = $fichaService->obtenerExpedienteCompleto($usuario);
            $checklist = $docService->obtenerChecklistUsuario($usuario);

            $viewData = [
                'usuario' => $usuario,
                'rol' => $expediente['rol'],
                'nombre_rol' => $expediente['nombre_rol'],
                'area' => $expediente['area'],
                'horarios' => $expediente['horarios'],
                'avance_documental' => $expediente['avance_documental'],
                'checklist' => $checklist,
                'fecha' => now()->format('d/m/Y H:i'),
                'usuario_solicitante' => $admin->name,
            ];

            $fileNameService = app(ReportFileNameService::class);
            $filename = $fileNameService->generate('expediente_' . $usuario->cod_usu, 'pdf');

            // Generar PDF en memoria o usando DomPDF/Spatie
            $pdfContent = null;
            try {
                $pdfContent = \Spatie\LaravelPdf\Facades\Pdf::view('reports.usuarios.expediente_ficha', $viewData)->output();
            } catch (\Throwable $e) {
                Log::warning("Spatie PDF falló en cola para enviar-ficha: {$e->getMessage()}. Usando DomPDF.");
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.usuarios.expediente_ficha', $viewData)
                    ->setPaper('a4', 'portrait')
                    ->setWarnings(false);
                $pdfContent = $pdf->output();
            }

            // Guardar temporalmente
            $tempPath = 'temp/' . uniqid() . '_' . $filename;
            Storage::disk('public')->put($tempPath, $pdfContent);
            $fullPath = storage_path('app/public/' . $tempPath);

            // Enviar correo adjunto de forma síncrona dentro del Job en segundo plano
            Mail::to($usuario->correo)
                ->cc($admin->correo ?? $admin->email)
                ->send(new UsuarioFichaAdjuntaMail($usuario, $fullPath, $filename));

            // Eliminar archivo temporal
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }

            activity('Usuarios')
                ->causedBy($admin)
                ->performedOn($usuario)
                ->event('reportes')
                ->log("Se envió con éxito por correo la ficha institucional oficial al destinatario {$usuario->correo}.");

        } catch (\Exception $e) {
            Log::error("Error en EnviarFichaUsuarioJob para usuario {$usuario->cod_usu}: " . $e->getMessage());
            throw $e;
        }
    }
}
