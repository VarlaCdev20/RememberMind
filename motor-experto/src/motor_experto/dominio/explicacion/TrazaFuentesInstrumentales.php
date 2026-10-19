<?php

declare(strict_types=1);

namespace MotorExperto\dominio\explicacion;

use MotorExperto\dominio\inferencia\MemoriaTrabajo;
use MotorExperto\dominio\objetos_valor\EvidenciaExperta;

/**
 * COMPONENTE DEL SISTEMA EXPERTO: Procedencia de la explicación instrumental.
 * Completa la traza declarativa con los originales verificados, incluidos los no disponibles.
 * Preserva microsegundos y no modifica el formato del contrato histórico COG-MEM.
 */
final class TrazaFuentesInstrumentales
{
    public function completar(array $traza, MemoriaTrabajo $memoria): array
    {
        $traza['fecha_corte'] = $memoria->contexto->fechaCorte->format('Y-m-d\TH:i:s.uP');
        foreach ($traza['rutas'] as &$ruta) {
            $e = $memoria->evidencia($ruta['evidencia']);
            $ruta['fecha_fuente'] = $e->fuente->fecha?->format('Y-m-d\TH:i:s.uP');
            $ruta['procedencia_instrumental'] = $this->procedencia($e);
        }
        unset($ruta);
        foreach ($traza['evidencias'] as &$fila) {
            $e = $memoria->evidencia($fila['id']);
            $fila['fecha_fuente'] = $e->fuente->fecha?->format('Y-m-d\TH:i:s.uP');
            $fila['procedencia_instrumental'] = $this->procedencia($e);
        }
        unset($fila);

        return $traza;
    }

    private function procedencia(EvidenciaExperta $e): ?array
    {
        if ($e->fuente->tabla !== 'aplicaciones_instrumento') {
            return null;
        }
        $c = $e->fuente->contexto;

        return ['cod_aplicacion' => $e->fuente->registro, 'cod_personal_original' => $e->fuente->personal,
            'cod_respuesta' => $c['cod_respuesta'] ?? null, 'cod_pregunta' => $c['cod_pregunta'] ?? null,
            'campo_original' => $c['campo_original'] ?? null,
            'instrumento' => $this->columnas($c['instrumento'] ?? null, ['cod_instrumento', 'codigo', 'nombre', 'tipo', 'version', 'estado']),
            'pregunta_original' => $this->columnas($c['pregunta_original'] ?? null,
                ['cod_pregunta', 'cod_instrumento', 'codigo', 'enunciado', 'dominio', 'tipo_respuesta', 'puntaje_maximo', 'orden', 'estado']),
            'registro_original' => $this->columnas($c['procedencia'] ?? null,
                ['cod_personal_aplicador', 'cod_usuario_registro', 'registrado_en', 'cod_documento_clinico', 'localizador_fuente', 'cod_aplicacion_anterior', 'estado_documento']),
            'fuente_edad' => $this->columnas($c['fuente_edad'] ?? null, ['tabla', 'campo', 'registro', 'fecha_nacimiento', 'fecha_aplicacion'])]
            + (($c['naturaleza_fuente'] ?? null) === 'OBSERVACION_CLINICA' ? ['naturaleza_fuente' => 'OBSERVACION_CLINICA',
                'opcion_original' => $this->columnas($c['opcion_original'] ?? null,
                    ['cod_opcion', 'cod_pregunta', 'nombre', 'valor', 'puntaje', 'orden', 'estado'])] : []);
    }

    private function columnas(?array $original, array $columnas): ?array
    {
        return $original === null ? null : array_intersect_key($original, array_fill_keys($columnas, true));
    }
}
