<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TipoDocumentoUsuario;

class TipoDocumentoUsuarioSeeder extends Seeder
{
    public function run(): void
    {
        $tipos = [
            [
                'nombre' => 'Cédula de Identidad (CI)',
                'descripcion' => 'Documento de identidad nacional obligatorio.',
                'aplica_roles' => ['super_admin', 'admin', 'personal_admin', 'personal_salud', 'voluntario', 'familiar'],
                'obligatorio' => true,
                'requiere_vencimiento' => true,
                'requiere_validacion' => true,
                'orden' => 1,
            ],
            [
                'nombre' => 'Compromiso de Confidencialidad',
                'descripcion' => 'Acuerdo firmado de resguardo de información institucional y de los adultos mayores.',
                'aplica_roles' => ['super_admin', 'admin', 'personal_admin', 'personal_salud', 'voluntario'],
                'obligatorio' => true,
                'requiere_vencimiento' => false,
                'requiere_validacion' => true,
                'orden' => 2,
            ],
            [
                'nombre' => 'Fotografía de Perfil',
                'descripcion' => 'Fotografía institucional formal para el expediente.',
                'aplica_roles' => ['super_admin', 'admin', 'personal_admin', 'personal_salud', 'voluntario', 'familiar'],
                'obligatorio' => true,
                'requiere_vencimiento' => false,
                'requiere_validacion' => true,
                'orden' => 3,
            ],
            [
                'nombre' => 'Contrato o Designación Interna',
                'descripcion' => 'Contrato laboral, memorándum o designación interna.',
                'aplica_roles' => ['super_admin', 'admin', 'personal_admin', 'personal_salud'],
                'obligatorio' => true,
                'requiere_vencimiento' => true,
                'requiere_validacion' => true,
                'orden' => 4,
            ],
            [
                'nombre' => 'Autorización Institucional',
                'descripcion' => 'Documento formal de autorización para acceso a sistemas.',
                'aplica_roles' => ['super_admin', 'admin'],
                'obligatorio' => true,
                'requiere_vencimiento' => false,
                'requiere_validacion' => true,
                'orden' => 5,
            ],
            [
                'nombre' => 'Currículum Vitae (CV) u Hoja de Vida',
                'descripcion' => 'Ficha laboral e historial de experiencia.',
                'aplica_roles' => ['personal_admin', 'personal_salud'],
                'obligatorio' => false,
                'requiere_vencimiento' => false,
                'requiere_validacion' => true,
                'orden' => 6,
            ],
            [
                'nombre' => 'Certificado de Antecedentes',
                'descripcion' => 'Certificado policial de antecedentes penales.',
                'aplica_roles' => ['personal_admin', 'personal_salud', 'voluntario'],
                'obligatorio' => false,
                'requiere_vencimiento' => true,
                'requiere_validacion' => true,
                'orden' => 7,
            ],
            [
                'nombre' => 'Título Académico',
                'descripcion' => 'Título profesional otorgado por universidad acreditada.',
                'aplica_roles' => ['personal_salud'],
                'obligatorio' => true,
                'requiere_vencimiento' => false,
                'requiere_validacion' => true,
                'orden' => 8,
            ],
            [
                'nombre' => 'Título en Provisión Nacional',
                'descripcion' => 'Título en provisión nacional legalizado.',
                'aplica_roles' => ['personal_salud'],
                'obligatorio' => true,
                'requiere_vencimiento' => false,
                'requiere_validacion' => true,
                'orden' => 9,
            ],
            [
                'nombre' => 'Matrícula o Registro Profesional',
                'descripcion' => 'Registro oficial ante el Colegio de Médicos, Enfermeros u organismo de salud correspondiente.',
                'aplica_roles' => ['personal_salud'],
                'obligatorio' => true,
                'requiere_vencimiento' => false,
                'requiere_validacion' => true,
                'orden' => 10,
            ],
            [
                'nombre' => 'Certificación de Especialidad o Formación',
                'descripcion' => 'Certificado de especialidad médica o certificaciones específicas de cuidado gerontológico o cognitivo.',
                'aplica_roles' => ['personal_salud'],
                'obligatorio' => false,
                'requiere_vencimiento' => false,
                'requiere_validacion' => true,
                'orden' => 11,
            ],
            [
                'nombre' => 'Formulario de Voluntariado',
                'descripcion' => 'Ficha oficial de solicitud y registro de voluntario.',
                'aplica_roles' => ['voluntario'],
                'obligatorio' => true,
                'requiere_vencimiento' => false,
                'requiere_validacion' => true,
                'orden' => 12,
            ],
            [
                'nombre' => 'Carta de Aceptación o Convenio de Voluntariado',
                'descripcion' => 'Convenio formal firmado entre la institución y el voluntario.',
                'aplica_roles' => ['voluntario'],
                'obligatorio' => true,
                'requiere_vencimiento' => false,
                'requiere_validacion' => true,
                'orden' => 13,
            ],
            [
                'nombre' => 'Disponibilidad Horaria Declarada',
                'descripcion' => 'Formulario con los días y horas de servicio comprometidos.',
                'aplica_roles' => ['voluntario'],
                'obligatorio' => true,
                'requiere_vencimiento' => false,
                'requiere_validacion' => true,
                'orden' => 14,
            ],
            [
                'nombre' => 'Respaldo de Vínculo o Responsabilidad Familiar',
                'descripcion' => 'Documentación legal de parentesco o tutoría legal del adulto mayor.',
                'aplica_roles' => ['familiar'],
                'obligatorio' => true,
                'requiere_vencimiento' => false,
                'requiere_validacion' => true,
                'orden' => 15,
            ],
            [
                'nombre' => 'Autorización de Contacto y Consentimiento',
                'descripcion' => 'Autorización para recepción de notificaciones y consentimientos médicos.',
                'aplica_roles' => ['familiar'],
                'obligatorio' => true,
                'requiere_vencimiento' => false,
                'requiere_validacion' => true,
                'orden' => 16,
            ],
            [
                'nombre' => 'Documento de Dirección o Referencia Domiciliaria',
                'descripcion' => 'Croquis o factura de servicios públicos de referencia familiar.',
                'aplica_roles' => ['familiar'],
                'obligatorio' => false,
                'requiere_vencimiento' => false,
                'requiere_validacion' => true,
                'orden' => 17,
            ],
            [
                'nombre' => 'Certificaciones de Capacitación Externa',
                'descripcion' => 'Cursos, congresos y formaciones complementarias.',
                'aplica_roles' => ['super_admin', 'admin', 'personal_admin', 'personal_salud', 'voluntario'],
                'obligatorio' => false,
                'requiere_vencimiento' => false,
                'requiere_validacion' => true,
                'orden' => 18,
            ]
        ];

        foreach ($tipos as $tipo) {
            TipoDocumentoUsuario::create($tipo);
        }
    }
}
