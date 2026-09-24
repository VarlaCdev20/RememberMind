<?php

namespace App\Services\Documentos;

use App\Models\Documento;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentacionUsuarioService
{
    public function tiposDisponibles(User $usuario): array
    {
        $tipos = [
            ['cod_tipo_doc' => 'CI', 'nombre' => 'Cédula de Identidad', 'descripcion' => 'Anverso y reverso legibles.', 'obligatorio' => true, 'requiere_vencimiento' => false],
            ['cod_tipo_doc' => 'FOTO', 'nombre' => 'Fotografía actual', 'descripcion' => 'Fotografía formal vigente.', 'obligatorio' => true, 'requiere_vencimiento' => false],
            ['cod_tipo_doc' => 'CV', 'nombre' => 'Hoja de Vida / CV', 'descripcion' => 'Currículum actualizado y documentado.', 'obligatorio' => true, 'requiere_vencimiento' => false],
            ['cod_tipo_doc' => 'CONTRATO', 'nombre' => 'Contrato o prestación profesional', 'descripcion' => 'Relación laboral o de servicios.', 'obligatorio' => false, 'requiere_vencimiento' => true],
            ['cod_tipo_doc' => 'CONFIDENCIALIDAD', 'nombre' => 'Declaración de confidencialidad', 'descripcion' => 'Compromiso de manejo de información sensible.', 'obligatorio' => false, 'requiere_vencimiento' => false],
        ];

        if ($usuario->hasAnyRole(['ENFERMEROS', 'MEDICO GENERAL/GERIATRA', 'PSICOLOGO/A', 'NUTRICIONISTA', 'FISIOTERAPEUTA', 'PEDAGOGO'])) {
            $tipos[] = ['cod_tipo_doc' => 'TITULO', 'nombre' => 'Título o certificado de formación', 'descripcion' => 'Respaldo académico o técnico.', 'obligatorio' => true, 'requiere_vencimiento' => false];
            $tipos[] = ['cod_tipo_doc' => 'MATRICULA', 'nombre' => 'Matrícula profesional', 'descripcion' => 'Registro profesional vigente.', 'obligatorio' => $usuario->hasRole('MEDICO GENERAL/GERIATRA'), 'requiere_vencimiento' => true];
        }

        return $tipos;
    }

    public function tipoDisponible(User $usuario, string $codigo): object
    {
        $tipo = collect($this->tiposDisponibles($usuario))->firstWhere('cod_tipo_doc', $codigo);
        abort_unless($tipo, 404, 'Tipo documental no válido.');
        return (object) $tipo;
    }

    public function obtenerChecklistUsuario(User $usuario): array
    {
        $documentos = Documento::query()->where('cod_usuario', $usuario->cod_usuario)
            ->whereNotIn('estado', ['ANULADO', 'ANULADA'])->orderByDesc('fecha_validacion')->get()->groupBy('tipo_documento');

        return collect($this->tiposDisponibles($usuario))->map(function (array $tipo) use ($documentos): array {
            $actual = $documentos->get($tipo['cod_tipo_doc'])?->first();
            return $tipo + ['requiere_validacion' => true, 'cargado' => $actual !== null, 'documento' => $actual, 'estado' => $actual?->estado ?? 'PENDIENTE'];
        })->all();
    }

    public function calcularAvanceDocumental(User $usuario): array
    {
        $items = collect($this->obtenerChecklistUsuario($usuario));
        $requeridos = $items->where('obligatorio', true);
        $validadosRequeridos = $requeridos->where('estado', 'VALIDADO')->count();
        return [
            'total_requeridos' => $requeridos->count(), 'total_opcionales' => $items->where('obligatorio', false)->count(),
            'cargados' => $items->where('cargado', true)->count(), 'validados' => $items->where('estado', 'VALIDADO')->count(),
            'pendientes_validacion' => $items->whereIn('estado', ['PENDIENTE', 'CARGADO'])->count(),
            'observados' => $items->where('estado', 'OBSERVADO')->count(), 'vencidos' => $items->where('estado', 'VENCIDO')->count(),
            'porcentaje_avance' => $requeridos->isEmpty() ? 100 : (int) round(($validadosRequeridos / $requeridos->count()) * 100),
            'completo' => $validadosRequeridos === $requeridos->count(),
        ];
    }

    public function subirDocumento(User $usuario, string $codTipoDoc, UploadedFile $file, ?string $fechaEmision = null, ?string $fechaVencimiento = null, ?string $observaciones = null, ?User $subidoPor = null): Documento
    {
        $tipo = $this->tipoDisponible($usuario, $codTipoDoc);
        $nombre = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $extension = strtolower($file->getClientOriginalExtension());
        $ruta = $file->storeAs("documentos/usuarios/{$usuario->cod_usuario}", time()."_{$nombre}.{$extension}", 'public');
        $previo = Documento::query()->where('cod_usuario', $usuario->cod_usuario)->where('tipo_documento', $codTipoDoc)
            ->whereIn('estado', ['PENDIENTE', 'CARGADO', 'VALIDADO', 'OBSERVADO', 'VENCIDO'])->first();
        $detalle = array_filter([$observaciones, $fechaEmision ? "Fecha de emisión: {$fechaEmision}" : null, "Nombre original: {$file->getClientOriginalName()}", 'Subido por: '.($subidoPor?->cod_usuario ?? auth()->id())]);
        $documento = Documento::create([
            'cod_documento' => 'DOC_'.strtoupper(Str::random(10)), 'cod_usuario' => $usuario->cod_usuario,
            'cod_documento_anterior' => $previo?->cod_documento, 'tipo_documento' => $codTipoDoc, 'nombre' => $tipo->nombre,
            'ruta_archivo' => $ruta, 'tipo_archivo' => $file->getClientMimeType() ?: 'application/octet-stream',
            'hash_archivo' => hash_file('sha256', Storage::disk('public')->path($ruta)), 'fecha_vencimiento' => $fechaVencimiento,
            'estado' => 'CARGADO', 'observacion' => implode("\n", $detalle),
        ]);
        $previo?->update(['estado' => 'REEMPLAZADO']);
        activity('Usuarios')->causedBy(auth()->user())->performedOn($usuario)->event('documentacion')->log(($previo ? 'Reemplazó' : 'Subió')." el documento '{$tipo->nombre}'.");
        return $documento;
    }

    public function validarDocumento(Documento $documento, User $validador): void
    {
        $documento->update(['estado' => 'VALIDADO', 'cod_usuario_validacion' => $validador->cod_usuario, 'fecha_validacion' => now()]);
        $this->registrarActividad($documento, $validador, 'Validó');
    }

    public function observarDocumento(Documento $documento, string $motivo, User $validador): void
    {
        $documento->update(['estado' => 'OBSERVADO', 'cod_usuario_validacion' => $validador->cod_usuario, 'observacion' => trim($documento->observacion."\nOBSERVADO: {$motivo}")]);
        $this->registrarActividad($documento, $validador, 'Observó');
    }

    public function anularDocumento(Documento $documento, string $motivo, User $validador): void
    {
        $documento->update(['estado' => 'ANULADO', 'cod_usuario_validacion' => $validador->cod_usuario, 'observacion' => trim($documento->observacion."\nANULADO: {$motivo}")]);
        $this->registrarActividad($documento, $validador, 'Anuló');
    }

    private function registrarActividad(Documento $documento, User $usuario, string $accion): void
    {
        activity('Usuarios')->causedBy($usuario)->performedOn($documento->usuario)->event('documentacion')->log("{$accion} el documento '{$documento->nombre}'.");
    }
}
