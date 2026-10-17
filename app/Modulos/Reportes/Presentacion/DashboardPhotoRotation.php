<?php

namespace App\Modulos\Reportes\Presentacion;

final class DashboardPhotoRotation
{
    private array $leyendas = [];

    public function altFor(string $url, string $fallback): string
    {
        return $this->leyendas[$url] ?? $fallback;
    }

    private function fotografiasInstitucionales(string $context): array
    {
        $usuario = auth()->user();
        if (! in_array($context, ['gerencia', 'administracion'], true) || ! $usuario?->hasAnyRole(['GERENTE', 'ADMINISTRADOR'])
            || ! \Illuminate\Support\Facades\Schema::hasTable('fotografias_institucionales')
            || ! app(\App\Modulos\Institucion\Presentacion\Policies\FotografiaInstitucionalPolicy::class)->viewAny($usuario)) {
            return [];
        }
        $urls = [];
        foreach (\App\Models\FotografiaInstitucional::where('estado', 'ACTIVA')->where('usar_en_bienvenida', true)
            ->orderBy('cod_fotografia')->get() as $foto) {
            if (! \Illuminate\Support\Facades\Storage::disk('local')->exists($foto->ruta_archivo)) continue;
            $url = route('admin.galeria.archivo', [$foto->getKey(), 'imagen']);
            $urls[] = $url;
            $this->leyendas[$url] = $foto->texto_alternativo ?: $foto->titulo;
        }
        return $urls;
    }
    private const PHOTO_DIRECTORY = 'images/FOTOS CENTRO DE ADULTOS MAYORES/';

    private const PHOTOS = [
        'care' => '621786801_1404497435021508_7880315777607437580_n.jpg',
        'cognitive' => '558487013_1337134818424437_2282337776297854403_n.jpg',
        'cooking' => '571112630_1324313336373252_907762429113477564_n.jpg',
        'craft' => '625859129_1410592147745370_5928827647682583422_n.jpg',
        'music' => '587076125_1351376883666897_3601048710050837046_n.jpg',
        'activity' => '591853022_1360929579378294_9150136501913618296_n.jpg',
        'medical' => '593542266_1360929526044966_7396297662771591420_n.jpg',
        'culture' => '595693419_1366687118802540_7877864884520394638_n.jpg',
        'community' => '489963938_1158744422930145_8442506970304201426_n.jpg',
        'nutrition' => '569897738_1324313409706578_2951129905561208154_n.jpg',
        'mobility' => '577031711_1337134741757778_4846830420773518569_n.jpg',
        'family' => '600320307_1368093028661949_6493928930338402983_n.jpg',
    ];

    private const PHOTO_PAIRS = [
        'enfermeria' => [
            ['care', 'cognitive'],
            ['cooking', 'craft'],
            ['music', 'activity'],
        ],
        'administracion' => [
            ['cooking', 'craft'],
            ['music', 'activity'],
            ['care', 'cognitive'],
        ],
    ];

    private const HERO_PHOTOS = [
        'medico' => ['medical', 'care', 'music'],
        'psicologia' => ['cognitive', 'craft', 'activity'],
        'superadmin' => ['community', 'cooking', 'music'],
        'administracion' => ['culture', 'cooking', 'music'],
        'nutricionista' => ['nutrition', 'cooking', 'craft'],
        'fisioterapeuta' => ['mobility', 'care', 'music'],
        'pedagogo' => ['cognitive', 'activity', 'craft'],
        'familiar' => ['family', 'music', 'cooking'],
        'enfermeria' => ['care', 'music', 'activity'],
    ];

    /** @return array{string, string} Rutas públicas del par seleccionado. */
    public function pair(string $context): array
    {
        $gestionadas = $this->fotografiasInstitucionales($context);
        if ($gestionadas) {
            $indice = $this->nextIndex('gallery-pair:'.$context, count($gestionadas));
            return [$gestionadas[$indice], $gestionadas[($indice + 1) % count($gestionadas)]];
        }
        $pairs = self::PHOTO_PAIRS[$context] ?? self::PHOTO_PAIRS['administracion'];
        [$primary, $secondary] = $pairs[$this->nextIndex('pair:'.$context, count($pairs))];

        return [self::path($primary), self::path($secondary)];
    }

    public function heroImage(string $context, string $fallback): string
    {
        $gestionadas = $this->fotografiasInstitucionales($context);
        if ($gestionadas) return $gestionadas[$this->nextIndex('gallery-hero:'.$context, count($gestionadas))];
        $photos = self::HERO_PHOTOS[$context] ?? [];
        $urls = $photos
            ? array_map(fn (string $photo) => asset(self::path($photo)), $photos)
            : [$fallback, asset(self::path('cooking')), asset(self::path('music'))];
        $urls = array_values(array_unique($urls));

        return $urls[$this->nextIndex('hero:'.$context, count($urls))];
    }

    private static function path(string $photo): string
    {
        return self::PHOTO_DIRECTORY.self::PHOTOS[$photo];
    }

    private function nextIndex(string $context, int $total): int
    {
        $key = 'dashboard_photo_rotation_'.md5($context);
        $previous = (int) session()->get($key, -1);
        $index = request()->isMethod('GET') || $previous < 0
            ? ($previous + 1) % $total
            : $previous % $total;
        session()->put($key, $index);

        return $index;
    }
}
