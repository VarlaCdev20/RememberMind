<?php

namespace App\Modulos\Identidad\Aplicacion\Consultas;

use App\Models\User;
use App\Modulos\Identidad\Aplicacion\Servicios\EnviosCorreoDocumental;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Activity;

/** Lectura del seguimiento técnico existente: una fila por solicitud, sin nueva persistencia. */
final class HistorialCorreoDocumental
{
    public function autorizar(?User $actor): void
    {
        abort_unless($actor !== null && $actor->estado === 'ACTIVO' && $actor->canAny(['usuarios.ver', 'usuarios.editar', 'usuarios.crear']), 403);
    }

    private function base(User $actor): Builder
    {
        $this->autorizar($actor);
        $tipos = [];
        if ($actor->can('usuarios.ver')) {
            $tipos[] = 'ficha';
        }
        if ($actor->canAny(['usuarios.editar', 'usuarios.crear'])) {
            $tipos[] = 'paquete';
        }

        return Activity::query()->where('log_name', 'Correo')->where('event', 'correo_estado')
            ->where('causer_type', $actor->getMorphClass())->where('causer_id', $actor->getKey())
            ->where('subject_type', $actor->getMorphClass())->whereIn('properties->tipo', $tipos);
    }

    public function ultimos(User $actor, array $filtros): Builder
    {
        $ids = $this->base($actor)->selectRaw('MAX(id)')->groupBy('properties->solicitud');
        $query = $this->base($actor)->whereIn('id', $ids)->with(['subject.personal', 'subject.contactos', 'causer.personal', 'causer.contactos']);
        if ($filtros['usuario'] ?? '') {
            $query->where('subject_id', $filtros['usuario']);
        }
        if ($filtros['busqueda'] ?? '') {
            $texto = $filtros['busqueda'];
            $coincide = fn (Builder $persona) => $persona->where(fn (Builder $nombres) => $nombres
                ->whereLike('nombres', '%'.$texto.'%')->orWhereLike('apellido_paterno', '%'.$texto.'%')->orWhereLike('apellido_materno', '%'.$texto.'%'));
            $query->whereHasMorph('subject', [User::class], fn (Builder $usuarios) => $usuarios
                ->where(fn (Builder $personas) => $personas->whereHas('personal', $coincide)->orWhereHas('contactos', $coincide)));
        }
        foreach (['estado', 'tipo'] as $campo) {
            if ($filtros[$campo] ?? '') {
                $query->where('properties->'.$campo, $filtros[$campo]);
            }
        }
        // Límites con zona horaria de la aplicación; no comparar fechas UTC con fechas locales.
        if ($filtros['desde'] ?? '') {
            $query->where('created_at', '>=', CarbonImmutable::parse($filtros['desde'], config('app.timezone'))->startOfDay());
        }
        if ($filtros['hasta'] ?? '') {
            $query->where('created_at', '<', CarbonImmutable::parse($filtros['hasta'], config('app.timezone'))->addDay()->startOfDay());
        }

        return $query->orderByDesc('id');
    }

    public function detalle(User $actor, string $solicitud): array
    {
        abort_unless(Str::isUuid($solicitud), 404);
        $eventos = $this->base($actor)->where('properties->solicitud', $solicitud)
            ->with(['subject.personal', 'subject.contactos', 'causer.personal', 'causer.contactos'])->orderBy('id')->get();
        abort_if($eventos->isEmpty(), 404);

        return ['actual' => $this->presentar($eventos->last()), 'eventos' => $eventos->map(fn (Activity $a) => $this->presentar($a))->all()];
    }

    public function presentar(Activity $registro): array
    {
        $p = $registro->properties;
        $estado = $p['estado'];
        [$titulo, $mensaje] = EnviosCorreoDocumental::ESTADOS[$estado] ?? ['Estado no reconocido', 'Consulte a soporte antes de volver a enviar.'];

        return ['solicitud' => $p['solicitud'], 'usuario' => (string) $registro->subject_id,
            'destinatario' => $this->nombre($registro->subject),
            'responsable' => $this->nombre($registro->causer),
            'tipo' => $p['tipo'], 'documento' => $p['tipo'] === 'ficha' ? 'Ficha institucional' : 'Paquete documental',
            'estado' => $estado, 'titulo' => $titulo, 'mensaje' => $mensaje,
            'tono' => match ($estado) {
                'ACEPTADO' => 'success', 'FALLIDO' => 'danger', 'INDETERMINADO', 'REINTENTO' => 'warning', default => 'neutral'
            },
            'intento' => $p['intento'], 'fecha' => $registro->created_at->timezone(config('app.timezone'))->format('d/m/Y H:i:s'),
            'proximo' => empty($p['proximo_intento']) ? null : CarbonImmutable::parse($p['proximo_intento'])->timezone(config('app.timezone'))->format('d/m/Y H:i:s')];
    }

    private function nombre(?User $usuario): string
    {
        if ($usuario === null) {
            return 'Cuenta no disponible';
        }
        $persona = $usuario->personal ?? $usuario->contactos->first();

        return $persona ? trim($persona->nombres.' '.$persona->apellido_paterno.' '.$persona->apellido_materno) : 'Cuenta sin nombre asociado';
    }
}
