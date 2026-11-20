<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Conservacion\BloquesExpertos as B;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Conservacion\DeclaracionesNodo as D;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Support\EsquemaSinTransaccion;
use Tests\Support\InformeCognitivoFixture;
use Tests\TestCase;

final class NormalizacionDeclaracionesSemanticasTest extends TestCase
{
    use EsquemaSinTransaccion;

    private function preparar(): array
    {
        require_once database_path('migrations/support/SepararDeclaracionesSemanticas20261120.php');
        $u = User::factory()->create();
        $this->actingAs($u);
        InformeCognitivoFixture::conocimiento($u);
        return [$u,new \SepararDeclaracionesSemanticas20261120];
    }

    public function test_conserva_nodos_y_declaraciones_completas_al_subir_revertir_reaplicar(): void
    {
        [$u,$m] = $this->preparar();

        $lecturas=[];
        foreach(['variables_expertas','criterios_dominios_resultado'] as $entidad) $lecturas[$entidad]=B::consulta($entidad)->orderBy(B::columnasLogicas($entidad)[0])->get()->toArray();
        $m->down();
        $nodos = DB::table('nodos_semanticos')->orderBy('cod_nodo_semantico')->get()->map(fn($r)=>(array)$r)->all();
        $m->up();
        self::assertCount(10,Schema::getColumnListing('nodos_semanticos'));
        self::assertFalse(Schema::hasColumn('declaraciones_nodo','cod_version_modelo'));
        foreach($lecturas as $entidad=>$filas) self::assertEquals($filas,D::consulta($entidad)->orderBy(D::columnas($entidad)[0])->get()->toArray());
        $m->down();
        $despues=DB::table('nodos_semanticos')->orderBy('cod_nodo_semantico')->get()->map(fn($r)=>(array)$r)->all();
        self::assertEquals($nodos,$despues);
        $m->up();
        foreach($lecturas as $entidad=>$filas) self::assertEquals($filas,D::consulta($entidad)->orderBy(D::columnas($entidad)[0])->get()->toArray());
    }

    public function test_ambos_tipos_coexisten_y_no_admiten_cambio_de_version_o_borrado_de_variable(): void
    {
        [$u,$m]=$this->preparar();
        $v=(array)D::consulta('variables_expertas')->where('cod_variable_experta','VAR_INS')->sole();
        D::insertar('criterios_dominios_resultado', [['cod_criterio_dominio_resultado'=>'VAR_INS','cod_nodo_criterio'=>$v['cod_nodo_semantico'],
            'cod_dominio_valores'=>$v['cod_dominio_valores'],'estado'=>'ACTIVO','fecha_hora_creacion'=>now(),'cod_usuario_creacion'=>$u->getKey()]]);
        self::assertSame(2,DB::table('declaraciones_nodo')->where('cod_declaracion','VAR_INS')->count());
        foreach([
            fn()=>DB::table('declaraciones_nodo')->where('cod_variable_experta','VAR_INS')->delete(),
            fn()=>DB::table('declaraciones_nodo')->where('cod_variable_experta','VAR_INS')->update(['estado'=>'INACTIVO']),
            fn()=>DB::table('nodos_semanticos')->where('cod_nodo_semantico',$v['cod_nodo_semantico'])->update(['cod_version_modelo'=>'VER_AJENA']),
        ] as $operacion) {
            try{DB::transaction($operacion);self::fail('No se puede reatribuir o retirar una declaración histórica.');}
            catch(QueryException $e){ self::assertContains($e->errorInfo[0],['23000','23514']); }
        }
        self::assertSame('ACTIVO',D::consulta('variables_expertas')->where('cod_variable_experta','VAR_INS')->value('estado'));
    }
    public function test_modelos_conservan_contexto_y_relaciones_al_recargarse(): void
    {
        [$u,$m]=$this->preparar();
        $variable=\App\Models\VariableExperta::query()->whereKey('VAR_INS')->sole();
        foreach([$variable,$variable->fresh('version'),$variable->refresh()] as $modelo) {
            self::assertSame('VER_TEST',$modelo->cod_version_modelo);
            self::assertSame('VER_TEST',$modelo->getRawOriginal('cod_version_modelo'));
            self::assertSame('VER_TEST',$modelo->version->getKey());
            self::assertSame($modelo->cod_nodo_semantico,$modelo->nodo->getKey());
        }
        $criterio=\App\Models\CriterioDominioResultado::query()->whereKey('CR_DOM')->sole();
        foreach([$criterio,$criterio->fresh('criterio'),$criterio->refresh()] as $modelo) {
            self::assertSame($modelo->cod_nodo_criterio,$modelo->criterio->getKey());
            self::assertSame('CR_DOM',$modelo->getRawOriginal('cod_criterio_dominio_resultado'));
        }
        $antes=DB::table('declaraciones_nodo')->count();
        try { D::insertar('variables_expertas',[['cod_variable_experta'=>'VAR_INCOHERENTE','cod_nodo_semantico'=>$variable->cod_nodo_semantico,'cod_version_modelo'=>'VER_AJENA']]);self::fail('Una versión ajena no puede conservarse.'); }
        catch(\DomainException) { self::assertSame($antes,DB::table('declaraciones_nodo')->count()); }
    }
}
