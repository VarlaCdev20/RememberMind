<?php

namespace App\Services\Reportes;

class ReportWatermarkService
{
    /**
     * Retorna el texto institucional de la marca de agua.
     *
     * @return string
     */
    public function getText(): string
    {
        return 'CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS';
    }

    /**
     * Retorna los estilos CSS necesarios para pintar la marca de agua de fondo.
     *
     * @return string
     */
    public function getCssStyles(): string
    {
        return "
            .report-watermark {
                position: fixed;
                top: 35%;
                left: 10%;
                width: 80%;
                text-align: center;
                opacity: 0.06;
                z-index: -1000;
                font-size: 72px;
                font-weight: 900;
                color: #2F3E5C;
                transform: rotate(-22deg);
                text-transform: uppercase;
                letter-spacing: 0.15em;
                pointer-events: none;
                user-select: none;
            }
        ";
    }
}
