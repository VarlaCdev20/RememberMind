<?php

namespace App\Modulos\Clinica\Aplicacion\Acciones;

use App\Models\Atencion;
use App\Models\ComponenteEstudio;
use App\Models\Derivacion;
use App\Models\DocumentoClinico;
use App\Models\EstudioClinico;
use App\Models\InformeEstudio;
use App\Models\Residente;
use App\Models\ResultadoEstudio;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

final class GestionarEstudiosClinicos
{
    private const ROLES_SOLICITUD = ['MEDICO GENERAL/GERIATRA'];

    public function __construct(private readonly AutorizacionClinicaService $autorizacion) {}

    public function solicitar(User $usuario, array $entrada, Residente $residente): mixed
    {
        $personal = $this->autorizacion->autorizarMutacion($usuario, $residente, 'estudios_clinicos.crear', self::ROLES_SOLICITUD);
        $datos = Validator::make($entrada, ['cod_atencion' => ['required', 'exists:atenciones,cod_atencion'], 'cod_tipo_estudio' => ['required', 'exists:tipos_estudio_clinico,cod_tipo_estudio'], 'motivo' => ['nullable', 'string'], 'prioridad' => ['nullable', 'string', 'max:20'], 'centro_medico' => ['nullable', 'string', 'max:160'], 'observacion' => ['nullable', 'string']])->validate();
        $this->validarAtencion($datos['cod_atencion'], $residente);
        $estudio = EstudioClinico::query()->create(['cod_estudio' => $this->codigo('EST'), 'cod_residente' => $residente->cod_residente, 'cod_personal' => $personal->cod_personal, ...$datos, 'fecha_solicitud' => now(), 'estado' => 'SOLICITADO']);

        return $estudio;
    }

    public function resultados(User $usuario, array $entrada, EstudioClinico $estudio): mixed
    {
        $residente = $estudio->residente()->firstOrFail();
        $this->autorizacion->autorizarMutacion($usuario, $residente, 'resultados_estudio.crear', self::ROLES_SOLICITUD);
        abort_unless(in_array($estudio->estado, ['SOLICITADO', 'EN_PROCESO', 'REALIZADO'], true), 409, 'El estudio ya no admite registro de resultados.');
        $datos = Validator::make($entrada, ['resultados' => ['required', 'array', 'min:1'], 'resultados.*.cod_componente' => ['required', 'distinct', 'exists:componentes_estudio,cod_componente'], 'resultados.*.valor_numerico' => ['nullable', 'numeric'], 'resultados.*.valor_texto' => ['nullable', 'string'], 'resultados.*.unidad' => ['nullable', 'string', 'max:40'], 'resultados.*.rango_referencia' => ['nullable', 'string', 'max:120'], 'resultados.*.clasificacion' => ['nullable', 'string', 'max:30'], 'resultados.*.observacion' => ['nullable', 'string']])->validate();
        $componentes = ComponenteEstudio::query()->where('cod_tipo_estudio', $estudio->cod_tipo_estudio)->pluck('cod_componente')->all();
        foreach ($datos['resultados'] as $fila) {
            abort_unless(in_array($fila['cod_componente'], $componentes, true), 422, 'El componente no pertenece al tipo de estudio.');
            abort_if(! array_key_exists('valor_numerico', $fila) && blank($fila['valor_texto'] ?? null), 422, 'Cada resultado requiere un valor.');
        }
        $registros = DB::transaction(function () use ($estudio, $datos): array {
            $estudioBloqueado = EstudioClinico::query()->lockForUpdate()->findOrFail($estudio->getKey());
            abort_unless(in_array($estudioBloqueado->estado, ['SOLICITADO', 'EN_PROCESO', 'REALIZADO'], true), 409, 'El estudio ya no admite registro de resultados.');
            $componentesSolicitados = collect($datos['resultados'])->pluck('cod_componente');
            $yaRegistrados = ResultadoEstudio::query()->where('cod_estudio', $estudioBloqueado->cod_estudio)->whereIn('cod_componente', $componentesSolicitados)->lockForUpdate()->exists();
            abort_if($yaRegistrados, 409, 'Un resultado clínico existente no puede sobrescribirse; registre una rectificación trazable.');
            $creados = [];
            foreach ($datos['resultados'] as $fila) {
                $creados[] = ResultadoEstudio::query()->create(['cod_resultado_estudio' => $this->codigo('RES'), 'cod_estudio' => $estudioBloqueado->cod_estudio, ...$fila]);
            }
            $estudioBloqueado->update(['fecha_realizacion' => now(), 'estado' => 'REALIZADO']);

            return $creados;
        });

        return $registros;
    }

