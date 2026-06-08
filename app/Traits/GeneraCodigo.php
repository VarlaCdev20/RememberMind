<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;

trait GeneraCodigo
{
    protected static function bootGeneraCodigo(): void
    {
        static::creating(function ($model) {
            $field = $model->getKeyName();
            if (property_exists($model, 'fieldCode')) {
                $field = $model->fieldCode;
            }

            if (!$model->getAttribute($field)) {
                $prefix = $model->prefixCode ?? 'REF';
                $digits = $model->digitsCode ?? 5; // 3 o 5 dígitos

                // Determinar el último número de forma segura en la base de datos
                $ultimo = DB::table($model->getTable())
                    ->where($field, 'like', $prefix . '_%')
                    ->orderByDesc($field)
                    ->value($field);

                $numero = 1;
                if ($ultimo) {
                    $partes = explode('_', $ultimo);
                    $ultimoNumero = (int) end($partes);
                    $numero = $ultimoNumero + 1;
                }

                $codigo = $prefix . '_' . str_pad($numero, $digits, '0', STR_PAD_LEFT);

                // Prevenir duplicidades en inserciones concurrentes
                while (DB::table($model->getTable())->where($field, $codigo)->exists()) {
                    $numero++;
                    $codigo = $prefix . '_' . str_pad($numero, $digits, '0', STR_PAD_LEFT);
                }

                $model->setAttribute($field, $codigo);
            }
        });
    }
}
