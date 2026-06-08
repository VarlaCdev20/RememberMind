<?php

namespace App\Exports;

use App\Models\AdultoMayor;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;

class AdultoIndividualExport implements WithMultipleSheets
{
    public function __construct(private AdultoMayor $adulto) {}

    public function sheets(): array
    {
        return [
            new AdultoGeneralSheet($this->adulto),
            new SignosVitalesIndSheet($this->adulto),
            new MedicacionesIndSheet($this->adulto),
            new ValoracionesIndSheet($this->adulto),
            new EvaluacionesCognitivasIndSheet($this->adulto),
        ];
    }
}

// ─────────────────────────────────────────────────────────────
// Hoja 1: Datos Generales
// ─────────────────────────────────────────────────────────────
class AdultoGeneralSheet implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize, WithStyles
{
    public function __construct(private AdultoMayor $adulto) {}

    public function title(): string
    {
        return 'Datos Generales';
    }

    public function headings(): array
    {
        return ['Campo', 'Valor'];
    }

    public function collection()
    {
        $a = $this->adulto;
        $edad = $a->fecha_nac ? \Carbon\Carbon::parse($a->fecha_nac)->age : '—';

        return collect([
            ['Código expediente',       $a->cod_am],
            ['Nombres',                 $a->nombres],
            ['Apellido paterno',        $a->ap_paterno],
            ['Apellido materno',        $a->ap_materno ?? '—'],
            ['C.I.',                    $a->ci . ($a->complemento_ci ? '-' . $a->complemento_ci : '') . ' ' . $a->expedicion_ci],
            ['Fecha de nacimiento',     $a->fecha_nac ? $a->fecha_nac->format('d/m/Y') : '—'],
            ['Edad',                    $edad . ' años'],
            ['Género',                  $a->genero ?? '—'],
            ['Estado civil',            $a->estado_civil ?? '—'],
            ['Grupo sanguíneo',         $a->grupo_sanguineo ?? '—'],
            ['Seguro de salud',         $a->seguro_salud ?? '—'],
            ['Nivel educativo',         $a->nivel_educat ?? '—'],
            ['Alergias conocidas',      $a->alergias ?? '—'],
            ['Celular',                 $a->celular ?? '—'],
            ['Teléfono fijo',           $a->telefono_fijo ?? '—'],
            ['Departamento residencia', $a->departamento_residencia ?? '—'],
            ['Ciudad / Municipio',      $a->ciudad_municipio ?? '—'],
            ['Zona / Barrio',           $a->zona ?? '—'],
            ['Calle / Avenida',         $a->calle ?? '—'],
            ['Fecha de ingreso',        $a->fecha_ing ? $a->fecha_ing->format('d/m/Y') : '—'],
            ['Tipo de ingreso',         $a->tipo_ing ?? '—'],
            ['Permanencia',             $a->permanencia ?? '—'],
            ['Estado actual',           $a->estado?->estado ?? '—'],
            ['Contacto emergencia',     $a->contacto_emergencia_nombre ?? '—'],
            ['Parentesco',              $a->contacto_emergencia_parentesco ?? '—'],
            ['Celular contacto',        $a->contacto_emergencia_celular ?? '—'],
            ['Dirección contacto',      $a->contacto_emergencia_direccion ?? '—'],
            ['Responsable principal',   $a->responsable_principal ? 'Sí' : 'No'],
            ['Autorizado inf. médica',  $a->autorizado_informacion_medica ? 'Sí' : 'No'],
            ['Generado el',             now()->format('d/m/Y H:i')],
        ]);
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                  'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2F3E5C']]],
            'A'=> ['font' => ['bold' => true]],
        ];
    }
}

// ─────────────────────────────────────────────────────────────
// Hoja 2: Signos Vitales
// ─────────────────────────────────────────────────────────────
class SignosVitalesIndSheet implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize, WithStyles
{
    public function __construct(private AdultoMayor $adulto) {}

    public function title(): string
    {
        return 'Signos Vitales';
    }

    public function headings(): array
    {
        return [
            'Fecha', 'Hora', 'Pres. Sistólica', 'Pres. Diastólica',
            'Frec. Cardíaca', 'Frec. Resp.', 'Temperatura (°C)',
            'Saturación (%)', 'Glucosa', 'Peso (kg)', 'Talla (m)', 'IMC', 'Estado',
        ];
    }

    public function collection()
    {
        return $this->adulto->signosVitales()
            ->orderByDesc('fecha')
            ->get()
            ->map(fn($s) => [
                $s->fecha ? $s->fecha->format('d/m/Y') : '—',
                $s->horaFormateada,
                $s->presion_sistolica,
                $s->presion_diastolica,
                $s->frecuencia_cardiaca,
                $s->frecuencia_respiratoria,
                $s->temperatura,
                $s->saturacion,
                $s->glucosa,
                $s->peso,
                $s->talla,
                $s->imc,
                $s->estado,
            ]);
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                  'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'C45F4B']]],
        ];
    }
}

