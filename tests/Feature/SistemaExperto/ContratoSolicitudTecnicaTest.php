<?php

namespace Tests\Feature\SistemaExperto;

use App\Modulos\ApoyoDecision\Aplicacion\Contratos\SolicitudEvaluacionTecnicaV1;
use App\Modulos\ApoyoDecision\Aplicacion\Eventos\EvaluacionTecnicaSolicitada;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class ContratoSolicitudTecnicaTest extends TestCase
{
    private function datos(): array
    {
        return json_decode(file_get_contents(base_path('contratos/evaluacion-experta/v1/solicitud-tecnica.json')), true, 512, JSON_THROW_ON_ERROR);
    }

    public function test_el_contrato_preserva_contexto_y_clave_de_reintento(): void
    {
        $solicitud = SolicitudEvaluacionTecnicaV1::desdeArray($this->datos());
        $reintento = SolicitudEvaluacionTecnicaV1::desdeArray($solicitud->paraArray());
        $this->assertSame($this->datos(), $reintento->paraArray());
        $this->assertSame($solicitud->claveIdempotencia(), $reintento->claveIdempotencia());
        $this->assertNotSame($solicitud->claveIdempotencia(), SolicitudEvaluacionTecnicaV1::desdeArray([...$this->datos(), 'version_conocimiento' => 'VER_OTRA'])->claveIdempotencia());
    }

    public function test_el_contrato_rechaza_activacion_clinica_y_version_desconocida(): void
    {
        foreach ([['uso' => 'CLINICA'], ['contrato' => 2], ['fecha_corte' => '2026-10-08'], ['residente' => ''], ['criterio' => null], ['extra' => true]] as $cambio) {
            try {
                SolicitudEvaluacionTecnicaV1::desdeArray([...$this->datos(), ...$cambio]);
                $this->fail('Se aceptó un contrato incompatible.');
            } catch (DomainException $error) {
                $this->assertNotEmpty($error->getMessage());
            }
        }
    }

    public function test_el_evento_se_emite_solo_despues_del_commit_exterior(): void
    {
        $recibidos = [];
        Event::listen(EvaluacionTecnicaSolicitada::class, function ($evento) use (&$recibidos): void {
            $recibidos[] = $evento->solicitud->claveIdempotencia();
        });
        $evento = new EvaluacionTecnicaSolicitada(SolicitudEvaluacionTecnicaV1::desdeArray($this->datos()));
        DB::beginTransaction();
        DB::beginTransaction();
        Event::dispatch($evento);
        $this->assertSame([], $recibidos);
        DB::commit();
        $this->assertSame([], $recibidos);
        DB::commit();
        $this->assertSame([$evento->solicitud->claveIdempotencia()], $recibidos);
        DB::beginTransaction();
        Event::dispatch($evento);
        DB::rollBack();
        $this->assertCount(1, $recibidos);
    }
}
