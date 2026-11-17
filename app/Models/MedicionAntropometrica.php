<?php

namespace App\Models;

class MedicionAntropometrica extends RegistroConsumo
{
    protected const TIPO = 'ANTROPOMETRIA';

    protected const ENTIDAD = 'mediciones_antropometricas';

    protected $table = 'mediciones_clinicas';

    protected $primaryKey = 'cod_registro_medicion';

    protected $appends = ['cod_medicion'];

    protected $hidden = ['cod_valoracion_dolor', 'intensidad', 'ubicacion', 'tipo_dolor', 'duracion', 'desencadenante', 'intervencion', 'respuesta', 'cod_valoracion_origen', 'frecuencia', 'factores_alivio', 'cod_registro_medicion', 'tipo_evento', 'cod_jornada', 'cod_atencion', 'presion_sistolica', 'presion_diastolica', 'frecuencia_cardiaca', 'frecuencia_respiratoria', 'temperatura', 'saturacion_oxigeno', 'glucemia', 'estado', 'cod_medicion_antropometrica'];

    protected $fillable = ['cod_medicion', 'cod_residente', 'cod_personal', 'fecha_hora', 'peso', 'talla', 'imc', 'perimetro_braquial', 'perimetro_pantorrilla', 'observacion'];

    public function getCodMedicionAttribute(): ?string
    {
        return $this->getKey();
    }

    public function setCodMedicionAttribute(string $codigo): void
    {
        $this->attributes[$this->getKeyName()] = $codigo;
    }

    protected function casts(): array
    {
        return ['fecha_hora' => 'datetime'];
    }
}
