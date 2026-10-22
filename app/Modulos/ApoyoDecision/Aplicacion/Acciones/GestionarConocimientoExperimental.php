<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Aplicacion\Acciones;

use App\Compartido\Aplicacion\Contratos\CodigosEntidad;
use App\Models\User;
use App\Models\VersionModeloExperto;
use App\Modulos\ApoyoDecision\Aplicacion\Contratos\ConocimientoVersionado;
use App\Modulos\ApoyoDecision\Aplicacion\Contratos\IdentidadesConocimiento;
use App\Modulos\ApoyoDecision\Dominio\Reglas\BaseTecnicaDesechable;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use MotorExperto\dominio\conocimiento\EditorPaqueteExperimental;
use MotorExperto\dominio\conocimiento\PaqueteConocimiento;
use MotorExperto\dominio\conocimiento\reglas\EvaluadorReglas;
use MotorExperto\dominio\inferencia\MemoriaTrabajo;

/** Gestión técnica explícita en testing descartable. No es autorización institucional ni entrada HTTP. */
final class GestionarConocimientoExperimental
{
    public function crear(User $autor, PaqueteConocimiento $p, string $codigo, string $motivo, ?string $anterior = null): string
    {
        $this->destino();
        if (! $p->soloPruebasTecnicas || ! preg_match('/^TEST-[A-Za-z0-9-]{1,25}$/D', $codigo) || strlen(trim($motivo)) < 10) {
            throw new DomainException('Se exige paquete TEST_ONLY, código técnico y fundamento explícito.');
        }

        return app(CodigosEntidad::class)->serializar('versiones_modelo_experto', function () use ($autor, $p, $codigo, $motivo, $anterior): string {
            $usuario = $this->autor($autor);
            if (DB::table('versiones_modelo_experto')->where('codigo_version', $codigo)->exists()) {
                throw new DomainException('El código ya fue conservado; no se sobrescribe.');
            }
            if ($anterior !== null) {
                $this->versionTecnica($anterior);
            }
            $version = app(CodigosEntidad::class)->siguiente('versiones_modelo_experto');
            $fecha = now()->format('Y-m-d H:i:s');
            $tablas = ['versiones_modelo_experto' => [['cod_version_modelo' => $p->version, 'codigo_version' => $codigo,
                'nombre' => 'Sistema experto: revisión técnica', 'descripcion' => 'Conocimiento sintético; sin activación asistencial.',
                'estado' => 'INACTIVO', 'fecha_hora_creacion' => $fecha, 'fecha_hora_vigencia' => null,
                'fecha_hora_retiro' => null, 'motivo_cambio' => trim($motivo), 'cod_usuario_creacion' => $usuario->getKey(),
                'cod_version_anterior' => $anterior, 'observacion' => 'TEST_ONLY; gestor restringido a base de pruebas desechable.']]];
            foreach (PaqueteConocimiento::CLAVES as $tabla => $pk) {
                $tablas[$tabla] = $p->tabla($tabla);
                $columnas = DB::getSchemaBuilder()->getColumnListing($tabla);
                foreach ($tablas[$tabla] as &$fila) {
                    // Metadatos de esta creación, derivados exclusivamente del actor autorizado.
                    if (in_array('cod_usuario_creacion', $columnas, true)) {
                        $fila['cod_usuario_creacion'] = $usuario->getKey();
                    }
                    if (in_array('fecha_hora_creacion', $columnas, true)) {
                        $fila['fecha_hora_creacion'] = $fecha;
                    }
                }
                unset($fila);
            }
            $traducidas = app(IdentidadesConocimiento::class)->traducir($tablas, $p->version, $version, false);
            foreach ($traducidas as $tabla => $filas) {
                if ($filas) {
                    \App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Conservacion\BloquesExpertos::insertar($tabla, $filas);
                }
            }
            $this->leer($usuario, $version, $p);
            activity('sistema_experto')->causedBy($usuario)->event('revision_tecnica_creada')
                ->withProperties(['version' => $version, 'anterior' => $anterior, 'uso' => 'TEST_ONLY'])->log('Revisión técnica creada sin modificar conocimiento anterior.');

            return $version;
        });
    }

    public function leer(User $autor, string $version, PaqueteConocimiento $contrato): PaqueteConocimiento
    {
        $this->destino();

        return DB::transaction(function () use ($autor, $version, $contrato): PaqueteConocimiento {
            $usuario = $this->autor($autor);
            $this->versionTecnica($version);
            $p = new PaqueteConocimiento($version, app(ConocimientoVersionado::class)->instantanea($version),
                $contrato->contratosRelaciones, true, $contrato->tipoReglaResultado, $contrato->contratosExtraccion);
            activity('sistema_experto')->causedBy($usuario)->event('revision_tecnica_consultada')
                ->withProperties(['version' => $version, 'uso' => 'TEST_ONLY'])->log('Consulta autorizada de una revisión técnica exacta.');

            return $p;
        });
    }

