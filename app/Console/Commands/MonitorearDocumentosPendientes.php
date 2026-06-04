<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Mail\DocumentoVencidoMail;

class MonitorearDocumentosPendientes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:monitorear-documentos-pendientes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitorea la tabla documentos_usuarios para detectar documentos PENDIENTES con más de 48 horas, actualizándolos a VENCIDO y alertando al usuario';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando monitoreo de documentos pendientes...');

        // Buscar documentos que siguen como PENDIENTE después de 48 horas de su creación
        $docsPendientes = DB::table('documentos_usuarios')
            ->where('estado', 'PENDIENTE')
            ->where('created_at', '<', now()->subHours(48))
            ->get();

        if ($docsPendientes->isEmpty()) {
            $this->info('No se encontraron documentos pendientes con más de 48 horas.');
            return Command::SUCCESS;
        }

        // Agrupar por usuario
        $agrupadosPorUsuario = $docsPendientes->groupBy('cod_usu');

        foreach ($agrupadosPorUsuario as $codUsu => $documentos) {
            $usuario = User::where('cod_usu', $codUsu)->first();
            if (!$usuario) {
                $this->warn("No se encontró el usuario con código: {$codUsu}");
                continue;
            }

            $nombresDocs = $documentos->pluck('nombre_documento')->toArray();

            // 1. Actualizar estado a VENCIDO en la base de datos
            $idsDocumentos = $documentos->pluck('cod_doc_usu')->toArray();
            DB::table('documentos_usuarios')
                ->whereIn('cod_doc_usu', $idsDocumentos)
                ->update([
                    'estado' => 'VENCIDO',
                    'updated_at' => now(),
                ]);

            // 2. Registrar la alerta de seguimiento en la trazabilidad / logs
            if (function_exists('activity')) {
                activity()
                    ->causedBy(null) // Ejecutado por el sistema/cron
                    ->performedOn($usuario)
                    ->useLog('AlertaDocumentosVencidos')
                    ->withProperties([
                        'usuario_registrado' => $usuario->nombres . ' ' . $usuario->ap_paterno,
                        'ci' => $usuario->numero_documento,
                        'documentos_vencidos' => $nombresDocs,
                        'detalle' => 'Actualizado automáticamente por tarea programada a estado VENCIDO tras expirar el plazo de 48 horas.'
                    ])
                    ->log("Alerta de control: Plazo de 48 horas expirado para subir " . implode(', ', $nombresDocs));
            }

            // 3. Enviar correo de notificación
            try {
                Mail::to($usuario->correo)->send(new DocumentoVencidoMail($usuario, $nombresDocs));
                $this->info("Notificación enviada a {$usuario->correo} por " . count($nombresDocs) . " documentos vencidos.");
            } catch (\Exception $e) {
                $this->error("Error al enviar correo a {$usuario->correo}: " . $e->getMessage());
            }
        }

        $this->info('Monitoreo completado.');
        return Command::SUCCESS;
    }
}
