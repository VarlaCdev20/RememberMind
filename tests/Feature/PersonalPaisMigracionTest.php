<?php

namespace Tests\Feature;

use App\Models\Personal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PersonalPaisMigracionTest extends TestCase
{
    use RefreshDatabase;

    private function ddl(): object { return require base_path('database/propuestas/gerencia/2026_11_04_000004_pais_documento_telefono_personal.php'); }

    public function test_extension_preserva_datos_tablas_y_no_infiere_paises(): void
    {
        $usuario = User::factory()->create();
        $personal = Personal::create(['cod_usuario'=>$usuario->getKey(),'nombres'=>'Persona','apellido_paterno'=>'Sintética',
            'numero_documento'=>'PRUEBA-ANTERIOR','telefono'=>'77712345','profesion'=>'ADMINISTRADOR','estado'=>'ACTIVO']);
        $antes = $personal->getAttributes();
        $tablas = Schema::getTableListing(); sort($tablas);
        $this->ddl()->up();
        $despues = Schema::getTableListing(); sort($despues);
        $this->assertSame($tablas, $despues);
        $this->assertTrue(Schema::hasColumns('personal', ['pais_documento','tipo_documento','pais_telefono']));
        $fila = (array) DB::table('personal')->where('cod_personal',$personal->getKey())->first();
        foreach ($antes as $campo=>$valor) $this->assertEquals($valor, $fila[$campo]);
        foreach (['pais_documento','tipo_documento','pais_telefono'] as $campo) $this->assertNull($fila[$campo]);
        $this->ddl()->down();
        $this->assertFalse(Schema::hasColumn('personal','pais_documento'));
        $this->assertSame('PRUEBA-ANTERIOR', $personal->fresh()->numero_documento);
    }

    public function test_rollback_no_descarta_metadatos_ya_registrados(): void
    {
        $this->ddl()->up();
        $usuario = User::factory()->create();
        Personal::create(['cod_usuario'=>$usuario->getKey(),'nombres'=>'Persona','apellido_paterno'=>'Sintética',
            'numero_documento'=>'PRUEBA-PAIS','pais_documento'=>'PE','tipo_documento'=>'DNI','pais_telefono'=>'BO',
            'profesion'=>'ADMINISTRADOR','estado'=>'ACTIVO']);
        try { $this->ddl()->down(); $this->fail('El rollback no puede descartar países registrados.'); }
        catch (\LogicException $e) { $this->assertStringContainsString('no se eliminan', $e->getMessage()); }
        $this->assertTrue(Schema::hasColumns('personal',['pais_documento','tipo_documento','pais_telefono']));
        $this->assertDatabaseHas('personal',['numero_documento'=>'PRUEBA-PAIS','pais_documento'=>'PE']);
    }
}
