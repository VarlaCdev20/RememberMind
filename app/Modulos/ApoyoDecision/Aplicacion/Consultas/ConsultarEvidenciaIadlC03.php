<?php

namespace App\Modulos\ApoyoDecision\Aplicacion\Consultas;

use App\Models\ControlFuncionalIadl;
use App\Models\Residente;
use App\Models\User;
use App\Modulos\Valoraciones\Dominio\Servicios\AnalizadorIadlC03;
use Carbon\Carbon;
use DomainException;
use Illuminate\Support\Facades\Gate;

/**
 * Contrato de lectura para O.R.I.O.N. C03. No cambia conocimiento ni emite
 * juicio clínico: aporta hechos documentados y conserva C03=U.
 */
final class ConsultarEvidenciaIadlC03
{
    public function __construct(private readonly AnalizadorIadlC03 $analizador)
    {
    }

    public function ejecutar(User $actor, Residente $residente, string $corte): array
    {
        abort_unless(
            $actor->can('controles_funcionales_iadl.ver')
                && Gate::forUser($actor)->allows('view', $residente),
            403
        );
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/D', $corte) !== 1) {
            throw new DomainException('La consulta de IADL necesita fecha de corte YYYY-MM-DD.');
        }
        $fecha = Carbon::createFromFormat('!Y-m-d', $corte);
        if (!$fecha || $fecha->format('Y-m-d') !== $corte) {
            throw new DomainException('Fecha de corte IADL inválida.');
        }

        $filas = ControlFuncionalIadl::query()
            ->where('cod_residente', $residente->cod_residente)
            ->where('fecha_hora', '<=', $fecha->copy()->endOfDay())
            ->orderBy('fecha_hora')->orderBy('registrado_en')
            ->get()->map(static fn (ControlFuncionalIadl $fila): array => [
                ...$fila->only([
                    'cod_control_iadl', 'cod_control_anterior', 'actividad', 'oportunidad',
                    'desempeno', 'tipo_ayuda', 'causa_ayuda', 'nivel_previo', 'fuente', 'estado'
                ]),
                'fecha_hora'=>$fila->fecha_hora->format('Y-m-d H:i:s'),
                'registrado_en'=>$fila->registrado_en->format('Y-m-d H:i:s'),
            ])->all();

        $sintesis = $this->analizador->analizar($filas);

        return [
            'uso'=>'LECTURA_IADL_DESCRIPTIVA_NO_CLINICA',
            'contrato'=>'IADL-C03-EXP-01',
            'cod_residente'=>$residente->cod_residente,
            'fecha_corte'=>$corte,
            'registros_consultados'=>count($filas),
            'traza_controles'=>array_column($filas, 'cod_control_iadl'),
            'sintesis'=>$sintesis,
            'criterio_dcl_c03'=>'U',
            'puede_emitir_clasificacion_dcl'=>false,
            'limites'=>'Sin revisión profesional, las IADL no justifican C03=S; el motor no atribuye demencia ni normalidad.',
        ];
    }
}
