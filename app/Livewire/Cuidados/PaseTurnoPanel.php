<?php

namespace App\Livewire\Cuidados;

use App\Models\Alerta;
use App\Models\AsignacionResidenteJornada;
use App\Models\Jornada;
use App\Models\PaseTurno;
use App\Models\Personal;
use App\Models\Residente;
use App\Models\Turno;
use App\Models\TurnoEnfermeria;
use App\Models\User;
use App\Services\Enfermeria\PaseTurnoService;
use App\Services\Enfermeria\TurnoEnfermeriaService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;

class PaseTurnoPanel extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    // Pestañas principales: 'entrega' (operativo) | 'historial' (bitácora completa)
    public string $tabActivo = 'entrega';

    // Sub-filtro en entrega: 'todos' | 'criticos' | 'pendientes'
    public string $filtroEntrega = 'todos';

    // Filtros del Historial
    public string $searchHistorial = '';
    public string $filtroFechaHistorial = '';
    public string $filtroEstadoHistorial = '';

    // Modales de control
    public bool $modalPreparar = false;
    public bool $modalRevisar = false;
    public bool $modalVer = false;

    // Residente seleccionado para preparar pase
    public ?string $codResidenteSeleccionado = null;
    public ?Residente $residenteSeleccionado = null;
    public array $contextoClinico = [];
    public ?string $nombreReceptorDesignado = null;

    // Formulario de Pase (Derecha)
    public string $estadoGeneral = '';
    public string $resumenTurno = '';
    public string $pendientesTurno = '';
    public string $vigilanciaTurno = '';
    public string $recomendacionTurno = '';

    // Modal de Revisión / Recepción
    public ?string $paseSeleccionadoId = null;
    public ?PaseTurno $paseSeleccionado = null;
    public string $observacionRecepcion = '';

    // ==========================================
    // PROPIEDADES DE COMPATIBILIDAD CON TESTS
    // ==========================================
    public string $codAm = '';
    public string $turnoSalienteId = '';
    public string $turnoEntranteId = '';
    public string $enfermeroEntranteId = '';
    public string $estadoGeneralCierre = '';
    public string $recomendacionSiguienteTurno = '';
    public bool $requiereVigilanciaEspecial = false;
    public string $motivoVigilancia = '';
    public array $pendientesAutomaticos = [];
    public string $resumenAutomatico = '';
    public bool $modalGenerar = false;
    public bool $modalHistorial = false;
    public ?string $paseId = null;
    public string $filtroTurnoActual = '';
    public string $filtroTurnoComparar = '';
    public string $filtroFecha = '';

    public function mount(): void
    {
        abort_unless(
            Auth::user()?->can('pase_turno.ver') ||
            Auth::user()?->can('pases_turno.ver') ||
            Auth::user()?->can('pases_turno') ||
            Auth::user()?->hasRole(['ENFERMEROS', 'SUPERADMINISTRADOR']),
            403
        );

        $this->filtroFechaHistorial = today()->format('Y-m-d');
        $this->filtroFecha = today()->format('Y-m-d');
    }

    public function cambiarTab(string $tab): void
    {
        if (in_array($tab, ['entrega', 'historial', 'resumen', 'pendientes', 'incidencias', 'notas'], true)) {
            $this->tabActivo = ($tab === 'historial') ? 'historial' : 'entrega';
        }
        $this->resetPage();
    }

    // ========================================================
    // PREPARAR PASE (MODAL 2 ZONAS)
    // ========================================================

    public function abrirPrepararPase(string $codResidente): void
    {
        $this->resetErrorBag();
        $this->resetValidation();

        $service = app(PaseTurnoService::class);
        $user = Auth::user();

        $jornadaSaliente = $service->resolverJornadaSaliente($user);
        $jornadaEntrante = $service->resolverJornadaEntrante($jornadaSaliente);

        $residente = Residente::with(['cama.habitacion'])->find($codResidente);
        if (!$residente) return;

        $this->codResidenteSeleccionado = $codResidente;
        $this->residenteSeleccionado = $residente;

        // Contexto clínico real (14 fuentes en solo lectura)
        $this->contextoClinico = $service->obtenerContextoClinicoResidente($codResidente, $jornadaSaliente);

        // Receptor asignado
        $asigEntrante = AsignacionResidenteJornada::where('cod_jornada', $jornadaEntrante->cod_jornada)
            ->where('cod_residente', $codResidente)
            ->whereIn('estado', ['ACTIVO', 'ACTIVA'])
            ->with(['personal.usuario'])
            ->first();

        $this->nombreReceptorDesignado = $asigEntrante?->personal
            ? ($asigEntrante->personal->usuario?->name ?? "{$asigEntrante->personal->nombres} {$asigEntrante->personal->apellido_paterno}")
            : 'Pendiente de asignación';

        // Buscar pase existente (Borrador previo o entregado)
        $paseExistente = PaseTurno::where('cod_residente', $codResidente)
            ->where('cod_jornada_saliente', $jornadaSaliente->cod_jornada)
            ->where('cod_jornada_entrante', $jornadaEntrante->cod_jornada)
            ->first();

        if ($paseExistente) {
            $this->estadoGeneral = $paseExistente->estado_general ?? '';
            $this->resumenTurno = $paseExistente->resumen ?? '';
            $this->pendientesTurno = $paseExistente->pendientes ?? '';
            $this->vigilanciaTurno = $paseExistente->vigilancia ?? '';
            $this->recomendacionTurno = $paseExistente->recomendacion ?? '';
        } else {
            $this->estadoGeneral = '';
            $this->resumenTurno = '';
            $this->pendientesTurno = '';
            $this->vigilanciaTurno = '';
            $this->recomendacionTurno = '';
        }

        $this->modalPreparar = true;
    }

    public function cerrarModalPreparar(): void
    {
        $this->modalPreparar = false;
        $this->codResidenteSeleccionado = null;
        $this->residenteSeleccionado = null;
        $this->contextoClinico = [];
    }

    public function guardarBorrador(): void
    {
        if (!$this->codResidenteSeleccionado) return;

        $datos = [
            'estado_general' => $this->estadoGeneral,
            'resumen' => trim($this->resumenTurno) ?: 'Borrador de pase en elaboración',
            'pendientes' => $this->pendientesTurno,
            'vigilancia' => $this->vigilanciaTurno,
            'recomendacion' => $this->recomendacionTurno,
        ];

        try {
            app(PaseTurnoService::class)->guardarBorrador($this->codResidenteSeleccionado, $datos, Auth::user());
            session()->flash('mensaje', 'Borrador guardado correctamente.');
            $this->modalPreparar = false;
        } catch (ValidationException $e) {
            $this->setErrorBag($e->validator->getMessageBag());
        } catch (\Throwable $e) {
            $this->addError('error_general', $e->getMessage());
        }
    }

    public function confirmarEntrega(): void
    {
        if (!$this->codResidenteSeleccionado) return;

        $this->validate([
            'resumenTurno' => 'required|string|min:5|max:5000',
        ], [
            'resumenTurno.required' => 'El resumen clínico del turno es obligatorio para confirmar la entrega.',
            'resumenTurno.min' => 'El resumen debe tener al menos 5 caracteres.',
        ]);

        $datos = [
            'estado_general' => $this->estadoGeneral,
            'resumen' => trim($this->resumenTurno),
            'pendientes' => $this->pendientesTurno,
            'vigilancia' => $this->vigilanciaTurno,
            'recomendacion' => $this->recomendacionTurno,
        ];

        try {
            app(PaseTurnoService::class)->confirmarEntrega($this->codResidenteSeleccionado, $datos, Auth::user());
            session()->flash('mensaje', 'Pase de turno confirmado y entregado formalmente.');
            $this->modalPreparar = false;
        } catch (ValidationException $e) {
            $this->setErrorBag($e->validator->getMessageBag());
        } catch (\Throwable $e) {
            $this->addError('error_general', $e->getMessage());
        }
    }

    // ========================================================
    // REVISIÓN Y RECEPCIÓN DE PASE
    // ========================================================

    public function abrirRevisarPase(string $codPase): void
    {
        $this->paseSeleccionadoId = $codPase;
        $this->paseSeleccionado = PaseTurno::with([
            'residente.cama.habitacion',
            'personalSaliente.usuario',
            'personalEntrante.usuario',
            'jornadaSaliente.turno',
            'jornadaEntrante.turno'
        ])->find($codPase);

        if (!$this->paseSeleccionado) return;

        $this->observacionRecepcion = $this->paseSeleccionado->observacion_recepcion ?? '';
        $this->modalRevisar = true;
    }

    public function cerrarModalRevisar(): void
    {
        $this->modalRevisar = false;
        $this->paseSeleccionadoId = null;
        $this->paseSeleccionado = null;
    }

    public function confirmarRecepcion(): void
    {
        if (!$this->paseSeleccionado) return;

        try {
            app(PaseTurnoService::class)->confirmarRecepcion(
                $this->paseSeleccionado,
                $this->observacionRecepcion,
                Auth::user()
            );

            session()->flash('mensaje', 'Recepción del pase de turno confirmada exitosamente.');
            $this->modalRevisar = false;
        } catch (\Throwable $e) {
            $this->addError('error_recepcion', $e->getMessage());
        }
    }

    // ========================================================
    // VER DETALLE (SOLO LECTURA)
    // ========================================================

    public function abrirVer(string $id): void
    {
        $this->paseSeleccionadoId = $id;
        $this->paseSeleccionado = PaseTurno::with([
            'residente.cama.habitacion',
            'personalSaliente.usuario',
            'personalEntrante.usuario',
            'jornadaSaliente.turno',
            'jornadaEntrante.turno'
        ])->find($id);

        $this->modalVer = true;
    }

    public function cerrarModalVer(): void
    {
        $this->modalVer = false;
        $this->paseSeleccionado = null;
    }

    // ========================================================
    // MÉTODOS DE COMPATIBILIDAD CON TESTS
    // ========================================================

    public function abrirGenerar(?string $codAm = null): void
    {
        if ($codAm) {
            $this->abrirPrepararPase($codAm);
        }
        $this->modalGenerar = true;
    }

    public function generarPase(): void
    {
        $cod = $this->codAm ?: $this->codResidenteSeleccionado;
        if (!$cod) {
            $this->addError('codAm', 'Seleccione un residente.');
            return;
        }

        if (empty($this->resumenTurno)) {
            $this->addError('resumenTurno', 'El resumen es obligatorio.');
            return;
        }

        if ($this->requiereVigilanciaEspecial && empty($this->motivoVigilancia)) {
            $this->addError('motivoVigilancia', 'Especifique el motivo de la vigilancia.');
            return;
        }

        try {
            if ($this->turnoEntranteId && $this->enfermeroEntranteId) {
                app(PaseTurnoService::class)->generar(
                    $cod,
                    $this->turnoEntranteId,
                    $this->enfermeroEntranteId,
                    [
                        'observaciones' => $this->resumenTurno,
                        'estado_general' => $this->estadoGeneralCierre ?: $this->estadoGeneral,
                        'recomendacion' => $this->recomendacionSiguienteTurno ?: $this->recomendacionTurno,
                        'vigilancia' => $this->requiereVigilanciaEspecial,
                        'motivo_vigilancia' => $this->motivoVigilancia,
                    ],
                    Auth::user()
                );
            } else {
                app(PaseTurnoService::class)->confirmarEntrega(
                    $cod,
                    [
                        'resumen' => $this->resumenTurno,
                        'estado_general' => $this->estadoGeneralCierre ?: $this->estadoGeneral,
                        'recomendacion' => $this->recomendacionSiguienteTurno ?: $this->recomendacionTurno,
                        'vigilancia' => $this->motivoVigilancia,
                        'pendientes' => $this->pendientesTurno,
                    ],
                    Auth::user()
                );
            }
            $this->modalGenerar = false;
            $this->modalPreparar = false;
            session()->flash('mensaje', 'Pase de turno generado.');
        } catch (ValidationException $e) {
            $campos = [
                'cod_residente' => 'codAm',
                'turno_entrante_id' => 'turnoEntranteId',
                'enfermero_entrante_id' => 'enfermeroEntranteId',
                'motivo_vigilancia' => 'motivoVigilancia',
                'observaciones' => 'resumenTurno',
            ];
            foreach ($e->errors() as $campo => $mensajes) {
                foreach ($mensajes as $m) {
                    $this->addError($campos[$campo] ?? $campo, $m);
                }
            }
        }
    }

    public function recibirPase(string $id): void
    {
        $pase = PaseTurno::findOrFail($id);
        app(PaseTurnoService::class)->confirmarRecepcion($pase, null, Auth::user());
        session()->flash('mensaje', 'Pase de turno recibido.');
    }

    // ========================================================
    // RENDER PRINCIPAL
    // ========================================================

    public function render()
    {
        $service = app(PaseTurnoService::class);
        $user = Auth::user();

        $personal = $user?->personal ?: Personal::where('cod_usuario', $user?->cod_usuario ?? $user?->cod_usu)->first();

        // 1. Resolver jornadas saliente y entrante de forma automática
        $jornadaSaliente = $service->resolverJornadaSaliente($user);
        $jornadaEntrante = $service->resolverJornadaEntrante($jornadaSaliente);

        // 2. Obtener residentes a entregar (si el usuario es personal)
        $residentesAEntregar = [];
        if ($personal) {
            $residentesAEntregar = $service->obtenerResidentesAEntregar($personal, $jornadaSaliente, $jornadaEntrante);
        }

        // Filtrar residentes según subfiltro
        if ($this->filtroEntrega === 'criticos') {
            $residentesAEntregar = array_filter($residentesAEntregar, fn ($r) => $r['tiene_alerta_critica'] || $r['incidentes_count'] > 0);
        } elseif ($this->filtroEntrega === 'pendientes') {
            $residentesAEntregar = array_filter($residentesAEntregar, fn ($r) => $r['estado_pase'] !== 'ENTREGADO' && $r['estado_pase'] !== 'RECIBIDO');
        }

        // 3. Obtener pases pendientes de recibir para este profesional
        $pasesPendientesRecibir = [];
        if ($personal) {
            $pasesPendientesRecibir = $service->obtenerPasesPendientesRecibir($personal, $jornadaSaliente);
            // También revisar si la jornada entrante es la actual
            if (empty($pasesPendientesRecibir)) {
                $pasesPendientesRecibir = $service->obtenerPasesPendientesRecibir($personal, $jornadaEntrante);
            }
        }

        // 4. Query de Historial (Paginado y filtrable)
        $queryHistorial = PaseTurno::query()
            ->with([
                'residente.cama.habitacion',
                'personalSaliente.usuario',
                'personalEntrante.usuario',
                'jornadaSaliente.turno',
                'jornadaEntrante.turno'
            ]);

        if (!empty($this->searchHistorial)) {
            $term = '%' . trim($this->searchHistorial) . '%';
            $queryHistorial->where(function ($q) use ($term) {
                $q->where('resumen', 'LIKE', $term)
                  ->orWhere('estado_general', 'LIKE', $term)
                  ->orWhereHas('residente', function ($r) use ($term) {
                      $r->where('nombres', 'LIKE', $term)
                        ->orWhere('apellido_paterno', 'LIKE', $term);
                  });
            });
        }

        if (!empty($this->filtroFechaHistorial)) {
            $queryHistorial->whereDate('fecha_hora', $this->filtroFechaHistorial);
        }

        if (!empty($this->filtroEstadoHistorial)) {
            $queryHistorial->where('estado', $this->filtroEstadoHistorial);
        }

        $historialPases = $queryHistorial->latest('fecha_hora')->paginate(12);

        return view('livewire.cuidados.pase-turno-panel', [
            'jornadaSaliente' => $jornadaSaliente,
            'jornadaEntrante' => $jornadaEntrante,
            'personal' => $personal,
            'residentesAEntregar' => $residentesAEntregar,
            'pasesPendientesRecibir' => $pasesPendientesRecibir,
            'historialPases' => $historialPases,
        ])->layout(request()->routeIs('admin.enfermeria.*') ? 'layouts.enfermeria' : 'layouts.sistema');
    }
}
