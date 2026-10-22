<?php

declare(strict_types=1);

namespace Tests\Unit;

use DateTimeImmutable;
use DomainException;
use MotorExperto\dominio\objetos_valor\ContextoEjecucion;
use MotorExperto\dominio\petersen\FuentePetersenInstitucional as F;
use MotorExperto\dominio\petersen\ValoracionOriginalPetersen as V;
use MotorExperto\soporte\conservacion\CapturaInstrumentalCanonica as C;
use MotorExperto\soporte\conservacion\CodecInstantanea as Codec;
use MotorExperto\soporte\conservacion\InstantaneaPetersen;
use MotorExperto\soporte\conservacion\MembresiasOriginalesPetersen as M;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/** La adquisición completa no concede admisibilidad ni modifica el contrato clínico. */
final class MembresiasOriginalesPetersenTest extends TestCase
{
    private function crear(bool $completa): InstantaneaPetersen
    {
        $fecha = new DateTimeImmutable('2026-10-18T10:00:00-04:00');
        $origen = new F('valoraciones_petersen_originales', 'JUICIO-TEST', 'RES-TEST', 'AUTOR-TEST', $fecha, null, 'VALORACION_ORIGINAL', true);
        $soportes = [];
        $filas = [];
        foreach ([0, 1] as $orden) {
            $soportes[] = new F('valoraciones_psicologicas', 'FUENTE-'.$orden, 'RES-TEST', 'AUTOR-TEST', $fecha, null, 'DIFERENCIAL', true,
                ['descripcion' => 'Soporte sintético original '.$orden]);
            $filas[] = ['cod_soporte_petersen' => 'SOPORTE-'.$orden, 'cod_valoracion_petersen' => 'JUICIO-TEST',
                'tipo_soporte' => 'DIFERENCIAL', 'orden' => $orden, 'metodo_original' => null, 'huella_perfil_original' => null]
                + array_replace(array_fill_keys(M::CLAVES_FUENTES, null), ['cod_valoracion_psicologica' => 'FUENTE-'.$orden]);
        }
        $juicio = new V('C04', 'S', 'Revisión diferencial sintética.', $origen, $soportes,
            ['revision_explicita' => true, 'factores_pertinentes_revisados' => true, 'impedimento_no_resuelto' => false]);
        $procedencia = [['cod_valoracion_petersen' => 'JUICIO-TEST', 'cod_residente' => 'RES-TEST', 'cod_personal' => 'AUTOR-TEST',
            'cod_usuario_registro' => 'USUARIO-TEST', 'elemento' => 'C04', 'tipo_registro' => 'REGISTRO', 'cod_valoracion_anterior' => null,
            'cod_documento_clinico' => null, 'localizador_fuente' => null, 'fecha_hora' => $fecha->format('Y-m-d\TH:i:s.uP'),
            'registrado_en' => $fecha->format('Y-m-d\TH:i:s.uP')]];

        return InstantaneaPetersen::crear(new ContextoEjecucion('EVAL-TEST', 'RES-TEST', 'VERSION-TEST', $fecha), [$juicio], null, null,
            'USUARIO-TEST', ['valoraciones_al_corte' => 1, 'exclusiones' => [], 'fuentes_originales_mutables' => true,
                'conservacion_completa' => false], procedencia: $procedencia,
            membresias: [['valoracion' => 'JUICIO-TEST', 'soportes' => ['SOPORTE-0', 'SOPORTE-1']]],
            soportesOriginales: $completa ? $filas : null);
    }

    public function test_v2_conserva_vinculos_y_v1_historica_no_se_completa_ni_cambia_sus_salidas(): void
    {
        $v1 = $this->crear(false);
        $v2 = $this->crear(true);
        self::assertSame(InstantaneaPetersen::CONTRATO, $v1->contenido['contrato']);
        self::assertArrayNotHasKey('soportes_originales', $v1->contenido);
        self::assertSame($v1->contenido, InstantaneaPetersen::verificar($v1->contenido, $v1->huella)->contenido);
        self::assertSame(InstantaneaPetersen::CONTRATO_MEMBRESIAS, $v2->contenido['contrato']);
        self::assertCount(2, $v2->contenido['soportes_originales']);
        self::assertSame($v1->contenido['salida'], $v2->contenido['salida']);
        self::assertSame($v2->contenido, InstantaneaPetersen::verificar($v2->contenido, $v2->huella)->contenido);
        self::assertSame($v2->contenido, InstantaneaPetersen::verificarIntegridad($v2->contenido, $v2->huella)->contenido);
        self::assertFalse($v2->contenido['limites']['persistido']);
    }

