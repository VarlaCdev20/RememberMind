<?php

namespace App\Modulos\Identidad\Aplicacion\Consultas;

use App\Models\DestinatarioSolicitudDocumental;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

final class ConsultarDocumentacionPersonal
{
    public function esGestor(User $actor): bool
    {
        return $actor->fresh()?->estado === 'ACTIVO' && ! $actor->hasRole('FAMILIAR')
            && $actor->hasAnyRole(['GERENTE', 'SUPERADMINISTRADOR']) && $actor->can('personal_institucional.ver');
    }

    public function consulta(User $actor): Builder
    {
        app(CapacidadDocumentalPersonal::class)->exigir();
        $actor = $actor->fresh();
        abort_unless($actor?->estado === 'ACTIVO' && ! $actor->hasRole('FAMILIAR'), 403);
        $gestor = $this->esGestor($actor);
        abort_unless($gestor || $actor->personal, 403);

        return DestinatarioSolicitudDocumental::query()
            ->when(! $gestor, fn ($q) => $q->where('cod_personal', $actor->personal->getKey()))
            ->with(['solicitud.solicitante.personal', 'personal.usuario', 'prorrogante.personal', 'entregas.documento'])
            ->orderBy('fecha_limite_original')->orderBy('cod_destinatario_documental');
    }

    public function situacion(DestinatarioSolicitudDocumental $destino): array
    {
        $ultima = $destino->entregas->sortByDesc(fn ($e) => $e->fecha_hora_registro->format('Y-m-d H:i:s').'|'.$e->getKey())->first();
        $estado = $ultima?->documento?->estado;
        $plazo = app(PlazoExpedienteDocumental::class)->resolver($destino);
        $limite = $plazo['limite'];
        if ($plazo['conflicto_plazo']) {
            return $plazo + ['situacion' => 'Plazo con concesiones contradictorias', 'aviso' => 'Revisar la historia: hay varias prórrogas para la misma alta.',
                'ultima' => $ultima, 'requiere_entrega' => ! in_array($estado, ['VALIDADO', 'CARGADO', 'PENDIENTE'], true)];
        }
        $situacion = match ($estado) {
            'VALIDADO' => 'Validado','CARGADO','PENDIENTE' => 'Recibido, pendiente de revisión','OBSERVADO' => 'Corrección solicitada',
            'ANULADO','ANULADA' => 'Documento anulado; revisar entrega',default => 'Sin entrega vigente'
        };
        $dias = (int) today()->diffInDays($limite, false);
        // Una revisión tardía no convierte una entrega original puntual en mora.
        // Se conserva el plazo; esto no concede una prórroga ni valida el documento.
        $primeraEntrega = $destino->entregas
            ->filter(fn ($e) => $e->documento && ! in_array($e->documento->estado, ['ANULADO', 'ANULADA'], true))
            ->sortBy('fecha_hora_registro')->first();
        $entregaEnPlazo = $primeraEntrega?->fecha_hora_registro?->lte($limite->copy()->endOfDay()) ?? false;
        $aviso = match (true) {
            $estado === 'VALIDADO' => null,
            in_array($estado, ['CARGADO', 'PENDIENTE'], true) => 'Documento recibido para revisión',
            $estado === 'OBSERVADO' && $entregaEnPlazo => 'Corrección pendiente; la entrega original se recibió en plazo',
            $estado === 'OBSERVADO' && $dias < 0 => 'Plazo registrado vencido; falta corregir el documento',
            $estado === 'OBSERVADO' && $dias <= 2 => 'Plazo próximo; falta corregir el documento',
            $estado === 'OBSERVADO' => 'Corrección solicitada; se conserva el plazo original',
            $dias < 0 => 'Plazo registrado vencido',
            $dias <= 2 => 'Plazo próximo: revisar documentación',
            default => null,
        };

        return compact('situacion', 'limite', 'aviso', 'ultima') + $plazo + ['requiere_entrega' => ! in_array($estado, ['VALIDADO', 'CARGADO', 'PENDIENTE'], true)];
    }

    public function avisos(User $actor): array
    {
        if (! app(CapacidadDocumentalPersonal::class)->disponible()) {
            return [];
        }
        $actor = $actor->fresh();
        if (! $actor || $actor->estado !== 'ACTIVO' || $actor->hasRole('FAMILIAR') || (! $this->esGestor($actor) && ! $actor->personal)) {
            return [];
        }
        $gestor = $this->esGestor($actor);

        $destinatarios = $this->consulta($actor)->get();
        app(PlazoExpedienteDocumental::class)->preparar($destinatarios);

        return $destinatarios->map(function ($d) use ($gestor) {
            $s = $this->situacion($d);
            if (! $s['aviso']) {
                return null;
            }

            return ['clave' => $d->getKey(), 'titulo' => $d->solicitud->nombre_documento, 'mensaje' => $s['aviso'],
                'persona' => $gestor ? trim($d->personal->nombres.' '.$d->personal->apellido_paterno) : null,
                'fecha' => $s['limite']?->format('d/m/Y') ?? 'Por revisar', 'url' => route('admin.personal.documentacion')];
        })->filter()->values()->all();
    }
}
