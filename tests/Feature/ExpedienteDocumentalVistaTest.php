<?php

namespace Tests\Feature;

use App\Models\Documento;
use App\Models\NotaClinica;
use App\Models\Residente;
use Tests\TestCase;

class ExpedienteDocumentalVistaTest extends TestCase
{
    public function test_documentos_v2_muestran_la_ultima_validacion_sin_inventar_fecha_de_ingreso(): void
    {
        $documentos = collect([
            new Documento(['cod_documento' => 'DOC_A', 'nombre' => 'Identificación', 'fecha_validacion' => '2025-03-02 10:00:00']),
            new Documento(['cod_documento' => 'DOC_B', 'nombre' => 'Consentimiento', 'fecha_validacion' => '2026-10-20 11:00:00']),
            new Documento(['cod_documento' => 'DOC_C', 'nombre' => 'Certificado', 'fecha_validacion' => null]),
        ]);

        $this->view('pages.adultos-mayores.show.carpetas._documentos', [
            'documentosLista' => $documentos, 'idAdulto' => 'RES_A',
        ])->assertSeeInOrder(['Última validación', '20/10/2026'])
            ->assertSee('Validación: 02/03/2025')
            ->assertSee('Sin fecha de validación')
            ->assertDontSee('Último Ingresado');
    }

    public function test_expediente_documental_sin_validaciones_o_sin_documentos_no_falla(): void
    {
        $this->view('pages.adultos-mayores.show.carpetas._documentos', [
            'documentosLista' => collect([new Documento(['cod_documento' => 'DOC_A', 'nombre' => 'Certificado'])]),
            'idAdulto' => 'RES_A',
        ])->assertSee('Sin fecha de validación');

        $this->view('pages.adultos-mayores.show.carpetas._documentos', [
            'documentosLista' => collect(), 'idAdulto' => 'RES_A',
        ])->assertSee('No hay documentos registrados en el expediente.');
    }

    public function test_identificacion_muestra_observacion_institucional_y_no_serializa_notas_clinicas(): void
    {
        $residente = new Residente(['observacion' => 'Ingreso acompañado por familiar.']);
        $residente->setRelation('observaciones', collect([new NotaClinica(['contenido' => 'Nota clínica reservada a su sección.'])]));

        $this->view('pages.adultos-mayores.show.carpetas._identificacion', [
            'adultoObj' => $residente, 'nombreCompleto' => 'Elena Rojas', 'ci' => 'CI-A',
            'fechaNacimientoFormateada' => '01/01/1940', 'edad' => 86, 'genero' => 'FEMENINO',
            'fechaIngresoFormateada' => '01/01/2025', 'estadoTexto' => 'ADMITIDO',
        ])->assertSee('Ingreso acompañado por familiar.')
            ->assertDontSee('Nota clínica reservada a su sección.')
            ->assertDontSee('cod_nota');
    }
}
