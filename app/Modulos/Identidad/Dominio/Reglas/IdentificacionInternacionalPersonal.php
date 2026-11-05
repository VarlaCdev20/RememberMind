<?php

namespace App\Modulos\Identidad\Dominio\Reglas;

use Illuminate\Validation\Rule;

/** Formatos de captura, no verificación de autenticidad ni atribución de operadora. */
final class IdentificacionInternacionalPersonal
{
    public const PAISES = [
        'BO'=>['nombre'=>'Bolivia','codigo'=>'+591','doc'=>'CI','telefono'=>'[567][0-9]{7}','max_tel'=>8],
        'AR'=>['nombre'=>'Argentina','codigo'=>'+54','doc'=>'DNI','telefono'=>'[0-9]{10,11}','max_tel'=>11],
        'BR'=>['nombre'=>'Brasil','codigo'=>'+55','doc'=>'CPF','telefono'=>'[0-9]{10,11}','max_tel'=>11],
        'CL'=>['nombre'=>'Chile','codigo'=>'+56','doc'=>'RUN','telefono'=>'9[0-9]{8}','max_tel'=>9],
        'PE'=>['nombre'=>'Perú','codigo'=>'+51','doc'=>'DNI','telefono'=>'9[0-9]{8}','max_tel'=>9],
        'PY'=>['nombre'=>'Paraguay','codigo'=>'+595','doc'=>'CEDULA','telefono'=>'9[0-9]{8}','max_tel'=>9],
        'UY'=>['nombre'=>'Uruguay','codigo'=>'+598','doc'=>'CEDULA','telefono'=>'9[0-9]{7}','max_tel'=>8],
        'CO'=>['nombre'=>'Colombia','codigo'=>'+57','doc'=>'CEDULA','telefono'=>'3[0-9]{9}','max_tel'=>10],
        'EC'=>['nombre'=>'Ecuador','codigo'=>'+593','doc'=>'CEDULA','telefono'=>'9[0-9]{8}','max_tel'=>9],
        'VE'=>['nombre'=>'Venezuela','codigo'=>'+58','doc'=>'CEDULA','telefono'=>'4[0-9]{9}','max_tel'=>10],
        'MX'=>['nombre'=>'México','codigo'=>'+52','doc'=>'CURP','telefono'=>'[0-9]{10}','max_tel'=>10],
        'ES'=>['nombre'=>'España','codigo'=>'+34','doc'=>'DNI','telefono'=>'[67][0-9]{8}','max_tel'=>9],
        'US'=>['nombre'=>'Estados Unidos','codigo'=>'+1','doc'=>'PASAPORTE','telefono'=>'[2-9][0-9]{2}[2-9][0-9]{6}','max_tel'=>10],
    ];

    public static function tipos(mixed $pais): array
    {
        $pais = is_string($pais) ? $pais : null;
        $doc = self::PAISES[$pais]['doc'] ?? null;
        return array_values(array_unique(array_filter([$doc, $pais === 'ES' ? 'NIE' : null, 'PASAPORTE'])));
    }

    public static function formato(?string $pais, ?string $tipo): array
    {
        if ($tipo === 'PASAPORTE') return ['regex'=>'[A-Z0-9]{1,30}','max'=>30,'captura'=>'documento','ayuda'=>'Letras y números, sin espacios; hasta 30 caracteres.'];
        return match ($pais) {
            'BO'=>['regex'=>'[0-9]{5,10}','max'=>10,'captura'=>'numero','ayuda'=>'CI: entre 5 y 10 dígitos.'],
            'AR'=>['regex'=>'[0-9]{7,8}','max'=>8,'captura'=>'numero','ayuda'=>'DNI: 7 u 8 dígitos, sin puntos.'],
            'BR'=>['regex'=>'[0-9]{11}','max'=>11,'captura'=>'numero','ayuda'=>'CPF: 11 dígitos, sin puntos ni guiones.'],
            'PE'=>['regex'=>'[0-9]{8}','max'=>8,'captura'=>'numero','ayuda'=>'DNI: 8 dígitos.'],
            'CL'=>['regex'=>'[0-9]{7,8}-?[0-9K]','max'=>10,'captura'=>'run','ayuda'=>'RUN con dígito verificador, sin puntos; ejemplo 12345678-5.'],
            'EC'=>['regex'=>'[0-9]{10}','max'=>10,'captura'=>'numero','ayuda'=>'Cédula: 10 dígitos.'],
            'MX'=>['regex'=>'[A-Z0-9]{18}','max'=>18,'captura'=>'documento','ayuda'=>'CURP: 18 caracteres alfanuméricos.'],
            'ES'=>['regex'=>$tipo === 'NIE' ? '[XYZ][0-9]{7}[A-Z]' : '[0-9]{8}[A-Z]','max'=>9,'captura'=>'documento','ayuda'=>$tipo === 'NIE' ? 'X, Y o Z, siete números y letra de control.' : 'Ocho números y letra de control.'],
            default=>['regex'=>'[0-9]{1,30}','max'=>30,'captura'=>'numero','ayuda'=>'Número nacional sin puntos ni espacios; hasta 30 dígitos.'],
        };
    }

    public static function reglas(mixed $paisDocumento, mixed $tipo, mixed $paisTelefono): array
    {
        $paisDocumento = is_string($paisDocumento) ? $paisDocumento : null;
        $tipo = is_string($tipo) ? $tipo : null;
        $paisTelefono = is_string($paisTelefono) ? $paisTelefono : null;
        $formato = self::formato($paisDocumento, $tipo);
        $telefono = self::PAISES[$paisTelefono]['telefono'] ?? '[0-9]{1,15}';
        return [
            'pais_documento'=>['required', Rule::in(array_keys(self::PAISES))],
            'tipo_documento'=>['required', Rule::in(self::tipos($paisDocumento))],
            'pais_telefono'=>['required', Rule::in(array_keys(self::PAISES))],
            'numero_documento'=>['required','string','max:30','regex:~^'.$formato['regex'].'$~D'],
            'expedido'=>$paisDocumento === 'BO' && $tipo === 'CI' ? ['required',Rule::in(['LP','CB','SC','OR','PT','CH','TJ','BE','PN'])] : ['nullable','prohibited'],
            'telefono'=>['required','string','regex:~^'.$telefono.'$~D'],
        ];
    }

    public static function telefonoInternacional(string $pais, string $numero): string
    {
        return self::PAISES[$pais]['codigo'].$numero;
    }
}
