<?php

namespace App\Services\Documentos;

use App\Models\User;
use App\Models\Documento;
use App\Models\DocumentoUsuario;
use App\Models\TipoDocumentoUsuario;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;

class DocumentacionUsuarioService
{
    /**
     * Obtiene el checklist documental para un usuario según su rol.
     * Combina el catálogo de tipos con los archivos ya cargados por el usuario.
     */
    public function obtenerChecklistUsuario(User $usuario): array
    {
        return Documento::query()
            ->where('cod_usuario', $usuario->cod_usuario)
            ->whereNotIn('estado', ['ANULADO', 'ANULADA'])
            ->orderBy('tipo_documento')
            ->get()
            ->groupBy('tipo_documento')
            ->map(function ($documentos, string $tipo): array {
                $actual = $documentos->sortByDesc('fecha_validacion')->first();

                return [
                    'cod_tipo_doc' => $tipo,
                    'nombre' => $tipo,
                    'descripcion' => $actual->nombre,
                    'obligatorio' => false,
                    'requiere_vencimiento' => $actual->fecha_vencimiento !== null,
                    'requiere_validacion' => true,
                    'cargado' => true,
                    'documento' => $actual,
                    'estado' => $actual->estado,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * Calcula métricas y el porcentaje de avance documental de un usuario.
     */
    public function calcularAvanceDocumental(User $usuario): array
    {
        $checklist = $this->obtenerChecklistUsuario($usuario);
        
        $totalRequeridos = 0;
        $totalOpcionales = 0;
        $cargadosRequeridos = 0;
        $validadosRequeridos = 0;
        
        $totalCargados = 0;
        $totalValidados = 0;
        $totalPendientes = 0;
        $totalObservados = 0;
        $totalVencidos = 0;

        foreach ($checklist as $item) {
            if ($item['obligatorio']) {
                $totalRequeridos++;
                if ($item['cargado']) {
                    $cargadosRequeridos++;
                    if ($item['estado'] === 'VALIDADO') {
                        $validadosRequeridos++;
                    }
                }
            } else {
                $totalOpcionales++;
            }

            if ($item['cargado']) {
                $totalCargados++;
                switch ($item['estado']) {
                    case 'VALIDADO':
                        $totalValidados++;
                        break;
                    case 'OBSERVADO':
                        $totalObservados++;
                        break;
                    case 'VENCIDO':
                        $totalVencidos++;
                        break;
                    default:
                        $totalPendientes++;
                        break;
                }
            }
        }

        // Avance basado en los obligatorios requeridos
        $porcentaje = $totalRequeridos > 0 
            ? round(($validadosRequeridos / $totalRequeridos) * 100) 
            : 100;

        return [
            'total_requeridos' => $totalRequeridos,
            'total_opcionales' => $totalOpcionales,
            'cargados' => $totalCargados,
            'validados' => $totalValidados,
            'pendientes_validacion' => $totalPendientes,
            'observados' => $totalObservados,
            'vencidos' => $totalVencidos,
            'porcentaje_avance' => $porcentaje,
            'completo' => ($validadosRequeridos === $totalRequeridos),
        ];
    }

    /**
     * Sube un documento al storage del usuario y lo registra en base de datos.
     */
    public function subirDocumento(
        User $usuario,
        string $codTipoDoc,
        UploadedFile $file,
        ?string $fechaEmision = null,
        ?string $fechaVencimiento = null,
        ?string $observaciones = null,
        ?User $subidoPor = null
    ): DocumentoUsuario {
        $tipo = TipoDocumentoUsuario::findOrFail($codTipoDoc);

        // Generar nombre de archivo limpio en UTF-8
        $nombreLimpio = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $extension = strtolower($file->getClientOriginalExtension());
        $fileName = time() . '_' . $nombreLimpio . '.' . $extension;
        
        // Guardar en storage public/documentos/usuarios/{cod_usu}
        $relPath = "documentos/usuarios/{$usuario->cod_usu}";
        $path = $file->storeAs($relPath, $fileName, 'public');

        // Si ya tiene un documento activo para este tipo, marcarlo como REEMPLAZADO o archivarlo
        $previo = DocumentoUsuario::where('cod_usu', $usuario->cod_usu)
            ->where('cod_tipo_doc', $codTipoDoc)
            ->whereIn('estado', ['PENDIENTE', 'CARGADO', 'VALIDADO', 'OBSERVADO', 'VENCIDO'])
            ->orderByDesc('created_at')
            ->first();

        $documento = DocumentoUsuario::create([
            'cod_usu' => $usuario->cod_usu,
            'cod_tipo_doc' => $codTipoDoc,
            'tipo_documento' => $tipo->nombre,
            'nombre_documento' => $tipo->nombre,
            'archivo_path' => $path,
            'nombre_original' => $file->getClientOriginalName(),
            'archivo' => $path,
            'mime_type' => $file->getClientMimeType(),
            'extension' => $extension,
            'tamanio' => $file->getSize(),
            'fecha_subida' => now(),
            'fecha_emision' => $fechaEmision,
            'fecha_vencimiento' => $fechaVencimiento,
            'estado' => 'CARGADO',
            'observaciones' => $observaciones,
            'subido_por' => $subidoPor?->cod_usu ?? auth()->id(),
            'creado_por' => auth()->id(),
        ]);

        if ($previo) {
            $previo->update([
                'estado' => 'REEMPLAZADO',
                'actualizado_por' => auth()->id(),
            ]);
            $documento->update([
                'reemplaza_a' => $previo->cod_doc_usu
            ]);

            activity('Usuarios')
                ->causedBy(auth()->user())
                ->performedOn($usuario)
                ->event('documentacion')
                ->log("Reemplazó el documento '{$tipo->nombre}' anterior por una nueva versión.");
        } else {
            activity('Usuarios')
                ->causedBy(auth()->user())
                ->performedOn($usuario)
                ->event('documentacion')
                ->log("Subió el documento obligatorio '{$tipo->nombre}' al expediente.");
        }

        return $documento;
    }

    /**
     * Valida un documento cargado.
     */
    public function validarDocumento(DocumentoUsuario $documento, User $validador): void
    {
        $documento->update([
            'estado' => 'VALIDADO',
            'validado_por' => $validador->cod_usu,
            'fecha_validacion' => now(),
            'actualizado_por' => $validador->cod_usu,
        ]);

        activity('Usuarios')
            ->causedBy($validador)
            ->performedOn($documento->usuario)
            ->event('documentacion')
            ->log("Validó satisfactoriamente el documento '{$documento->nombre_documento}'.");
    }

    /**
     * Marca un documento como observado especificando un motivo.
     */
    public function observarDocumento(DocumentoUsuario $documento, string $motivo, User $validador): void
    {
        $documento->update([
            'estado' => 'OBSERVADO',
            'motivo_observacion' => $motivo,
            'actualizado_por' => $validador->cod_usu,
        ]);

        activity('Usuarios')
            ->causedBy($validador)
            ->performedOn($documento->usuario)
            ->event('documentacion')
            ->log("Observó el documento '{$documento->nombre_documento}' con motivo: {$motivo}.");
    }

    /**
     * Anula un documento especificando un motivo.
     */
    public function anularDocumento(DocumentoUsuario $documento, string $motivo, User $validador): void
    {
        $documento->update([
            'estado' => 'ANULADO',
            'observaciones' => $documento->observaciones . "\n[ANULADO] Motivo: " . $motivo,
            'motivo_observacion' => $motivo,
            'actualizado_por' => $validador->cod_usu,
        ]);

        activity('Usuarios')
            ->causedBy($validador)
            ->performedOn($documento->usuario)
            ->event('documentacion')
            ->log("Anuló el documento '{$documento->nombre_documento}'.");
    }
}
