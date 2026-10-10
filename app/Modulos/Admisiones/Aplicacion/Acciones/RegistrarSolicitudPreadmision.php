<?php

namespace App\Modulos\Admisiones\Aplicacion\Acciones;

use App\Mail\PreadmisionDocumentosPendientesMail;
use App\Models\Contacto;
use App\Models\Documento;
use App\Models\Preadmision;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class RegistrarSolicitudPreadmision
{
    public function ejecutar(User $usuario, array $datos): array
    {
        return DB::transaction(function () use ($usuario, $datos): array {
            $hayPendientes48h = ! empty($datos['docs_pendientes_48h']);
            $fechaLimite48h = now()->addHours(48);

            $contacto = Contacto::create([
                'cod_contacto' => 'CTO_'.Str::upper(Str::random(12)),
                'nombres' => $this->normalizar($datos['familiar_nombres']),
                'apellido_paterno' => $this->normalizar($datos['familiar_ap_paterno']),
                'apellido_materno' => $this->normalizar($datos['familiar_ap_materno']),
                'numero_documento' => trim($datos['familiar_ci']) ?: null,
                'celular' => trim($datos['familiar_celular']),
                'correo' => trim($datos['familiar_correo']) ?: null,
                'direccion' => $this->normalizar($datos['familiar_direccion']),
                'estado' => 'ACTIVO',
            ]);

            $preadmision = Preadmision::create([
                'cod_preadmision' => 'PRE_'.Str::upper(Str::random(12)),
                'cod_contacto' => $contacto->cod_contacto,
                'cod_usuario_registro' => $usuario->cod_usuario,
                'estado' => 'PENDIENTE',
                'fecha_solicitud' => now(),

                'nombres' => $this->normalizar($datos['nombres']),
                'apellido_paterno' => $this->normalizar($datos['ap_paterno']),
                'apellido_materno' => $this->normalizar($datos['ap_materno']),
                'numero_documento' => trim($datos['ci']),
                'expedicion_documento' => $datos['expedicion_ci'],
                'fecha_nacimiento' => $datos['fecha_nac'],
                'genero' => $datos['genero'],
                'estado_civil' => $datos['estado_civil'],
                'telefono' => trim($datos['celular'] ?: $datos['telefono']) ?: null,
                'direccion' => implode(', ', array_filter([
                    $this->normalizar($datos['departamento_residencia']),
                    $this->normalizar($datos['ciudad_municipio']),
                    $this->normalizar($datos['zona']),
                    $this->normalizar($datos['calle']),
                    $this->normalizar($datos['direccion_referencia']),
                ])),

                'motivo_ingreso' => $datos['motivo_ingreso'],
                'procedencia' => $datos['procedencia_ingreso'],
                'tipo_ingreso' => $datos['tipo_ingreso'],
                'permanencia' => $datos['permanencia'],
                'prioridad' => $datos['prioridad'],
                'descripcion_caso' => $this->normalizar($datos['descripcion_caso']),
            ]);

            $docsPendientesNombres = [];

            foreach ($datos['configuracion_documentos'] as $docConfig) {
                if ($docConfig['es_generado_sistema']) {
                    continue;
                }

                $tipo = $docConfig['tipo'];
                $propiedad = $docConfig['propiedad'];
                $archivo = $datos['archivos'][$propiedad];
                $esPendiente48h = in_array($tipo, $datos['docs_pendientes_48h'], true) && empty($archivo);

                if (! empty($archivo) && ! is_string($archivo)) {
                    $path = $archivo->store("preadmisiones/{$preadmision->cod_pre}", 'public');

                    Documento::create([
                        'cod_documento' => 'DOC_'.Str::upper(Str::random(12)),
                        'cod_preadmision' => $preadmision->cod_preadmision,
                        'cod_contacto' => $contacto->cod_contacto,
                        'tipo_documento' => $tipo,
                        'nombre' => $archivo->getClientOriginalName(),
                        'ruta_archivo' => $path,
                        'tipo_archivo' => $archivo->getMimeType() ?: 'application/octet-stream',
                        'hash_archivo' => hash_file('sha256', Storage::disk('public')->path($path)),
                        'estado' => 'VIGENTE',
                        'observacion' => $docConfig['grupo'],
                    ]);
                } elseif ($esPendiente48h) {
                    $docsPendientesNombres[] = $docConfig['nombre'];
                }
            }

            $dirPdf = "preadmisiones/{$preadmision->cod_pre}/institucionales";
            Storage::disk('public')->makeDirectory($dirPdf);

            $todosGenerados = true;

            foreach ($datos['configuracion_documentos'] as $docConfig) {
                if (! $docConfig['es_generado_sistema']) {
                    continue;
                }

                $archivoPdf = null;
                $estadoDoc = 'PENDIENTE_FIRMA';

                try {
                    $pdf = Pdf::loadView($docConfig['vista_pdf'], [
                        'preadmision' => $preadmision,
                        'fecha' => now()->format('d/m/Y H:i'),
                    ]);

                    $filePath = "{$dirPdf}/{$docConfig['archivo_pdf']}";
                    Storage::disk('public')->put($filePath, $pdf->output());

                    $archivoPdf = $filePath;
                    $estadoDoc = 'GENERADO';
                } catch (\Throwable $exception) {
                    report($exception);
                    $todosGenerados = false;
                }

                if ($archivoPdf) {
                    Documento::create([
                        'cod_documento' => 'DOC_'.Str::upper(Str::random(12)),
                        'cod_preadmision' => $preadmision->cod_preadmision,
                        'cod_contacto' => $contacto->cod_contacto,
                        'tipo_documento' => $docConfig['tipo'],
                        'nombre' => $docConfig['nombre'],
                        'ruta_archivo' => $archivoPdf,
                        'tipo_archivo' => 'application/pdf',
                        'hash_archivo' => hash_file('sha256', Storage::disk('public')->path($archivoPdf)),
                        'estado' => 'VIGENTE',
                        'observacion' => 'Generado por el sistema al confirmar la preadmisión.',
                    ]);
                }
            }

            $correoPendiente = false;
            if (! empty($docsPendientesNombres) && ! empty($preadmision->familiar_correo)) {
                try {
                    Mail::to($preadmision->familiar_correo)
                        ->send(new PreadmisionDocumentosPendientesMail(
                            $preadmision,
                            $docsPendientesNombres,
                            $fechaLimite48h->format('d/m/Y H:i')
                        ));
                } catch (\Throwable $exception) {
                    report($exception);
                    $correoPendiente = true;
                }
            }

            activity('Admisiones')
                ->causedBy($usuario)
                ->performedOn($preadmision)
                ->log('Solicitud de preadmisión registrada y pendiente de revisión.');

            return compact('docsPendientesNombres', 'todosGenerados', 'correoPendiente');
        });
    }

    private function normalizar(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : mb_strtoupper($value, 'UTF-8');
    }
}
