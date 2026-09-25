<?php

namespace App\Backend\Modulos\Documentos\Servicios;

use App\Models\Documento;
use App\Models\Residente;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class DocumentacionResidenteService
{
    public const CATEGORIAS = [
        'CLINICO' => ['nombre' => 'Clínico'],
        'ADMINISTRATIVO' => ['nombre' => 'Administrativo'],
        'IMAGEN' => ['nombre' => 'Imágenes'],
        'LEGAL' => ['nombre' => 'Legales'],
        'PERSONAL' => ['nombre' => 'Personal'],
    ];

    public function obtenerDocumentosResidente(Residente $residente): Collection
    {
        return Documento::query()
            ->where('cod_residente', $residente->cod_residente)
            ->where('estado', '!=', 'ARCHIVADO')
            ->orderByDesc('fecha_validacion')
            ->get()
            ->map(fn (Documento $documento) => $this->normalizarDocumentoBd($documento, $residente))
            ->values();
    }

    public function obtenerDocumentos(Residente $residente): Collection
    {
        return $this->obtenerDocumentosResidente($residente);
    }

    /**
     * Adapta únicamente metadatos persistidos o comprobables en almacenamiento.
     */
    public function normalizarDocumentoBd(Documento $documento, Residente $residente): array
    {
        $extension = strtolower(pathinfo((string) $documento->ruta_archivo, PATHINFO_EXTENSION));
        $esPdf = $extension === 'pdf';
        $esImagen = in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true);
        $categoria = $this->resolverCategoria($documento, $esImagen);
        $visual = $this->obtenerConfigVisualTipo($categoria, $extension, $esPdf, $esImagen);
        $tamanoBytes = $this->obtenerTamanoReal((string) $documento->ruta_archivo);
        $fecha = $documento->fecha_validacion;
        $autor = $documento->cod_usuario ? User::query()->find($documento->cod_usuario) : null;
        $validador = $documento->cod_usuario_validacion ? User::query()->find($documento->cod_usuario_validacion) : null;
        $estaValidado = $fecha !== null && $documento->cod_usuario_validacion !== null;
        $urlArchivo = route('admin.documentos.descargar', ['documento' => $documento->cod_documento]);

        $trazabilidad = collect();
        if ($estaValidado) {
            $trazabilidad->push([
                'fecha' => $fecha->format('d/m/Y H:i'),
                'titulo' => 'Documento validado',
                'usuario' => $validador?->name ?? 'Usuario no identificado',
                'descripcion' => $documento->observacion ?: 'Sin observación de validación.',
            ]);
        }

        return [
            'id' => 'doc_bd_'.$documento->cod_documento,
            'bd_id' => $documento->cod_documento,
            'nombre' => $documento->nombre ?: 'Documento sin título',
            'descripcion' => $documento->observacion ?: 'Sin descripción registrada.',
            'categoria' => $categoria,
            'categoria_nombre' => self::CATEGORIAS[$categoria]['nombre'] ?? $categoria,
            'tipo' => $documento->tipo_documento ?: $visual['tipo_nombre'],
            'tipo_etiqueta' => $documento->tipo_documento ?: $visual['tipo_nombre'],
            'fecha' => $fecha?->format('Y-m-d'),
            'fecha_formateada' => $fecha?->format('d/m/Y') ?? 'No registrada',
            'hora_formateada' => $fecha?->format('H:i') ?? '',
            'tamano' => $tamanoBytes !== null ? $this->formatearBytes($tamanoBytes) : 'No disponible',
            'tamano_bytes' => $tamanoBytes,
            'extension' => $extension ?: 'archivo',
            'mime_type' => $documento->tipo_archivo ?: 'application/octet-stream',
            'es_pdf' => $esPdf,
            'es_imagen' => $esImagen,
            'es_verificado' => $estaValidado,
            'icono' => $visual['icono'],
            'icono_bg' => $visual['icono_bg'],
            'icono_color' => $visual['icono_color'],
            'badge_bg' => $visual['badge_bg'],
            'badge_color' => $visual['badge_color'],
            'badge_border' => $visual['badge_border'],
            'url_descarga' => $urlArchivo,
            'url_preview' => $esPdf || $esImagen ? $urlArchivo : null,
            'subido_por' => $autor?->name ?? 'No registrado',
            'area_origen' => 'No registrada',
            'relacionado_con' => 'Residente '.$residente->cod_residente,
            'observaciones' => $documento->observacion,
            'trazabilidad' => $trazabilidad->all(),
            'estado' => $documento->estado,
        ];
    }

    public function calcularMetricas(Collection $documentos): array
    {
        return [
            'total' => $documentos->count(),
            'clinicos' => $documentos->where('categoria', 'CLINICO')->count(),
            'administrativos' => $documentos->where('categoria', 'ADMINISTRATIVO')->count(),
            'imagenes' => $documentos->where('categoria', 'IMAGEN')->count(),
            'legales' => $documentos->where('categoria', 'LEGAL')->count(),
        ];
    }

    public function obtenerMetricas(Collection $documentos): array
    {
        return $this->calcularMetricas($documentos);
    }

    public function filtrarDocumentos(Collection $documentos, ?string $busqueda, ?string $tipo, ?string $categoria, string $orden = 'recientes'): Collection
    {
        $filtrados = $documentos;

        if ($busqueda && trim($busqueda) !== '') {
            $termino = mb_strtolower(trim($busqueda));
            $filtrados = $filtrados->filter(function (array $item) use ($termino): bool {
                return str_contains(mb_strtolower($item['nombre'] ?? ''), $termino)
                    || str_contains(mb_strtolower($item['descripcion'] ?? ''), $termino)
                    || str_contains(mb_strtolower($item['tipo'] ?? ''), $termino)
                    || str_contains(mb_strtolower($item['categoria_nombre'] ?? ''), $termino)
                    || str_contains(mb_strtolower($item['subido_por'] ?? ''), $termino);
            });
        }

        if ($categoria && ! in_array(strtoupper($categoria), ['TODAS', 'TODOS'], true)) {
            $filtrados = $filtrados->where('categoria', strtoupper($categoria));
        }

        if ($tipo && ! in_array(strtoupper($tipo), ['TODOS', 'TODAS'], true)) {
            $tipoBuscado = mb_strtolower(trim($tipo));
            $filtrados = $filtrados->filter(fn (array $item): bool =>
                str_contains(mb_strtolower($item['tipo'] ?? ''), $tipoBuscado)
                || str_contains(mb_strtolower($item['tipo_etiqueta'] ?? ''), $tipoBuscado)
            );
        }

        return match ($orden) {
            'antiguos' => $filtrados->sortBy(fn (array $item) => $item['fecha'] ?? '9999-12-31')->values(),
            'nombre_asc' => $filtrados->sortBy('nombre', SORT_NATURAL | SORT_FLAG_CASE)->values(),
            'nombre_desc' => $filtrados->sortByDesc('nombre', SORT_NATURAL | SORT_FLAG_CASE)->values(),
            'tamano_desc' => $filtrados->sortByDesc(fn (array $item) => $item['tamano_bytes'] ?? -1)->values(),
            default => $filtrados->sortByDesc(fn (array $item) => $item['fecha'] ?? '')->values(),
        };
    }

    public function filtrarYOrdenar(Collection $documentos, ?string $busqueda, ?string $tipo, ?string $categoria, string $orden = 'recientes'): Collection
    {
        return $this->filtrarDocumentos($documentos, $busqueda, $tipo, $categoria, $orden);
    }

    public function obtenerConfigVisualTipo(string $categoria, string $extension, bool $esPdf, bool $esImagen): array
    {
        [$icono, $iconoBg, $iconoColor] = match (true) {
            $esPdf => ['ph-file-pdf', 'bg-rose-50', 'text-rose-600'],
            $esImagen => ['ph-image', 'bg-emerald-50', 'text-emerald-600'],
            default => ['ph-file-text', 'bg-blue-50', 'text-blue-600'],
        };

        $badge = match ($categoria) {
            'CLINICO' => ['bg-blue-50', 'text-blue-700', 'border-blue-200', 'Informe clínico'],
            'ADMINISTRATIVO' => ['bg-purple-50', 'text-purple-700', 'border-purple-200', 'Administrativo'],
            'IMAGEN' => ['bg-emerald-50', 'text-emerald-700', 'border-emerald-200', 'Estudio de imagen'],
            'LEGAL' => ['bg-indigo-50', 'text-indigo-700', 'border-indigo-200', 'Legal'],
            'PERSONAL' => ['bg-slate-50', 'text-slate-700', 'border-slate-200', 'Identificación'],
            default => ['bg-slate-50', 'text-slate-700', 'border-slate-200', 'Documento'],
        };

        return [
            'icono' => $icono,
            'icono_bg' => $iconoBg,
            'icono_color' => $iconoColor,
            'badge_bg' => $badge[0],
            'badge_color' => $badge[1],
            'badge_border' => $badge[2],
            'tipo_nombre' => $badge[3],
        ];
    }

    private function resolverCategoria(Documento $documento, bool $esImagen): string
    {
        $texto = mb_strtolower(($documento->tipo_documento ?? '').' '.($documento->nombre ?? ''));

        return match (true) {
            str_contains($texto, 'legal'), str_contains($texto, 'consent') => 'LEGAL',
            $esImagen, str_contains($texto, 'imagen'), str_contains($texto, 'radio'), str_contains($texto, 'ecg'), str_contains($texto, 'foto') => 'IMAGEN',
            str_contains($texto, 'admin'), str_contains($texto, 'form'), str_contains($texto, 'seguro') => 'ADMINISTRATIVO',
            str_contains($texto, 'ident'), str_contains($texto, 'person'), str_contains($texto, 'cédula'), str_contains($texto, 'cedula') => 'PERSONAL',
            default => 'CLINICO',
        };
    }

    private function obtenerTamanoReal(string $ruta): ?int
    {
        if ($ruta === '') {
            return null;
        }

        try {
            return Storage::disk('local')->exists($ruta) ? Storage::disk('local')->size($ruta) : null;
        } catch (\Throwable) {
            return null;
        }
    }

    private function formatearBytes(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1).' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 0).' KB';
        }

        return $bytes.' B';
    }
}
