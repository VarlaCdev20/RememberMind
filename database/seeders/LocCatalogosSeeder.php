<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LocDepartamento;
use App\Models\LocMunicipio;
use App\Models\LocZona;
use App\Models\LocCalle;

class LocCatalogosSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Departamentos
        $lapaz = LocDepartamento::firstOrCreate(['nombre' => 'LA PAZ']);
        $santacruz = LocDepartamento::firstOrCreate(['nombre' => 'SANTA CRUZ']);
        $cochabamba = LocDepartamento::firstOrCreate(['nombre' => 'COCHABAMBA']);

        // 2. Municipios para La Paz
        $munLaPaz = LocMunicipio::firstOrCreate([
            'departamento_id' => $lapaz->id,
            'nombre' => 'LA PAZ'
        ]);
        
        LocMunicipio::firstOrCreate([
            'departamento_id' => $lapaz->id,
            'nombre' => 'EL ALTO'
        ]);

        // 3. Zonas para Municipio La Paz
        $zonas = [
            'SAN PEDRO', 'SOPOCACHI', 'MIRAFLORES', 'OBRAJES', 
            'CALACOTO', 'VILLA FÁTIMA', 'CENTRO', 'MAX PAREDES', 
            'VILLA COPACABANA'
        ];

        $zonasCreadas = [];
        foreach ($zonas as $zona) {
            $zonasCreadas[$zona] = LocZona::firstOrCreate([
                'municipio_id' => $munLaPaz->id,
                'nombre' => $zona
            ]);
        }

        // 4. Calles para la zona San Pedro
        $callesSanPedro = [
            'AV. 20 DE OCTUBRE',
            'CALLE JUAN AGUIRRE',
            'CALLE COLOMBIA',
            'CALLE NICOLÁS ACOSTA',
            'AV. ARCE',
            'PLAZA SAN PEDRO'
        ];

        foreach ($callesSanPedro as $calle) {
            LocCalle::firstOrCreate([
                'zona_id' => $zonasCreadas['SAN PEDRO']->id,
                'nombre' => $calle
            ]);
        }
    }
}
