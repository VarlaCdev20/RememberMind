<?php

namespace App\Models;

class RegistroEliminacion extends ModeloOperativo
{
    protected $table = 'registros_eliminacion';
    protected $primaryKey = 'cod_eliminacion';
    protected $fillable = ['cod_eliminacion', 'cod_residente', 'cod_personal', 'cod_jornada', 'fecha_hora',
        'tipo_eliminacion', 'cantidad', 'caracteristica', 'continencia', 'observacion', 'estado',
        'cantidad_cualitativa', 'volumen_ml', 'color_orina', 'aspecto_orina', 'olor_orina', 'tipo_miccion',
        'tipo_bristol', 'color_heces', 'esfuerzo_defecacion', 'presencia_sangre', 'presencia_moco',
        'molestia_eliminacion', 'descripcion_molestia'];

    public const URINARIOS = ['volumen_ml', 'color_orina', 'aspecto_orina', 'olor_orina', 'tipo_miccion'];
    public const INTESTINALES = ['tipo_bristol', 'color_heces', 'esfuerzo_defecacion', 'presencia_moco'];
    public const COMUNES = ['cantidad_cualitativa', 'continencia', 'presencia_sangre', 'molestia_eliminacion', 'descripcion_molestia', 'observacion'];
    public const OPCIONES = [
        'cantidad_cualitativa' => ['ESCASA' => 'Escasa', 'HABITUAL' => 'Habitual', 'ABUNDANTE' => 'Abundante'],
        'color_orina' => ['CLARA' => 'Clara', 'AMARILLO_CLARO' => 'Amarillo claro', 'AMARILLO' => 'Amarillo', 'AMBAR' => 'Ámbar', 'OSCURA' => 'Oscura', 'ROJIZA' => 'Rojiza', 'OTRO' => 'Otro'],
        'aspecto_orina' => ['CLARO' => 'Claro', 'TURBIO' => 'Turbio', 'SEDIMENTO' => 'Con sedimento', 'HEMATICO_APARENTE' => 'Aspecto hemático', 'OTRO' => 'Otro'],
        'olor_orina' => ['HABITUAL' => 'Habitual', 'INTENSO' => 'Intenso', 'INUSUAL' => 'Inusual'],
        'tipo_miccion' => ['ESPONTANEA' => 'Espontánea', 'ASISTIDA' => 'Asistida'],
        'color_heces' => ['MARRON' => 'Marrón', 'MARRON_CLARO' => 'Marrón claro', 'MARRON_OSCURO' => 'Marrón oscuro', 'ROJIZO' => 'Rojizo', 'NEGRUZCO' => 'Negruzco', 'OTRO' => 'Otro'],
        'esfuerzo_defecacion' => ['SIN_ESFUERZO' => 'Sin esfuerzo', 'CON_ESFUERZO' => 'Con esfuerzo', 'DIFICULTOSO' => 'Dificultoso'],
    ];

    protected function casts(): array
    {
        return ['fecha_hora' => 'datetime', 'volumen_ml' => 'decimal:2', 'tipo_bristol' => 'integer',
            'presencia_sangre' => 'boolean', 'presencia_moco' => 'boolean', 'molestia_eliminacion' => 'boolean'];
    }

    public function resumenOperacional(): array
    {
        $labels = ['cantidad_cualitativa' => 'Cantidad', 'volumen_ml' => 'Volumen medido', 'continencia' => 'Continencia',
            'color_orina' => 'Color de orina', 'aspecto_orina' => 'Aspecto', 'olor_orina' => 'Olor', 'tipo_miccion' => 'Micción',
            'tipo_bristol' => 'Bristol', 'color_heces' => 'Color de heces', 'esfuerzo_defecacion' => 'Esfuerzo',
            'presencia_sangre' => 'Sangre observada', 'presencia_moco' => 'Moco observado',
            'molestia_eliminacion' => 'Molestia observada/referida', 'descripcion_molestia' => 'Descripción de molestia'];
        $campos = [];
        foreach ($labels as $campo => $label) {
            $valor = $this->{$campo};
            if ($valor === null || $valor === '') continue;
            $texto = match ($campo) {
                'volumen_ml' => $valor.' mL', 'tipo_bristol' => 'Tipo '.$valor,
                'presencia_sangre', 'presencia_moco', 'molestia_eliminacion' => $valor ? 'Sí' : 'No',
                'continencia' => ['CONTINENTE' => 'Continente', 'INCONTINENCIA_URINARIA' => 'Incontinencia urinaria', 'INCONTINENCIA_FECAL' => 'Incontinencia fecal'][$valor] ?? (string) $valor,
                default => self::OPCIONES[$campo][$valor] ?? (string) $valor,
            };
            $campos[] = ['nombre' => $label, 'valor' => $texto];
        }
        $nuevos = array_merge(self::URINARIOS, self::INTESTINALES, ['cantidad_cualitativa', 'presencia_sangre', 'molestia_eliminacion', 'descripcion_molestia']);
        $legacy = ! collect($nuevos)->contains(fn ($campo) => $this->{$campo} !== null)
            && ($this->cantidad !== null || $this->caracteristica !== null);
        if ($legacy) {
            if ($this->cantidad !== null) $campos[] = ['nombre' => 'Cantidad anterior', 'valor' => $this->cantidad];
            if ($this->caracteristica !== null) $campos[] = ['nombre' => 'Características anteriores', 'valor' => $this->caracteristica];
        }
        return ['codigo' => $this->cod_eliminacion, 'tipo' => $this->tipo_eliminacion,
            'tipo_label' => ['URINARIA' => 'Eliminación urinaria', 'INTESTINAL' => 'Eliminación intestinal'][$this->tipo_eliminacion] ?? $this->tipo_eliminacion,
            'fecha' => $this->fecha_hora->format('d/m/Y'), 'hora' => $this->fecha_hora->format('H:i'),
            'grupo' => $this->fecha_hora->isToday() ? 'Hoy' : ($this->fecha_hora->isYesterday() ? 'Ayer' : $this->fecha_hora->format('d/m/Y')),
            'campos' => $campos, 'legacy' => $legacy, 'observacion' => $this->observacion];
    }
}