// ─────────────────────────────────────────────────────────────
// Hoja 3: Medicación
// ─────────────────────────────────────────────────────────────
class MedicacionesIndSheet implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize, WithStyles
{
    public function __construct(private AdultoMayor $adulto) {}

    public function title(): string
    {
        return 'Medicación';
    }

    public function headings(): array
    {
        return [
            'Medicamento', 'Dosis', 'Frecuencia', 'Vía Admin.',
            'Hora Programada', 'F. Inicio', 'F. Fin', 'Médico Indica',
            'Observación', 'Estado',
        ];
    }

    public function collection()
    {
        return $this->adulto->medicaciones()
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($m) => [
                $m->nombre_medicamento,
                $m->dosis,
                $m->frecuencia,
                $m->via_administracion,
                $m->hora_programada ? substr((string)$m->hora_programada, 0, 5) : '—',
                $m->fecha_inicio ? $m->fecha_inicio->format('d/m/Y') : '—',
                $m->fecha_fin    ? $m->fecha_fin->format('d/m/Y')    : '—',
                $m->medico_indica   ?? '—',
                $m->observacion     ?? '—',
                $m->estado,
            ]);
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                  'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '617453']]],
        ];
    }
}

// ─────────────────────────────────────────────────────────────
// Hoja 4: Valoración Funcional
// ─────────────────────────────────────────────────────────────
class ValoracionesIndSheet implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize, WithStyles
{
    public function __construct(private AdultoMayor $adulto) {}

    public function title(): string
    {
        return 'Valoración Funcional';
    }

    public function headings(): array
    {
        return [
            'Fecha', 'Índice Barthel', 'Nivel Dependencia', 'Riesgo Caída',
            'Come Solo', 'Se Baña Solo', 'Se Viste Solo', 'Va al Baño Solo',
            'Camina Solo', 'Usa Bastón', 'Usa Andador', 'Usa Silla Ruedas',
            'Baja Visión', 'Baja Audición', 'Estado',
        ];
    }

    public function collection()
    {
        return $this->adulto->valoracionesFuncionales()
            ->orderByDesc('fecha_valoracion')
            ->get()
            ->map(fn($v) => [
                $v->fecha_valoracion ? $v->fecha_valoracion->format('d/m/Y') : '—',
                $v->indice_barthel,
                $v->nivel_dependencia ?? '—',
                $v->riesgo_caida      ?? '—',
                $v->come_solo         ? 'Sí' : 'No',
                $v->se_bana_solo      ? 'Sí' : 'No',
                $v->se_viste_solo     ? 'Sí' : 'No',
                $v->va_bano_solo      ? 'Sí' : 'No',
                $v->camina_solo       ? 'Sí' : 'No',
                $v->usa_baston        ? 'Sí' : 'No',
                $v->usa_andador       ? 'Sí' : 'No',
                $v->usa_silla_ruedas  ? 'Sí' : 'No',
                $v->baja_vision       ? 'Sí' : 'No',
                $v->baja_audicion     ? 'Sí' : 'No',
                $v->estado            ?? '—',
            ]);
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                  'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '5B5F97']]],
        ];
    }
}

// ─────────────────────────────────────────────────────────────
// Hoja 5: Evaluaciones Cognitivas
// ─────────────────────────────────────────────────────────────
class EvaluacionesCognitivasIndSheet implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize, WithStyles
{
    public function __construct(private AdultoMayor $adulto) {}

    public function title(): string
    {
        return 'Evaluaciones Cognitivas';
    }

    public function headings(): array
    {
        return [
            'Fecha', 'Tipo Evaluación', 'Puntaje Obtenido', 'Puntaje Máximo',
            'Interpretación', 'Nivel Riesgo', 'Registrado Por',
        ];
    }

    public function collection()
    {
        return $this->adulto->evaluacionesCognitivas()
            ->with(['tipoEvaluacion', 'user'])
            ->orderByDesc('fecha_eval')
            ->get()
            ->map(fn($e) => [
                $e->fecha_eval ? $e->fecha_eval->format('d/m/Y') : '—',
                $e->tipoEvaluacion?->nombre ?? '—',
                $e->puntaje_total,
                $e->puntaje_maximo,
                $e->resultado_interpretacion ?? '—',
                $e->nivel_riesgo ?? '—',
                $e->user?->name ?? '—',
            ]);
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                  'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2F3E5C']]],
        ];
    }
}
