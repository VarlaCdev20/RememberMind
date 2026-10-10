<?php

namespace App\Modulos\Identidad\Aplicacion\Acciones;

use App\Mail\PersonalInstitucionalBienvenidaMail;
use App\Models\Documento;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class GuardarPersonalInstitucional
{
    public function guardar(?User $actor, array $entrada, ?array $clasificacion): User
    {
        if ($entrada['esEdicion'] && ($clasificacion['tipo_personal'] ?? null) === 'salud' && ($clasificacion['rol_operativo'] ?? null) === 'MEDICO_GENERAL' && $entrada['estado'] === 'ACTIVO') {
            if (User::role('MEDICO GENERAL/GERIATRA')->where('estado', 'ACTIVO')->where('cod_usuario', '!=', $entrada['usuarioId'])->exists()) {
                throw ValidationException::withMessages(['roles_seleccionados' => 'Ya existe un Médico General principal activo en el sistema.']);
            }
        }
        DB::beginTransaction();
        try {
            if ($entrada['esEdicion']) {
                $usuario = User::findOrFail($entrada['usuarioId']);
            } else {
                $usuario = new User;
                $usuario->password = $entrada['contrasena_temporal'];
                $usuario->debe_cambiar_password = $entrada['forzar_cambio_password'];
                $usuario->created_at = Carbon::parse($entrada['fecha_registro'].' '.$entrada['hora_registro']);
            }

            $usuario->nombres = mb_strtoupper(trim($entrada['nombres']), 'UTF-8');
            $usuario->ap_paterno = $entrada['ap_paterno'] ? mb_strtoupper(trim($entrada['ap_paterno']), 'UTF-8') : null;
            $usuario->ap_materno = $entrada['ap_materno'] ? mb_strtoupper(trim($entrada['ap_materno']), 'UTF-8') : null;
            $usuario->correo = trim(mb_strtolower($entrada['correo'], 'UTF-8'));
            $usuario->telefono = $entrada['telefono'];
            $usuario->numero_documento = mb_strtoupper(trim($entrada['numero_documento']), 'UTF-8');
            $usuario->expedido = $entrada['expedido'] ? mb_strtoupper(trim($entrada['expedido']), 'UTF-8') : null;
            $usuario->fecha_nacimiento = $entrada['fecha_nacimiento'];
            $usuario->genero = $entrada['genero'];
            $usuario->estado = $entrada['estado'];
            $usuario->observaciones = $entrada['observaciones'] ? mb_strtoupper(trim($entrada['observaciones']), 'UTF-8') : null;
            $usuario->direccion = $entrada['direccion'] ? mb_strtoupper(trim($entrada['direccion']), 'UTF-8') : null;
            $usuario->zona = $entrada['zona'] ? mb_strtoupper(trim($entrada['zona']), 'UTF-8') : null;
            $usuario->ciudad = $entrada['ciudad'] ? mb_strtoupper(trim($entrada['ciudad']), 'UTF-8') : null;
            $usuario->cod_area = $entrada['cod_area'];

            if ($entrada['foto_perfil'] && ! is_string($entrada['foto_perfil'])) {
                $path = $entrada['foto_perfil']->store('perfiles', 'public');
                $usuario->foto_de_perfil = $path;
            }

            $usuario->save();

            // Sync Spatie Roles correctly
            if (! empty($entrada['roles_seleccionados'])) {
                $usuario->syncRoles($entrada['roles_seleccionados']);
            } else {
                $usuario->syncRoles([]);
            }

            $this->guardarDatosLaboralesEnUsuario($actor, $entrada, $usuario, $clasificacion);

            // Registrar exclusivamente archivos reales en la tabla documental unificada V2.
            $documentosFaltantes = [];
            $responsableId = $actor?->getKey();

            foreach ($entrada['documentos_configurados'] as $doc) {
                $esInstitucional = ($doc['tipo'] === 'institucional');
                $estadoDoc = $entrada['estado_documentos'][$doc['id']] ?? 'PENDIENTE';

                if ($estadoDoc === 'CARGADO' && isset($entrada['archivos_temporales'][$doc['id']])) {
                    $file = $entrada['archivos_temporales'][$doc['id']];
                    if (! is_string($file)) {
                        $path = $file->store('documentos_personal', 'public');
                        $anterior = Documento::where('cod_usuario', $usuario->cod_usuario)
                            ->where('tipo_documento', $doc['id'])->whereNotIn('estado', ['ANULADO', 'REEMPLAZADO'])->first();
                        Documento::create([
                            'cod_documento' => 'DOC_'.strtoupper(Str::random(10)),
                            'cod_usuario' => $usuario->cod_usuario,
                            'cod_documento_anterior' => $anterior?->cod_documento,
                            'tipo_documento' => $doc['id'],
                            'nombre' => $doc['nombre'],
                            'ruta_archivo' => $path,
                            'tipo_archivo' => $file->getMimeType() ?: 'application/octet-stream',
                            'hash_archivo' => hash_file('sha256', \Storage::disk('public')->path($path)),
                            'estado' => 'CARGADO',
                            'observacion' => 'Subido por: '.$responsableId,
                        ]);
                        $anterior?->update(['estado' => 'REEMPLAZADO']);
                    }
                } elseif ($estadoDoc === 'OBSERVADO') {
                    $documentosFaltantes[] = $doc['nombre'].': '.($entrada['observacion_documentos'][$doc['id']] ?? 'Documento observado.');
                } elseif ($estadoDoc === 'PENDIENTE') {
                    if ($esInstitucional && in_array($doc['id'], ['CONTRATO', 'CONFIDENCIALIDAD'])) {
                        $documentosFaltantes[] = $doc['nombre'];
                    }
                }
            }

            // Log follow-up alert if institutional documents are missing
            if (! empty($documentosFaltantes) && function_exists('activity')) {
                activity()
                    ->causedBy($actor)
                    ->performedOn($usuario)
                    ->useLog('AlertaSeguimiento')
                    ->withProperties([
                        'usuario_registrado' => $usuario->nombres.' '.$usuario->ap_paterno,
                        'ci' => $usuario->numero_documento,
                        'documentos_faltantes' => $documentosFaltantes,
                        'plazo_horas' => 48,
                    ])
                    ->log('Alerta de Seguimiento: Ingresante registrado en estado PENDIENTE_INSTITUCIONAL debido a falta de documentación institucional firmada obligatoria ('.implode(', ', $documentosFaltantes).'). Requiere regularización en 48 horas.');
            }

            // Log activity
            if (function_exists('activity')) {
                activity()
                    ->causedBy($actor)
                    ->performedOn($usuario)
                    ->log($entrada['esEdicion'] ? 'Actualizó datos de personal institucional' : 'Registró nuevo personal institucional');
            }

            DB::commit();

            if (! $entrada['esEdicion'] && class_exists(PersonalInstitucionalBienvenidaMail::class)) {
                $documentos_pendientes_arr = [];
                $documentos_subidos_arr = [];
                $documentos_institucionales_generados_arr = [];
                $pdfPaths = [];

                foreach ($entrada['documentos_configurados'] as $doc) {
                    $estadoDoc = $entrada['estado_documentos'][$doc['id']] ?? 'PENDIENTE';
                    if ($estadoDoc === 'PENDIENTE' || $estadoDoc === 'OBSERVADO') {
                        $documentos_pendientes_arr[] = $doc['nombre'];
                    } elseif ($estadoDoc === 'CARGADO' || $estadoDoc === 'FIRMADO_SUBIDO' || $estadoDoc === 'GENERADO') {
                        $documentos_subidos_arr[] = $doc['nombre'];
                    }

                    if ($doc['tipo'] === 'institucional' && in_array($estadoDoc, ['GENERADO', 'FIRMADO_SUBIDO'])) {
                        $documentos_institucionales_generados_arr[] = $doc['nombre'];

                        $fileMap = [
                            'FICHA' => 'ficha.pdf',
                            'CONTRATO' => 'contrato.pdf',
                            'CONFIDENCIALIDAD' => 'confidencialidad.pdf',
                            'FUNCIONES' => 'acta_funciones.pdf',
                        ];
                        if (isset($fileMap[$doc['id']]) && ! empty($entrada['tempId'])) {
                            $path = \Illuminate\Support\Facades\Storage::disk('public')->path("personal-institucional/temp/{$entrada['tempId']}/{$fileMap[$doc['id']]}");
                            if (file_exists($path)) {
                                $pdfPaths[$fileMap[$doc['id']]] = $path;
                            }
                        }
                    }
                }

                try {
                    Mail::to($usuario->correo)->send(
                        new PersonalInstitucionalBienvenidaMail(
                            $usuario,
                            $clasificacion['rol_label'] ?? 'Personal',
                            $clasificacion['area_nombre'] ?? 'Área Operativa',
                            $entrada['estado'],
                            $documentos_pendientes_arr,
                            Carbon::now()->addHours(48)->toDateString(),
                            $pdfPaths,
                            $entrada['contrasena_temporal'],
                            $documentos_subidos_arr,
                            $documentos_institucionales_generados_arr
                        )
                    );
                } catch (\Exception $mailEx) {
                    logger()->error('No se pudo enviar correo de bienvenida: '.$mailEx->getMessage());
                }
            }

            return $usuario;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function guardarDatosLaboralesEnUsuario(?User $actor, array $entrada, User $usuario, ?array $clasificacion): void
    {
        $datos = array_filter([
            $entrada['observaciones'] ? trim($entrada['observaciones']) : null,
            $clasificacion ? 'Clasificación: '.$clasificacion['tipo_label'].' / '.$clasificacion['rol_label'] : null,
            $entrada['institucion_formacion'] ? 'Formación: '.$entrada['institucion_formacion'] : null,
            $entrada['matricula_prof'] ? 'Matrícula: '.$entrada['matricula_prof'] : null,
            $entrada['anios_exp'] ? 'Años experiencia: '.$entrada['anios_exp'] : null,
            $entrada['subtipo_enfermeria'] ? 'Subtipo enfermería: '.$entrada['subtipo_enfermeria'] : null,
            $entrada['nivel_responsabilidad'] ? 'Nivel responsabilidad: '.$entrada['nivel_responsabilidad'] : null,
        ]);

        $usuario->forceFill([
            'observaciones' => $datos ? mb_strtoupper(implode(' | ', $datos), 'UTF-8') : $usuario->observaciones,
        ])->save();
    }
}
