<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Presentacion\Soporte;

use App\Modulos\ApoyoDecision\Aplicacion\DTOs\LenguajeResultadosExperto;

/** Presenta exclusivamente DTOs ya autorizados. No calcula criterios ni divulga la traza técnica. */
final class ExploracionClinicaVisible
{
    public function presentar(?array $perfil, ?array $informe, ?array $consulta, ?array $datos, bool $enfermeria, bool $entrenamiento): array
    {
        $petersen = $enfermeria ? null : ($perfil['petersen_institucional'] ?? null);
        $areas = [];
        foreach (LenguajeResultadosExperto::CRITERIOS as $codigo => $meta) {
            $actual = $petersen['dominios'][$codigo] ?? null;
            $codigoEstado = $actual['estado'] ?? null;
            $estado = $actual ? $this->estado((string) $codigoEstado, true) : ($informe['areas'][$codigo]['estado'] ?? 'Resultado no disponible');
            $componentes = $consulta['componentes'][$codigo] ?? [];
            $areas[] = ['id' => $codigo, 'nombre' => $meta['nombre'], 'icono' => $meta['icono'],
                'estado' => $estado, 'codigo' => $codigoEstado, 'origen' => $actual ? 'Consulta actual' : 'Historia conservada',
                'conclusion' => $actual ? 'Estado emitido por el motor a la fecha de corte de esta consulta.' : ($informe['areas'][$codigo]['conclusion'] ?? 'Sin resultado conservado para esta área.'),
                'motivos' => array_map(MotivoPetersenVisible::explicar(...), $actual['motivos'] ?? []),
                'componentes' => $componentes, 'valoraciones' => $this->valoraciones($actual['valoraciones'] ?? [])];
        }
        $requisitos = [];
        foreach (['C01' => 'Cambio corroborado', 'C02' => 'Alteración objetiva', 'C03' => 'Independencia instrumental relativa',
            'C04' => 'Evaluación diferencial', 'C05' => 'Calidad e interpretabilidad'] as $codigo => $nombre) {
            $dato = $petersen['requisitos'][$codigo] ?? null;
            $requisitos[] = ['id' => $codigo, 'nombre' => $nombre,
                'estado' => $dato ? $this->estado((string) ($dato['estado'] ?? ''), false) : 'Sin resultado disponible en esta lectura',
                'codigo' => $dato['estado'] ?? null, 'valoraciones' => $this->valoraciones($dato['valoraciones'] ?? []),
                'dependencias' => array_map($this->nombreElemento(...), $dato['derivado_de'] ?? []),
                'motivos' => array_map(MotivoPetersenVisible::explicar(...), $dato['motivos'] ?? [])];
        }
        $fuentes = [];
        $vinculos = [];
        $documentos = [];
        foreach (array_merge($petersen['dominios'] ?? [], $petersen['requisitos'] ?? []) as $codigo => $elemento) {
            foreach ($elemento['valoraciones'] ?? [] as $valoracion) {
                foreach ($valoracion['soportes'] ?? [] as $soporte) {
                    $clave = ($soporte['tabla'] ?? '').':'.($soporte['registro'] ?? '');
                    $vinculos[$clave][$codigo] = $this->nombreElemento($codigo);
                    if (($soporte['tabla'] ?? '') === 'documentos_clinicos') {
                        $documentos[$clave] = $soporte;
                    }
                }
            }
        }
        foreach ($perfil['capturas_originales']['capturas'] ?? [] as $captura) {
            $documento = $captura['documento_original'] ?? null;
            $fuentes[] = ['id' => hash('sha256', $captura['cod_aplicacion']), 'nombre' => $captura['nombre_instrumento'] ?: 'Instrumento sin nombre',
                'fecha' => FechaVisible::mostrar($captura['fecha_hora'] ?? null), 'autor' => $captura['autor'] ?: 'Autoría no disponible',
                'version' => $captura['version_instrumento'] ?: 'Versión no documentada', 'estado' => $captura['estado_original'] ?? 'Estado no disponible',
                'origen' => $documento ? 'Documento clínico vinculado' : 'Registro institucional',
                'documento' => $documento ? ['estado' => $documento['estado'] ?? 'No disponible',
                    'fecha' => FechaVisible::mostrar($documento['fecha_hora'] ?? null),
                    'verificacion' => ($captura['procedencia']['estado_documento'] ?? '') === 'VERIFICADO' ? 'Original verificado' : 'Original no verificable'] : null,
                'motivos' => array_map(LenguajeResultadosExperto::condicionInstrumental(...), $captura['motivos'] ?? []),
                'uso' => isset($vinculos['aplicaciones_instrumento:'.$captura['cod_aplicacion']])
                    ? 'Vinculada como soporte a: '.implode(', ', $vinculos['aplicaciones_instrumento:'.$captura['cod_aplicacion']]).' (cálculo actual).'
                    : 'Disponible en la consulta; su presencia no acredita participación en un resultado.'];
        }
        foreach ($documentos as $clave => $documento) {
            $fuentes[] = ['id' => hash('sha256', $clave), 'nombre' => 'Documento clínico de soporte',
                'fecha' => FechaVisible::mostrar($documento['fecha'] ?? null), 'autor' => 'Autoría conservada en el documento original',
                'version' => 'No corresponde a una versión instrumental', 'estado' => 'Vinculado a valoración original',
                'origen' => 'Documento clínico vinculado', 'documento' => null, 'motivos' => [],
                'descripcion' => $documento['datos_originales']['descripcion'] ?? null,
                'uso' => 'Vinculado como soporte a: '.implode(', ', $vinculos[$clave]).' (cálculo actual).'];
        }
        $resumen = $petersen ? ResumenPetersenVisible::presentar($petersen, false) : null;
        $patrones = ['PET-01' => 'Posible DCL amnésico de dominio único', 'PET-02' => 'Posible DCL amnésico multidominio',
            'PET-03' => 'Posible DCL no amnésico de dominio único', 'PET-04' => 'Posible DCL no amnésico multidominio'];

        return ['areas' => $areas, 'requisitos' => $requisitos, 'fuentes' => $fuentes,
            'resultado' => $petersen ? ['titulo' => $patrones[$petersen['patron'] ?? ''] ?? $resumen['titulo'],
                'estado' => $resumen['estado'], 'explicacion' => $resumen['explicacion'],
                'fecha' => FechaVisible::mostrar($petersen['fecha_corte'] ?? null), 'origen' => 'Cálculo actual · sin guardar',
                'motivos' => array_map(MotivoPetersenVisible::explicar(...), $petersen['impedimentos'] ?? [])] : null,
            'historia' => ['disponible' => ($datos['evaluacion'] ?? null) !== null,
                'fecha' => FechaVisible::mostrar($datos['evaluacion']['fecha'] ?? null),
                'titulo' => $consulta['titulo'] ?? 'Sin conclusión experta disponible',
                'resumen' => $consulta['resumen'] ?? 'No hay una evaluación histórica publicable en esta lectura.'],
            'entrenamiento' => $entrenamiento, 'solo_lectura' => $enfermeria,
            'con_mediciones' => count(array_filter($areas, fn ($a) => array_filter($a['componentes'], fn ($c) => count($c['mediciones']) > 0)))];
    }