    public function informar(User $usuario, array $entrada, EstudioClinico $estudio): mixed
    {
        $residente = $estudio->residente()->firstOrFail();
        $personal = $this->autorizacion->autorizarMutacion($usuario, $residente, 'informes_estudio.crear', self::ROLES_SOLICITUD);
        abort_unless(in_array($estudio->estado, ['REALIZADO', 'INFORMADO'], true), 409, 'El estudio debe estar realizado antes de emitir un informe.');
        $datos = Validator::make($entrada, ['hallazgos' => ['nullable', 'string'], 'conclusion' => ['required', 'string', 'min:5'], 'recomendacion' => ['nullable', 'string'], 'origen' => ['required', 'in:INTERNO,EXTERNO'], 'profesional_externo' => ['nullable', 'required_if:origen,EXTERNO', 'string', 'max:160']])->validate();
        $informe = InformeEstudio::query()->create(['cod_informe_estudio' => $this->codigo('INF'), 'cod_estudio' => $estudio->cod_estudio, 'cod_personal' => $personal->cod_personal, ...$datos, 'fecha_hora' => now(), 'estado' => 'VIGENTE']);

        return $informe;
    }

    public function documento(User $usuario, array $entrada, Residente $residente): mixed
    {
        $personal = $this->autorizacion->autorizarMutacion($usuario, $residente, 'documentos_clinicos.crear', self::ROLES_SOLICITUD);
        $datos = Validator::make($entrada, ['archivo' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:20480'], 'cod_estudio' => ['nullable', 'exists:estudios_clinicos,cod_estudio'], 'cod_atencion' => ['nullable', 'exists:atenciones,cod_atencion'], 'tipo_documento' => ['required', 'string', 'max:60'], 'titulo' => ['required', 'string', 'max:180'], 'descripcion' => ['nullable', 'string'], 'origen' => ['nullable', 'string', 'max:20'], 'observacion' => ['nullable', 'string']])->validate();
        if (isset($datos['cod_estudio'])) {
            abort_unless(EstudioClinico::query()->whereKey($datos['cod_estudio'])->where('cod_residente', $residente->cod_residente)->exists(), 422, 'El estudio no pertenece al residente.');
        }
        $this->validarAtencion($datos['cod_atencion'] ?? null, $residente);
        $archivo = data_get($entrada, 'archivo');
        unset($datos['archivo']);
        $codigo = $this->codigo('DCL');
        $ruta = $archivo->storeAs('documentos-clinicos/'.$residente->cod_residente, $codigo.'.'.$archivo->extension(), 'local');
        $documento = DocumentoClinico::query()->create(['cod_documento_clinico' => $codigo, 'cod_residente' => $residente->cod_residente, 'cod_personal' => $personal->cod_personal, ...$datos, 'ruta_archivo' => $ruta, 'formato' => $archivo->getMimeType() ?: 'application/octet-stream', 'tamano_bytes' => $archivo->getSize(), 'hash_archivo' => hash_file('sha256', $archivo->getRealPath()), 'fecha_hora' => now(), 'estado' => 'VIGENTE']);

        return $documento;
    }

    public function derivar(User $usuario, array $entrada, Residente $residente): mixed
    {
        $personal = $this->autorizacion->autorizarMutacion($usuario, $residente, 'derivaciones.crear', self::ROLES_SOLICITUD);
        $datos = Validator::make($entrada, ['cod_area_solicitante' => ['required', 'exists:areas,cod_area'], 'cod_area_receptora' => ['required', 'different:cod_area_solicitante', 'exists:areas,cod_area'], 'cod_personal_receptor' => ['nullable', 'exists:personal,cod_personal'], 'cod_atencion' => ['nullable', 'exists:atenciones,cod_atencion'], 'motivo' => ['required', 'string'], 'prioridad' => ['nullable', 'string', 'max:20']])->validate();
        $this->validarAtencion($datos['cod_atencion'] ?? null, $residente);
        abort_unless($datos['cod_area_solicitante'] === $this->autorizacion->areaActiva($personal, $datos['cod_area_solicitante'])->cod_area, 422, 'El área solicitante no corresponde a la asignación clínica vigente del profesional.');

        return Derivacion::query()->create(['cod_derivacion' => $this->codigo('DER'), 'cod_residente' => $residente->cod_residente, 'cod_personal_solicitante' => $personal->cod_personal, ...$datos, 'fecha_hora' => now(), 'estado' => 'PENDIENTE']);
    }

    private function validarAtencion(?string $codigo, Residente $residente): void
    {
        if ($codigo !== null) {
            abort_unless(Atencion::query()->whereKey($codigo)->where('cod_residente', $residente->cod_residente)->exists(), 422, 'La atención no pertenece al residente.');
        }
    }

    private function codigo(string $prefijo): string
    {
        return $prefijo.'_'.Str::upper(Str::random(12));
    }
}
