<?php

namespace App\Services\Usuarios;

use App\Models\User;
use Carbon\Carbon;

class DocumentosUsuarioService
{
    /**
     * Retorna los requisitos documentales (Checklist) que el usuario debe entregar físicamente/digitalmente.
     */
    public function documentosRequeridosPorRol(string $rol): array
    {
        $rol = strtolower($rol);
        if ($rol === 'superadministrador' || $rol === 'administrador' || $rol === 'admin') {
            $rol = 'personal_admin';
        } elseif (in_array(strtoupper($rol), ['ENFERMEROS', 'MEDICO GENERAL/GERIATRA', 'PSICOLOGO/A', 'PEDAGOGO', 'NUTRICIONISTA', 'FISIOTERAPEUTA'])) {
            $rol = 'personal_salud';
        }

        switch ($rol) {
            case 'personal_institucional':
                return [
                    'Cédula de identidad',
                    'Documento de designación institucional',
                    'Contrato o vínculo institucional',
                    'Documento de confidencialidad firmado',
                    'Contacto de emergencia',
                    'Fotografía institucional',
                ];
            case 'personal_admin':
                return [
                    'Cédula de identidad',
                    'Hoja de vida',
                    'Contrato o designación',
                    'Documento de confidencialidad firmado',
                    'Certificado de antecedentes si aplica',
                    'Contacto de emergencia',
                    'Fotografía institucional',
                ];
            case 'personal_salud':
                return [
                    'Cédula de identidad',
                    'Hoja de vida',
                    'Título profesional',
                    'Matrícula profesional',
                    'Registro SEDES o equivalente',
                    'Certificado de especialidad si aplica',
                    'Contrato o designación',
                    'Documento de confidencialidad clínica firmado',
                    'Certificado de salud ocupacional si aplica',
                    'Carnet de vacunas si aplica',
                    'Contacto de emergencia',
                    'Fotografía institucional',
                ];
            case 'voluntario':
                return [
                    'Cédula de identidad',
                    'Formulario de voluntariado',
                    'Carta de compromiso firmada',
                    'Documento de confidencialidad firmado',
                    'Disponibilidad horaria',
                    'Contacto de emergencia',
                    'Fotografía institucional',
                    'Certificado de antecedentes si aplica',
                    'Carta de presentación si es estudiante',
                ];
            case 'familiar':
                return [
                    'Cédula de identidad',
                    'Documento que acredite parentesco si aplica',
                    'Celular verificado',
                    'Dirección actual',
                    'Contacto alternativo',
                    'Autorización de contacto institucional',
                    'Documento de responsabilidad familiar si es responsable principal',
                    'Autorización para recibir información del adulto mayor',
                    'Autorización de retiro/salida si aplica',
                ];
            default:
                return [
                    'Cédula de identidad',
                    'Fotografía institucional',
                ];
        }
    }

    public function documentosInstitucionalesPorRol(string $rol): array
    {
        return array_map(function ($doc) {
            return "admin.usuarios.documentos.individuales.{$doc['slug']}";
        }, $this->documentosGeneradosPorRol($rol));
    }

