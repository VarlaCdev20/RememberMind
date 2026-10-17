<?php

namespace App\Modulos\Institucion\Presentacion\Livewire\Paginas;

use App\Models\FotografiaInstitucional;
use App\Models\User;
use App\Modulos\Institucion\Aplicacion\Acciones\GestionarFotografiasInstitucionales;
use App\Modulos\Institucion\Presentacion\Policies\FotografiaInstitucionalPolicy;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class GaleriaInstitucionalPanel extends Component
{
    use WithFileUploads, WithPagination;

    public string $busqueda = '';
    public string $estado = 'ACTIVA';
    public bool $formularioAbierto = false;
    public array $archivos = [];
    public string $titulo = '';
    public string $descripcion = '';
    public string $fecha_fotografia = '';
    public string $texto_alternativo = '';
    public bool $usar_en_bienvenida = false;
    public array $resultados = [];
    public string $mensaje = '';
    #[Locked] public ?string $editando = null;
    #[Locked] public array $original = [];

    private function actor(): ?User
    {
        return auth()->user() ? User::find(auth()->id()) : null;
    }

    private function autorizar(bool $escritura = false): void
    {
        $policy = app(FotografiaInstitucionalPolicy::class);
        abort_unless($escritura ? $policy->manage($this->actor()) : $policy->viewAny($this->actor()), 403);
    }

    public function mount(): void { $this->autorizar(); }

    public function updated(string $nombre): void
    {
        if (in_array($nombre, ['busqueda', 'estado'], true)) $this->resetPage();
    }

    private function datos(): array
    {
        return ['titulo' => $this->titulo, 'descripcion' => $this->descripcion ?: null,
            'fecha_fotografia' => $this->fecha_fotografia ?: null, 'texto_alternativo' => $this->texto_alternativo ?: null,
            'usar_en_bienvenida' => $this->usar_en_bienvenida];
    }

    public function tieneCambios(): bool
    {
        return count($this->archivos) > 0 || ($this->formularioAbierto && $this->datos() !== $this->original);
    }

    public function nuevo(): void
    {
        $this->autorizar(true);
        $this->cerrar();
        $this->formularioAbierto = true;
        $this->original = $this->datos();
    }

    public function editar(string $codigo): void
    {
        $this->autorizar(true);
        $foto = FotografiaInstitucional::findOrFail($codigo);
        $this->cerrar();
        $this->editando = $codigo;
        $this->titulo = $foto->titulo;
        $this->descripcion = $foto->descripcion ?? '';
        $this->fecha_fotografia = $foto->fecha_fotografia?->format('Y-m-d') ?? '';
        $this->texto_alternativo = $foto->texto_alternativo ?? '';
        $this->usar_en_bienvenida = $foto->usar_en_bienvenida;
        $this->formularioAbierto = true;
        $this->original = $this->datos();
    }

    public function cerrar(): void
    {
        $this->reset('editando', 'archivos', 'titulo', 'descripcion', 'fecha_fotografia', 'texto_alternativo', 'usar_en_bienvenida', 'formularioAbierto', 'original');
        $this->resetValidation();
        $this->dispatch('gallery-editor-reset');
    }

    public function guardar(GestionarFotografiasInstitucionales $accion): void
    {
        $this->autorizar(true);
        $this->validate(['titulo' => 'required|string|max:160', 'descripcion' => 'nullable|string|max:2000',
            'fecha_fotografia' => 'nullable|date_format:Y-m-d', 'texto_alternativo' => 'nullable|string|max:255',
            'archivos' => $this->editando ? 'array|max:0' : 'required|array|min:1|max:10'], [
                'titulo.required' => 'Escribe un título descriptivo.', 'archivos.required' => 'Selecciona al menos una fotografía.',
                'archivos.max' => 'Puedes subir hasta 10 fotografías por lote.', 'fecha_fotografia.date_format' => 'Revisa la fecha de la fotografía.']);
        if (array_sum(array_map(fn ($archivo) => $archivo->getSize(), $this->archivos)) > 6 * 1024 * 1024) {
            throw ValidationException::withMessages(['archivos' => 'El lote completo debe pesar como máximo 6 MB.']);
        }
        $this->resultados = [];
        $this->mensaje = '';
        if ($this->editando) {
            try { $accion->editar($this->actor(), $this->editando, $this->datos()); }
            catch (ValidationException $error) { throw $error; }
            catch (\Throwable $error) { report($error); $this->addError('guardado', 'No se pudieron guardar los cambios. Tu información sigue aquí para volver a intentar.'); return; }
            $this->cerrar();
            $this->mensaje = 'Datos de la fotografía actualizados.';
            return;
        }
        $fallidas = [];
        $guardadas = $existentes = 0;
        foreach ($this->archivos as $archivo) {
            try {
                $resultado = $accion->subir($this->actor(), $archivo, $this->datos());
                $resultado['nueva'] ? $guardadas++ : $existentes++;
                $this->resultados[] = ['nombre' => $archivo->getClientOriginalName(), 'estado' => $resultado['nueva'] ? 'Guardada' : 'Ya estaba en la galería ('.$resultado['foto']->estado.')'];
            } catch (ValidationException $error) {
                $fallidas[] = $archivo;
                $this->resultados[] = ['nombre' => $archivo->getClientOriginalName(), 'estado' => collect($error->errors())->flatten()->first()];
            } catch (\Symfony\Component\HttpKernel\Exception\HttpException $error) {
                throw $error;
            } catch (\Throwable $error) {
                report($error);
                $fallidas[] = $archivo;
                $this->resultados[] = ['nombre' => $archivo->getClientOriginalName(), 'estado' => 'No se pudo guardar. Puedes volver a intentar.'];
            }
        }
        $this->archivos = $fallidas;
        $this->mensaje = $guardadas.' guardadas · '.$existentes.' ya existentes · '.count($fallidas).' pendientes de guardar.';
        if (! $fallidas) $this->cerrar();
        $this->resetPage();
    }

    public function cambiarEstado(string $codigo, string $estado, GestionarFotografiasInstitucionales $accion): void
    {
        $this->autorizar(true);
        try { $accion->estado($this->actor(), $codigo, $estado); }
        catch (ValidationException $error) { throw $error; }
        catch (\Throwable $error) { report($error); $this->addError('guardado', 'No se pudo cambiar el estado. Vuelve a intentar.'); return; }
        $this->mensaje = $estado === 'ARCHIVADA' ? 'Fotografía archivada; su archivo se conserva.' : 'Fotografía reactivada.';
        $this->resetPage();
    }

    public function render()
    {
        $this->autorizar();
        $this->validateOnly('estado', ['estado' => 'required|in:ACTIVA,ARCHIVADA']);
        $fotos = FotografiaInstitucional::where('estado', $this->estado)
            ->when(trim($this->busqueda) !== '', fn ($query) => $query->where(function ($query) {
                $query->where('titulo', 'like', '%'.trim($this->busqueda).'%')->orWhere('descripcion', 'like', '%'.trim($this->busqueda).'%');
            }))->orderByDesc('fecha_registro')->orderByDesc('cod_fotografia')->paginate(12);
        return view('institucion::livewire.galeria-institucional-panel', ['fotografias' => $fotos,
            'puedeGestionar' => app(FotografiaInstitucionalPolicy::class)->manage($this->actor())])->layout('layouts.sistema');
    }
}
