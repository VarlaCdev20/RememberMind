<?php

namespace App\Modulos\Identidad\Infraestructura\Geografia;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class GeocodificadorDireccionPersonal
{
    public function centrar(string $pais, string $nombrePais, string $departamento, ?string $municipio, ?string $zona = null): array
    {
        $datos = $this->consultar('search', ['q'=>implode(', ', array_filter([$zona, $municipio, $departamento, $nombrePais])),
            'countrycodes'=>strtolower($pais), 'limit'=>1]);
        $lugar = $datos[0] ?? null;
        if (! is_array($lugar) || ! is_numeric($lugar['lat'] ?? null) || ! is_numeric($lugar['lon'] ?? null)) {
            $this->fallar('No se encontró esa zona o región. Revisa su nombre, municipio y departamento o completa la dirección manualmente.');
        }
        return ['lat'=>(float) $lugar['lat'], 'lon'=>(float) $lugar['lon'], 'zoom'=>$zona ? 15 : ($municipio ? 12 : 8),
            'contexto'=>implode(', ', array_filter([$zona, $municipio, $departamento, $nombrePais]))];
    }

    public function resolver(float $latitud, float $longitud, string $pais): array
    {
        $datos = $this->consultar('reverse', ['lat'=>round($latitud, 6), 'lon'=>round($longitud, 6), 'zoom'=>18]);
        $a = $datos['address'] ?? null;
        if (! is_array($a) || strtoupper($a['country_code'] ?? '') !== $pais) {
            $this->fallar('No se encontró una dirección en el país seleccionado. Elige otro punto o utiliza la dirección manual.');
        }
        $texto = fn ($valor, $max) => Str::limit(mb_strtoupper(trim(preg_replace('/[\x00-\x1F\x7F]+/u', ' ', strip_tags((string) $valor)))), $max, '');
        $componentes = [
            'departamento'=>$texto($a['state'] ?? $a['region'] ?? '', 60),
            'municipio'=>$texto($a['city'] ?? $a['town'] ?? $a['village'] ?? $a['municipality'] ?? '', 60),
            'zona'=>$texto($a['neighbourhood'] ?? $a['suburb'] ?? $a['quarter'] ?? $a['city_district'] ?? '', 60),
            'calle'=>$texto($a['road'] ?? $a['pedestrian'] ?? $a['residential'] ?? '', 80),
            'numero'=>$texto($a['house_number'] ?? '', 15),
        ];
        $direccion = implode(', ', array_filter([$componentes['calle'], $componentes['numero'], $componentes['zona'],
            $componentes['municipio'], $componentes['departamento'], $texto($a['country'] ?? '', 60)]));
        if (! filled($direccion)) $this->fallar('Este punto no tiene dirección disponible. Elige otro punto o completa la dirección manualmente.');
        return [...$componentes, 'direccion'=>$texto($direccion, 255)];
    }

    private function consultar(string $operacion, array $parametros): array
    {
        $clave = 'personal-mapa:'.hash('sha256', config('services.direccion_personal.geocoding_url').'|'.$operacion.'|'.json_encode($parametros));
        if (($guardado = Cache::get($clave)) !== null) return $guardado;
        $bloqueo = Cache::lock('personal-mapa:consulta', 15);
        if (! $bloqueo->get()) $this->fallar('El mapa está atendiendo otra consulta. Espera dos segundos y vuelve a intentarlo.');
        try {
            // Global por aplicación, sin autocompletado ni consultas periódicas.
            if (! Cache::add('personal-mapa:intervalo', true, 2)) $this->fallar('Espera dos segundos antes de consultar otra ubicación.');
            try {
                $respuesta = Http::acceptJson()->withUserAgent(config('services.direccion_personal.user_agent'))
                    ->connectTimeout(3)->timeout(8)->get(rtrim(config('services.direccion_personal.geocoding_url'), '/').'/'.$operacion,
                        [...$parametros, 'format'=>'jsonv2', 'addressdetails'=>1, 'accept-language'=>'es']);
            } catch (ConnectionException) {
                $this->fallar('No se pudo conectar con el mapa. Reintenta o completa la dirección manualmente.');
            }
            if (! $respuesta->successful() || ! is_array($datos = $respuesta->json()) || isset($datos['error'])) {
                $this->fallar('El servicio de direcciones no está disponible para esta ubicación. Reintenta o utiliza la dirección manual.');
            }
            Cache::put($clave, $datos, now()->addDay());
            return $datos;
        } finally {
            $bloqueo->release();
        }
    }

    private function fallar(string $mensaje): never
    {
        throw ValidationException::withMessages(['mapa_direccion'=>$mensaje]);
    }
}
