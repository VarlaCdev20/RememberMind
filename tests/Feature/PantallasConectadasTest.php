<?php

namespace Tests\Feature;

use App\Models\AdultoMayor;
use App\Models\User;
use Database\Seeders\EstadoAdultoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PantallasConectadasTest extends TestCase
{
    use RefreshDatabase;

    public function test_paginas_conectadas_renderizan_con_un_residente(): void
    {
        $this->seed([EstadoAdultoSeeder::class, \Database\Seeders\RolesAndPermissionsSeeder::class]);
        $user = User::factory()->create();
        $user->assignRole(Role::findOrCreate('SUPERADMINISTRADOR', 'web'));
        $adulto = AdultoMayor::factory()->create(['cod_est_adul' => 'EST_001']);
        $this->actingAs($user)->withoutExceptionHandling();
        $fallos = [];
        foreach (Route::getRoutes() as $ruta) {
            if (!in_array('GET', $ruta->methods()) || !str_starts_with($ruta->uri(), 'admin/')) continue;
            if (preg_match('~(?:pdf|excel|csv|imprimir|/ver$|/documentos/[^/]+$)~', $ruta->uri())) continue;
            $uri = str_replace(['{adulto_mayor}', '{adulto}', '{usuario}', '{user}'], [$adulto->cod_am, $adulto->cod_am, $user->cod_usu, $user->cod_usu], $ruta->uri());
            if (str_contains($uri, '{')) continue;
            try {
                $response = $this->get('/'.$uri);
                if ($response->getStatusCode() >= 400) $fallos[] = $uri.': HTTP '.$response->getStatusCode();
            } catch (\Throwable $e) {
                $fallos[] = $uri.': '.$e->getMessage();
            }
        }
        $this->assertSame([], $fallos, implode("\n", $fallos));
    }
}
