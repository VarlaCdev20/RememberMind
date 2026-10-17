<?php

namespace App\Modulos\Clinica\Aplicacion\Acciones;

use App\Models\Alergia;
use App\Models\AntecedenteClinico;
use App\Models\Atencion;
use App\Models\Diagnostico;
use App\Models\Incidente;
use App\Models\IndicacionClinica;
use App\Models\MedicionAntropometrica;
use App\Models\NotaClinica;
use App\Models\Residente;
use App\Models\SignoVital;
use App\Models\User;
use App\Models\ValoracionDolor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

final class RegistrarExpedienteClinico
{
    private const ROLES_CLINICOS = ['MEDICO GENERAL/GERIATRA', 'ENFERMEROS', 'PSICOLOGO/A', 'NUTRICIONISTA', 'FISIOTERAPEUTA', 'PEDAGOGO'];

    public function __construct(private readonly AutorizacionClinicaService $autorizacion) {}

    public function crearAtencion(User $usuario, array $entrada, Residente $residente): mixed
    {
        $personal = $this->autorizacion->autorizarMutacion($usuario, $residente, 'atenciones.crear', self::ROLES_CLINICOS);
        $datos = Validator::make($entrada, ['cod_area' => ['required', 'exists:areas,cod_area'], 'tipo_atencion' => ['required', 'string', 'max:60'], 'motivo' => ['nullable', 'string'], 'observacion' => ['nullable', 'string']])->validate();
        $area = $this->autorizacion->areaActiva($personal, $datos['cod_area']);
        abort_unless($datos['cod_area'] === $area->cod_area, 422, 'El área indicada no corresponde a la asignación clínica vigente del profesional.');
        $atencion = Atencion::query()->create(['cod_atencion' => app(\App\Compartido\Aplicacion\Contratos\CodigosEntidad::class)->siguiente('atenciones'), 'cod_residente' => $residente->cod_residente, 'cod_personal' => $personal->cod_personal, ...$datos, 'fecha_hora' => now(), 'estado' => 'ABIERTA']);

        return $atencion;
    }

    public function registrar(User $usuario, array $entrada, Residente $residente, string $tipo): mixed
    {
        $definicion = $this->definiciones()[$tipo] ?? abort(404);
        $personal = $this->autorizacion->autorizarMutacion($usuario, $residente, $definicion['permiso'], $definicion['roles']);
        $datos = Validator::make($entrada, $definicion['reglas'])->validate();
        if (isset($datos['cod_atencion'])) {
            abort_unless(Atencion::query()->whereKey($datos['cod_atencion'])->where('cod_residente', $residente->cod_residente)->exists(), 422, 'La atención no pertenece al residente.');
        }
        /** @var class-string<Model> $modelo */
        $modelo = $definicion['modelo'];
        $registro = $modelo::query()->create([$definicion['pk'] => app(\App\Compartido\Aplicacion\Contratos\CodigosEntidad::class)->siguiente((new $modelo)->getTable()), 'cod_residente' => $residente->cod_residente, 'cod_personal' => $personal->cod_personal, ...$datos, ...$definicion['valores']]);

        return $registro;
    }

