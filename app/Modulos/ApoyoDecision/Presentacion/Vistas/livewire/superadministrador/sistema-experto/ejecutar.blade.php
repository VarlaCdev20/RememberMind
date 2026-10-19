<form wire:submit="ejecutarPerfil" class="space-y-4">
    <div>
        <label for="lab-escenario" class="block font-semibold mb-2">Caso ficticio</label>
        <select id="lab-escenario" wire:model="escenarioSeleccionado" class="rm-input w-full" aria-describedby="lab-escenario-ayuda @error('escenarioSeleccionado') lab-escenario-error @enderror" @error('escenarioSeleccionado') aria-invalid="true" @enderror>
            @foreach($escenariosPerfil as $id => $nombre)<option value="{{ $id }}">{{ $nombre }}</option>@endforeach
        </select>
        <p id="lab-escenario-ayuda" class="text-sm text-rm-secondary mt-2">No recibe residentes ni fuentes clínicas. La fecha de corte pertenece al caso ficticio.</p>
        @error('escenarioSeleccionado')<p id="lab-escenario-error" role="alert">{{ $message }}</p>@enderror
    </div>
    <p class="text-sm">Versión experimental G09: <strong>{{ \MotorExperto\investigacion\g09\CatalogoReglasG09Experimental::VERSION }}</strong>. Las versiones de Memoria, Petersen y su puente se muestran en el resultado.</p>
    <button type="submit" wire:loading.attr="disabled" wire:target="ejecutarPerfil" class="rm-btn rm-btn-primary" @disabled(!$laboratorioDisponible)>
        <span wire:loading.remove wire:target="ejecutarPerfil">Ejecutar evaluación</span><span wire:loading wire:target="ejecutarPerfil">Evaluando caso sintético…</span>
    </button>
    <p class="text-sm text-rm-secondary">La selección de otra revisión para ejecutar está restringida al contrato de consola. La versión consultada en Conocimiento no cambia esta ejecución.</p>
</form>
