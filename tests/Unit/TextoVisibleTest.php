<?php

namespace Tests\Unit;

use App\Compartido\Aplicacion\Formato\TextoVisible;
use PHPUnit\Framework\TestCase;

class TextoVisibleTest extends TestCase
{
    public function test_oculta_referencias_internas_sin_perder_el_texto_clinico(): void
    {
        $original = '[heridas:COD_HER_000004] Herida cortante en Pierna derecha [observación de enfermería]';

        $this->assertSame('Herida cortante en Pierna derecha [observación de enfermería]', TextoVisible::sinCodigosInternos($original));
        $this->assertSame('Derivación creada hacia área receptora.', TextoVisible::sinCodigosInternos('Derivación creada (COD_DER_000001) hacia área receptora.'));
        $this->assertSame('Consultar el registro.', TextoVisible::sinCodigosInternos('Consultar COD_HER_000004 el registro.'));
        $this->assertSame('Incidente registrado.', TextoVisible::sinCodigosInternos('Incidente (LP_AL_11) registrado.'));
        $this->assertSame('[heridas:COD_HER_000004] Herida cortante en Pierna derecha [observación de enfermería]', $original);
    }

    public function test_conserva_codigos_clinicos_datos_y_saltos_de_linea(): void
    {
        $clinico = "CIE-10: E11.9 · ATC: A10BA02 · HER2\nDosis: 500 mg · Hab. 203 · Cama 203-A";

        $this->assertSame($clinico, TextoVisible::sinCodigosInternos($clinico));
        $this->assertSame('', TextoVisible::sinCodigosInternos(null));
        $this->assertSame('Nota: herida revisada.', TextoVisible::sinCodigosInternos('Nota: [heridas:COD_HER_000004] herida revisada.'));
    }
}