    private function definiciones(): array
    {
        $texto = ['nullable', 'string'];

        return ['nota' => ['modelo' => NotaClinica::class, 'pk' => 'cod_nota', 'prefijo' => 'NOT', 'permiso' => 'notas_clinicas.crear', 'roles' => self::ROLES_CLINICOS, 'reglas' => ['cod_atencion' => ['required', 'exists:atenciones,cod_atencion'], 'tipo_nota' => ['required', 'string', 'max:50'], 'contenido' => ['required', 'string']], 'valores' => ['fecha_hora' => now(), 'estado' => 'VIGENTE']], 'antecedente' => ['modelo' => AntecedenteClinico::class, 'pk' => 'cod_antecedente', 'prefijo' => 'ANT', 'permiso' => 'antecedentes_clinicos.crear', 'roles' => ['MEDICO GENERAL/GERIATRA'], 'reglas' => ['tipo_antecedente' => ['required', 'string', 'max:60'], 'descripcion' => ['required', 'string'], 'fecha_referencia' => ['nullable', 'date'], 'fuente_informacion' => ['nullable', 'string', 'max:80'], 'observacion' => $texto], 'valores' => ['estado' => 'ACTIVO']], 'diagnostico' => ['modelo' => Diagnostico::class, 'pk' => 'cod_diagnostico', 'prefijo' => 'DIA', 'permiso' => 'diagnosticos.crear', 'roles' => ['MEDICO GENERAL/GERIATRA'], 'reglas' => ['cod_atencion' => ['required', 'exists:atenciones,cod_atencion'], 'codigo_clinico' => ['nullable', 'string', 'max:30'], 'nombre' => ['required', 'string', 'max:160'], 'tipo' => ['nullable', 'string', 'max:50'], 'certeza' => ['nullable', 'string', 'max:30'], 'observacion' => $texto], 'valores' => ['fecha_hora' => now(), 'estado' => 'ACTIVO']], 'alergia' => ['modelo' => Alergia::class, 'pk' => 'cod_alergia', 'prefijo' => 'ALE', 'permiso' => 'alergias.crear', 'roles' => ['MEDICO GENERAL/GERIATRA'], 'reglas' => ['tipo' => ['nullable', 'string', 'max:40'], 'sustancia' => ['required', 'string', 'max:120'], 'reaccion' => $texto, 'gravedad' => ['nullable', 'string', 'max:30'], 'observacion' => $texto], 'valores' => ['fecha_hora' => now(), 'estado' => 'ACTIVA']], 'signo-vital' => ['modelo' => SignoVital::class, 'pk' => 'cod_signo', 'prefijo' => 'SIG', 'permiso' => 'signos_vitales.crear', 'roles' => ['MEDICO GENERAL/GERIATRA', 'ENFERMEROS'], 'reglas' => ['cod_jornada' => ['nullable', 'exists:jornadas,cod_jornada'], 'cod_atencion' => ['nullable', 'exists:atenciones,cod_atencion'], 'presion_sistolica' => ['nullable', 'numeric', 'between:0,300'], 'presion_diastolica' => ['nullable', 'numeric', 'between:0,200'], 'frecuencia_cardiaca' => ['nullable', 'numeric', 'between:0,300'], 'frecuencia_respiratoria' => ['nullable', 'numeric', 'between:0,100'], 'temperatura' => ['nullable', 'numeric', 'between:25,50'], 'saturacion_oxigeno' => ['nullable', 'numeric', 'between:0,100'], 'glucemia' => ['nullable', 'numeric', 'min:0'], 'observacion' => $texto], 'valores' => ['fecha_hora' => now(), 'estado' => 'VIGENTE']], 'dolor' => ['modelo' => ValoracionDolor::class, 'pk' => 'cod_valoracion_dolor', 'prefijo' => 'DOL', 'permiso' => 'valoraciones_dolor.crear', 'roles' => ['MEDICO GENERAL/GERIATRA', 'ENFERMEROS', 'FISIOTERAPEUTA'], 'reglas' => ['cod_atencion' => ['nullable', 'exists:atenciones,cod_atencion'], 'intensidad' => ['nullable', 'integer', 'between:0,10'], 'ubicacion' => ['nullable', 'string', 'max:120'], 'tipo_dolor' => ['nullable', 'string', 'max:60'], 'duracion' => ['nullable', 'string', 'max:80'], 'desencadenante' => $texto, 'intervencion' => $texto, 'respuesta' => $texto], 'valores' => ['fecha_hora' => now(), 'estado' => 'VIGENTE']], 'antropometria' => ['modelo' => MedicionAntropometrica::class, 'pk' => 'cod_medicion', 'prefijo' => 'MED', 'permiso' => 'mediciones_antropometricas.crear', 'roles' => ['MEDICO GENERAL/GERIATRA', 'NUTRICIONISTA'], 'reglas' => ['peso' => ['nullable', 'numeric', 'min:0'], 'talla' => ['nullable', 'numeric', 'min:50', 'max:240'], 'imc' => ['nullable', 'numeric', 'min:0'], 'perimetro_braquial' => ['nullable', 'numeric', 'min:0'], 'perimetro_pantorrilla' => ['nullable', 'numeric', 'min:0'], 'observacion' => $texto], 'valores' => ['fecha_hora' => now()]], 'incidente' => ['modelo' => Incidente::class, 'pk' => 'cod_incidente', 'prefijo' => 'INC', 'permiso' => 'incidentes.crear', 'roles' => ['MEDICO GENERAL/GERIATRA', 'ENFERMEROS'], 'reglas' => ['cod_jornada' => ['nullable', 'exists:jornadas,cod_jornada'], 'tipo_incidente' => ['required', 'string', 'max:60'], 'gravedad' => ['nullable', 'string', 'max:30'], 'lugar' => ['nullable', 'string', 'max:120'], 'descripcion' => ['required', 'string'], 'medida_inmediata' => $texto, 'requiere_medico' => ['required', 'boolean'], 'requiere_derivacion' => ['required', 'boolean'], 'observacion' => $texto], 'valores' => ['fecha_hora' => now(), 'estado' => 'ABIERTO']], 'indicacion' => ['modelo' => IndicacionClinica::class, 'pk' => 'cod_indicacion', 'prefijo' => 'IND', 'permiso' => 'indicaciones_clinicas.crear', 'roles' => ['MEDICO GENERAL/GERIATRA'], 'reglas' => ['cod_atencion' => ['required', 'exists:atenciones,cod_atencion'], 'tipo_indicacion' => ['required', 'string', 'max:40'], 'descripcion' => ['required', 'string'], 'prioridad' => ['nullable', 'string', 'max:20'], 'observacion' => $texto], 'valores' => ['fecha_hora' => now(), 'estado' => 'ACTIVA']]];
    }

}
