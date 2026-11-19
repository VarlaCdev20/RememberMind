@props(['id'])

<fieldset class="rm-management-formats" :disabled="descargandoExportacion">
    <legend>¿En qué formato?</legend>
    <label><input type="radio" name="formato-{{ $id }}" value="pdf" x-model="formatoExportacion"><strong>PDF</strong><span>Lectura e impresión</span></label>
    <label><input type="radio" name="formato-{{ $id }}" value="csv" x-model="formatoExportacion"><strong>CSV</strong><span>Hoja de cálculo</span></label>
</fieldset>
