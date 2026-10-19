<?php

declare(strict_types=1);

namespace Tests\Unit;

use DomainException;
use MotorExperto\dominio\conocimiento\red_semantica\RedSemantica;
use MotorExperto\dominio\orion\ValueObjects\Cognicion\SubcomponenteCognitivoEnum;
use MotorExperto\investigacion\dcl\CatalogoPerfilCincoDominios as Catalogo;
use PHPUnit\Framework\TestCase;

final class CatalogoPerfilCincoDominiosTest extends TestCase
{
    public function test_red_cubre_exactamente_cinco_dominios_y_19_conceptos_con_un_solo_propietario(): void
    {
        $r = Catalogo::construir();
        $red = new RedSemantica($r['version'], $r['nodos'], $r['relaciones'], $r['contratos']);
        self::assertCount(24, $red->nodos());
        self::assertCount(19, $r['relaciones']);
        self::assertSame(array_map(static fn ($s) => $s->value, SubcomponenteCognitivoEnum::cases()), array_keys(Catalogo::subcomponentes()));
        foreach (SubcomponenteCognitivoEnum::cases() as $sub) {
            $relaciones = $red->salientes($sub->value, 'SUBCOMPONENTE_DE');
            self::assertCount(1, $relaciones);
            self::assertSame($sub->dominio()->value, $relaciones[0]['cod_nodo_destino']);
        }
        self::assertSame([], Catalogo::subcomponentes()['MEM-S01']['reglas']);
        self::assertSame('COG-EJE', Catalogo::subcomponentes()['EJE-S03']['dominio']);
        self::assertSame(['INACTIVO'], array_values(array_unique(array_column($r['nodos'], 'estado'))));
    }

    public function test_red_rechaza_relacion_de_otra_version_sin_modificar_catalogo_original(): void
    {
        $original = Catalogo::construir();
        $alterada = $original;
        $alterada['relaciones'][0]['cod_version_modelo'] = 'OTRA_VERSION';
        try {
            new RedSemantica($alterada['version'], $alterada['nodos'], $alterada['relaciones'], $alterada['contratos']);
            self::fail('Se admitió una relación ajena.');
        } catch (DomainException) {
            self::assertSame($original, Catalogo::construir());
        }
    }
}
