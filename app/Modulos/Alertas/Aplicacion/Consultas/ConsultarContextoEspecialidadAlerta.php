<?php

namespace App\Modulos\Alertas\Aplicacion\Consultas;

use App\Compartido\Aplicacion\Formato\TextoVisible;
use App\Models\Atencion;
use App\Models\NotaClinica;
use App\Models\Personal;
use App\Models\SeguimientoPedagogico;
use App\Models\User;
use App\Models\ValoracionFuncional;
use App\Models\ValoracionNutricional;
use App\Models\ValoracionPsicologica;
use App\Modulos\Alertas\Aplicacion\Formato\LenguajeAlerta;

/** Última fuente profesional del residente: contexto, no causalidad ni valoración nueva. */
final class ConsultarContextoEspecialidadAlerta
{
    public function consultar(User $usuario, string $codigo): ?array
    {
        $perfil = app(ExperienciaAlertas::class)->paraUsuario($usuario);
        $definicion = app(CamposRegistroEspecialidad::class)->paraRol($perfil['rol']);
        if (! $definicion) {
            return null;
        }
        $alerta = app(BandejaAlertas::class)->consulta($usuario)->with('residente')->findOrFail($codigo);
        [$modelo, $permiso] = match ($definicion['tipo']) {
            'nota' => [NotaClinica::class, 'notas_clinicas.ver'],
            'psicologia' => [ValoracionPsicologica::class, 'valoraciones_psicologicas.ver'],
            'nutricion' => [ValoracionNutricional::class, 'valoraciones_nutricionales.ver'],
            'funcional' => [ValoracionFuncional::class, 'valoraciones_funcionales.ver'],
            'pedagogia' => [SeguimientoPedagogico::class, 'seguimientos_pedagogicos.ver'],
        };
        $autorizado = $usuario->can($permiso);
        $registro = $autorizado ? $modelo::query()->where('cod_residente', $alerta->cod_residente)
            ->whereIn('estado', ['VIGENTE', 'ACTIVA', 'ACTIVO'])->orderByDesc('fecha_hora')
            ->orderByDesc((new $modelo)->getKeyName())
            ->first(['cod_personal', 'fecha_hora', ...($definicion['requiereAtencion'] ? ['cod_atencion'] : []), ...array_keys($definicion['campos'])]) : null;
        $campos = [];
        foreach ($definicion['campos'] as $campo => [$etiqueta, $tipo]) {
            $valor = $registro?->getAttribute($campo);
            $campos[$campo] = ['etiqueta' => $etiqueta, 'valor' => $valor === null || $valor === '' ? 'Sin registrar' : match ($tipo) {
                'booleano' => $valor ? 'Sí' : 'No',
                'decimal' => number_format((float) $valor, 2, ',', '.').' mL por día',
                default => TextoVisible::sinCodigosInternos((string) $valor),
            }];
        }
        $personal = $registro?->cod_personal ? Personal::find($registro->cod_personal, ['cod_personal', 'nombres', 'apellido_paterno', 'apellido_materno', 'profesion']) : null;
        $atencion = $registro && $definicion['requiereAtencion'] && $usuario->can('atenciones.ver')
            ? Atencion::whereKey($registro->cod_atencion)->where('cod_residente', $alerta->cod_residente)->with('area')->first(['cod_atencion', 'cod_area', 'fecha_hora', 'tipo_atencion']) : null;

        return ['autorizado' => $autorizado, 'hayRegistro' => $registro !== null,
            'residenteCodigo' => $alerta->cod_residente, 'rol' => $perfil['rol'],
            'atencion' => $atencion ? ['tipo' => $atencion->getRawOriginal('tipo_atencion'), 'fecha' => $atencion->fecha_hora?->format('d/m/Y H:i'), 'area' => $atencion->area?->nombre] : null,
            'residente' => $alerta->residente?->nombre_completo, 'fecha' => $registro?->fecha_hora?->format('d/m/Y H:i'),
            'autor' => $registro ? LenguajeAlerta::responsable($personal) : null,
            'titulo' => $definicion['tipo'] === 'nota' ? 'Nota clínica del residente' : $definicion['titulo'], 'campos' => $campos, 'secciones' => $definicion['secciones']];
    }
}
