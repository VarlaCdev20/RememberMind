<?php

namespace App\Modulos\Identidad\Aplicacion\Consultas;

use App\Models\DestinatarioSolicitudDocumental;
use App\Models\Documento;
use App\Models\User;
use App\Modulos\Identidad\Aplicacion\Acciones\AutorizarGestionPersonal;
use Illuminate\Database\Eloquent\Builder;

final class ConsultarDocumentacionPersonal
{
    public function archivos(User $actor): Builder
    {
        $actor = $actor->fresh(['roles', 'personal']);
        abort_unless($actor?->estado === 'ACTIVO' && ! $actor->hasRole('FAMILIAR'), 403);
        $gestor = $this->esGestor($actor);
        abort_unless($gestor || $actor->personal, 403);

        $consulta = Documento::query()->whereNotNull('cod_usuario')
            ->whereNull('cod_residente')->whereNull('cod_contacto')->whereNull('cod_preadmision')
            ->whereHas('usuario.personal')
            ->where(function (Builder $q) use ($actor, $gestor): void {
                $q->where('cod_usuario', $actor->getKey());
                if ($gestor) {
                    $q->orWhereHas('usuario', function (Builder $u) use ($actor): void {
                        if (! $actor->hasRole('SUPERADMINISTRADOR')) {
                            $u->whereHas('roles')->whereDoesntHave('roles', fn ($r) => $r->whereNotIn('name', AutorizarGestionPersonal::ROLES_TRABAJADOR));
                        }
                    });
                }
            })->with(['usuario.personal', 'usuario.roles']);
        if (app(CapacidadDocumentalPersonal::class)->disponible()) {
            $consulta->orderByDesc('fecha_hora_carga');
        }

        return $consulta->orderBy('nombre')->orderBy('cod_documento');
    }

    public function esGestor(User $actor): bool
    {
        return $actor->fresh()?->estado === 'ACTIVO' && ! $actor->hasRole('FAMILIAR')
            && $actor->hasAnyRole(['GERENTE', 'SUPERADMINISTRADOR']) && $actor->can('personal_institucional.ver');
    }

    /** Solicitudes consultables desde el mismo alcance de titulares de la biblioteca. */
    public function solicitudesEnCarpetas(User $actor, bool $soloRolesGestionables = false): Builder
    {
        $consulta = $this->consulta($actor);

        return $consulta->whereHas('personal.usuario', function (Builder $u) use ($actor, $soloRolesGestionables): void {
            $u->where('cod_usuario', $actor->getKey());
            if ($this->esGestor($actor)) {
                $u->orWhere(function (Builder $equipo) use ($actor, $soloRolesGestionables): void {
                    if ($soloRolesGestionables || ! $actor->hasRole('SUPERADMINISTRADOR')) {
                        $equipo->whereHas('roles')->whereDoesntHave('roles', fn ($r) => $r->whereNotIn('name', AutorizarGestionPersonal::ROLES_TRABAJADOR));
                    } else {
                        $equipo->whereNotNull('cod_usuario');
                    }
                });
            }
        });
    }

    public function exigirCarpeta(User $actor, string $codigo): void
    {
        $hayArchivos = $this->archivos($actor)->where('cod_usuario', $codigo)->exists();
        $haySolicitudes = ! $hayArchivos && app(CapacidadDocumentalPersonal::class)->disponible()
            && $this->solicitudesEnCarpetas($actor)->whereHas('personal', fn ($p) => $p->where('cod_usuario', $codigo))->exists();
        abort_unless($hayArchivos || $haySolicitudes, 404);
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
                'tipoAviso' => 'plazo_por_revisar', 'ultima' => $ultima, 'requiere_entrega' => ! in_array($estado, ['VALIDADO', 'CARGADO', 'PENDIENTE'], true)];
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
        [$tipoAviso, $aviso] = match (true) {
            $estado === 'VALIDADO' => [null, null],
            in_array($estado, ['CARGADO', 'PENDIENTE'], true) => ['recibido', 'Documento recibido para revisión'],
            $estado === 'OBSERVADO' && $entregaEnPlazo => ['correccion', 'Corrección pendiente; la entrega original se recibió en plazo'],
            $estado === 'OBSERVADO' && $dias < 0 => ['vencido', 'Plazo registrado vencido; falta corregir el documento'],
            $estado === 'OBSERVADO' && $dias <= 2 => ['proximo', 'Plazo próximo; falta corregir el documento'],
            $estado === 'OBSERVADO' => ['correccion', 'Corrección solicitada; se conserva el plazo original'],
            $dias < 0 => ['vencido', 'Plazo registrado vencido'],
            $dias <= 2 => ['proximo', 'Plazo próximo: revisar documentación'],
            default => [null, null],
        };

        return compact('situacion', 'limite', 'aviso', 'tipoAviso', 'ultima') + $plazo + ['requiere_entrega' => ! in_array($estado, ['VALIDADO', 'CARGADO', 'PENDIENTE'], true)];
    }

    public function avisosGerencia(User $actor): ?array
    {
        $actor = $actor->fresh();

        return $actor?->can('alertas.ver') && $this->esGestor($actor) && app(CapacidadDocumentalPersonal::class)->disponible()
            ? $this->avisos($actor, soloExpedientes: true) : null;
    }

    public function avisos(User $actor, bool $soloExpedientes = false): array
    {
        if (! app(CapacidadDocumentalPersonal::class)->disponible()) {
            return [];
        }
        $actor = $actor->fresh();
        if (! $actor || $actor->estado !== 'ACTIVO' || $actor->hasRole('FAMILIAR') || (! $this->esGestor($actor) && ! $actor->personal)) {
            return [];
        }
        $gestor = $this->esGestor($actor);

        $destinatarios = ($soloExpedientes ? $this->solicitudesEnCarpetas($actor, soloRolesGestionables: true) : $this->consulta($actor))->get();
        app(PlazoExpedienteDocumental::class)->preparar($destinatarios);

        return $destinatarios->map(function ($d) use ($gestor) {
            $s = $this->situacion($d);
            if (! $s['aviso']) {
                return null;
            }

            return ['clave' => $d->getKey(), 'titulo' => $d->solicitud->nombre_documento, 'mensaje' => $s['aviso'], 'tipo' => $s['tipoAviso'],
                'persona' => $gestor ? trim($d->personal->nombres.' '.$d->personal->apellido_paterno) : null,
                'fecha' => $s['limite']?->format('d/m/Y') ?? 'Por revisar', 'url' => route('admin.personal.documentacion', ['vista' => 'solicitudes'])];
        })->filter()->values()->all();
    }
}
