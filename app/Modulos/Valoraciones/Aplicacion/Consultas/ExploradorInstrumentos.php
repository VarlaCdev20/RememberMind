<?php

declare(strict_types=1);

namespace App\Modulos\Valoraciones\Aplicacion\Consultas;

use App\Models\Instrumento;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use MotorExperto\dominio\instrumentos\RegistroMetodosInstrumentales;

/** Proyección de lectura. Nunca instala instrumentos ni ejecuta métodos investigados. */
final class ExploradorInstrumentos
{
    public const VERSION = 'GUIAS-2026-10-22.2';

    public const DOMINIOS = ['MEM' => 'Memoria', 'ATE' => 'Atención', 'EJE' => 'Funciones ejecutivas', 'LEN' => 'Lenguaje', 'VIS' => 'Visuoespacial'];

    public function tarjetas(string $area): Collection
    {
        $catalogo = app(InstrumentosDelArea::class)->catalogados($area)->orderBy('nombre')->orderBy('cod_instrumento')
            ->with(['preguntas' => fn ($q) => $q->where('estado', 'ACTIVA')->select('cod_pregunta', 'cod_instrumento', 'dominio')])
            ->withCount(['preguntas as preguntas_vigentes' => fn ($q) => $q->where('estado', 'ACTIVA')])->get();
        $tarjetas = $catalogo->map(function (Instrumento $instrumento): array {
            $observacional = $instrumento->tipo === 'OBSERVACIONAL';
            $dominios = $instrumento->preguntas->pluck('dominio')->map(static fn ($dominio) => str_starts_with((string) $dominio, 'COG-')
                ? substr($dominio, 4, 3) : substr((string) $dominio, 0, 3))
                ->filter(static fn ($dominio) => isset(self::DOMINIOS[$dominio]))->unique()->values()->all();

            return ['id' => 'catalogo:'.$instrumento->getKey(), 'codigo' => $instrumento->codigo,
                'cod_instrumento' => $instrumento->getKey(), 'nombre' => $instrumento->nombre,
                'version' => $instrumento->version ?: 'Versión no documentada',
                'descripcion' => $instrumento->descripcion ?: 'Consulta las condiciones del catálogo y la fuente original antes de registrar.',
                'tipo' => $instrumento->tipo, 'dominios' => $dominios,
                'modalidad' => $observacional ? 'Observación' : 'Aplicación profesional',
                'estado' => $instrumento->estado !== 'ACTIVO' ? 'INACTIVO_HISTORICO'
                    : ($instrumento->preguntas_vigentes > 0 ? 'HABILITADO_PARA_CAPTURA' : 'SIN_PREGUNTAS_VIGENTES'),
                'estado_catalogo' => $instrumento->estado,
                'aporte' => $observacional ? 'Puede aportar contexto' : 'Requiere medición original',
                'fuente' => 'Catálogo institucional · '.$instrumento->codigo.' · '.($instrumento->version ?: 'sin versión'),
                'entradas' => [], 'identidad' => [], 'url' => null];
        });

        if ($area === 'ARE_COG') {
            foreach ((new RegistroMetodosInstrumentales)->metodos() as $codigo => $metodo) {
                $dominios = array_values(array_unique(array_map(static fn ($c) => substr($c, 0, 3), $metodo['componentes'] ?? [])));
                $fuente = $metodo['fuente'] ?? '';
                $url = is_string($fuente) && filter_var($fuente, FILTER_VALIDATE_URL) && str_starts_with($fuente, 'https://') ? $fuente : null;
                $tarjetas->push(['id' => 'candidato:'.$codigo, 'codigo' => $codigo, 'cod_instrumento' => null,
                    'nombre' => $metodo['instrumento'], 'version' => $codigo, 'tipo' => 'INVESTIGACION',
                    'descripcion' => 'Método documentado para '.implode(', ', array_map(static fn ($d) => self::DOMINIOS[$d] ?? $d, $dominios)).'. Su presencia no autoriza una aplicación clínica.',
                    'dominios' => $dominios, 'modalidad' => 'Consulta documental', 'estado' => 'CANDIDATO_INVESTIGADO',
                    'estado_catalogo' => 'No instalado por esta biblioteca', 'aporte' => 'No evaluable desde esta guía',
                    'fuente' => is_string($fuente) ? $fuente : 'Registro técnico de métodos · '.$codigo,
                    'url' => $url, 'identidad' => $metodo['identidad'] ?? [], 'entradas' => array_keys($metodo['entradas'] ?? [])]);
            }
        }

        return $tarjetas;
    }

