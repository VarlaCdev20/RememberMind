<?php

namespace Tests\Feature;

use App\Backend\Modulos\Reportes\Servicios\DashboardPhotoRotation;
use Tests\TestCase;

class DashboardPhotoRotationTest extends TestCase
{
    public function test_cada_rol_cambia_su_foto_al_recargar_y_conserva_la_actual_en_un_post(): void
    {
        session()->start();
        $rotation = app(DashboardPhotoRotation::class);
        $fallback = asset('images/FOTOS CENTRO DE ADULTOS MAYORES/489963938_1158744422930145_8442506970304201426_n.jpg');

        foreach (['superadmin', 'administracion', 'medico', 'psicologia', 'nutricionista', 'fisioterapeuta', 'pedagogo', 'familiar', 'enfermeria', 'perfil-general'] as $role) {
            session()->forget('dashboard_photo_rotation_'.md5('hero:'.$role));
            $first = $rotation->heroImage($role, $fallback);
            $second = $rotation->heroImage($role, $fallback);

            $this->assertNotSame($first, $second, $role);
            $this->assertFileExists(public_path(rawurldecode(parse_url($first, PHP_URL_PATH))));
            $this->assertFileExists(public_path(rawurldecode(parse_url($second, PHP_URL_PATH))));

            request()->setMethod('POST');
            $this->assertSame($second, $rotation->heroImage($role, $fallback), $role);
            request()->setMethod('GET');
        }
    }

    public function test_ambos_pares_de_fotos_rotan_juntos_en_administracion_y_enfermeria(): void
    {
        session()->start();
        $rotation = app(DashboardPhotoRotation::class);

        foreach (['administracion', 'enfermeria'] as $role) {
            session()->forget('dashboard_photo_rotation_'.md5('pair:'.$role));
            $first = $rotation->pair($role);
            $second = $rotation->pair($role);

            $this->assertNotSame($first[0], $second[0], $role);
            $this->assertNotSame($first[1], $second[1], $role);
            foreach ([$first[0], $first[1], $second[0], $second[1]] as $path) {
                $this->assertFileExists(public_path($path));
            }
        }
    }
}
