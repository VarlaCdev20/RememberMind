<?php

namespace App\Modulos\Alertas\Aplicacion\Consultas;

use App\Compartido\Aplicacion\Formato\TextoVisible;
use App\Models\SignoVital;
use App\Models\User;
use App\Modulos\Alertas\Aplicacion\Formato\LenguajeAlerta;

/** Proyección de relaciones guardadas. No interpreta causalidad ni crea historia. */
final class ConsultarTrazaAlerta
{
    public function consultar(User $usuario, string $codigo, ?array $contextoProfesional = null): array
    {
        $alerta = app(BandejaAlertas::class)->consulta($usuario)
            ->with(['residente', 'responsable'])
            ->withCount('eventos')->findOrFail($codigo);
        $eventos = $alerta->eventos()->with('usuario')->orderByDesc('fecha_hora')
            ->orderByDesc('cod_evento_alerta')->limit(8)->get()->reverse()->values();
        $nodos = [
            ['id' => 'residente', 'label' => 'Residente', 'texto' => $alerta->residente?->nombre_completo ?: 'Residente no disponible', 'tono' => 'neutral'],
            ['id' => 'alerta', 'label' => 'Alerta', 'texto' => LenguajeAlerta::tipo($alerta->tipo), 'tono' => 'info'],
            ['id' => 'estado', 'label' => 'Estado actual', 'texto' => LenguajeAlerta::estado($alerta->estado), 'tono' => $alerta->estado === 'CERRADA' ? 'success' : 'info'],
        ];
        $aristas = [
            ['desde' => 'alerta', 'hasta' => 'residente', 'label' => 'pertenece a'],
            ['desde' => 'alerta', 'hasta' => 'estado', 'label' => 'estado actual'],
        ];
        $nodos[1]['detalles'] = [
            ['etiqueta' => 'Motivo del aviso', 'valor' => TextoVisible::sinCodigosInternos($alerta->descripcion)],
            ['etiqueta' => 'Prioridad registrada', 'valor' => LenguajeAlerta::prioridad($alerta->prioridad)],
            ['etiqueta' => 'Fecha del aviso', 'valor' => $alerta->fecha_hora?->format('d/m/Y H:i') ?: 'Sin fecha registrada'],
        ];
        if ($alerta->responsable) {
            $nodos[] = ['id' => 'responsable', 'label' => 'Responsable', 'texto' => LenguajeAlerta::responsable($alerta->responsable), 'tono' => 'neutral'];
            $aristas[] = ['desde' => 'alerta', 'hasta' => 'responsable', 'label' => 'asignada a'];
        }
        // Una referencia lógica sin registro verificable o sin permiso no es un nodo fuente.
        $signo = in_array(mb_strtoupper(trim((string) $alerta->modulo)), ['SIGNOS', 'SIGNOS_VITALES'], true) && $usuario->can('signos_vitales.ver') && filled($alerta->cod_registro)
            ? SignoVital::query()->where('cod_residente', $alerta->cod_residente)->find($alerta->cod_registro) : null;
        if ($signo) {
            $nodos[] = ['id' => 'origen', 'label' => 'Registro de signos', 'texto' => 'Medición del '.($signo->fecha_hora?->format('d/m/Y H:i') ?: 'día no registrado'), 'tono' => 'info'];
            $indiceSigno = array_key_last($nodos);
            $nodos[$indiceSigno]['grupo'] = 'origen';
            $nodos[$indiceSigno]['detalles'] = [];
            foreach (['presion_sistolica' => ['Presión sistólica', 'mmHg'], 'presion_diastolica' => ['Presión diastólica', 'mmHg'], 'frecuencia_cardiaca' => ['Frecuencia cardíaca', 'lpm'], 'frecuencia_respiratoria' => ['Frecuencia respiratoria', 'rpm'], 'temperatura' => ['Temperatura', '°C'], 'saturacion_oxigeno' => ['Saturación de oxígeno', '%'], 'glucemia' => ['Glucemia', 'mg/dL']] as $campo => [$etiqueta,$unidad]) {
                if ($signo->$campo !== null) {
                    $nodos[$indiceSigno]['detalles'][] = ['etiqueta' => $etiqueta, 'valor' => $signo->$campo.' '.$unidad];
                }
            }
            $aristas[] = ['desde' => 'alerta', 'hasta' => 'origen', 'label' => 'se originó en'];
        }
        $origenOperativo = in_array(mb_strtoupper(trim((string) $alerta->modulo)), ['INCIDENTE', 'INCIDENTES', 'SOLICITUD_MEDICA', 'PLAN', 'MEDICACION'], true)
            ? app(ConsultarOrigenOperativoAlerta::class)->consultar($usuario, $codigo) : ['disponible' => false, 'nodos' => [], 'aristas' => []];
        $nodos = [...$nodos, ...$origenOperativo['nodos']];
        $aristas = [...$aristas, ...$origenOperativo['aristas']];
        $movimientos = [];
        foreach ($eventos as $indice => $evento) {
            $id = 'evento-'.$evento->getKey();
            $texto = ($evento->usuario?->name ?: 'Autor no disponible').' · '.($evento->fecha_hora?->format('d/m/Y H:i') ?: 'Sin fecha registrada').' · '.TextoVisible::sinCodigosInternos($evento->descripcion);
            if ($indice >= $eventos->count() - 3) {
                $nodos[] = ['id' => $id, 'label' => match ($evento->tipo_evento) {
                    'CREACION','CREADA' => 'Aviso registrado', 'ATENCION','INTERVENCION' => 'Atención documentada', 'CIERRE' => 'Cierre documentado', 'SEGUIMIENTO' => 'Seguimiento guardado', default => str_replace('_', ' ', $evento->tipo_evento)
                }, 'texto' => $texto, 'tono' => $evento->tipo_evento === 'CIERRE' ? 'success' : 'neutral'];
                $aristas[] = ['desde' => 'alerta', 'hasta' => $id, 'label' => 'registró evento'];
            }
            $movimientos[] = ['id' => $id, 'tipo' => str_replace('_', ' ', $evento->tipo_evento), 'texto' => $texto];
        }

        $perfil = app(ExperienciaAlertas::class)->paraUsuario($usuario);
        abort_if($contextoProfesional && ($contextoProfesional['residenteCodigo'] !== $alerta->cod_residente || $contextoProfesional['rol'] !== $perfil['rol']), 422);
        $vinculos = app(VinculosProfesionalesAlertas::class)->paraAlertas($usuario, collect([$alerta]))[$codigo] ?? [];
        if (! in_array($perfil['rol'], array_column($vinculos, 'rol'), true)) {
            $contextoProfesional = null;
        }
        foreach ($vinculos as $indice => $vinculo) {
            $id = 'especialidad-'.$indice;
            $nodos[] = ['id' => $id, 'label' => $vinculo['nombre'], 'texto' => implode(' · ', $vinculo['motivos']),
                'tono' => 'info', 'grupo' => 'caso', 'detalles' => array_map(fn ($motivo) => ['etiqueta' => 'Motivo del vínculo', 'valor' => $motivo], $vinculo['motivos'])];
            $aristas[] = ['desde' => 'alerta', 'hasta' => $id, 'label' => 'vínculo profesional documentado'];
        }
        [$titulo, $proposito] = match ($perfil['foco']) {
            'turno' => ['Ruta del cuidado', 'Relaciona el residente, la responsabilidad y las actuaciones guardadas para continuar el cuidado.'],
            'clinico' => ['Revisión clínica', 'Distingue el origen verificable de la alerta de la última nota clínica del residente, conservando su autor profesional.'],
            'psicologia' => ['Red de bienestar', 'Conecta el residente con ánimo, conducta y continuidad documentados en su valoración.'],
            'nutricion' => ['Alimentación e hidratación', 'Permite recorrer alimentación, hidratación y continuidad de la última valoración nutricional.'],
            'funcional' => ['Movilidad y autonomía', 'Relaciona la valoración funcional con capacidad motora, autonomía y continuidad registradas.'],
            'pedagogia' => ['Recorrido de participación', 'Permite consultar observación de la actividad, cambios y continuidad del seguimiento pedagógico.'],
            'coordinacion' => ['Mesa de coordinación', 'Muestra quién está a cargo, la situación del aviso y los eventos que sustentan el seguimiento.'],
            'supervision' => ['Equipo y responsabilidades', 'Permite supervisar al responsable, la situación y las actuaciones documentadas del caso seleccionado.'],
            default => ['Origen e historial', 'Permite inspeccionar relaciones verificables, origen consultable y autoría de los eventos.'],
        };
        if (isset(VinculosProfesionalesAlertas::ESPECIALIDADES[$perfil['rol']]) && ! in_array($perfil['rol'], array_column($vinculos, 'rol'), true)) {
            $titulo = 'Origen y responsabilidad del caso';
            $proposito = 'Este caso no tiene vínculo verificable con tu especialidad. Consulta su origen y responsabilidad o elige un caso vinculado para revisar el contexto profesional.';
        }
        $nodos = array_map(fn ($nodo) => $nodo + ['grupo' => str_starts_with($nodo['id'], 'evento-') ? 'eventos' : 'caso'], $nodos);
        $aristas = array_map(fn ($arista) => $arista + ['tipo' => 'relacion'], $aristas);
        if ($contextoProfesional && $contextoProfesional['autorizado'] && $contextoProfesional['hayRegistro']) {
            $nodos[] = ['id' => 'contexto-profesional', 'label' => $contextoProfesional['titulo'],
                'texto' => 'Último registro vigente del residente · '.$contextoProfesional['fecha'], 'tono' => 'info', 'grupo' => 'profesional'];
            $aristas[] = ['desde' => 'residente', 'hasta' => 'contexto-profesional', 'label' => 'contexto del residente', 'tipo' => 'contexto'];
            $nodos[] = ['id' => 'autor-profesional', 'label' => 'Autor profesional', 'texto' => $contextoProfesional['autor'], 'tono' => 'neutral', 'grupo' => 'profesional'];
            $aristas[] = ['desde' => 'contexto-profesional', 'hasta' => 'autor-profesional', 'label' => 'registrado por', 'tipo' => 'relacion'];
            if ($contextoProfesional['atencion']) {
                $atencion = $contextoProfesional['atencion'];
                $nodos[] = ['id' => 'atencion-profesional', 'label' => 'Atención y área', 'texto' => $atencion['tipo'], 'tono' => 'neutral', 'grupo' => 'profesional',
                    'detalles' => [['etiqueta' => 'Área de atención', 'valor' => $atencion['area'] ?: 'Sin área registrada'], ['etiqueta' => 'Fecha de atención', 'valor' => $atencion['fecha'] ?: 'Sin fecha registrada']]];
                $aristas[] = ['desde' => 'contexto-profesional', 'hasta' => 'atencion-profesional', 'label' => 'respaldado por atención', 'tipo' => 'relacion'];
            }
            foreach (array_keys($contextoProfesional['secciones']) as $indiceSeccion => $seccion) {
                $campos = $contextoProfesional['secciones'][$seccion];
                $id = 'area-'.count($nodos);
                $detalles = array_map(fn ($campo) => $contextoProfesional['campos'][$campo], $campos);
                $nodos[] = ['id' => $id, 'label' => $seccion, 'texto' => 'Campos guardados de '.$contextoProfesional['titulo'],
                    'detalles' => $detalles, 'tono' => 'neutral', 'grupo' => 'profesional', 'seccion' => $indiceSeccion];
                $aristas[] = ['desde' => 'contexto-profesional', 'hasta' => $id, 'label' => 'contiene campos', 'tipo' => 'campo'];
            }
        }

        return ['codigo' => $alerta->getKey(), 'residente' => $alerta->residente?->nombre_completo,
            'foco' => $perfil['foco'], 'origenModulo' => mb_strtoupper(trim((string) $alerta->modulo)), 'estadoCodigo' => $alerta->estado,
            'titulo' => $titulo, 'proposito' => $proposito, 'inicio' => $origenOperativo['disponible'] ? 'origen-operativo' : ($contextoProfesional && $contextoProfesional['autorizado'] && $contextoProfesional['hayRegistro'] ? 'contexto-profesional' : ($signo ? 'origen' : 'alerta')),
            'nodos' => $nodos, 'aristas' => $aristas, 'movimientos' => array_reverse($movimientos),
            'especialidades' => $vinculos,
            'totalEventos' => $alerta->eventos_count,
            'fuenteDisponible' => $signo !== null || $origenOperativo['disponible']];
    }
}
