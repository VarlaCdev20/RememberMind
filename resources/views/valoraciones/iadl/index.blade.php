@component('layouts.sistema')
    <header class="mb-4 rounded-xl bg-white p-5 border border-slate-200">
        <h2 class="font-semibold text-xl text-slate-900">Actividades instrumentales de la vida diaria (IADL)</h2>
        <p class="text-sm text-slate-600">Residente: {{ $adulto->nombres }} {{ $adulto->apellido_paterno }} · {{ $adulto->cod_residente }}</p>
    </header><div class="max-w-7xl mx-auto p-4 sm:p-6 space-y-6">
        <section class="rounded-xl bg-white p-5 shadow-sm border border-slate-200">
            <h3 class="font-semibold text-slate-900">Situación funcional documentada</h3>
            <p class="mt-2 text-sm text-slate-700">
                Estado técnico: <strong>{{ str_replace('_', ' ', $sintesis['estado_tecnico']) }}</strong>.
                {{ $sintesis['actividades_con_oportunidad'] }} actividades tuvieron oportunidad de ejecución;
                {{ $sintesis['actividades_sin_oportunidad'] }} no permiten evaluar capacidad.
            </p>
            <p class="mt-2 text-amber-800 bg-amber-50 rounded-lg p-3 text-sm">
                Criterio DCL-C03: <strong>PENDIENTE DE VALORACIÓN PROFESIONAL</strong>.
                La rutina institucional puede impedir realizar una tarea sin que exista incapacidad.
                Barthel/Katz no sustituyen la valoración de IADL; esta pantalla no diagnostica deterioro cognitivo ni demencia.
            </p>
        </section>

        @if(session('status'))
            <div role="status" class="rounded-lg bg-emerald-50 p-3 text-sm text-emerald-900">
                {{ session('status') }}
            </div>
        @endif
        @if($errors->any())
            <div role="alert" class="rounded-lg bg-rose-50 p-4 text-sm text-rose-900">
                <p class="font-semibold">Revisa los datos antes de registrar:</p>
                <ul class="list-disc ml-5 mt-1 space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(auth()->user()->can($anterior ? 'controles_funcionales_iadl.rectificar' : 'controles_funcionales_iadl.crear'))
            <form method="POST" action="{{ route('admin.adultos-mayores.iadl.store', ['adulto_mayor' => $adulto->cod_residente]) }}"
                class="rounded-xl bg-white p-5 shadow-sm border border-slate-200 space-y-5">
                @csrf
                <div>
                    <h3 class="font-semibold text-slate-900">{{ $anterior ? 'Rectificar observación (sin borrar la anterior)' : 'Registrar observación IADL' }}</h3>
                    <p class="text-sm text-slate-600 mt-1">Una fila registra una actividad y su contexto, no un diagnóstico ni una puntuación global.</p>
                </div>
                @if($anterior)
                    <input type="hidden" name="cod_control_anterior" value="{{ $anterior->cod_control_iadl }}">
                    <p class="text-sm text-blue-900 bg-blue-50 rounded p-3">
                        Rectificación de {{ $anterior->cod_control_iadl }}. Conserva actividad y fecha originales.
                    </p>
                @endif
                <div class="grid gap-4 md:grid-cols-2">
                    <label class="block text-sm font-medium text-slate-800">
                        Fecha y hora del hecho
                        <input class="mt-1 block w-full border-slate-300 rounded-lg" type="datetime-local" name="fecha_hora"
                            required value="{{ old('fecha_hora', $anterior ? $anterior->fecha_hora->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i')) }}">
                    </label>
                    <label class="block text-sm font-medium text-slate-800">
                        Actividad instrumental
                        <select class="mt-1 block w-full border-slate-300 rounded-lg" name="actividad" required>
                            <option value="">Seleccione la actividad</option>
                            @foreach($actividades as $clave => $nombre)
                                <option value="{{ $clave }}" @selected(old('actividad', $anterior?->actividad) === $clave)>{{ $nombre }}</option>
                            @endforeach
                        </select>
                    </label>
                    @foreach($opciones as $campo => $valores)
                        <label class="block text-sm font-medium text-slate-800">
                            {{ match($campo) {
                                'oportunidad' => '¿Hubo oportunidad de realizarla?',
                                'desempeno' => 'Desempeño observado o referido',
                                'tipo_ayuda' => 'Tipo de apoyo',
                                'causa_ayuda' => 'Motivo del apoyo (no presuponer causa cognitiva)',
                                'nivel_previo' => 'Nivel previo conocido',
                                'fuente' => 'Origen de la información',
                                default => str_replace('_', ' ', ucfirst($campo))
                            } }}
                            <select class="mt-1 block w-full border-slate-300 rounded-lg" name="{{ $campo }}" required>
                                <option value="">Seleccione</option>
                                @foreach($valores as $opcion)
                                    <option value="{{ $opcion }}" @selected(old($campo, $anterior?->{$campo}) === $opcion)>
                                        {{ str_replace('_', ' ', $opcion) }}
                                    </option>
                                @endforeach
                            </select>
                        </label>
                    @endforeach
                </div>
                <label class="block text-sm font-medium text-slate-800">
                    Descripción contextual (obligatoria si recibió ayuda)
                    <textarea name="observacion" rows="3" maxlength="2000"
                        class="mt-1 block w-full border-slate-300 rounded-lg"
                        placeholder="Ejemplo: la medicación la organiza Enfermería por protocolo; no se observó al residente intentarla.">{{ old('observacion', $anterior?->observacion) }}</textarea>
                </label>
                <button type="submit" class="inline-flex items-center rounded-lg bg-indigo-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-800">
                    {{ $anterior ? 'Guardar rectificación' : 'Registrar observación' }}
                </button>
            </form>
        @endif

        <section class="rounded-xl bg-white shadow-sm border border-slate-200">
            <div class="p-5 border-b border-slate-200">
                <h3 class="font-semibold text-slate-900">Historial IADL — no se eliminan observaciones</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-slate-50 text-slate-700">
                        <tr>
                            <th class="p-3">Fecha</th><th class="p-3">Actividad</th><th class="p-3">Oportunidad</th>
                            <th class="p-3">Desempeño</th><th class="p-3">Ayuda / causa</th>
                            <th class="p-3">Fuente</th><th class="p-3">Historial</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($registros as $fila)
                            <tr>
                                <td class="p-3 whitespace-nowrap">{{ $fila['fecha_hora'] }}</td>
                                <td class="p-3">{{ $actividades[$fila['actividad']] ?? $fila['actividad'] }}</td>
                                <td class="p-3">{{ str_replace('_', ' ', $fila['oportunidad']) }}</td>
                                <td class="p-3">{{ str_replace('_', ' ', $fila['desempeno']) }}</td>
                                <td class="p-3">{{ str_replace('_', ' ', $fila['tipo_ayuda']) }} / {{ str_replace('_', ' ', $fila['causa_ayuda']) }}</td>
                                <td class="p-3">{{ str_replace('_', ' ', $fila['fuente']) }}</td>
                                <td class="p-3 text-xs">
                                    {{ $fila['estado'] }}
                                    @if($fila['cod_control_anterior'])
                                        <p>Corrige {{ $fila['cod_control_anterior'] }}</p>
                                    @endif
                                    @if(auth()->user()->can('controles_funcionales_iadl.rectificar'))
                                        <a class="text-indigo-700 underline" href="{{ route('admin.adultos-mayores.iadl.index', ['adulto_mayor' => $adulto->cod_residente, 'rectificar' => $fila['cod_control_iadl']]) }}">Rectificar</a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="p-6 text-center text-slate-600">Sin observaciones IADL. C03 permanece desconocido.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endcomponent
