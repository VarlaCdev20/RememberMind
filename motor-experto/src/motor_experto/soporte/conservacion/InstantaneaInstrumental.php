<?php

declare(strict_types=1);

namespace MotorExperto\soporte\conservacion;

use DomainException;
use MotorExperto\adaptadores\entrada\ConstructorMemoriaInstrumental;
use MotorExperto\aplicacion\casos_uso\EvaluarConocimientoCognitivo;
use MotorExperto\aplicacion\casos_uso\EvaluarPerfilInstitucional;
use MotorExperto\dominio\conocimiento\PaqueteConocimiento;
use MotorExperto\dominio\conocimiento\versiones\PaqueteCognitivoInstitucional;
use MotorExperto\dominio\objetos_valor\ContextoEjecucion;

/**
 * COMPONENTE DEL SISTEMA EXPERTO: Preparación de conservación y reproducción.
 * DTO inmutable en memoria con fuentes, contrato, revisión y salida declarativa verificables.
 * No es un repositorio, archivo clínico, firma profesional ni autorización para persistir JSON.
 */
final readonly class InstantaneaInstrumental
{
    private function __construct(public array $contenido, public string $huella) {}

    public static function crear(PaqueteConocimiento $p, ContextoEjecucion $c, array $capturas, array $vinculos, string $usuarioEjecucion, array $versionOriginal,
        string $contrato = 'INSTRUMENTAL-DECLARATIVO-2'): self
    {
        if (! in_array($contrato, ['INSTRUMENTAL-DECLARATIVO-1', 'INSTRUMENTAL-DECLARATIVO-2'], true)) {
            throw new DomainException('Contrato de preparación desconocido.');
        }
        if ($usuarioEjecucion === '' || strlen($usuarioEjecucion) > 20) {
            throw new DomainException('La preparación requiere la cuenta de ejecución identificada.');
        }
        if (($versionOriginal['cod_version_modelo'] ?? null) !== $p->version) {
            throw new DomainException('La cabecera histórica no identifica la revisión del conocimiento.');
        }
        $fuentes = (new CapturaInstrumentalCanonica)->construir($capturas, $contrato === 'INSTRUMENTAL-DECLARATIVO-2');
        if ($fuentes['fecha_corte'] !== $c->fechaCorte->format('Y-m-d\TH:i:s.uP')) {
            throw new DomainException('El corte de las fuentes no coincide con el de la instantánea.');
        }
        $declaracion = $p->declaracion();
        foreach (PaqueteConocimiento::CLAVES as $tabla => $pk) {
            usort($declaracion['tablas'][$tabla], static fn ($a, $b) => $a[$pk] <=> $b[$pk]);
        }
        $datos = CapturaInstrumentalCanonica::ordenar(['contrato' => $contrato,
            'contexto' => ['evaluacion' => $c->evaluacion, 'residente' => $c->residente, 'version' => $c->version, 'usuario_ejecucion' => $usuarioEjecucion,
                'fecha_corte' => CodecInstantanea::fecha('corte', $c->fechaCorte)],
            'cabecera_conocimiento' => array_intersect_key($versionOriginal, array_fill_keys(['cod_version_modelo', 'codigo_version',
                'nombre', 'descripcion', 'estado', 'fecha_hora_creacion', 'fecha_hora_vigencia', 'fecha_hora_retiro', 'motivo_cambio',
                'cod_version_anterior', 'cod_usuario_creacion', 'observacion'], true)),
            'conocimiento' => $declaracion, 'capturas' => $fuentes, 'vinculos' => $vinculos]);
        $datos['salida'] = self::ejecutar($datos);
        $datos = CapturaInstrumentalCanonica::ordenar($datos);

        return new self($datos, CodecInstantanea::huella($datos));
    }

    public static function verificar(array $contenido, string $huella): self
    {
        $contenido = CapturaInstrumentalCanonica::ordenar($contenido);
        if (! in_array($contenido['contrato'] ?? null, ['INSTRUMENTAL-DECLARATIVO-1', 'INSTRUMENTAL-DECLARATIVO-2'], true)
            || ! hash_equals(CodecInstantanea::huella($contenido), $huella)) {
            throw new DomainException('Contrato o huella de instantánea incompatible.');
        }
        $recalculada = self::ejecutar($contenido);
        if (CodecInstantanea::huella($recalculada) !== CodecInstantanea::huella($contenido['salida'])) {
            throw new DomainException('La salida no se reproduce con las fuentes y el conocimiento conservados.');
        }

        return new self($contenido, $huella);
    }

    /** No consulta SQL, configuración actual, instrumentos actuales ni definiciones actuales de reglas. */
    private static function ejecutar(array $d): array
    {
        $k = $d['conocimiento'];
        $p = new PaqueteConocimiento($k['version'], $k['tablas'], $k['contratos_relaciones'], $k['solo_pruebas_tecnicas'],
            $k['tipo_regla_resultado'], $k['contratos_extraccion']);
        $fecha = CodecInstantanea::leerFecha('corte', (object) $d['contexto']['fecha_corte']);
        $c = new ContextoEjecucion($d['contexto']['evaluacion'], $d['contexto']['residente'], $d['contexto']['version'], $fecha);
        $entrada = (new ConstructorMemoriaInstrumental)->construir($p, $c, $d['capturas'], $d['vinculos'], $d['contrato'] === 'INSTRUMENTAL-DECLARATIVO-2');

        $ejecutor = $d['contrato'] === 'INSTRUMENTAL-DECLARATIVO-2' && PaqueteCognitivoInstitucional::reconoce($p)
            ? new EvaluarPerfilInstitucional : new EvaluarConocimientoCognitivo;

        return CapturaInstrumentalCanonica::ordenar(['resultado' => $ejecutor->ejecutar($p, $entrada['memoria']),
            'fuentes_excluidas' => $entrada['fuentes_excluidas']]);
    }
}
