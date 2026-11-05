<?php

namespace Tests\Unit;

use App\Modulos\Identidad\Dominio\Reglas\IdentificacionInternacionalPersonal as Identificacion;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class IdentificacionInternacionalPersonalTest extends TestCase
{
    public static function formatos(): array
    {
        return [
            ['BO','CI','12345678','LP','BO','57712345',true],
            ['BO','CI','12345678','LP','BO','67712345',true],
            ['BO','CI','12345678','LP','BO','77712345',true],
            ['BO','CI','12345678','LP','BO','47712345',false],
            ['BO','CI','AB123456', 'LP','BO','77712345',false],
            ['BO','CI','12345678',null,'BO','77712345',false],
            ['BO','PASAPORTE','AB123456',null,'BO','77712345',true],
            ['PE','DNI','12345678',null,'PE','912345678',true],
            ['PE','DNI','1234567',null,'PE','912345678',false],
            ['PE','DNI','12345678','LP','PE','912345678',false],
            ['ES','DNI','12345678Z',null,'ES','612345678',true],
            ['ES','NIE','X1234567L',null,'ES','712345678',true],
            ['CL','RUN','12345678-5',null,'CL','912345678',true],
            ['BR','CPF','12345678901',null,'BR','11912345678',true],
            ['MX','CURP','ABCD900101HDFRRN09',null,'MX','5512345678',true],
            ['US','SSN','123456789',null,'US','2025550199',false],
            ['ZZ','CI','12345678',null,'BO','77712345',false],
        ];
    }

    #[DataProvider('formatos')]
    public function test_formato_y_contexto_se_validan_sin_consultar_servicios_externos(string $pais, string $tipo, string $numero, ?string $expedido, string $paisTel, string $telefono, bool $valido): void
    {
        $validador = Validator::make(['pais_documento'=>$pais,'tipo_documento'=>$tipo,'numero_documento'=>$numero,
            'expedido'=>$expedido,'pais_telefono'=>$paisTel,'telefono'=>$telefono], Identificacion::reglas($pais,$tipo,$paisTel));
        $this->assertSame($valido, $validador->passes(), implode(' ', $validador->errors()->all()));
    }
}