    public static function alteraciones(): array
    {
        return [
            'colección omitida' => ['OMITIR'], 'soporte omitido' => ['FALTA'], 'campo desconocido' => ['CAMPO'],
            'soporte repetido' => ['REPETIDO'], 'propietario ajeno' => ['PROPIETARIO'], 'residente ajeno' => ['RESIDENTE'],
            'fuente sin FK' => ['SIN_FK'], 'fuente con dos FK' => ['DOS_FK'], 'orden textual' => ['ORDEN_TEXTO'],
            'orden original repetido' => ['ORDEN_REPETIDO'], 'orden de lista alterado' => ['LISTA'],
            'fuente intercambiada' => ['FUENTE'], 'tipo intercambiado' => ['TIPO'],
            'método sin medición' => ['METODO'], 'huella sin medición' => ['HUELLA'],
            'dominio revisado sin FK' => ['DOMINIO'], 'código truncado' => ['CODIGO'],
        ];
    }

    public static function contratosMedicion(): array
    {
        return [['METODO'], ['HUELLA']];
    }

    #[DataProvider('contratosMedicion')]
    public function test_medicion_exige_el_metodo_y_la_huella_del_perfil_original(string $cambiar): void
    {
        $d = $this->crear(true)->contenido;
        $perfil = ['id' => 'PERFIL-TEST', 'metodo' => 'METODO-TEST'];
        $s = &$d['soportes_originales'][0];
        $s['tipo_soporte'] = 'MEDICION';
        $s['cod_valoracion_psicologica'] = null;
        $s['cod_aplicacion'] = 'FUENTE-0';
        $s['metodo_original'] = 'METODO-TEST';
        $s['huella_perfil_original'] = Codec::huella(C::ordenar($perfil));
        $f = &$d['valoraciones'][0]['soportes'][0];
        $f['tabla'] = 'aplicaciones_instrumento';
        $f['tipo'] = 'MEDICION';
        $f['datos_originales'] = ['captura_original' => ['perfil_captura_original' => $perfil]];
        M::comprobar($d); // Comprueba la correspondencia documental; no interpreta esta fixture.
        if ($cambiar === 'METODO') {
            $s['metodo_original'] = 'OTRO-METODO';
        } else {
            $s['huella_perfil_original'] = str_repeat('a', 64);
        }
        unset($s, $f);
        $this->expectException(DomainException::class);
        InstantaneaPetersen::verificarIntegridad($d, Codec::huella(C::ordenar($d)));
    }

    #[DataProvider('alteraciones')]
    public function test_no_acepta_vinculos_incompletos_aunque_se_recalcule_la_huella(string $tipo): void
    {
        $d = $this->crear(true)->contenido;
        switch ($tipo) {
            case 'OMITIR': unset($d['soportes_originales']);
                break;
            case 'FALTA': array_pop($d['soportes_originales']);
                break;
            case 'CAMPO': $d['soportes_originales'][0]['otro_campo'] = 'No declarado';
                break;
            case 'REPETIDO': $d['soportes_originales'][] = $d['soportes_originales'][0];
                break;
            case 'PROPIETARIO': $d['soportes_originales'][0]['cod_valoracion_petersen'] = 'OTRO-JUICIO';
                break;
            case 'RESIDENTE': $d['procedencia_valoraciones'][0]['cod_residente'] = 'OTRO-RESIDENTE';
                break;
            case 'SIN_FK': $d['soportes_originales'][0]['cod_valoracion_psicologica'] = null;
                break;
            case 'DOS_FK': $d['soportes_originales'][0]['cod_control_cognitivo'] = 'OTRO-CONTROL';
                break;
            case 'ORDEN_TEXTO': $d['soportes_originales'][0]['orden'] = '0';
                break;
            case 'ORDEN_REPETIDO': $d['soportes_originales'][1]['orden'] = 0;
                break;
            case 'LISTA': $d['soportes_originales'] = array_reverse($d['soportes_originales']);
                break;
            case 'FUENTE': $d['soportes_originales'][0]['cod_valoracion_psicologica'] = 'OTRA-FUENTE';
                break;
            case 'TIPO': $d['soportes_originales'][0]['tipo_soporte'] = 'CALIDAD';
                break;
            case 'METODO': $d['soportes_originales'][0]['metodo_original'] = 'No aplicable';
                break;
            case 'HUELLA': $d['soportes_originales'][0]['huella_perfil_original'] = str_repeat('a', 64);
                break;
            case 'DOMINIO': $d['soportes_originales'][0]['tipo_soporte'] = 'DOMINIO_REVISADO';
                break;
            case 'CODIGO': $d['soportes_originales'][0]['cod_soporte_petersen'] = str_repeat('X', 21);
                break;
        }
        $this->expectException(DomainException::class);
        InstantaneaPetersen::verificarIntegridad($d, Codec::huella(C::ordenar($d)));
    }
}