    public function retirar(User $autor, string $version, string $motivo): void
    {
        $this->destino();
        if (strlen(trim($motivo)) < 10) {
            throw new DomainException('Indique el motivo técnico de retiro.');
        }
        DB::transaction(function () use ($autor, $version, $motivo): void {
            $usuario = $this->autor($autor);
            $fila = $this->versionTecnica($version);
            if ($fila->fecha_hora_retiro !== null) {
                return;
            }
            // Excepción acotada al metadato de elegibilidad; nunca cambia estado ni contenido histórico.
            DB::table('versiones_modelo_experto')->where('cod_version_modelo', $version)->update(['fecha_hora_retiro' => now()->format('Y-m-d H:i:s')]);
            activity('sistema_experto')->causedBy($usuario)->event('revision_tecnica_retirada')
                ->withProperties(['version' => $version, 'motivo' => trim($motivo), 'uso' => 'TEST_ONLY'])->log('Paquete retirado de nuevas pruebas; contenido histórico conservado.');
        });
    }

    /** La selección, bloqueo y cálculo comparten transacción con el retiro; una lectura previa no autoriza ejecución. */
    public function probar(User $autor, string $version, PaqueteConocimiento $contrato, MemoriaTrabajo $memoria, string $criterio): array
    {
        $this->destino();

        return DB::transaction(function () use ($autor, $version, $contrato, $memoria, $criterio): array {
            $usuario = $this->autor($autor);
            if ($this->versionTecnica($version)->fecha_hora_retiro !== null) {
                throw new DomainException('Paquete retirado de nuevas ejecuciones.');
            }
            if ($memoria->contexto->version !== $version) {
                throw new DomainException('La memoria pertenece a otra revisión.');
            }
            $p = $this->leer($usuario, $version, $contrato);
            (new EditorPaqueteExperimental)->verificarMemoria($p, $memoria, $criterio);
            $reglas = (new EvaluadorReglas)->evaluar($p, $memoria, $criterio);
            activity('sistema_experto')->causedBy($usuario)->event('revision_tecnica_probada')
                ->withProperties(['version' => $version, 'evaluacion' => $memoria->contexto->evaluacion, 'uso' => 'TEST_ONLY'])
                ->log('Reglas probadas con una memoria sintética verificada.');

            return ['uso' => 'TEST_ONLY', 'version' => $version, 'evaluacion' => $memoria->contexto->evaluacion,
                'reglas' => $reglas, 'persistido' => false];
        });
    }

    /** Mantiene elegibilidad y cálculo bajo el mismo bloqueo. El consumidor es código de aplicación, nunca entrada del archivo. */
    public function conRevisionElegible(User $autor, string $version, PaqueteConocimiento $contrato, callable $calcular): array
    {
        $this->destino();

        return DB::transaction(function () use ($autor, $version, $contrato, $calcular): array {
            $usuario = $this->autor($autor);
            if ($this->versionTecnica($version)->fecha_hora_retiro !== null) {
                throw new DomainException('Paquete retirado de nuevas ejecuciones.');
            }

            return $calcular($this->leer($usuario, $version, $contrato));
        });
    }

    /** Mantiene ambas revisiones elegibles bajo bloqueo durante toda la comparación de la batería. */
    public function compararImpacto(User $autor, string $versionAnterior, string $versionSucesora, PaqueteConocimiento $contrato, array $casos): array
    {
        $this->destino();

        return DB::transaction(function () use ($autor, $versionAnterior, $versionSucesora, $contrato, $casos): array {
            $usuario = $this->autor($autor);
            $versiones = array_unique([$versionAnterior, $versionSucesora]);
            sort($versiones, SORT_STRING);
            foreach ($versiones as $version) {
                if ($this->versionTecnica($version)->fecha_hora_retiro !== null) {
                    throw new DomainException('Paquete retirado de nuevas ejecuciones.');
                }
            }

            $resultado = (new EditorPaqueteExperimental)->compararImpacto($this->leer($usuario, $versionAnterior, $contrato),
                $this->leer($usuario, $versionSucesora, $contrato), $casos);
            activity('sistema_experto')->causedBy($usuario)->event('revisiones_tecnicas_comparadas')
                ->withProperties(['version_anterior' => $versionAnterior, 'version_sucesora' => $versionSucesora,
                    'casos' => array_keys($resultado['casos']), 'uso' => 'TEST_ONLY'])->log('Impacto comparado sobre los mismos originales sintéticos.');

            return $resultado;
        });
    }

    private function destino(): void
    {
        $c = DB::connection();
        if (! app()->environment('testing') || ! app()->runningInConsole()
            || ! BaseTecnicaDesechable::permite($c->getDriverName(), $c->getDatabaseName(), $c->getConfig('username'), $c->getConfig('host'))) {
            throw new DomainException('Gestión experimental disponible solo en consola de testing y base desechable.');
        }
    }

    private function autor(User $autor): User
    {
        $usuario = User::query()->lockForUpdate()->findOrFail($autor->getKey());
        Gate::forUser($usuario)->authorize('consultar', VersionModeloExperto::class);

        return $usuario;
    }

    private function versionTecnica(string $id): object
    {
        $fila = DB::table('versiones_modelo_experto')->where('cod_version_modelo', $id)->lockForUpdate()->first();
        if (! $fila || ! str_starts_with($fila->codigo_version, 'TEST-') || $fila->estado !== 'INACTIVO') {
            throw new DomainException('La operación solo admite revisiones inactivas creadas para pruebas.');
        }

        return $fila;
    }
}
