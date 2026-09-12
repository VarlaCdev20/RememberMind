<?php

namespace App\Actions\Identidad\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;
use Throwable;

class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    /**
     * Países disponibles y su prefijo telefónico.
     *
     * El código nunca se toma directamente desde el navegador.
     * Se calcula en servidor según el país seleccionado.
     */
    private const CODIGOS_TELEFONO = [
        'Bolivia' => '+591',
        'Argentina' => '+54',
        'Brasil' => '+55',
        'Chile' => '+56',
        'Perú' => '+51',
        'Paraguay' => '+595',
        'Uruguay' => '+598',
        'Colombia' => '+57',
        'Ecuador' => '+593',
        'Venezuela' => '+58',
        'México' => '+52',
        'España' => '+34',
        'Estados Unidos' => '+1',
        'Otro' => null,
    ];


    /**
     * Actualiza únicamente información autorizada
     * desde "Mi perfil".
     *
     * @param  array<string, mixed>  $input
     */
    public function update(
        User $user,
        array $input
    ): void {
        /*
         * Si existe una fotografía temporal,
         * podemos inferir la sección incluso si el
         * estado auxiliar no hubiese llegado.
         */
        $seccion = $input['_seccion']
            ?? (
                isset($input['photo'])
                ? 'fotografia'
                : null
            );


        /*
         * ÚNICAS operaciones autorizadas.
         *
         * Cualquier otro dato enviado desde el navegador
         * será ignorado.
         */
        Validator::make(
            [
                '_seccion' => $seccion,
            ],
            [
                '_seccion' => [
                    'required',
                    Rule::in([
                        'contacto',
                        'emergencia',
                        'fotografia',
                    ]),
                ],
            ],
            [
                '_seccion.required' =>
                'No se pudo determinar qué información deseas actualizar.',

                '_seccion.in' =>
                'La operación solicitada no está permitida.',
            ]
        )->validateWithBag(
            'updateProfileInformation'
        );


        match ($seccion) {
            'contacto' => $this->actualizarContacto(
                $user,
                $input
            ),

            'emergencia' => $this->actualizarEmergencia(
                $user,
                $input
            ),

            'fotografia' => $this->actualizarFotografia(
                $user,
                $input
            ),
        };
    }


    /**
     * ==============================================================
     * CONTACTO PERSONAL
     * ==============================================================
     */
    private function actualizarContacto(
        User $user,
        array $input
    ): void {
        $pais = trim(
            (string) (
                $input['pais_telefono']
                ?? ''
            )
        );

        $telefono = $this->soloDigitos(
            $input['telefono']
                ?? null
        );

        $codigoTelefono = self::CODIGOS_TELEFONO[$pais] ?? null;


        $datos = [
            'pais_telefono' => $pais,
            'telefono' => $telefono,
        ];


        $validated = Validator::make(
            $datos,
            [
                'pais_telefono' => [
                    'required',
                    'string',
                    Rule::in(
                        array_keys(
                            self::CODIGOS_TELEFONO
                        )
                    ),
                ],

                'telefono' => [
                    'required',
                    'string',
                    'max:20',
                    'regex:/^\d+$/',

                    function (
                        string $attribute,
                        mixed $value,
                        \Closure $fail
                    ) use (
                        $user,
                        $pais,
                        $codigoTelefono
                    ): void {
                        $this->validarTelefonoPorPais(
                            pais: $pais,
                            telefono: (string) $value,
                            fail: $fail
                        );

                        $duplicado = User::query()
                            ->where(
                                'codigo_telefono',
                                $codigoTelefono
                            )
                            ->where(
                                'telefono',
                                $value
                            )
                            ->where(
                                'cod_usu',
                                '!=',
                                $user->cod_usu
                            )
                            ->exists();

                        if ($duplicado) {
                            $fail(
                                'Este número de teléfono ya está registrado en otra cuenta.'
                            );
                        }
                    },
                ],
            ],
            [
                'pais_telefono.required' =>
                'Selecciona el país correspondiente al teléfono.',

                'pais_telefono.in' =>
                'El país seleccionado no es válido.',

                'telefono.required' =>
                'Ingresa tu número de teléfono.',

                'telefono.regex' =>
                'El teléfono solo puede contener números.',

                'telefono.max' =>
                'El teléfono no puede superar los 20 caracteres.',
            ]
        )->validateWithBag(
            'updateProfileInformation'
        );


        $user->forceFill([
            'pais_telefono' =>
            $validated['pais_telefono'],

            'codigo_telefono' =>
            $codigoTelefono,

            'telefono' =>
            $validated['telefono'],
        ])->save();


        $this->registrarActividad(
            user: $user,
            descripcion: 'El usuario actualizó su información de contacto desde Mi perfil.',
            camposPermitidos: [
                'pais_telefono',
                'codigo_telefono',
                'telefono',
            ],
        );
    }


    /**
     * ==============================================================
     * CONTACTO DE EMERGENCIA
     * ==============================================================
     */
    private function actualizarEmergencia(
        User $user,
        array $input
    ): void {
        $datos = [
            'contacto_emergencia' =>
            $this->normalizarMayusculas(
                $input['contacto_emergencia']
                    ?? null
            ),

            'ap_paterno_emergencia' =>
            $this->normalizarMayusculas(
                $input['ap_paterno_emergencia']
                    ?? null
            ),

            'ap_materno_emergencia' =>
            $this->normalizarMayusculas(
                $input['ap_materno_emergencia']
                    ?? null
            ),

            'parentesco_emergencia' =>
            $this->normalizarMayusculas(
                $input['parentesco_emergencia']
                    ?? null
            ),

            'celular_emergencia' =>
            $this->soloDigitos(
                $input['celular_emergencia']
                    ?? null
            ),
        ];


        $telefonoUsuario = $this->soloDigitos(
            $user->telefono
        );


        $validated = Validator::make(
            $datos,
            [
                'contacto_emergencia' => [
                    'required',
                    'string',
                    'min:2',
                    'max:150',
                    'regex:/^[\pL\s\-\']+$/u',
                ],

                'ap_paterno_emergencia' => [
                    'nullable',
                    'required_without:ap_materno_emergencia',
                    'string',
                    'max:80',
                    'regex:/^[\pL\s\-\']+$/u',
                ],

                'ap_materno_emergencia' => [
                    'nullable',
                    'required_without:ap_paterno_emergencia',
                    'string',
                    'max:80',
                    'regex:/^[\pL\s\-\']+$/u',
                ],

                'parentesco_emergencia' => [
                    'required',
                    'string',
                    'min:2',
                    'max:100',
                ],

                'celular_emergencia' => [
                    'required',
                    'string',
                    'min:6',
                    'max:15',
                    'regex:/^\d+$/',

                    function (
                        string $attribute,
                        mixed $value,
                        \Closure $fail
                    ) use (
                        $telefonoUsuario
                    ): void {
                        if (
                            $telefonoUsuario
                            && $value === $telefonoUsuario
                        ) {
                            $fail(
                                'El celular de emergencia debe ser diferente a tu teléfono personal.'
                            );
                        }
                    },
                ],
            ],
            [
                'contacto_emergencia.required' =>
                'Ingresa el nombre del contacto de emergencia.',

                'contacto_emergencia.min' =>
                'El nombre debe contener al menos 2 caracteres.',

                'contacto_emergencia.regex' =>
                'El nombre solo puede contener letras, espacios, guiones y apóstrofes.',


                'ap_paterno_emergencia.required_without' =>
                'Debes registrar al menos un apellido.',

                'ap_materno_emergencia.required_without' =>
                'Debes registrar al menos un apellido.',

                'ap_paterno_emergencia.regex' =>
                'El apellido paterno contiene caracteres no permitidos.',

                'ap_materno_emergencia.regex' =>
                'El apellido materno contiene caracteres no permitidos.',


                'parentesco_emergencia.required' =>
                'Ingresa el parentesco o vínculo.',


                'celular_emergencia.required' =>
                'Ingresa el celular del contacto de emergencia.',

                'celular_emergencia.min' =>
                'El celular debe contener al menos 6 dígitos.',

                'celular_emergencia.max' =>
                'El celular no puede superar los 15 dígitos.',

                'celular_emergencia.regex' =>
                'El celular solo puede contener números.',
            ]
        )->validateWithBag(
            'updateProfileInformation'
        );


        $user->forceFill([
            'contacto_emergencia' =>
            $validated['contacto_emergencia'],

            'ap_paterno_emergencia' =>
            $validated['ap_paterno_emergencia'] ?? null,

            'ap_materno_emergencia' =>
            $validated['ap_materno_emergencia'] ?? null,

            'parentesco_emergencia' =>
            $validated['parentesco_emergencia'],

            'celular_emergencia' =>
            $validated['celular_emergencia'],
        ])->save();


        $this->registrarActividad(
            user: $user,
            descripcion: 'El usuario actualizó su contacto de emergencia desde Mi perfil.',
            camposPermitidos: [
                'contacto_emergencia',
                'ap_paterno_emergencia',
                'ap_materno_emergencia',
                'parentesco_emergencia',
                'celular_emergencia',
            ],
        );
    }


    /**
     * ==============================================================
     * FOTOGRAFÍA
     * ==============================================================
     */
    private function actualizarFotografia(
        User $user,
        array $input
    ): void {
        $eliminarFoto = filter_var(
            $input['eliminar_foto']
                ?? false,
            FILTER_VALIDATE_BOOLEAN
        );


        /*
         * ----------------------------------------------------------
         * Eliminar fotografía actual
         * ----------------------------------------------------------
         */
        if ($eliminarFoto) {
            $rutaAnterior = $user->foto_de_perfil;


            $user->forceFill([
                'foto_de_perfil' => null,
            ])->save();


            if (
                $rutaAnterior
                && Storage::disk('public')
                ->exists($rutaAnterior)
            ) {
                Storage::disk('public')
                    ->delete($rutaAnterior);
            }


            $this->registrarActividad(
                user: $user,
                descripcion: 'El usuario eliminó su fotografía de perfil.',
                camposPermitidos: [
                    'foto_de_perfil',
                ],
            );

            return;
        }


        /*
         * ----------------------------------------------------------
         * Nueva fotografía
         * ----------------------------------------------------------
         */
        $validated = Validator::make(
            [
                'photo' =>
                $input['photo']
                    ?? null,
            ],
            [
                'photo' => [
                    'required',
                    'image',
                    'mimes:jpg,jpeg,png,webp',
                    'max:4096',
                ],
            ],
            [
                'photo.required' =>
                'Selecciona una fotografía antes de guardar.',

                'photo.image' =>
                'El archivo seleccionado debe ser una imagen válida.',

                'photo.mimes' =>
                'La fotografía debe estar en formato JPG, JPEG, PNG o WEBP.',

                'photo.max' =>
                'La fotografía no puede superar los 4 MB.',
            ]
        )->validateWithBag(
            'updateProfileInformation'
        );


        $rutaAnterior = $user->foto_de_perfil;

        $rutaNueva = $validated['photo']->store(
            'usuarios/fotos',
            'public'
        );


        try {
            $user->forceFill([
                'foto_de_perfil' =>
                $rutaNueva,
            ])->save();


            /*
             * Eliminamos la fotografía anterior
             * únicamente después de guardar
             * correctamente la nueva ruta.
             */
            if (
                $rutaAnterior
                && $rutaAnterior !== $rutaNueva
                && Storage::disk('public')
                ->exists($rutaAnterior)
            ) {
                Storage::disk('public')
                    ->delete($rutaAnterior);
            }


            $this->registrarActividad(
                user: $user,
                descripcion: 'El usuario actualizó su fotografía de perfil.',
                camposPermitidos: [
                    'foto_de_perfil',
                ],
            );
        } catch (Throwable $exception) {
            /*
             * Si la base de datos falla,
             * no dejamos archivos huérfanos.
             */
            if (
                Storage::disk('public')
                ->exists($rutaNueva)
            ) {
                Storage::disk('public')
                    ->delete($rutaNueva);
            }

            throw $exception;
        }
    }


    /**
     * ==============================================================
     * VALIDACIÓN TELEFÓNICA POR PAÍS
     * ==============================================================
     */
    private function validarTelefonoPorPais(
        string $pais,
        string $telefono,
        \Closure $fail
    ): void {
        $longitud = strlen($telefono);

        $primerDigito = substr(
            $telefono,
            0,
            1
        );


        switch ($pais) {
            case 'Bolivia':
                if (
                    $longitud !== 8
                    || ! in_array(
                        $primerDigito,
                        ['6', '7'],
                        true
                    )
                ) {
                    $fail(
                        'En Bolivia el celular debe tener 8 dígitos y comenzar con 6 o 7.'
                    );
                }

                break;


            case 'Chile':
                if (
                    $longitud !== 9
                    || $primerDigito !== '9'
                ) {
                    $fail(
                        'En Chile el celular debe tener 9 dígitos y comenzar con 9.'
                    );
                }

                break;


            case 'Perú':
                if (
                    $longitud !== 9
                    || $primerDigito !== '9'
                ) {
                    $fail(
                        'En Perú el celular debe tener 9 dígitos y comenzar con 9.'
                    );
                }

                break;


            case 'Colombia':
                if (
                    $longitud !== 10
                    || $primerDigito !== '3'
                ) {
                    $fail(
                        'En Colombia el celular debe tener 10 dígitos y comenzar con 3.'
                    );
                }

                break;


            case 'México':
                if ($longitud !== 10) {
                    $fail(
                        'En México el celular debe tener exactamente 10 dígitos.'
                    );
                }

                break;


            case 'España':
                if (
                    $longitud !== 9
                    || ! in_array(
                        $primerDigito,
                        ['6', '7'],
                        true
                    )
                ) {
                    $fail(
                        'En España el celular debe tener 9 dígitos y comenzar con 6 o 7.'
                    );
                }

                break;


            case 'Estados Unidos':
                if ($longitud !== 10) {
                    $fail(
                        'En Estados Unidos el número debe tener exactamente 10 dígitos.'
                    );
                }

                break;


            case 'Brasil':
                if (
                    $longitud < 10
                    || $longitud > 11
                ) {
                    $fail(
                        'En Brasil el número debe tener entre 10 y 11 dígitos.'
                    );
                }

                break;


            /*
             * Argentina, Paraguay, Uruguay,
             * Ecuador, Venezuela y otros.
             *
             * Se usa una regla internacional general
             * mientras no exista una regla institucional
             * específica para esos países.
             */
            default:
                if (
                    $longitud < 6
                    || $longitud > 15
                ) {
                    $fail(
                        'El número debe contener entre 6 y 15 dígitos.'
                    );
                }

                break;
        }
    }


    /**
     * ==============================================================
     * NORMALIZACIÓN
     * ==============================================================
     */
    private function soloDigitos(
        mixed $valor
    ): ?string {
        if (
            $valor === null
            || trim((string) $valor) === ''
        ) {
            return null;
        }


        $normalizado = preg_replace(
            '/\D+/',
            '',
            (string) $valor
        );


        return $normalizado !== ''
            ? $normalizado
            : null;
    }


    private function normalizarMayusculas(
        mixed $valor
    ): ?string {
        if (
            $valor === null
            || trim((string) $valor) === ''
        ) {
            return null;
        }


        $normalizado = preg_replace(
            '/\s+/u',
            ' ',
            trim((string) $valor)
        );


        return mb_strtoupper(
            $normalizado,
            'UTF-8'
        );
    }


    /**
     * ==============================================================
     * AUDITORÍA
     * ==============================================================
     *
     * No registramos los valores sensibles.
     * Solo qué campos fueron modificados.
     */
    private function registrarActividad(
        User $user,
        string $descripcion,
        array $camposPermitidos
    ): void {
        $camposModificados = array_values(
            array_intersect(
                array_keys(
                    $user->getChanges()
                ),
                $camposPermitidos
            )
        );


        if (empty($camposModificados)) {
            return;
        }


        if (! function_exists('activity')) {
            return;
        }


        activity('Usuarios')
            ->causedBy($user)
            ->performedOn($user)
            ->event('actualizacion_perfil')
            ->withProperties([
                'origen' => 'mi_perfil',
                'campos' => $camposModificados,
            ])
            ->log($descripcion);
    }
}
