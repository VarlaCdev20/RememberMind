<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Presentacion\Soporte;

use DomainException;

/** Proyecta vínculos ya autorizados. No ejecuta ni completa una inferencia clínica. */
final class ConexionesResidenteVisible
{
    private const AREAS = [
        'COG-MEM' => ['Memoria', 'ph-brain'],
        'COG-ATE' => ['Atención', 'ph-target'],
        'COG-EJE' => ['Funciones ejecutivas', 'ph-tree-structure'],
        'COG-LEN' => ['Lenguaje', 'ph-chat-circle-text'],
        'COG-VIS' => ['Función visuoespacial', 'ph-eye'],
    ];

    private const REQUISITOS = [
        'C01' => ['Cambio corroborado', 'ph-clock-counter-clockwise'],
        'C02' => ['Alteración objetiva', 'ph-intersect'],
        'C03' => ['Independencia instrumental relativa', 'ph-person-simple-walk'],
        'C04' => ['Evaluación diferencial', 'ph-stethoscope'],
        'C05' => ['Calidad e interpretabilidad', 'ph-shield-check'],
    ];

    public function presentar(string $residente, ?array $petersen, ?array $capturas = null): array
    {
        $elementos = $originales = $soportes = [];
        foreach ($petersen === null ? [] : self::AREAS + self::REQUISITOS as $codigo => [$nombre, $icono]) {
            $esArea = isset(self::AREAS[$codigo]);
            $dato = ($esArea ? $petersen['dominios'] : $petersen['requisitos'])[$codigo] ?? null;
            if (! is_array($dato)) {
                continue;
            }
            $estado = $this->estado((string) ($dato['estado'] ?? ''), $esArea);
            $nodos = [$codigo => ['id' => $codigo, 'nombre' => $nombre, 'tipo' => $esArea ? 'Área' : 'Requisito',
                'estado' => $estado, 'columna' => 2, 'version' => null,
                'datos' => ['Alcance' => 'Estado de la consulta actual; no es un diagnóstico.', 'Estado documentado' => $estado]]];
            $aristas = [];
            foreach ($dato['valoraciones'] ?? [] as $valoracion) {
                $origen = $valoracion['origen'];
                $this->verificarResidente($residente, $origen);
                $id = 'valoracion-'.hash('sha256', $origen['tabla'].':'.$origen['registro']);
                $originales[$id] = true;
                $nodos[$id] = ['id' => $id, 'nombre' => 'Valoración de '.$nombre, 'tipo' => 'Valoración',
                    'estado' => $this->estado((string) $valoracion['estado'], $esArea), 'columna' => 1, 'version' => null,
                    'datos' => ['Justificación original' => $valoracion['justificacion'],
                        'Fecha' => FechaVisible::mostrar($origen['fecha'] ?? null), 'Autoría' => 'Conservada en el registro original']];
                $aristas[$id.'>'.$codigo] = ['origen' => $id, 'destino' => $codigo, 'relacion' => 'Valoración utilizada para este elemento'];
                foreach ($valoracion['soportes'] ?? [] as $soporte) {
                    $this->verificarResidente($residente, $soporte);
                    $sid = 'soporte-'.hash('sha256', $soporte['tabla'].':'.$soporte['registro']);
                    $soportes[$sid] = true;
                    $tipo = match ($soporte['tipo'] ?? '') {
                        'MEDICION' => 'Medición original', 'IADL' => 'Registro de funcionalidad',
                        'HISTORIA_PREVIA' => 'Historia previa', 'CORROBORACION' => 'Corroboración',
                        'DIFERENCIAL' => 'Revisión diferencial', 'CALIDAD' => 'Condiciones de interpretación',
                        default => 'Soporte original',
                    };
                    $nodos[$sid] = ['id' => $sid, 'nombre' => $tipo, 'tipo' => 'Soporte',
                        'estado' => 'Vinculado a la valoración', 'columna' => 0, 'version' => null,
                        'datos' => ['Fuente' => $tipo, 'Fecha' => FechaVisible::mostrar($soporte['fecha'] ?? null),
                            'Alcance' => 'Su vínculo procede de la valoración original; no acredita por sí solo una conclusión clínica.']];
                    $aristas[$sid.'>'.$id] = ['origen' => $sid, 'destino' => $id, 'relacion' => 'Soporte documentado de la valoración'];
                }
            }

            // Únicamente las dependencias que el motor declaró para C02, sin inferir otras.
            foreach ($dato['derivado_de'] ?? [] as $dominio) {
                if ($codigo !== 'C02' || ! isset(self::AREAS[$dominio], $petersen['dominios'][$dominio])) {
                    continue;
                }
                $nodos[$dominio] = ['id' => $dominio, 'nombre' => self::AREAS[$dominio][0], 'tipo' => 'Área',
                    'estado' => $this->estado($petersen['dominios'][$dominio]['estado'], true), 'columna' => 0, 'version' => null,
                    'datos' => ['Relación' => 'Dependencia declarada por el cálculo de este requisito.']];
                $aristas[$dominio.'>C02'] = ['origen' => $dominio, 'destino' => 'C02', 'relacion' => 'Derivación declarada por el motor'];
            }

            $elementos[] = ['id' => $codigo, 'nombre' => $nombre, 'icono' => $icono,
                'grupo' => $esArea ? 'areas' : 'requisitos', 'estado' => $estado,
                'codigoEstado' => $dato['estado'] ?? '', 'valoraciones' => count($dato['valoraciones'] ?? []),
                'cantidadVisible' => count($dato['valoraciones'] ?? []).' valoraciones vinculadas',
                'motivos' => array_map(MotivoPetersenVisible::explicar(...), $dato['motivos'] ?? []),
                'grafo' => ['nodos' => array_values($nodos), 'aristas' => array_values($aristas)]];
        }

        $registros = [];
        foreach ($capturas['capturas'] ?? [] as $captura) {
            if (($captura['cod_residente'] ?? null) !== $residente || empty($captura['cod_aplicacion'])) {
                throw new DomainException('La aplicación no pertenece al expediente solicitado.');
            }
            $tipo = $captura['instrumento_actual']['tipo'] ?? 'OTRO';
            $registros[$tipo][$captura['cod_aplicacion']] = $captura;
        }
        foreach ($registros as $tipo => $filas) {
            $nombre = match ($tipo) {
                'COGNITIVO' => 'Aplicaciones cognitivas', 'FUNCIONAL' => 'Aplicaciones funcionales',
                'OBSERVACIONAL' => 'Aplicaciones observacionales', default => 'Otras aplicaciones',
            };
            $id = 'fuentes-'.hash('sha256', $tipo);
            $nodos = [['id' => $id, 'nombre' => $nombre, 'tipo' => 'Conjunto de registros', 'columna' => 2,
                'estado' => count($filas).' registros disponibles', 'version' => null,
                'datos' => ['Alcance' => 'Aplicaciones del expediente actual. No son una evaluación cognitiva ni un diagnóstico.']]];
            $aristas = [];
            foreach ($filas as $captura) {
                $sid = 'aplicacion-'.hash('sha256', $captura['cod_aplicacion']);
                $nodos[] = ['id' => $sid, 'nombre' => $captura['nombre_instrumento'] ?: 'Instrumento sin nombre',
                    'tipo' => 'Registro', 'columna' => 0, 'version' => null,
                    'estado' => $captura['estado_original'] ?? 'Estado no disponible',
                    'datos' => ['Fecha' => FechaVisible::mostrar($captura['fecha_hora'] ?? null),
                        'Profesional' => $captura['autor'] ?: 'Autoría no disponible',
                        'Versión del instrumento' => $captura['version_instrumento'] ?: 'No documentada',
                        'Alcance' => 'Registro original consultado, sin atribuirle una interpretación nueva.']];
                $aristas[] = ['origen' => $sid, 'destino' => $id, 'relacion' => 'Aplicación perteneciente a este expediente'];
            }
            $elementos[] = ['id' => $id, 'nombre' => $nombre, 'icono' => 'ph-files', 'grupo' => 'fuentes',
                'estado' => 'Registros del expediente', 'codigoEstado' => 'DOCUMENTAL', 'valoraciones' => 0,
                'cantidadVisible' => count($filas).' registros disponibles', 'motivos' => [],
                'grafo' => ['nodos' => $nodos, 'aristas' => $aristas]];
        }

        return ['disponible' => $elementos !== [], 'elementos' => $elementos, 'valoraciones' => count($originales),
            'soportes' => count($soportes), 'fecha' => $petersen['fecha_corte'] ?? $capturas['fecha_corte'] ?? null];
    }

    private function verificarResidente(string $residente, array $fuente): void
    {
        if (($fuente['residente'] ?? null) !== $residente || empty($fuente['tabla']) || empty($fuente['registro'])) {
            throw new DomainException('La conexión no corresponde a una fuente identificada del residente.');
        }
    }

    private function estado(string $codigo, bool $area): string
    {
        return ($area ? ['A' => 'Alteración sustentada', 'P' => 'Rendimiento esperado sustentado',
            'U' => 'Información insuficiente', 'I' => 'No interpretable', 'X' => 'Información contradictoria']
            : ['S' => 'Sustentado', 'N' => 'No acreditado', 'U' => 'Pendiente de valoración', 'X' => 'Información contradictoria'])[$codigo]
            ?? 'Estado no interpretable';
    }
}