    public function documentosGeneradosPorRol(string $rol): array
    {
        $rol = strtolower($rol);
        if ($rol === 'superadministrador' || $rol === 'administrador' || $rol === 'admin') {
            $rol = 'personal_admin';
        } elseif (in_array(strtoupper($rol), ['ENFERMEROS', 'MEDICO GENERAL/GERIATRA', 'PSICOLOGO/A', 'PEDAGOGO', 'NUTRICIONISTA', 'FISIOTERAPEUTA'])) {
            $rol = 'personal_salud';
        }

        $generales = [
            [
                'slug' => 'constancia-registro',
                'nombre' => 'Constancia de registro institucional',
                'descripcion' => 'Certifica el registro del usuario en el sistema.',
                'tipo' => 'general',
                'disponible' => true,
            ],
            [
                'slug' => 'checklist-requeridos',
                'nombre' => 'Checklist de documentos requeridos',
                'descripcion' => 'Lista de requisitos que debe entregar el usuario.',
                'tipo' => 'general',
                'disponible' => true,
            ],
            [
                'slug' => 'checklist-institucional',
                'nombre' => 'Checklist de documentos emitidos',
                'descripcion' => 'Documentos que la institución entrega al usuario.',
                'tipo' => 'general',
                'disponible' => true,
            ],
            [
                'slug' => 'carta-bienvenida',
                'nombre' => 'Carta de bienvenida',
                'descripcion' => 'Bienvenida oficial a la institución.',
                'tipo' => 'general',
                'disponible' => true,
            ],
            [
                'slug' => 'confidencialidad',
                'nombre' => 'Compromiso de confidencialidad',
                'descripcion' => 'Reglas generales de confidencialidad.',
                'tipo' => 'general',
                'disponible' => true,
            ],
            [
                'slug' => 'uso-responsable',
                'nombre' => 'Compromiso de uso responsable',
                'descripcion' => 'Reglamento de uso del sistema.',
                'tipo' => 'general',
                'disponible' => true,
            ],
        ];

        $especificos = [];
        switch ($rol) {
            case 'admin':
                $especificos = [
                    ['slug' => 'responsabilidad-administrativa', 'nombre' => 'Acta de responsabilidad administrativa', 'descripcion' => 'Responsabilidades para administradores.', 'tipo' => 'rol', 'disponible' => true],
                    ['slug' => 'autorizacion-administrativa', 'nombre' => 'Autorización de acceso administrativo', 'descripcion' => 'Acreditación de acceso a áreas restringidas.', 'tipo' => 'rol', 'disponible' => true],
                ];
                break;
            case 'personal_admin':
                $especificos = [
                    ['slug' => 'responsabilidad-documental', 'nombre' => 'Acta de responsabilidad documental', 'descripcion' => 'Compromiso en el manejo de archivos institucionales.', 'tipo' => 'rol', 'disponible' => true],
                    ['slug' => 'protocolo-documental', 'nombre' => 'Protocolo de manejo administrativo/documental', 'descripcion' => 'Directrices de la oficina.', 'tipo' => 'rol', 'disponible' => true],
                ];
                break;
            case 'personal_salud':
                $especificos = [
                    ['slug' => 'confidencialidad-clinica', 'nombre' => 'Compromiso de confidencialidad clínica', 'descripcion' => 'Protección de datos médicos de los pacientes.', 'tipo' => 'rol', 'disponible' => true],
                    ['slug' => 'responsabilidad-clinica', 'nombre' => 'Responsabilidad clínica institucional', 'descripcion' => 'Reglamento para profesionales en salud.', 'tipo' => 'rol', 'disponible' => true],
                    ['slug' => 'protocolo-registro-clinico', 'nombre' => 'Protocolo básico de registro clínico', 'descripcion' => 'Normas de llenado de historiales médicos.', 'tipo' => 'rol', 'disponible' => true],
                ];
                break;
            case 'voluntario':
                $especificos = [
                    ['slug' => 'compromiso-voluntario', 'nombre' => 'Carta de compromiso voluntario', 'descripcion' => 'Acuerdo básico de voluntariado.', 'tipo' => 'rol', 'disponible' => true],
                    ['slug' => 'reglamento-voluntariado', 'nombre' => 'Reglamento básico de voluntariado', 'descripcion' => 'Normativas internas para el apoyo social.', 'tipo' => 'rol', 'disponible' => true],
                    ['slug' => 'trato-digno', 'nombre' => 'Protocolo de trato digno al adulto mayor', 'descripcion' => 'Código ético de conducta.', 'tipo' => 'rol', 'disponible' => true],
                ];
                break;
            case 'familiar':
                $especificos = [
                    ['slug' => 'constancia-familiar', 'nombre' => 'Constancia de registro como familiar/responsable', 'descripcion' => 'Certificado de vinculación.', 'tipo' => 'rol', 'disponible' => true],
                    ['slug' => 'autorizacion-comunicacion', 'nombre' => 'Autorización de comunicación institucional', 'descripcion' => 'Consentimiento para llamadas o mensajes.', 'tipo' => 'rol', 'disponible' => true],
                    ['slug' => 'reglamento-visitas', 'nombre' => 'Reglamento de visitas', 'descripcion' => 'Normativa de ingreso y horarios.', 'tipo' => 'rol', 'disponible' => true],
                    ['slug' => 'protocolo-emergencias', 'nombre' => 'Protocolo de contacto en emergencias', 'descripcion' => 'Pautas para situaciones críticas.', 'tipo' => 'rol', 'disponible' => true],
                ];
                break;
        }

        return array_merge($generales, $especificos);
    }

    public function documentoDisponibleParaRol(string $rol, string $documento): bool
    {
        $disponibles = $this->documentosGeneradosPorRol($rol);
        foreach ($disponibles as $doc) {
            if ($doc['slug'] === $documento) {
                return true;
            }
        }
        return false;
    }

