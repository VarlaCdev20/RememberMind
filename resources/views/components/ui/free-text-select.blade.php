@props(['id', 'label', 'model', 'options', 'maxlength' => 60, 'required' => false, 'help' => null, 'icon' => 'ph-list', 'placeholder' => 'Selecciona una opción'])
<div class="rm-free-text-select" x-data="rmFreeTextSelect($wire.entangle(@js($model)), @js(array_values($options)))">
    <x-ui.field :for="$id" :label="$label" :error="$model" :required="$required" :help="$help">
        <div class="rm-free-text-select__control">
            <i class="ph-bold {{ $icon }}" aria-hidden="true"></i>
            <select id="{{ $id }}" x-ref="native" class="rm-select" :value="choice" @change="choose($event.target.value)" @required($required)
                aria-invalid="{{ $errors->has($model) ? 'true' : 'false' }}" aria-describedby="{{ $id }}-guide{{ $help ? ' '.$id.'-help' : '' }}{{ $errors->has($model) ? ' '.$id.'-error' : '' }}">
                <option value="">{{ $placeholder }}</option>
                @foreach($options as $option)<option value="{{ $option }}">{{ $option }}</option>@endforeach
                <option value="__custom">Otro / escribir…</option>
            </select>
        </div>
        <p id="{{ $id }}-guide" class="rm-help">¿No está en la lista? Elige «Otro / escribir».</p>
        @isset($examples)
            <details class="rm-free-text-select__examples" @keydown.escape.stop="if($el.open) { $el.open = false; $refs.examplesTrigger.focus() }">
                <summary x-ref="examplesTrigger"><i class="ph-bold ph-images" aria-hidden="true"></i> Ver opciones ilustradas <i class="ph-bold ph-caret-down" aria-hidden="true"></i></summary>
                <div class="rm-free-text-select__examples-body">
                    <label for="{{ $id }}-search" class="rm-label">Buscar {{ mb_strtolower($label) }}</label>
                    <input id="{{ $id }}-search" class="rm-input" type="search" x-model="search" @input.stop @change.stop @keydown.enter.prevent placeholder="Escribe para filtrar los ejemplos…" aria-controls="{{ $id }}-examples">
                    <p class="rm-help">Esquemas de apoyo; no representan el aspecto clínico ni determinan la clasificación.</p>
                    <div id="{{ $id }}-examples" class="rm-free-text-select__examples-grid" role="group" aria-label="Opciones ilustradas: {{ mb_strtolower($label) }}">{{ $examples }}</div>
                    <p x-show="visibleOptions.length === 0" x-cloak class="rm-help" role="status">Sin coincidencias. Puedes usar «Otro / escribir».</p>
                    <button type="button" class="rm-free-text-select__write-option" @click="chooseIllustration('__custom', $el)"><i class="ph-bold ph-pencil-simple" aria-hidden="true"></i> Otro / escribir mi descripción</button>
                </div>
            </details>
        @endisset
        <div x-show="choice === '__custom'" x-cloak class="rm-free-text-select__custom">
            <label for="{{ $id }}-custom" class="rm-label">Especifica: {{ mb_strtolower($label) }}</label>
            <input id="{{ $id }}-custom" x-ref="custom" class="rm-input" type="text" :value="customText" @input="write($event.target.value)"
                maxlength="{{ $maxlength }}" :disabled="choice !== '__custom'" :required="choice === '__custom' && @js((bool) $required)"
                placeholder="Escribe tu descripción" aria-invalid="{{ $errors->has($model) ? 'true' : 'false' }}"
                @if($errors->has($model)) aria-describedby="{{ $id }}-error" @endif>
        </div>
    </x-ui.field>
</div>