    private function valoraciones(array $valoraciones): array
    {
        return array_map(fn ($v) => ['estado' => $v['estado'], 'justificacion' => $v['justificacion'],
            'fecha' => FechaVisible::mostrar($v['origen']['fecha'] ?? null),
            'soportes' => array_map(fn ($s) => ['tipo' => str_replace('_', ' ', $s['tipo'] ?? 'Soporte original'),
                'fecha' => FechaVisible::mostrar($s['fecha'] ?? null)], $v['soportes'] ?? [])], $valoraciones);
    }

    private function nombreElemento(string $codigo): string
    {
        return LenguajeResultadosExperto::CRITERIOS[$codigo]['nombre'] ?? [
            'C01' => 'Cambio corroborado', 'C02' => 'Alteración objetiva', 'C03' => 'Independencia instrumental relativa',
            'C04' => 'Evaluación diferencial', 'C05' => 'Calidad e interpretabilidad',
        ][$codigo] ?? 'Elemento valorado';
    }

    private function estado(string $estado, bool $area): string
    {
        return ($area ? ['A' => 'Alteración sustentada', 'P' => 'Rendimiento esperado sustentado', 'U' => 'Información insuficiente',
            'I' => 'No interpretable', 'X' => 'Información contradictoria']
            : ['S' => 'Sustentado', 'N' => 'No acreditado', 'U' => 'Pendiente de valoración', 'X' => 'Información contradictoria'])[$estado] ?? 'Estado no interpretable';
    }
}