    public function datosUsuario(User $user): array
    {
        $rol = $user->roles->first()?->name ?? 'sin_rol';
        return [
            'usuario' => $user,
            'nombre_completo' => mb_strtoupper($user->nombres . ' ' . $user->ap_paterno . ' ' . $user->ap_materno, 'UTF-8'),
            'ci' => $user->numero_documento . ($user->expedido ? ' ' . $user->expedido : ''),
            'correo' => $user->correo ?? 'NO REGISTRADO',
            'celular' => $user->telefono ?? 'NO REGISTRADO',
            'direccion' => mb_strtoupper($user->direccion ?? 'NO REGISTRADA', 'UTF-8'),
            'rol' => mb_strtoupper(str_replace('_', ' ', $rol), 'UTF-8'),
            'rol_raw' => $rol,
            'estado_acceso' => $user->acceso_sistema ?? 'HABILITADO',
            'fecha_registro' => $user->created_at ? $user->created_at->format('d/m/Y') : now()->format('d/m/Y'),
            'requisitos' => $this->documentosRequeridosPorRol($rol),
            'documentos_institucionales' => $this->documentosGeneradosPorRol($rol),
            'area_cargo' => mb_strtoupper($this->obtenerAreaCargo($user, $rol), 'UTF-8'),
            'vinculos' => $this->obtenerVinculosFamiliares($user, $rol),
        ];
    }

    public function datosDocumentoIndividual(User $user, string $documento): array
    {
        $datos = $this->datosUsuario($user);
        $rol = $datos['rol_raw'];
        
        $docData = null;
        foreach ($this->documentosGeneradosPorRol($rol) as $doc) {
            if ($doc['slug'] === $documento) {
                $docData = $doc;
                break;
            }
        }

        $datos['documento_actual'] = $docData;
        $datos['fecha_actual'] = now()->isoFormat('D \d\e MMMM \d\e YYYY');
        
        return $datos;
    }

    public function vistaDocumentoIndividual(string $documento, string $rol): string
    {
        return "admin.usuarios.documentos.individuales.{$documento}";
    }

    public function nombreArchivoDocumento(User $user, string $documento): string
    {
        $nombreDoc = str_replace(' ', '_', strtolower(trim($user->nombres . '_' . $user->ap_paterno)));
        $slugSafe = str_replace('-', '_', $documento);
        return "{$slugSafe}_{$nombreDoc}_" . now()->format('Ymd') . ".pdf";
    }

    public function nombreArchivoPaquete(User $user): string
    {
        $rol = $user->roles->first()?->name ?? 'usuario';
        $nombreDoc = str_replace(' ', '_', strtolower(trim($user->nombres . '_' . $user->ap_paterno)));
        return "paquete_documental_{$nombreDoc}_{$rol}.pdf";
    }

    public function prepararPaquete(User $user): array
    {
        $datos = $this->datosUsuario($user);
        $rol = $datos['rol_raw'];
        $datos['vistas_paquete'] = $this->documentosInstitucionalesPorRol($rol);
        $datos['fecha_actual'] = now()->isoFormat('D \d\e MMMM \d\e YYYY');
        return $datos;
    }

    public function datosDocumentoUsuario(User $user): array
    {
        // Wrapper for compatibility
        return $this->prepararPaquete($user);
    }

    private function obtenerAreaCargo(User $user, string $rol): string
    {
        $rolUpper = strtoupper($rol);
        if (in_array($rolUpper, ['ENFERMEROS', 'MEDICO GENERAL/GERIATRA', 'PSICOLOGO/A', 'PEDAGOGO', 'NUTRICIONISTA', 'FISIOTERAPEUTA'])) {
            return $rolUpper;
        }
        if (in_array($rolUpper, ['SUPERADMINISTRADOR', 'ADMINISTRADOR'])) {
            return $rolUpper;
        }
        if ($rolUpper === 'VOLUNTARIO' && $user->voluntarios->first()) {
            return $user->voluntarios->first()->area_apoyo ?? 'APOYO GENERAL';
        }
        return 'N/A';
    }

    private function obtenerVinculosFamiliares(User $user, string $rol): array
    {
        if ($rol !== 'familiar') {
            return [];
        }

        $fam = $user->familiares->first();
        if (!$fam) return [];

        $vinculos = [];
        foreach ($fam->adultosMayores as $am) {
            $vinculos[] = [
                'nombre' => mb_strtoupper($am->nombres . ' ' . $am->ap_paterno . ' ' . $am->ap_materno, 'UTF-8'),
                'parentesco' => mb_strtoupper($am->pivot->parentesco_vinculo ?? 'FAMILIAR', 'UTF-8'),
            ];
        }

        return $vinculos;
    }
}