    public function filtrar(Collection $tarjetas, string $busqueda, string $biblioteca, string $dominio, string $estado, string $tipo, string $modalidad): Collection
    {
        $terminos = preg_split('/\s+/u', Str::lower(Str::ascii(mb_substr(trim($busqueda), 0, 120))), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return $tarjetas->filter(function (array $t) use ($terminos, $biblioteca, $dominio, $estado, $tipo, $modalidad): bool {
            if (($biblioteca === 'candidatos') !== ($t['estado'] === 'CANDIDATO_INVESTIGADO')
                || ($dominio !== '' && ! in_array($dominio, $t['dominios'], true))
                || ($estado !== '' && $estado !== $t['estado']) || ($tipo !== '' && $tipo !== $t['tipo'])
                || ($modalidad !== '' && $modalidad !== $t['modalidad'])) {
                return false;
            }
            $texto = Str::lower(Str::ascii(implode(' ', [$t['nombre'], $t['codigo'], $t['descripcion'], $t['tipo'], ...array_map(static fn ($d) => self::DOMINIOS[$d] ?? $d, $t['dominios'])])));
            foreach (array_slice($terminos, 0, 8) as $termino) {
                if (! str_contains($texto, $termino)) {
                    return false;
                }
            }

            return true;
        })->values();
    }

    public function guia(array $tarjeta): array
    {
        $funcional = $tarjeta['tipo'] === 'FUNCIONAL';
        $observacional = $tarjeta['tipo'] === 'OBSERVACIONAL';
        $limite = $funcional
            ? 'Un registro de actividades básicas, incluido Barthel cuando exista, no demuestra independencia IADL ni acredita automáticamente C03. Su valoración original conserva el circuito y responsables autorizados.'
            : ($observacional ? 'Este instrumento conserva observaciones del catálogo institucional. No equivale a una prueba estandarizada ni acredita una alteración instrumental por sí solo.'
                : 'La referencia de investigación no demuestra licencia ni disponibilidad clínica. Aplicar un instrumento no demuestra por sí solo deterioro cognitivo: requiere medidas originales, contexto y revisión profesional.');

        return $tarjeta + ['version_guia' => self::VERSION, 'validacion' => 'REFERENCIA_NO_CLINICA',
            'precaucion' => 'Guía pendiente de validación clínica. Esta orientación de registro no sustituye el manual autorizado de la edición real ni habilita su aplicación.',
            'materiales' => 'Manual y materiales autorizados de la edición aplicada; fuente original identificable. Verifica disponibilidad y derechos con el responsable clínico.',
            'campos_preview' => [
                ['id' => 'contexto', 'nombre' => 'Residente y fecha clínica', 'icono' => 'ph-identification-card',
                    'campos' => 'cod_residente · fecha_eval · hora_eval', 'origen' => 'Selección autorizada del residente y fecha de la aplicación.',
                    'ayuda' => 'Comprueba para quién y cuándo se registra. La guía no selecciona residentes ni propone fechas clínicas.',
                    'limite' => 'La autorización se vuelve a comprobar en el servidor.'],
                ['id' => 'fuente', 'nombre' => 'Instrumento y fuente', 'icono' => 'ph-book-open',
                    'campos' => 'cod_instrumento · transcripcion · localizador_fuente · original_cotejado', 'origen' => $tarjeta['fuente'],
                    'ayuda' => 'La ficha muestra '. $tarjeta['nombre'].', versión '.$tarjeta['version'].'. En transcripción se exige la fuente original y su cotejo.',
                    'limite' => $tarjeta['estado'] === 'CANDIDATO_INVESTIGADO' ? 'Este candidato no está habilitado para capturar desde la biblioteca.' : 'La captura utiliza únicamente el catálogo vigente y sus preguntas autorizadas.'],
                ['id' => 'respuestas', 'nombre' => 'Respuestas y observaciones', 'icono' => 'ph-pencil-line',
                    'campos' => 'valores · puntajesOriginales · observacionesRespuesta · observaciones', 'origen' => 'Preguntas y tipos del catálogo vigente; valores originales documentados.',
                    'ayuda' => 'Conserva la respuesta original. Cero y No son respuestas; la ausencia de medición permanece sin respuesta.',
                    'limite' => 'Esta vista previa no incluye ítems, estímulos, respuestas ni puntuaciones de ejemplo.'],
                ['id' => 'revision', 'nombre' => 'Autoría y revisión', 'icono' => 'ph-clipboard-text',
                    'campos' => 'Profesional de la sesión · revisión · motivo_rectificacion cuando corresponde', 'origen' => 'Cuenta y personal activos, revisión previa y confirmación real del servidor.',
                    'ayuda' => 'El profesional responsable procede de la sesión. La confirmación aparece cuando el servidor registra la aplicación.',
                    'limite' => 'Marcar un paso de esta guía no guarda datos ni sustituye la revisión clínica.'],
            ],
            'limite' => $limite,
            'relacion' => 'La interpretación requiere fuentes autorizadas, método y versión, condiciones, episodio, autor y medidas originales. La condición conceptual instrumental exige C1 ∧ C2 ∧ C3 de la misma aplicación. Si faltan datos o hay discrepancias, corresponde abstención con razones. Petersen conserva cinco dominios y C01/C03/C04/C05 originales; C02 se deriva. Medicina y Psicología revisan por separado.',
            'pasos' => [
                ['titulo' => 'Comprueba el contexto', 'icono' => 'ph-identification-card', 'preparar' => 'Confirma residente, profesional, instrumento y edición.', 'interaccion' => 'Explica el propósito y comprueba las condiciones de participación.', 'dato' => 'Identidad, fecha y condiciones documentadas.', 'precaucion' => 'No presupongas condiciones ni completes información clínica por defecto.'],
                ['titulo' => 'Consulta la fuente autorizada', 'icono' => 'ph-book-open', 'preparar' => 'Localiza el manual y los materiales de la edición real.', 'interaccion' => 'Sigue únicamente las indicaciones autorizadas por el responsable clínico.', 'dato' => 'Fuente y localizador del documento original cuando corresponda.', 'precaucion' => 'Esta guía no contiene estímulos, consignas, tiempos ni baremos de aplicación.'],
                ['titulo' => 'Conserva lo observado', 'icono' => 'ph-pencil-line', 'preparar' => 'Revisa las preguntas vigentes y sus tipos.', 'interaccion' => 'Registra solamente lo obtenido, respetando el contexto del residente.', 'dato' => 'Respuesta original: número, texto, opción o Sí/No según el catálogo.', 'precaucion' => 'Cero y No son datos; lo no medido permanece sin respuesta.'],
                ['titulo' => 'Revisa antes de confirmar', 'icono' => 'ph-clipboard-text', 'preparar' => 'Contrasta residente, fecha, respuestas y autoría.', 'interaccion' => 'Conserva las observaciones relevantes y las limitaciones de la fuente.', 'dato' => 'Confirmación real del servidor y antecedente si existe rectificación.', 'precaucion' => 'No hay autoguardado clínico. Una corrección debe conservar la versión anterior.'],
            ]];
    }
}
