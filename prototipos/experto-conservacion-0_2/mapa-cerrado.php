<?php

declare(strict_types=1);

// Contrato cerrado OFFLINE; no contiene registros ni salidas clínicas.
return array (
  'contrato' => 'PROYECCION-COMPUESTA-F02-1',
  'raiz' => 'm001',
  'modelos' => 
  array (
    'm001' => 
    array (
      'tabla' => 'entradas_compuestas',
      'padre' => NULL,
      'ruta' => '',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'instrumental' => 
          array (
            'clase' => 'objeto',
            'campos' => 
            array (
              'cabecera_conocimiento' => 
              array (
                'clase' => 'objeto',
                'campos' => 
                array (
                  'cod_usuario_creacion' => 
                  array (
                    'clase' => 'escalar',
                  ),
                  'cod_version_anterior' => 
                  array (
                    'clase' => 'escalar',
                  ),
                  'cod_version_modelo' => 
                  array (
                    'clase' => 'escalar',
                  ),
                  'codigo_version' => 
                  array (
                    'clase' => 'escalar',
                  ),
                  'descripcion' => 
                  array (
                    'clase' => 'escalar',
                  ),
                  'estado' => 
                  array (
                    'clase' => 'escalar',
                  ),
                  'fecha_hora_creacion' => 
                  array (
                    'clase' => 'escalar',
                  ),
                  'fecha_hora_retiro' => 
                  array (
                    'clase' => 'escalar',
                  ),
                  'fecha_hora_vigencia' => 
                  array (
                    'clase' => 'escalar',
                  ),
                  'motivo_cambio' => 
                  array (
                    'clase' => 'escalar',
                  ),
                  'nombre' => 
                  array (
                    'clase' => 'escalar',
                  ),
                  'observacion' => 
                  array (
                    'clase' => 'escalar',
                  ),
                ),
              ),
              'capturas' => 
              array (
                'clase' => 'objeto',
                'campos' => 
                array (
                  'capturas' => 
                  array (
                    'clase' => 'coleccion',
                    'modo' => 'lista',
                    'modelo' => 'm002',
                    'identidad' => 'Ca877034852',
                  ),
                  'disponible' => 
                  array (
                    'clase' => 'escalar',
                  ),
                  'fecha_corte' => 
                  array (
                    'clase' => 'escalar',
                  ),
                  'persistido' => 
                  array (
                    'clase' => 'escalar',
                  ),
                ),
              ),
              'conocimiento' => 
              array (
                'clase' => 'objeto',
                'campos' => 
                array (
                  'contratos_extraccion' => 
                  array (
                    'clase' => 'objeto',
                    'campos' => 
                    array (
                      'RESPUESTA_INSTITUCIONAL' => 
                      array (
                        'clase' => 'escalar',
                      ),
                      'RESPUESTA_ORIGINAL' => 
                      array (
                        'clase' => 'escalar',
                      ),
                    ),
                  ),
                  'contratos_relaciones' => 
                  array (
                    'clase' => 'coleccion',
                    'modo' => 'lista',
                    'modelo' => 'm008',
                    'identidad' => 'C4f168aeb0b',
                  ),
                  'solo_pruebas_tecnicas' => 
                  array (
                    'clase' => 'escalar',
                  ),
                  'tablas' => 
                  array (
                    'clase' => 'objeto',
                    'campos' => 
                    array (
                      'condiciones_regla_experta' => 
                      array (
                        'clase' => 'coleccion',
                        'modo' => 'lista',
                        'modelo' => 'k_condiciones_regla_experta',
                        'identidad' => 'I',
                      ),
                      'consecuencias_regla_experta' => 
                      array (
                        'clase' => 'coleccion',
                        'modo' => 'lista',
                        'modelo' => 'k_consecuencias_regla_experta',
                        'identidad' => 'I',
                      ),
                      'criterios_dominios_resultado' => 
                      array (
                        'clase' => 'coleccion',
                        'modo' => 'lista',
                        'modelo' => 'k_criterios_dominios_resultado',
                        'identidad' => 'I',
                      ),
                      'dominios_valores_expertos' => 
                      array (
                        'clase' => 'coleccion',
                        'modo' => 'lista',
                        'modelo' => 'k_dominios_valores_expertos',
                        'identidad' => 'I',
                      ),
                      'fuentes_datos_expertas' => 
                      array (
                        'clase' => 'coleccion',
                        'modo' => 'lista',
                        'modelo' => 'k_fuentes_datos_expertas',
                        'identidad' => 'I',
                      ),
                      'mapeos_valores_fuente' => 
                      array (
                        'clase' => 'coleccion',
                        'modo' => 'lista',
                        'modelo' => 'k_mapeos_valores_fuente',
                        'identidad' => 'I',
                      ),
                      'mapeos_variables_fuente' => 
                      array (
                        'clase' => 'coleccion',
                        'modo' => 'lista',
                        'modelo' => 'k_mapeos_variables_fuente',
                        'identidad' => 'I',
                      ),
                      'nodos_semanticos' => 
                      array (
                        'clase' => 'coleccion',
                        'modo' => 'lista',
                        'modelo' => 'k_nodos_semanticos',
                        'identidad' => 'I',
                      ),
                      'reglas_expertas' => 
                      array (
                        'clase' => 'coleccion',
                        'modo' => 'lista',
                        'modelo' => 'k_reglas_expertas',
                        'identidad' => 'I',
                      ),
                      'relaciones_semanticas' => 
                      array (
                        'clase' => 'coleccion',
                        'modo' => 'lista',
                        'modelo' => 'k_relaciones_semanticas',
                        'identidad' => 'I',
                      ),
                      'valores_semanticos' => 
                      array (
                        'clase' => 'coleccion',
                        'modo' => 'lista',
                        'modelo' => 'k_valores_semanticos',
                        'identidad' => 'I',
                      ),
                      'variables_expertas' => 
                      array (
                        'clase' => 'coleccion',
                        'modo' => 'lista',
                        'modelo' => 'k_variables_expertas',
                        'identidad' => 'I',
                      ),
                    ),
                  ),
                  'tipo_regla_resultado' => 
                  array (
                    'clase' => 'escalar',
                  ),
                  'version' => 
                  array (
                    'clase' => 'escalar',
                  ),
                ),
              ),
              'contexto' => 
              array (
                'clase' => 'objeto',
                'campos' => 
                array (
                  'evaluacion' => 
                  array (
                    'clase' => 'escalar',
                  ),
                  'fecha_corte' => 
                  array (
                    'clase' => 'objeto',
                    'campos' => 
                    array (
                      'corte_offset' => 
                      array (
                        'clase' => 'escalar',
                      ),
                      'corte_utc' => 
                      array (
                        'clase' => 'escalar',
                      ),
                      'corte_zona' => 
                      array (
                        'clase' => 'escalar',
                      ),
                    ),
                  ),
                  'residente' => 
                  array (
                    'clase' => 'escalar',
                  ),
                  'usuario_ejecucion' => 
                  array (
                    'clase' => 'escalar',
                  ),
                  'version' => 
                  array (
                    'clase' => 'escalar',
                  ),
                ),
              ),
              'contrato' => 
              array (
                'clase' => 'escalar',
              ),
              'vinculos' => 
              array (
                'clase' => 'coleccion',
                'modo' => 'mapa',
                'modelo' => 'm010',
                'identidad' => 'C7874dd01a9',
              ),
            ),
          ),
          'petersen' => 
          array (
            'clase' => 'objeto',
            'campos' => 
            array (
              'adquisicion' => 
              array (
                'clase' => 'objeto',
                'campos' => 
                array (
                  'conservacion_completa' => 
                  array (
                    'clase' => 'escalar',
                  ),
                  'exclusiones' => 
                  array (
                    'clase' => 'coleccion',
                    'modo' => 'lista',
                    'modelo' => 'm013',
                    'identidad' => 'Cefd2a57b6f',
                  ),
                  'fuentes_originales_mutables' => 
                  array (
                    'clase' => 'escalar',
                  ),
                  'valoraciones_al_corte' => 
                  array (
                    'clase' => 'escalar',
                  ),
                ),
              ),
              'constancias_documentales' => 
              array (
                'clase' => 'coleccion',
                'modo' => 'lista',
                'modelo' => 'm014',
                'identidad' => 'C7eea8071b0',
              ),
              'contexto' => 
              array (
                'clase' => 'objeto',
                'campos' => 
                array (
                  'evaluacion' => 
                  array (
                    'clase' => 'escalar',
                  ),
                  'fecha_corte' => 
                  array (
                    'clase' => 'objeto',
                    'campos' => 
                    array (
                      'corte_offset' => 
                      array (
                        'clase' => 'escalar',
                      ),
                      'corte_utc' => 
                      array (
                        'clase' => 'escalar',
                      ),
                      'corte_zona' => 
                      array (
                        'clase' => 'escalar',
                      ),
                    ),
                  ),
                  'residente' => 
                  array (
                    'clase' => 'escalar',
                  ),
                  'usuario_ejecucion' => 
                  array (
                    'clase' => 'escalar',
                  ),
                  'version' => 
                  array (
                    'clase' => 'escalar',
                  ),
                ),
              ),
              'contrato' => 
              array (
                'clase' => 'escalar',
              ),
              'gobierno' => 
              array (
                'clase' => 'objeto',
                'campos' => 
                array (
                  'cabecera' => 
                  array (
                    'clase' => 'objeto',
                    'campos' => 
                    array (
                      'cod_usuario_creacion' => 
                      array (
                        'clase' => 'escalar',
                      ),
                      'cod_version_anterior' => 
                      array (
                        'clase' => 'escalar',
                      ),
                      'cod_version_modelo' => 
                      array (
                        'clase' => 'escalar',
                      ),
                      'codigo_version' => 
                      array (
                        'clase' => 'escalar',
                      ),
                      'descripcion' => 
                      array (
                        'clase' => 'escalar',
                      ),
                      'estado' => 
                      array (
                        'clase' => 'escalar',
                      ),
                      'fecha_hora_creacion' => 
                      array (
                        'clase' => 'escalar',
                      ),
                      'fecha_hora_retiro' => 
                      array (
                        'clase' => 'escalar',
                      ),
                      'fecha_hora_vigencia' => 
                      array (
                        'clase' => 'escalar',
                      ),
                      'motivo_cambio' => 
                      array (
                        'clase' => 'escalar',
                      ),
                      'nombre' => 
                      array (
                        'clase' => 'escalar',
                      ),
                      'observacion' => 
                      array (
                        'clase' => 'escalar',
                      ),
                    ),
                  ),
                  'conocimiento' => 
                  array (
                    'clase' => 'objeto',
                    'campos' => 
                    array (
                      'contratos_extraccion' => 
                      array (
                        'clase' => 'objeto',
                        'campos' => 
                        array (
                          'RESPUESTA_INSTITUCIONAL' => 
                          array (
                            'clase' => 'escalar',
                          ),
                          'RESPUESTA_ORIGINAL' => 
                          array (
                            'clase' => 'escalar',
                          ),
                        ),
                      ),
                      'contratos_relaciones' => 
                      array (
                        'clase' => 'coleccion',
                        'modo' => 'lista',
                        'modelo' => 'm015',
                        'identidad' => 'C91c39626e7',
                      ),
                      'solo_pruebas_tecnicas' => 
                      array (
                        'clase' => 'escalar',
                      ),
                      'tablas' => 
                      array (
                        'clase' => 'objeto',
                        'campos' => 
                        array (
                          'condiciones_regla_experta' => 
                          array (
                            'clase' => 'coleccion',
                            'modo' => 'lista',
                            'modelo' => 'k_condiciones_regla_experta',
                            'identidad' => 'P',
                          ),
                          'consecuencias_regla_experta' => 
                          array (
                            'clase' => 'coleccion',
                            'modo' => 'lista',
                            'modelo' => 'k_consecuencias_regla_experta',
                            'identidad' => 'P',
                          ),
                          'criterios_dominios_resultado' => 
                          array (
                            'clase' => 'coleccion',
                            'modo' => 'lista',
                            'modelo' => 'k_criterios_dominios_resultado',
                            'identidad' => 'P',
                          ),
                          'dominios_valores_expertos' => 
                          array (
                            'clase' => 'coleccion',
                            'modo' => 'lista',
                            'modelo' => 'k_dominios_valores_expertos',
                            'identidad' => 'P',
                          ),
                          'fuentes_datos_expertas' => 
                          array (
                            'clase' => 'coleccion',
                            'modo' => 'lista',
                            'modelo' => 'k_fuentes_datos_expertas',
                            'identidad' => 'P',
                          ),
                          'mapeos_valores_fuente' => 
                          array (
                            'clase' => 'coleccion',
                            'modo' => 'lista',
                            'modelo' => 'k_mapeos_valores_fuente',
                            'identidad' => 'P',
                          ),
                          'mapeos_variables_fuente' => 
                          array (
                            'clase' => 'coleccion',
                            'modo' => 'lista',
                            'modelo' => 'k_mapeos_variables_fuente',
                            'identidad' => 'P',
                          ),
                          'nodos_semanticos' => 
                          array (
                            'clase' => 'coleccion',
                            'modo' => 'lista',
                            'modelo' => 'k_nodos_semanticos',
                            'identidad' => 'P',
                          ),
                          'reglas_expertas' => 
                          array (
                            'clase' => 'coleccion',
                            'modo' => 'lista',
                            'modelo' => 'k_reglas_expertas',
                            'identidad' => 'P',
                          ),
                          'relaciones_semanticas' => 
                          array (
                            'clase' => 'coleccion',
                            'modo' => 'lista',
                            'modelo' => 'k_relaciones_semanticas',
                            'identidad' => 'P',
                          ),
                          'valores_semanticos' => 
                          array (
                            'clase' => 'coleccion',
                            'modo' => 'lista',
                            'modelo' => 'k_valores_semanticos',
                            'identidad' => 'P',
                          ),
                          'variables_expertas' => 
                          array (
                            'clase' => 'coleccion',
                            'modo' => 'lista',
                            'modelo' => 'k_variables_expertas',
                            'identidad' => 'P',
                          ),
                        ),
                      ),
                      'tipo_regla_resultado' => 
                      array (
                        'clase' => 'escalar',
                      ),
                      'version' => 
                      array (
                        'clase' => 'escalar',
                      ),
                    ),
                  ),
                ),
              ),
              'limites' => 
              array (
                'clase' => 'objeto',
                'campos' => 
                array (
                  'persistido' => 
                  array (
                    'clase' => 'escalar',
                  ),
                  'reproduce_adquisicion_sql' => 
                  array (
                    'clase' => 'escalar',
                  ),
                  'revalida_bytes_documentales' => 
                  array (
                    'clase' => 'escalar',
                  ),
                  'revalida_permisos_historicos' => 
                  array (
                    'clase' => 'escalar',
                  ),
                ),
              ),
              'membresias_soportes' => 
              array (
                'clase' => 'coleccion',
                'modo' => 'lista',
                'modelo' => 'm017',
                'identidad' => 'Ce2d972a31d',
              ),
              'procedencia_valoraciones' => 
              array (
                'clase' => 'coleccion',
                'modo' => 'lista',
                'modelo' => 'm019',
                'identidad' => 'Cd31d2cbcf0',
              ),
              'valoraciones' => 
              array (
                'clase' => 'coleccion',
                'modo' => 'lista',
                'modelo' => 'm020',
                'identidad' => 'Cc09aa72640',
              ),
            ),
          ),
        ),
      ),
    ),
    'm002' => 
    array (
      'tabla' => 'capturas_a87703',
      'padre' => 'm001',
      'ruta' => '/instrumental/capturas/capturas',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'aplicacion_actual' => 
          array (
            'clase' => 'objeto',
            'campos' => 
            array (
              'clasificacion' => 
              array (
                'clase' => 'escalar',
              ),
              'cod_aplicacion' => 
              array (
                'clase' => 'escalar',
              ),
              'cod_aplicacion_anterior' => 
              array (
                'clase' => 'escalar',
              ),
              'cod_atencion' => 
              array (
                'clase' => 'escalar',
              ),
              'cod_documento_clinico' => 
              array (
                'clase' => 'escalar',
              ),
              'cod_instrumento' => 
              array (
                'clase' => 'escalar',
              ),
              'cod_personal' => 
              array (
                'clase' => 'escalar',
              ),
              'cod_residente' => 
              array (
                'clase' => 'escalar',
              ),
              'cod_usuario_registro' => 
              array (
                'clase' => 'escalar',
              ),
              'estado' => 
              array (
                'clase' => 'escalar',
              ),
              'fecha_hora' => 
              array (
                'clase' => 'escalar',
              ),
              'interpretacion' => 
              array (
                'clase' => 'escalar',
              ),
              'localizador_fuente' => 
              array (
                'clase' => 'escalar',
              ),
              'observacion' => 
              array (
                'clase' => 'escalar',
              ),
              'puntaje_maximo' => 
              array (
                'clase' => 'escalar',
              ),
              'puntaje_total' => 
              array (
                'clase' => 'escalar',
              ),
              'registrado_en' => 
              array (
                'clase' => 'escalar',
              ),
            ),
          ),
          'atencion_original' => 
          array (
            'clase' => 'objeto',
            'campos' => 
            array (
              'cod_atencion' => 
              array (
                'clase' => 'escalar',
              ),
              'cod_residente' => 
              array (
                'clase' => 'escalar',
              ),
            ),
          ),
          'autor' => 
          array (
            'clase' => 'escalar',
          ),
          'cod_aplicacion' => 
          array (
            'clase' => 'escalar',
          ),
          'cod_instrumento' => 
          array (
            'clase' => 'escalar',
          ),
          'cod_personal' => 
          array (
            'clase' => 'escalar',
          ),
          'cod_residente' => 
          array (
            'clase' => 'escalar',
          ),
          'codigo_instrumento' => 
          array (
            'clase' => 'escalar',
          ),
          'documento_original' => 
          array (
            'clase' => 'objeto',
            'campos' => 
            array (
              'cod_documento_clinico' => 
              array (
                'clase' => 'escalar',
              ),
              'cod_personal' => 
              array (
                'clase' => 'escalar',
              ),
              'cod_residente' => 
              array (
                'clase' => 'escalar',
              ),
              'estado' => 
              array (
                'clase' => 'escalar',
              ),
              'fecha_hora' => 
              array (
                'clase' => 'escalar',
              ),
              'hash_archivo' => 
              array (
                'clase' => 'escalar',
              ),
            ),
          ),
          'edad_en_aplicacion' => 
          array (
            'clase' => 'escalar',
          ),
          'estado_original' => 
          array (
            'clase' => 'escalar',
          ),
          'extraccion_adquirida' => 
          array (
            'clase' => 'escalar',
          ),
          'fecha_hora' => 
          array (
            'clase' => 'escalar',
          ),
          'fuente_edad' => 
          array (
            'clase' => 'objeto',
            'campos' => 
            array (
              'campo' => 
              array (
                'clase' => 'escalar',
              ),
              'fecha_aplicacion' => 
              array (
                'clase' => 'escalar',
              ),
              'fecha_nacimiento' => 
              array (
                'clase' => 'escalar',
              ),
              'registro' => 
              array (
                'clase' => 'escalar',
              ),
              'tabla' => 
              array (
                'clase' => 'escalar',
              ),
            ),
          ),
          'instrumento_actual' => 
          array (
            'clase' => 'objeto',
            'campos' => 
            array (
              'cod_instrumento' => 
              array (
                'clase' => 'escalar',
              ),
              'codigo' => 
              array (
                'clase' => 'escalar',
              ),
              'descripcion' => 
              array (
                'clase' => 'escalar',
              ),
              'estado' => 
              array (
                'clase' => 'escalar',
              ),
              'nombre' => 
              array (
                'clase' => 'escalar',
              ),
              'puntaje_maximo' => 
              array (
                'clase' => 'escalar',
              ),
              'tipo' => 
              array (
                'clase' => 'escalar',
              ),
              'version' => 
              array (
                'clase' => 'escalar',
              ),
            ),
          ),
          'motivos' => 
          array (
            'clase' => 'coleccion',
            'modo' => 'lista',
            'modelo' => 'm003',
            'identidad' => 'C03fac49475',
          ),
          'nombre_instrumento' => 
          array (
            'clase' => 'escalar',
          ),
          'perfil_captura_original' => 
          array (
            'clase' => 'objeto',
            'campos' => 
            array (
              'cod_instrumento' => 
              array (
                'clase' => 'escalar',
              ),
              'codigo_instrumento' => 
              array (
                'clase' => 'escalar',
              ),
              'id' => 
              array (
                'clase' => 'escalar',
              ),
              'identidad_instrumento' => 
              array (
                'clase' => 'objeto',
                'campos' => 
                array (
                  'edicion_protocolo' => 
                  array (
                    'clase' => 'escalar',
                  ),
                  'forma' => 
                  array (
                    'clase' => 'escalar',
                  ),
                  'idioma' => 
                  array (
                    'clase' => 'escalar',
                  ),
                  'instrumento' => 
                  array (
                    'clase' => 'escalar',
                  ),
                ),
              ),
              'metodo' => 
              array (
                'clase' => 'escalar',
              ),
              'selectores' => 
              array (
                'clase' => 'coleccion',
                'modo' => 'mapa',
                'modelo' => 'm004',
                'identidad' => 'C53b52bbfc6',
              ),
              'version_instrumento' => 
              array (
                'clase' => 'escalar',
              ),
            ),
          ),
          'preguntas_declarativas_originales' => 
          array (
            'clase' => 'coleccion',
            'modo' => 'mapa',
            'modelo' => 'm005',
            'identidad' => 'Ca070df23f8',
          ),
          'procedencia' => 
          array (
            'clase' => 'objeto',
            'campos' => 
            array (
              'cod_aplicacion_anterior' => 
              array (
                'clase' => 'escalar',
              ),
              'cod_documento_clinico' => 
              array (
                'clase' => 'escalar',
              ),
              'cod_personal_aplicador' => 
              array (
                'clase' => 'escalar',
              ),
              'cod_usuario_registro' => 
              array (
                'clase' => 'escalar',
              ),
              'estado_documento' => 
              array (
                'clase' => 'escalar',
              ),
              'localizador_fuente' => 
              array (
                'clase' => 'escalar',
              ),
              'registrado_en' => 
              array (
                'clase' => 'escalar',
              ),
            ),
          ),
          'respuestas_originales' => 
          array (
            'clase' => 'coleccion',
            'modo' => 'lista',
            'modelo' => 'm006',
            'identidad' => 'C388d96740f',
          ),
          'version_instrumento' => 
          array (
            'clase' => 'escalar',
          ),
          'vinculos_preguntas_actuales' => 
          array (
            'clase' => 'coleccion',
            'modo' => 'mapa',
            'modelo' => 'm007',
            'identidad' => 'C55341a554f',
          ),
        ),
      ),
    ),
    'm003' => 
    array (
      'tabla' => 'motivos_03fac4',
      'padre' => 'm002',
      'ruta' => '/instrumental/capturas/capturas/motivos',
      'estructura' => 
      array (
        'clase' => 'escalar',
      ),
    ),
    'm004' => 
    array (
      'tabla' => 'selectores_53b52b',
      'padre' => 'm002',
      'ruta' => '/instrumental/capturas/capturas/perfil_captura_original/selectores',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'campo' => 
          array (
            'clase' => 'escalar',
          ),
          'cod_pregunta' => 
          array (
            'clase' => 'escalar',
          ),
        ),
      ),
    ),
    'm005' => 
    array (
      'tabla' => 'preguntas_declarativas_originales_a070df',
      'padre' => 'm002',
      'ruta' => '/instrumental/capturas/capturas/preguntas_declarativas_originales',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'cod_instrumento' => 
          array (
            'clase' => 'escalar',
          ),
          'cod_pregunta' => 
          array (
            'clase' => 'escalar',
          ),
          'codigo' => 
          array (
            'clase' => 'escalar',
          ),
          'dominio' => 
          array (
            'clase' => 'escalar',
          ),
          'enunciado' => 
          array (
            'clase' => 'escalar',
          ),
          'estado' => 
          array (
            'clase' => 'escalar',
          ),
          'orden' => 
          array (
            'clase' => 'escalar',
          ),
          'puntaje_maximo' => 
          array (
            'clase' => 'escalar',
          ),
          'tipo_respuesta' => 
          array (
            'clase' => 'escalar',
          ),
        ),
      ),
    ),
    'm006' => 
    array (
      'tabla' => 'respuestas_originales_388d96',
      'padre' => 'm002',
      'ruta' => '/instrumental/capturas/capturas/respuestas_originales',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'cod_aplicacion' => 
          array (
            'clase' => 'escalar',
          ),
          'cod_opcion' => 
          array (
            'clase' => 'escalar',
          ),
          'cod_pregunta' => 
          array (
            'clase' => 'escalar',
          ),
          'cod_respuesta' => 
          array (
            'clase' => 'escalar',
          ),
          'codigo_pregunta' => 
          array (
            'clase' => 'escalar',
          ),
          'coherente' => 
          array (
            'clase' => 'escalar',
          ),
          'dominio_original' => 
          array (
            'clase' => 'escalar',
          ),
          'estado_pregunta_actual' => 
          array (
            'clase' => 'escalar',
          ),
          'observacion' => 
          array (
            'clase' => 'escalar',
          ),
          'opcion_actual' => 
          array (
            'clase' => 'objeto',
            'campos' => 
            array (
              'cod_opcion' => 
              array (
                'clase' => 'escalar',
              ),
              'cod_pregunta' => 
              array (
                'clase' => 'escalar',
              ),
              'estado' => 
              array (
                'clase' => 'escalar',
              ),
              'nombre' => 
              array (
                'clase' => 'escalar',
              ),
              'orden' => 
              array (
                'clase' => 'escalar',
              ),
              'puntaje' => 
              array (
                'clase' => 'escalar',
              ),
              'valor' => 
              array (
                'clase' => 'escalar',
              ),
            ),
          ),
          'pregunta' => 
          array (
            'clase' => 'escalar',
          ),
          'pregunta_actual' => 
          array (
            'clase' => 'objeto',
            'campos' => 
            array (
              'cod_instrumento' => 
              array (
                'clase' => 'escalar',
              ),
              'cod_pregunta' => 
              array (
                'clase' => 'escalar',
              ),
              'codigo' => 
              array (
                'clase' => 'escalar',
              ),
              'dominio' => 
              array (
                'clase' => 'escalar',
              ),
              'enunciado' => 
              array (
                'clase' => 'escalar',
              ),
              'estado' => 
              array (
                'clase' => 'escalar',
              ),
              'orden' => 
              array (
                'clase' => 'escalar',
              ),
              'puntaje_maximo' => 
              array (
                'clase' => 'escalar',
              ),
              'tipo_respuesta' => 
              array (
                'clase' => 'escalar',
              ),
            ),
          ),
          'puntaje' => 
          array (
            'clase' => 'escalar',
          ),
          'tipo_respuesta' => 
          array (
            'clase' => 'escalar',
          ),
          'valor_logico' => 
          array (
            'clase' => 'escalar',
          ),
          'valor_numero' => 
          array (
            'clase' => 'escalar',
          ),
          'valor_texto' => 
          array (
            'clase' => 'escalar',
          ),
        ),
      ),
    ),
    'm007' => 
    array (
      'tabla' => 'vinculos_preguntas_actuales_55341a',
      'padre' => 'm002',
      'ruta' => '/instrumental/capturas/capturas/vinculos_preguntas_actuales',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'pertenece_al_instrumento' => 
          array (
            'clase' => 'escalar',
          ),
          'pregunta_actual' => 
          array (
            'clase' => 'objeto',
            'campos' => 
            array (
              'cod_instrumento' => 
              array (
                'clase' => 'escalar',
              ),
              'cod_pregunta' => 
              array (
                'clase' => 'escalar',
              ),
              'codigo' => 
              array (
                'clase' => 'escalar',
              ),
              'dominio' => 
              array (
                'clase' => 'escalar',
              ),
              'enunciado' => 
              array (
                'clase' => 'escalar',
              ),
              'estado' => 
              array (
                'clase' => 'escalar',
              ),
              'orden' => 
              array (
                'clase' => 'escalar',
              ),
              'puntaje_maximo' => 
              array (
                'clase' => 'escalar',
              ),
              'tipo_respuesta' => 
              array (
                'clase' => 'escalar',
              ),
            ),
          ),
          'selector' => 
          array (
            'clase' => 'objeto',
            'campos' => 
            array (
              'campo' => 
              array (
                'clase' => 'escalar',
              ),
              'cod_pregunta' => 
              array (
                'clase' => 'escalar',
              ),
            ),
          ),
          'tiene_respuesta' => 
          array (
            'clase' => 'escalar',
          ),
        ),
      ),
    ),
    'm008' => 
    array (
      'tabla' => 'contratos_relaciones_4f168a',
      'padre' => 'm001',
      'ruta' => '/instrumental/conocimiento/contratos_relaciones',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'habilita_participacion' => 
          array (
            'clase' => 'escalar',
          ),
          'modalidades' => 
          array (
            'clase' => 'coleccion',
            'modo' => 'lista',
            'modelo' => 'm009',
            'identidad' => 'C9e85749841',
          ),
          'tipo_destino' => 
          array (
            'clase' => 'escalar',
          ),
          'tipo_origen' => 
          array (
            'clase' => 'escalar',
          ),
          'tipo_relacion' => 
          array (
            'clase' => 'escalar',
          ),
        ),
      ),
    ),
    'm009' => 
    array (
      'tabla' => 'modalidades_9e8574',
      'padre' => 'm008',
      'ruta' => '/instrumental/conocimiento/contratos_relaciones/modalidades',
      'estructura' => 
      array (
        'clase' => 'escalar',
      ),
    ),
    'k_condiciones_regla_experta' => 
    array (
      'tabla' => 'copia_condiciones_regla_experta',
      'padre' => 'm001',
      'ruta' => '/instrumental/conocimiento/tablas/condiciones_regla_experta',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'cod_condicion_regla' => 
          array (
            'clase' => 'escalar',
          ),
          'cod_regla_experta' => 
          array (
            'clase' => 'escalar',
          ),
          'cod_valor_semantico' => 
          array (
            'clase' => 'escalar',
          ),
          'cod_variable_experta' => 
          array (
            'clase' => 'escalar',
          ),
        ),
      ),
    ),
    'k_consecuencias_regla_experta' => 
    array (
      'tabla' => 'copia_consecuencias_regla_experta',
      'padre' => 'm001',
      'ruta' => '/instrumental/conocimiento/tablas/consecuencias_regla_experta',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'cod_consecuencia_regla' => 
          array (
            'clase' => 'escalar',
          ),
          'cod_criterio_dominio_resultado' => 
          array (
            'clase' => 'escalar',
          ),
          'cod_regla_experta' => 
          array (
            'clase' => 'escalar',
          ),
          'cod_valor_semantico' => 
          array (
            'clase' => 'escalar',
          ),
        ),
      ),
    ),
    'k_criterios_dominios_resultado' => 
    array (
      'tabla' => 'copia_criterios_dominios_resultado',
      'padre' => 'm001',
      'ruta' => '/instrumental/conocimiento/tablas/criterios_dominios_resultado',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'cod_criterio_dominio_resultado' => 
          array (
            'clase' => 'escalar',
          ),
          'cod_dominio_valores' => 
          array (
            'clase' => 'escalar',
          ),
          'cod_nodo_criterio' => 
          array (
            'clase' => 'escalar',
          ),
          'cod_usuario_creacion' => 
          array (
            'clase' => 'escalar',
          ),
          'estado' => 
          array (
            'clase' => 'escalar',
          ),
          'fecha_hora_creacion' => 
          array (
            'clase' => 'escalar',
          ),
          'observacion' => 
          array (
            'clase' => 'escalar',
          ),
        ),
      ),
    ),
    'k_dominios_valores_expertos' => 
    array (
      'tabla' => 'copia_dominios_valores_expertos',
      'padre' => 'm001',
      'ruta' => '/instrumental/conocimiento/tablas/dominios_valores_expertos',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'cod_dominio_valores' => 
          array (
            'clase' => 'escalar',
          ),
          'cod_usuario_creacion' => 
          array (
            'clase' => 'escalar',
          ),
          'cod_version_modelo' => 
          array (
            'clase' => 'escalar',
          ),
          'codigo_dominio' => 
          array (
            'clase' => 'escalar',
          ),
          'descripcion' => 
          array (
            'clase' => 'escalar',
          ),
          'estado' => 
          array (
            'clase' => 'escalar',
          ),
          'fecha_hora_creacion' => 
          array (
            'clase' => 'escalar',
          ),
          'nombre' => 
          array (
            'clase' => 'escalar',
          ),
          'observacion' => 
          array (
            'clase' => 'escalar',
          ),
          'tipo_dominio' => 
          array (
            'clase' => 'escalar',
          ),
        ),
      ),
    ),
    'k_fuentes_datos_expertas' => 
    array (
      'tabla' => 'copia_fuentes_datos_expertas',
      'padre' => 'm001',
      'ruta' => '/instrumental/conocimiento/tablas/fuentes_datos_expertas',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'campo_estado' => 
          array (
            'clase' => 'escalar',
          ),
          'campo_personal' => 
          array (
            'clase' => 'escalar',
          ),
          'campo_pk_raiz' => 
          array (
            'clase' => 'escalar',
          ),
          'campo_residente' => 
          array (
            'clase' => 'escalar',
          ),
          'campo_temporal' => 
          array (
            'clase' => 'escalar',
          ),
          'clave_adaptador' => 
          array (
            'clase' => 'escalar',
          ),
          'cod_fuente_dato_experta' => 
          array (
            'clase' => 'escalar',
          ),
          'cod_usuario_creacion' => 
          array (
            'clase' => 'escalar',
          ),
          'cod_version_modelo' => 
          array (
            'clase' => 'escalar',
          ),
          'codigo_fuente' => 
          array (
            'clase' => 'escalar',
          ),
          'estado' => 
          array (
            'clase' => 'escalar',
          ),
          'fecha_hora_creacion' => 
          array (
            'clase' => 'escalar',
          ),
          'nombre' => 
          array (
            'clase' => 'escalar',
          ),
          'observacion' => 
          array (
            'clase' => 'escalar',
          ),
          'tabla_raiz' => 
          array (
            'clase' => 'escalar',
          ),
          'tipo_fuente' => 
          array (
            'clase' => 'escalar',
          ),
        ),
      ),
    ),
    'k_mapeos_valores_fuente' => 
    array (
      'tabla' => 'copia_mapeos_valores_fuente',
      'padre' => 'm001',
      'ruta' => '/instrumental/conocimiento/tablas/mapeos_valores_fuente',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'cod_mapeo_valor_fuente' => 
          array (
            'clase' => 'escalar',
          ),
          'cod_mapeo_variable_fuente' => 
          array (
            'clase' => 'escalar',
          ),
          'cod_usuario_creacion' => 
          array (
            'clase' => 'escalar',
          ),
          'cod_valor_semantico' => 
          array (
            'clase' => 'escalar',
          ),
          'estado' => 
          array (
            'clase' => 'escalar',
          ),
          'estado_aprobacion' => 
          array (
            'clase' => 'escalar',
          ),
          'fecha_hora_creacion' => 
          array (
            'clase' => 'escalar',
          ),
          'fecha_hora_vigencia_desde' => 
          array (
            'clase' => 'escalar',
          ),
          'fecha_hora_vigencia_hasta' => 
          array (
            'clase' => 'escalar',
          ),
          'observacion' => 
          array (
            'clase' => 'escalar',
          ),
          'tipo_valor_fuente' => 
          array (
            'clase' => 'escalar',
          ),
          'valor_fuente_exacto' => 
          array (
            'clase' => 'escalar',
          ),
        ),
      ),
    ),
    'k_mapeos_variables_fuente' => 
    array (
      'tabla' => 'copia_mapeos_variables_fuente',
      'padre' => 'm001',
      'ruta' => '/instrumental/conocimiento/tablas/mapeos_variables_fuente',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'campo_valor' => 
          array (
            'clase' => 'escalar',
          ),
          'clave_selector' => 
          array (
            'clase' => 'escalar',
          ),
          'cod_fuente_dato_experta' => 
          array (
            'clase' => 'escalar',
          ),
          'cod_mapeo_variable_fuente' => 
          array (
            'clase' => 'escalar',
          ),
          'cod_usuario_creacion' => 
          array (
            'clase' => 'escalar',
          ),
          'cod_variable_experta' => 
          array (
            'clase' => 'escalar',
          ),
          'cod_version_modelo' => 
          array (
            'clase' => 'escalar',
          ),
          'estado' => 
          array (
            'clase' => 'escalar',
          ),
          'fecha_hora_creacion' => 
          array (
            'clase' => 'escalar',
          ),
          'observacion' => 
          array (
            'clase' => 'escalar',
          ),
          'tipo_extraccion' => 
          array (
            'clase' => 'escalar',
          ),
        ),
      ),
    ),
    'k_nodos_semanticos' => 
    array (
      'tabla' => 'copia_nodos_semanticos',
      'padre' => 'm001',
      'ruta' => '/instrumental/conocimiento/tablas/nodos_semanticos',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'cod_nodo_semantico' => 
          array (
            'clase' => 'escalar',
          ),
          'cod_usuario_creacion' => 
          array (
            'clase' => 'escalar',
          ),
          'cod_version_modelo' => 
          array (
            'clase' => 'escalar',
          ),
          'codigo_semantico' => 
          array (
            'clase' => 'escalar',
          ),
          'definicion' => 
          array (
            'clase' => 'escalar',
          ),
          'estado' => 
          array (
            'clase' => 'escalar',
          ),
          'fecha_hora_creacion' => 
          array (
            'clase' => 'escalar',
          ),
          'nombre' => 
          array (
            'clase' => 'escalar',
          ),
          'observacion' => 
          array (
            'clase' => 'escalar',
          ),
          'tipo_nodo' => 
          array (
            'clase' => 'escalar',
          ),
        ),
      ),
    ),
    'k_reglas_expertas' => 
    array (
      'tabla' => 'copia_reglas_expertas',
      'padre' => 'm001',
      'ruta' => '/instrumental/conocimiento/tablas/reglas_expertas',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'cod_regla_anterior' => 
          array (
            'clase' => 'escalar',
          ),
          'cod_regla_experta' => 
          array (
            'clase' => 'escalar',
          ),
          'cod_usuario_creacion' => 
          array (
            'clase' => 'escalar',
          ),
          'cod_usuario_revision' => 
          array (
            'clase' => 'escalar',
          ),
          'cod_version_modelo' => 
          array (
            'clase' => 'escalar',
          ),
          'codigo_regla' => 
          array (
            'clase' => 'escalar',
          ),
          'descripcion' => 
          array (
            'clase' => 'escalar',
          ),
          'estado' => 
          array (
            'clase' => 'escalar',
          ),
          'estado_revision' => 
          array (
            'clase' => 'escalar',
          ),
          'fecha_hora_creacion' => 
          array (
            'clase' => 'escalar',
          ),
          'fecha_hora_revision' => 
          array (
            'clase' => 'escalar',
          ),
          'justificacion_revision' => 
          array (
            'clase' => 'escalar',
          ),
          'nombre' => 
          array (
            'clase' => 'escalar',
          ),
          'observacion' => 
          array (
            'clase' => 'escalar',
          ),
          'tipo_regla' => 
          array (
            'clase' => 'escalar',
          ),
        ),
      ),
    ),
    'k_relaciones_semanticas' => 
    array (
      'tabla' => 'copia_relaciones_semanticas',
      'padre' => 'm001',
      'ruta' => '/instrumental/conocimiento/tablas/relaciones_semanticas',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'cod_nodo_destino' => 
          array (
            'clase' => 'escalar',
          ),
          'cod_nodo_origen' => 
          array (
            'clase' => 'escalar',
          ),
          'cod_relacion_semantica' => 
          array (
            'clase' => 'escalar',
          ),
          'cod_usuario_creacion' => 
          array (
            'clase' => 'escalar',
          ),
          'cod_version_modelo' => 
          array (
            'clase' => 'escalar',
          ),
          'estado' => 
          array (
            'clase' => 'escalar',
          ),
          'fecha_hora_creacion' => 
          array (
            'clase' => 'escalar',
          ),
          'modalidad_relacion' => 
          array (
            'clase' => 'escalar',
          ),
          'observacion' => 
          array (
            'clase' => 'escalar',
          ),
          'significado' => 
          array (
            'clase' => 'escalar',
          ),
          'tipo_relacion' => 
          array (
            'clase' => 'escalar',
          ),
        ),
      ),
    ),
    'k_valores_semanticos' => 
    array (
      'tabla' => 'copia_valores_semanticos',
      'padre' => 'm001',
      'ruta' => '/instrumental/conocimiento/tablas/valores_semanticos',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'cod_dominio_valores' => 
          array (
            'clase' => 'escalar',
          ),
          'cod_usuario_creacion' => 
          array (
            'clase' => 'escalar',
          ),
          'cod_valor_semantico' => 
          array (
            'clase' => 'escalar',
          ),
          'codigo_valor' => 
          array (
            'clase' => 'escalar',
          ),
          'definicion_semantica' => 
          array (
            'clase' => 'escalar',
          ),
          'estado' => 
          array (
            'clase' => 'escalar',
          ),
          'estado_aprobacion' => 
          array (
            'clase' => 'escalar',
          ),
          'fecha_hora_creacion' => 
          array (
            'clase' => 'escalar',
          ),
          'nombre' => 
          array (
            'clase' => 'escalar',
          ),
          'observacion' => 
          array (
            'clase' => 'escalar',
          ),
        ),
      ),
    ),
    'k_variables_expertas' => 
    array (
      'tabla' => 'copia_variables_expertas',
      'padre' => 'm001',
      'ruta' => '/instrumental/conocimiento/tablas/variables_expertas',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'cod_dominio_valores' => 
          array (
            'clase' => 'escalar',
          ),
          'cod_nodo_propietario_primario' => 
          array (
            'clase' => 'escalar',
          ),
          'cod_nodo_semantico' => 
          array (
            'clase' => 'escalar',
          ),
          'cod_usuario_creacion' => 
          array (
            'clase' => 'escalar',
          ),
          'cod_variable_experta' => 
          array (
            'clase' => 'escalar',
          ),
          'cod_version_modelo' => 
          array (
            'clase' => 'escalar',
          ),
          'estado' => 
          array (
            'clase' => 'escalar',
          ),
          'fecha_hora_creacion' => 
          array (
            'clase' => 'escalar',
          ),
          'observacion' => 
          array (
            'clase' => 'escalar',
          ),
          'papel_inferencial' => 
          array (
            'clase' => 'escalar',
          ),
          'tipo_semantico' => 
          array (
            'clase' => 'escalar',
          ),
        ),
      ),
    ),
    'm010' => 
    array (
      'tabla' => 'vinculos_7874dd',
      'padre' => 'm001',
      'ruta' => '/instrumental/vinculos',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'campo' => 
          array (
            'clase' => 'escalar',
          ),
          'cod_instrumento' => 
          array (
            'clase' => 'escalar',
          ),
          'cod_pregunta' => 
          array (
            'clase' => 'escalar',
          ),
          'codigo_instrumento' => 
          array (
            'clase' => 'escalar',
          ),
          'componente' => 
          array (
            'clase' => 'escalar',
          ),
          'perfil_interpretativo' => 
          array (
            'clase' => 'objeto',
            'campos' => 
            array (
              'codigo' => 
              array (
                'clase' => 'escalar',
              ),
              'componente' => 
              array (
                'clase' => 'escalar',
              ),
              'fuente' => 
              array (
                'clase' => 'escalar',
              ),
              'instrumento' => 
              array (
                'clase' => 'objeto',
                'campos' => 
                array (
                  'codigo' => 
                  array (
                    'clase' => 'escalar',
                  ),
                  'version' => 
                  array (
                    'clase' => 'escalar',
                  ),
                ),
              ),
              'mediciones_requeridas' => 
              array (
                'clase' => 'coleccion',
                'modo' => 'mapa',
                'modelo' => 'm011',
                'identidad' => 'C66a9c79cf7',
              ),
              'metodo' => 
              array (
                'clase' => 'escalar',
              ),
              'predicados' => 
              array (
                'clase' => 'coleccion',
                'modo' => 'lista',
                'modelo' => 'm012',
                'identidad' => 'C94c27667c0',
              ),
              'version' => 
              array (
                'clase' => 'escalar',
              ),
            ),
          ),
          'version_instrumento' => 
          array (
            'clase' => 'escalar',
          ),
        ),
      ),
    ),
    'm011' => 
    array (
      'tabla' => 'mediciones_requeridas_66a9c7',
      'padre' => 'm010',
      'ruta' => '/instrumental/vinculos/perfil_interpretativo/mediciones_requeridas',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'campo' => 
          array (
            'clase' => 'escalar',
          ),
          'cod_pregunta' => 
          array (
            'clase' => 'escalar',
          ),
        ),
      ),
    ),
    'm012' => 
    array (
      'tabla' => 'predicados_94c276',
      'padre' => 'm010',
      'ruta' => '/instrumental/vinculos/perfil_interpretativo/predicados',
      'estructura' => 
      array (
        'clase' => 'escalar',
      ),
    ),
    'm013' => 
    array (
      'tabla' => 'exclusiones_efd2a5',
      'padre' => 'm001',
      'ruta' => '/petersen/adquisicion/exclusiones',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'elemento' => 
          array (
            'clase' => 'escalar',
          ),
          'motivo' => 
          array (
            'clase' => 'escalar',
          ),
          'valoracion' => 
          array (
            'clase' => 'escalar',
          ),
        ),
      ),
    ),
    'm014' => 
    array (
      'tabla' => 'constancias_documentales_7eea80',
      'padre' => 'm001',
      'ruta' => '/petersen/constancias_documentales',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'cod_documento_clinico' => 
          array (
            'clase' => 'escalar',
          ),
          'cod_personal' => 
          array (
            'clase' => 'escalar',
          ),
          'cod_residente' => 
          array (
            'clase' => 'escalar',
          ),
          'estado' => 
          array (
            'clase' => 'escalar',
          ),
          'fecha_hora' => 
          array (
            'clase' => 'escalar',
          ),
          'hash_archivo' => 
          array (
            'clase' => 'escalar',
          ),
          'localizador_fuente' => 
          array (
            'clase' => 'escalar',
          ),
          'papel' => 
          array (
            'clase' => 'escalar',
          ),
          'valoracion' => 
          array (
            'clase' => 'escalar',
          ),
          'verificado' => 
          array (
            'clase' => 'escalar',
          ),
        ),
      ),
    ),
    'm015' => 
    array (
      'tabla' => 'contratos_relaciones_91c396',
      'padre' => 'm001',
      'ruta' => '/petersen/gobierno/conocimiento/contratos_relaciones',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'habilita_participacion' => 
          array (
            'clase' => 'escalar',
          ),
          'modalidades' => 
          array (
            'clase' => 'coleccion',
            'modo' => 'lista',
            'modelo' => 'm016',
            'identidad' => 'Cdb8280ab29',
          ),
          'tipo_destino' => 
          array (
            'clase' => 'escalar',
          ),
          'tipo_origen' => 
          array (
            'clase' => 'escalar',
          ),
          'tipo_relacion' => 
          array (
            'clase' => 'escalar',
          ),
        ),
      ),
    ),
    'm016' => 
    array (
      'tabla' => 'modalidades_db8280',
      'padre' => 'm015',
      'ruta' => '/petersen/gobierno/conocimiento/contratos_relaciones/modalidades',
      'estructura' => 
      array (
        'clase' => 'escalar',
      ),
    ),
    'm017' => 
    array (
      'tabla' => 'membresias_soportes_e2d972',
      'padre' => 'm001',
      'ruta' => '/petersen/membresias_soportes',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'soportes' => 
          array (
            'clase' => 'coleccion',
            'modo' => 'lista',
            'modelo' => 'm018',
            'identidad' => 'C4607c4e418',
          ),
          'valoracion' => 
          array (
            'clase' => 'escalar',
          ),
        ),
      ),
    ),
    'm018' => 
    array (
      'tabla' => 'soportes_4607c4',
      'padre' => 'm017',
      'ruta' => '/petersen/membresias_soportes/soportes',
      'estructura' => 
      array (
        'clase' => 'escalar',
      ),
    ),
    'm019' => 
    array (
      'tabla' => 'procedencia_valoraciones_d31d2c',
      'padre' => 'm001',
      'ruta' => '/petersen/procedencia_valoraciones',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'cod_documento_clinico' => 
          array (
            'clase' => 'escalar',
          ),
          'cod_personal' => 
          array (
            'clase' => 'escalar',
          ),
          'cod_residente' => 
          array (
            'clase' => 'escalar',
          ),
          'cod_usuario_registro' => 
          array (
            'clase' => 'escalar',
          ),
          'cod_valoracion_anterior' => 
          array (
            'clase' => 'escalar',
          ),
          'cod_valoracion_petersen' => 
          array (
            'clase' => 'escalar',
          ),
          'elemento' => 
          array (
            'clase' => 'escalar',
          ),
          'fecha_hora' => 
          array (
            'clase' => 'escalar',
          ),
          'localizador_fuente' => 
          array (
            'clase' => 'escalar',
          ),
          'registrado_en' => 
          array (
            'clase' => 'escalar',
          ),
          'tipo_registro' => 
          array (
            'clase' => 'escalar',
          ),
        ),
      ),
    ),
    'm020' => 
    array (
      'tabla' => 'valoraciones_c09aa7',
      'padre' => 'm001',
      'ruta' => '/petersen/valoraciones',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'comprobaciones' => 
          array (
            'clase' => 'objeto',
            'campos' => 
            array (
              'alcance_dominio' => 
              array (
                'clase' => 'escalar',
              ),
              'atribucion_revisada' => 
              array (
                'clase' => 'escalar',
              ),
              'cambio_respecto_previo' => 
              array (
                'clase' => 'escalar',
              ),
              'condiciones_revisadas' => 
              array (
                'clase' => 'escalar',
              ),
              'contexto_interpretado' => 
              array (
                'clase' => 'escalar',
              ),
              'corroboracion_independiente' => 
              array (
                'clase' => 'escalar',
              ),
              'exclusiones_revisadas' => 
              array (
                'clase' => 'escalar',
              ),
              'factores_pertinentes_revisados' => 
              array (
                'clase' => 'escalar',
              ),
              'impedimento_no_resuelto' => 
              array (
                'clase' => 'escalar',
              ),
              'oportunidad_adecuada' => 
              array (
                'clase' => 'escalar',
              ),
              'revision_explicita' => 
              array (
                'clase' => 'escalar',
              ),
              'revision_integrada' => 
              array (
                'clase' => 'escalar',
              ),
              'suficiencia_conjunto' => 
              array (
                'clase' => 'escalar',
              ),
            ),
          ),
          'dominios_revisados' => 
          array (
            'clase' => 'coleccion',
            'modo' => 'mapa',
            'modelo' => 'm021',
            'identidad' => 'Caf7f6dc239',
          ),
          'elemento' => 
          array (
            'clase' => 'escalar',
          ),
          'estado' => 
          array (
            'clase' => 'escalar',
          ),
          'justificacion' => 
          array (
            'clase' => 'escalar',
          ),
          'origen' => 
          array (
            'clase' => 'objeto',
            'campos' => 
            array (
              'autor' => 
              array (
                'clase' => 'escalar',
              ),
              'datos_originales' => 
              array (
                'clase' => 'objeto',
                'campos' => 
                array (
                ),
              ),
              'episodio' => 
              array (
                'clase' => 'escalar',
              ),
              'fecha' => 
              array (
                'clase' => 'escalar',
              ),
              'registro' => 
              array (
                'clase' => 'escalar',
              ),
              'residente' => 
              array (
                'clase' => 'escalar',
              ),
              'tabla' => 
              array (
                'clase' => 'escalar',
              ),
              'tipo' => 
              array (
                'clase' => 'escalar',
              ),
              'vigente' => 
              array (
                'clase' => 'escalar',
              ),
            ),
          ),
          'soportes' => 
          array (
            'clase' => 'coleccion',
            'modo' => 'lista',
            'modelo' => 'm022',
            'identidad' => 'C027418f6cc',
          ),
        ),
      ),
    ),
    'm021' => 
    array (
      'tabla' => 'dominios_revisados_af7f6d',
      'padre' => 'm020',
      'ruta' => '/petersen/valoraciones/dominios_revisados',
      'estructura' => 
      array (
        'clase' => 'escalar',
      ),
    ),
    'm022' => 
    array (
      'tabla' => 'soportes_027418',
      'padre' => 'm020',
      'ruta' => '/petersen/valoraciones/soportes',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'autor' => 
          array (
            'clase' => 'escalar',
          ),
          'datos_originales' => 
          array (
            'clase' => 'objeto',
            'campos' => 
            array (
              'actividad' => 
              array (
                'clase' => 'escalar',
              ),
              'captura_original' => 
              array (
                'clase' => 'objeto',
                'campos' => 
                array (
                  'aplicacion_actual' => 
                  array (
                    'clase' => 'objeto',
                    'campos' => 
                    array (
                      'clasificacion' => 
                      array (
                        'clase' => 'escalar',
                      ),
                      'cod_aplicacion' => 
                      array (
                        'clase' => 'escalar',
                      ),
                      'cod_aplicacion_anterior' => 
                      array (
                        'clase' => 'escalar',
                      ),
                      'cod_atencion' => 
                      array (
                        'clase' => 'escalar',
                      ),
                      'cod_documento_clinico' => 
                      array (
                        'clase' => 'escalar',
                      ),
                      'cod_instrumento' => 
                      array (
                        'clase' => 'escalar',
                      ),
                      'cod_personal' => 
                      array (
                        'clase' => 'escalar',
                      ),
                      'cod_residente' => 
                      array (
                        'clase' => 'escalar',
                      ),
                      'cod_usuario_registro' => 
                      array (
                        'clase' => 'escalar',
                      ),
                      'estado' => 
                      array (
                        'clase' => 'escalar',
                      ),
                      'fecha_hora' => 
                      array (
                        'clase' => 'escalar',
                      ),
                      'interpretacion' => 
                      array (
                        'clase' => 'escalar',
                      ),
                      'localizador_fuente' => 
                      array (
                        'clase' => 'escalar',
                      ),
                      'observacion' => 
                      array (
                        'clase' => 'escalar',
                      ),
                      'puntaje_maximo' => 
                      array (
                        'clase' => 'escalar',
                      ),
                      'puntaje_total' => 
                      array (
                        'clase' => 'escalar',
                      ),
                      'registrado_en' => 
                      array (
                        'clase' => 'escalar',
                      ),
                    ),
                  ),
                  'atencion_original' => 
                  array (
                    'clase' => 'objeto',
                    'campos' => 
                    array (
                      'cod_atencion' => 
                      array (
                        'clase' => 'escalar',
                      ),
                      'cod_residente' => 
                      array (
                        'clase' => 'escalar',
                      ),
                    ),
                  ),
                  'autor' => 
                  array (
                    'clase' => 'escalar',
                  ),
                  'cod_aplicacion' => 
                  array (
                    'clase' => 'escalar',
                  ),
                  'cod_instrumento' => 
                  array (
                    'clase' => 'escalar',
                  ),
                  'cod_personal' => 
                  array (
                    'clase' => 'escalar',
                  ),
                  'cod_residente' => 
                  array (
                    'clase' => 'escalar',
                  ),
                  'codigo_instrumento' => 
                  array (
                    'clase' => 'escalar',
                  ),
                  'documento_original' => 
                  array (
                    'clase' => 'objeto',
                    'campos' => 
                    array (
                      'cod_documento_clinico' => 
                      array (
                        'clase' => 'escalar',
                      ),
                      'cod_personal' => 
                      array (
                        'clase' => 'escalar',
                      ),
                      'cod_residente' => 
                      array (
                        'clase' => 'escalar',
                      ),
                      'estado' => 
                      array (
                        'clase' => 'escalar',
                      ),
                      'fecha_hora' => 
                      array (
                        'clase' => 'escalar',
                      ),
                      'hash_archivo' => 
                      array (
                        'clase' => 'escalar',
                      ),
                    ),
                  ),
                  'edad_en_aplicacion' => 
                  array (
                    'clase' => 'escalar',
                  ),
                  'estado_original' => 
                  array (
                    'clase' => 'escalar',
                  ),
                  'extraccion_adquirida' => 
                  array (
                    'clase' => 'escalar',
                  ),
                  'fecha_hora' => 
                  array (
                    'clase' => 'escalar',
                  ),
                  'fuente_edad' => 
                  array (
                    'clase' => 'objeto',
                    'campos' => 
                    array (
                      'campo' => 
                      array (
                        'clase' => 'escalar',
                      ),
                      'fecha_aplicacion' => 
                      array (
                        'clase' => 'escalar',
                      ),
                      'fecha_nacimiento' => 
                      array (
                        'clase' => 'escalar',
                      ),
                      'registro' => 
                      array (
                        'clase' => 'escalar',
                      ),
                      'tabla' => 
                      array (
                        'clase' => 'escalar',
                      ),
                    ),
                  ),
                  'instrumento_actual' => 
                  array (
                    'clase' => 'objeto',
                    'campos' => 
                    array (
                      'cod_instrumento' => 
                      array (
                        'clase' => 'escalar',
                      ),
                      'codigo' => 
                      array (
                        'clase' => 'escalar',
                      ),
                      'descripcion' => 
                      array (
                        'clase' => 'escalar',
                      ),
                      'estado' => 
                      array (
                        'clase' => 'escalar',
                      ),
                      'nombre' => 
                      array (
                        'clase' => 'escalar',
                      ),
                      'puntaje_maximo' => 
                      array (
                        'clase' => 'escalar',
                      ),
                      'tipo' => 
                      array (
                        'clase' => 'escalar',
                      ),
                      'version' => 
                      array (
                        'clase' => 'escalar',
                      ),
                    ),
                  ),
                  'motivos' => 
                  array (
                    'clase' => 'coleccion',
                    'modo' => 'lista',
                    'modelo' => 'm023',
                    'identidad' => 'Ca33175a241',
                  ),
                  'nombre_instrumento' => 
                  array (
                    'clase' => 'escalar',
                  ),
                  'perfil_captura_original' => 
                  array (
                    'clase' => 'objeto',
                    'campos' => 
                    array (
                      'cod_instrumento' => 
                      array (
                        'clase' => 'escalar',
                      ),
                      'codigo_instrumento' => 
                      array (
                        'clase' => 'escalar',
                      ),
                      'id' => 
                      array (
                        'clase' => 'escalar',
                      ),
                      'identidad_instrumento' => 
                      array (
                        'clase' => 'objeto',
                        'campos' => 
                        array (
                          'edicion_protocolo' => 
                          array (
                            'clase' => 'escalar',
                          ),
                          'forma' => 
                          array (
                            'clase' => 'escalar',
                          ),
                          'idioma' => 
                          array (
                            'clase' => 'escalar',
                          ),
                          'instrumento' => 
                          array (
                            'clase' => 'escalar',
                          ),
                        ),
                      ),
                      'metodo' => 
                      array (
                        'clase' => 'escalar',
                      ),
                      'selectores' => 
                      array (
                        'clase' => 'coleccion',
                        'modo' => 'mapa',
                        'modelo' => 'm024',
                        'identidad' => 'Cc9e61d6130',
                      ),
                      'version_instrumento' => 
                      array (
                        'clase' => 'escalar',
                      ),
                    ),
                  ),
                  'preguntas_declarativas_originales' => 
                  array (
                    'clase' => 'coleccion',
                    'modo' => 'mapa',
                    'modelo' => 'm025',
                    'identidad' => 'C52d856d563',
                  ),
                  'procedencia' => 
                  array (
                    'clase' => 'objeto',
                    'campos' => 
                    array (
                      'cod_aplicacion_anterior' => 
                      array (
                        'clase' => 'escalar',
                      ),
                      'cod_documento_clinico' => 
                      array (
                        'clase' => 'escalar',
                      ),
                      'cod_personal_aplicador' => 
                      array (
                        'clase' => 'escalar',
                      ),
                      'cod_usuario_registro' => 
                      array (
                        'clase' => 'escalar',
                      ),
                      'estado_documento' => 
                      array (
                        'clase' => 'escalar',
                      ),
                      'localizador_fuente' => 
                      array (
                        'clase' => 'escalar',
                      ),
                      'registrado_en' => 
                      array (
                        'clase' => 'escalar',
                      ),
                    ),
                  ),
                  'respuestas_originales' => 
                  array (
                    'clase' => 'coleccion',
                    'modo' => 'lista',
                    'modelo' => 'm026',
                    'identidad' => 'C014dd1988b',
                  ),
                  'version_instrumento' => 
                  array (
                    'clase' => 'escalar',
                  ),
                  'vinculos_preguntas_actuales' => 
                  array (
                    'clase' => 'coleccion',
                    'modo' => 'mapa',
                    'modelo' => 'm027',
                    'identidad' => 'C7b4dbb2388',
                  ),
                ),
              ),
              'causa_ayuda' => 
              array (
                'clase' => 'escalar',
              ),
              'descripcion' => 
              array (
                'clase' => 'escalar',
              ),
              'desempeno' => 
              array (
                'clase' => 'escalar',
              ),
              'dominio' => 
              array (
                'clase' => 'escalar',
              ),
              'fuente' => 
              array (
                'clase' => 'escalar',
              ),
              'nivel_previo' => 
              array (
                'clase' => 'escalar',
              ),
              'oportunidad' => 
              array (
                'clase' => 'escalar',
              ),
              'tipo_ayuda' => 
              array (
                'clase' => 'escalar',
              ),
            ),
          ),
          'episodio' => 
          array (
            'clase' => 'escalar',
          ),
          'fecha' => 
          array (
            'clase' => 'escalar',
          ),
          'registro' => 
          array (
            'clase' => 'escalar',
          ),
          'residente' => 
          array (
            'clase' => 'escalar',
          ),
          'tabla' => 
          array (
            'clase' => 'escalar',
          ),
          'tipo' => 
          array (
            'clase' => 'escalar',
          ),
          'vigente' => 
          array (
            'clase' => 'escalar',
          ),
        ),
      ),
    ),
    'm023' => 
    array (
      'tabla' => 'motivos_a33175',
      'padre' => 'm022',
      'ruta' => '/petersen/valoraciones/soportes/datos_originales/captura_original/motivos',
      'estructura' => 
      array (
        'clase' => 'escalar',
      ),
    ),
    'm024' => 
    array (
      'tabla' => 'selectores_c9e61d',
      'padre' => 'm022',
      'ruta' => '/petersen/valoraciones/soportes/datos_originales/captura_original/perfil_captura_original/selectores',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'campo' => 
          array (
            'clase' => 'escalar',
          ),
          'cod_pregunta' => 
          array (
            'clase' => 'escalar',
          ),
        ),
      ),
    ),
    'm025' => 
    array (
      'tabla' => 'preguntas_declarativas_originales_52d856',
      'padre' => 'm022',
      'ruta' => '/petersen/valoraciones/soportes/datos_originales/captura_original/preguntas_declarativas_originales',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'cod_instrumento' => 
          array (
            'clase' => 'escalar',
          ),
          'cod_pregunta' => 
          array (
            'clase' => 'escalar',
          ),
          'codigo' => 
          array (
            'clase' => 'escalar',
          ),
          'dominio' => 
          array (
            'clase' => 'escalar',
          ),
          'enunciado' => 
          array (
            'clase' => 'escalar',
          ),
          'estado' => 
          array (
            'clase' => 'escalar',
          ),
          'orden' => 
          array (
            'clase' => 'escalar',
          ),
          'puntaje_maximo' => 
          array (
            'clase' => 'escalar',
          ),
          'tipo_respuesta' => 
          array (
            'clase' => 'escalar',
          ),
        ),
      ),
    ),
    'm026' => 
    array (
      'tabla' => 'respuestas_originales_014dd1',
      'padre' => 'm022',
      'ruta' => '/petersen/valoraciones/soportes/datos_originales/captura_original/respuestas_originales',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'cod_aplicacion' => 
          array (
            'clase' => 'escalar',
          ),
          'cod_opcion' => 
          array (
            'clase' => 'escalar',
          ),
          'cod_pregunta' => 
          array (
            'clase' => 'escalar',
          ),
          'cod_respuesta' => 
          array (
            'clase' => 'escalar',
          ),
          'codigo_pregunta' => 
          array (
            'clase' => 'escalar',
          ),
          'coherente' => 
          array (
            'clase' => 'escalar',
          ),
          'dominio_original' => 
          array (
            'clase' => 'escalar',
          ),
          'estado_pregunta_actual' => 
          array (
            'clase' => 'escalar',
          ),
          'observacion' => 
          array (
            'clase' => 'escalar',
          ),
          'opcion_actual' => 
          array (
            'clase' => 'objeto',
            'campos' => 
            array (
              'cod_opcion' => 
              array (
                'clase' => 'escalar',
              ),
              'cod_pregunta' => 
              array (
                'clase' => 'escalar',
              ),
              'estado' => 
              array (
                'clase' => 'escalar',
              ),
              'nombre' => 
              array (
                'clase' => 'escalar',
              ),
              'orden' => 
              array (
                'clase' => 'escalar',
              ),
              'puntaje' => 
              array (
                'clase' => 'escalar',
              ),
              'valor' => 
              array (
                'clase' => 'escalar',
              ),
            ),
          ),
          'pregunta' => 
          array (
            'clase' => 'escalar',
          ),
          'pregunta_actual' => 
          array (
            'clase' => 'objeto',
            'campos' => 
            array (
              'cod_instrumento' => 
              array (
                'clase' => 'escalar',
              ),
              'cod_pregunta' => 
              array (
                'clase' => 'escalar',
              ),
              'codigo' => 
              array (
                'clase' => 'escalar',
              ),
              'dominio' => 
              array (
                'clase' => 'escalar',
              ),
              'enunciado' => 
              array (
                'clase' => 'escalar',
              ),
              'estado' => 
              array (
                'clase' => 'escalar',
              ),
              'orden' => 
              array (
                'clase' => 'escalar',
              ),
              'puntaje_maximo' => 
              array (
                'clase' => 'escalar',
              ),
              'tipo_respuesta' => 
              array (
                'clase' => 'escalar',
              ),
            ),
          ),
          'puntaje' => 
          array (
            'clase' => 'escalar',
          ),
          'tipo_respuesta' => 
          array (
            'clase' => 'escalar',
          ),
          'valor_logico' => 
          array (
            'clase' => 'escalar',
          ),
          'valor_numero' => 
          array (
            'clase' => 'escalar',
          ),
          'valor_texto' => 
          array (
            'clase' => 'escalar',
          ),
        ),
      ),
    ),
    'm027' => 
    array (
      'tabla' => 'vinculos_preguntas_actuales_7b4dbb',
      'padre' => 'm022',
      'ruta' => '/petersen/valoraciones/soportes/datos_originales/captura_original/vinculos_preguntas_actuales',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'pertenece_al_instrumento' => 
          array (
            'clase' => 'escalar',
          ),
          'pregunta_actual' => 
          array (
            'clase' => 'objeto',
            'campos' => 
            array (
              'cod_instrumento' => 
              array (
                'clase' => 'escalar',
              ),
              'cod_pregunta' => 
              array (
                'clase' => 'escalar',
              ),
              'codigo' => 
              array (
                'clase' => 'escalar',
              ),
              'dominio' => 
              array (
                'clase' => 'escalar',
              ),
              'enunciado' => 
              array (
                'clase' => 'escalar',
              ),
              'estado' => 
              array (
                'clase' => 'escalar',
              ),
              'orden' => 
              array (
                'clase' => 'escalar',
              ),
              'puntaje_maximo' => 
              array (
                'clase' => 'escalar',
              ),
              'tipo_respuesta' => 
              array (
                'clase' => 'escalar',
              ),
            ),
          ),
          'selector' => 
          array (
            'clase' => 'objeto',
            'campos' => 
            array (
              'campo' => 
              array (
                'clase' => 'escalar',
              ),
              'cod_pregunta' => 
              array (
                'clase' => 'escalar',
              ),
            ),
          ),
          'tiene_respuesta' => 
          array (
            'clase' => 'escalar',
          ),
        ),
      ),
    ),
  ),
  'columnas' => 
  array (
    '' => 'contenido',
    '/instrumental' => 'instrumental',
    '/instrumental/cabecera_conocimiento' => 'instrumental_cabecera_conocimiento',
    '/instrumental/cabecera_conocimiento/cod_usuario_creacion' => 'instrumental_cabecera_conocim_7674583a82',
    '/instrumental/cabecera_conocimiento/cod_version_anterior' => 'instrumental_cabecera_conocim_6c21c50668',
    '/instrumental/cabecera_conocimiento/cod_version_modelo' => 'instrumental_cabecera_conocim_68b964076f',
    '/instrumental/cabecera_conocimiento/codigo_version' => 'instrumental_cabecera_conocim_e2a79a7cc9',
    '/instrumental/cabecera_conocimiento/descripcion' => 'instrumental_cabecera_conocim_a36ff15d99',
    '/instrumental/cabecera_conocimiento/estado' => 'instrumental_cabecera_conocim_e69185a327',
    '/instrumental/cabecera_conocimiento/fecha_hora_creacion' => 'instrumental_cabecera_conocim_d577f1e297',
    '/instrumental/cabecera_conocimiento/fecha_hora_retiro' => 'instrumental_cabecera_conocim_d0b04ad083',
    '/instrumental/cabecera_conocimiento/fecha_hora_vigencia' => 'instrumental_cabecera_conocim_ddd9cca7b0',
    '/instrumental/cabecera_conocimiento/motivo_cambio' => 'instrumental_cabecera_conocim_0775b2f332',
    '/instrumental/cabecera_conocimiento/nombre' => 'instrumental_cabecera_conocim_7aedddf78e',
    '/instrumental/cabecera_conocimiento/observacion' => 'instrumental_cabecera_conocim_86120582e3',
    '/instrumental/capturas' => 'instrumental_capturas',
    '/instrumental/capturas/capturas' => 'instrumental_capturas_capturas',
    '/aplicacion_actual' => 'aplicacion_actual',
    '/aplicacion_actual/clasificacion' => 'aplicacion_actual_clasificacion',
    '/aplicacion_actual/cod_aplicacion' => 'aplicacion_actual_cod_aplicacion',
    '/aplicacion_actual/cod_aplicacion_anterior' => 'aplicacion_actual_cod_aplicac_0c682deb22',
    '/aplicacion_actual/cod_atencion' => 'aplicacion_actual_cod_atencion',
    '/aplicacion_actual/cod_documento_clinico' => 'aplicacion_actual_cod_documento_clinico',
    '/aplicacion_actual/cod_instrumento' => 'aplicacion_actual_cod_instrumento',
    '/aplicacion_actual/cod_personal' => 'aplicacion_actual_cod_personal',
    '/aplicacion_actual/cod_residente' => 'aplicacion_actual_cod_residente',
    '/aplicacion_actual/cod_usuario_registro' => 'aplicacion_actual_cod_usuario_registro',
    '/aplicacion_actual/estado' => 'aplicacion_actual_estado',
    '/aplicacion_actual/fecha_hora' => 'aplicacion_actual_fecha_hora',
    '/aplicacion_actual/interpretacion' => 'aplicacion_actual_interpretacion',
    '/aplicacion_actual/localizador_fuente' => 'aplicacion_actual_localizador_fuente',
    '/aplicacion_actual/observacion' => 'aplicacion_actual_observacion',
    '/aplicacion_actual/puntaje_maximo' => 'aplicacion_actual_puntaje_maximo',
    '/aplicacion_actual/puntaje_total' => 'aplicacion_actual_puntaje_total',
    '/aplicacion_actual/registrado_en' => 'aplicacion_actual_registrado_en',
    '/atencion_original' => 'atencion_original',
    '/atencion_original/cod_atencion' => 'atencion_original_cod_atencion',
    '/atencion_original/cod_residente' => 'atencion_original_cod_residente',
    '/autor' => 'autor',
    '/cod_aplicacion' => 'cod_aplicacion',
    '/cod_instrumento' => 'cod_instrumento',
    '/cod_personal' => 'cod_personal',
    '/cod_residente' => 'cod_residente',
    '/codigo_instrumento' => 'codigo_instrumento',
    '/documento_original' => 'documento_original',
    '/documento_original/cod_documento_clinico' => 'documento_original_cod_documento_clinico',
    '/documento_original/cod_personal' => 'documento_original_cod_personal',
    '/documento_original/cod_residente' => 'documento_original_cod_residente',
    '/documento_original/estado' => 'documento_original_estado',
    '/documento_original/fecha_hora' => 'documento_original_fecha_hora',
    '/documento_original/hash_archivo' => 'documento_original_hash_archivo',
    '/edad_en_aplicacion' => 'edad_en_aplicacion',
    '/estado_original' => 'estado_original',
    '/extraccion_adquirida' => 'extraccion_adquirida',
    '/fecha_hora' => 'fecha_hora',
    '/fuente_edad' => 'fuente_edad',
    '/fuente_edad/campo' => 'fuente_edad_campo',
    '/fuente_edad/fecha_aplicacion' => 'fuente_edad_fecha_aplicacion',
    '/fuente_edad/fecha_nacimiento' => 'fuente_edad_fecha_nacimiento',
    '/fuente_edad/registro' => 'fuente_edad_registro',
    '/fuente_edad/tabla' => 'fuente_edad_tabla',
    '/instrumento_actual' => 'instrumento_actual',
    '/instrumento_actual/cod_instrumento' => 'instrumento_actual_cod_instrumento',
    '/instrumento_actual/codigo' => 'instrumento_actual_codigo',
    '/instrumento_actual/descripcion' => 'instrumento_actual_descripcion',
    '/instrumento_actual/estado' => 'instrumento_actual_estado',
    '/instrumento_actual/nombre' => 'instrumento_actual_nombre',
    '/instrumento_actual/puntaje_maximo' => 'instrumento_actual_puntaje_maximo',
    '/instrumento_actual/tipo' => 'instrumento_actual_tipo',
    '/instrumento_actual/version' => 'instrumento_actual_version',
    '/motivos' => 'motivos',
    '/nombre_instrumento' => 'nombre_instrumento',
    '/perfil_captura_original' => 'perfil_captura_original',
    '/perfil_captura_original/cod_instrumento' => 'perfil_captura_original_cod_instrumento',
    '/perfil_captura_original/codigo_instrumento' => 'perfil_captura_original_codig_df5f32b01a',
    '/perfil_captura_original/id' => 'perfil_captura_original_id',
    '/perfil_captura_original/identidad_instrumento' => 'perfil_captura_original_ident_9749c603ee',
    '/perfil_captura_original/identidad_instrumento/edicion_protocolo' => 'perfil_captura_original_ident_a0889690f5',
    '/perfil_captura_original/identidad_instrumento/forma' => 'perfil_captura_original_ident_83ec325f6f',
    '/perfil_captura_original/identidad_instrumento/idioma' => 'perfil_captura_original_ident_0186dcf93c',
    '/perfil_captura_original/identidad_instrumento/instrumento' => 'perfil_captura_original_ident_8d3297dec3',
    '/perfil_captura_original/metodo' => 'perfil_captura_original_metodo',
    '/perfil_captura_original/selectores' => 'perfil_captura_original_selectores',
    '/campo' => 'campo',
    '/cod_pregunta' => 'cod_pregunta',
    '/perfil_captura_original/version_instrumento' => 'perfil_captura_original_versi_9ab1a95b19',
    '/preguntas_declarativas_originales' => 'preguntas_declarativas_originales',
    '/codigo' => 'codigo',
    '/dominio' => 'dominio',
    '/enunciado' => 'enunciado',
    '/estado' => 'estado',
    '/orden' => 'orden',
    '/puntaje_maximo' => 'puntaje_maximo',
    '/tipo_respuesta' => 'tipo_respuesta',
    '/procedencia' => 'procedencia',
    '/procedencia/cod_aplicacion_anterior' => 'procedencia_cod_aplicacion_anterior',
    '/procedencia/cod_documento_clinico' => 'procedencia_cod_documento_clinico',
    '/procedencia/cod_personal_aplicador' => 'procedencia_cod_personal_aplicador',
    '/procedencia/cod_usuario_registro' => 'procedencia_cod_usuario_registro',
    '/procedencia/estado_documento' => 'procedencia_estado_documento',
    '/procedencia/localizador_fuente' => 'procedencia_localizador_fuente',
    '/procedencia/registrado_en' => 'procedencia_registrado_en',
    '/respuestas_originales' => 'respuestas_originales',
    '/cod_opcion' => 'cod_opcion',
    '/cod_respuesta' => 'cod_respuesta',
    '/codigo_pregunta' => 'codigo_pregunta',
    '/coherente' => 'coherente',
    '/dominio_original' => 'dominio_original',
    '/estado_pregunta_actual' => 'estado_pregunta_actual',
    '/observacion' => 'observacion',
    '/opcion_actual' => 'opcion_actual',
    '/opcion_actual/cod_opcion' => 'opcion_actual_cod_opcion',
    '/opcion_actual/cod_pregunta' => 'opcion_actual_cod_pregunta',
    '/opcion_actual/estado' => 'opcion_actual_estado',
    '/opcion_actual/nombre' => 'opcion_actual_nombre',
    '/opcion_actual/orden' => 'opcion_actual_orden',
    '/opcion_actual/puntaje' => 'opcion_actual_puntaje',
    '/opcion_actual/valor' => 'opcion_actual_valor',
    '/pregunta' => 'pregunta',
    '/pregunta_actual' => 'pregunta_actual',
    '/pregunta_actual/cod_instrumento' => 'pregunta_actual_cod_instrumento',
    '/pregunta_actual/cod_pregunta' => 'pregunta_actual_cod_pregunta',
    '/pregunta_actual/codigo' => 'pregunta_actual_codigo',
    '/pregunta_actual/dominio' => 'pregunta_actual_dominio',
    '/pregunta_actual/enunciado' => 'pregunta_actual_enunciado',
    '/pregunta_actual/estado' => 'pregunta_actual_estado',
    '/pregunta_actual/orden' => 'pregunta_actual_orden',
    '/pregunta_actual/puntaje_maximo' => 'pregunta_actual_puntaje_maximo',
    '/pregunta_actual/tipo_respuesta' => 'pregunta_actual_tipo_respuesta',
    '/puntaje' => 'puntaje',
    '/valor_logico' => 'valor_logico',
    '/valor_numero' => 'valor_numero',
    '/valor_texto' => 'valor_texto',
    '/version_instrumento' => 'version_instrumento',
    '/vinculos_preguntas_actuales' => 'vinculos_preguntas_actuales',
    '/pertenece_al_instrumento' => 'pertenece_al_instrumento',
    '/selector' => 'selector',
    '/selector/campo' => 'selector_campo',
    '/selector/cod_pregunta' => 'selector_cod_pregunta',
    '/tiene_respuesta' => 'tiene_respuesta',
    '/instrumental/capturas/disponible' => 'instrumental_capturas_disponible',
    '/instrumental/capturas/fecha_corte' => 'instrumental_capturas_fecha_corte',
    '/instrumental/capturas/persistido' => 'instrumental_capturas_persistido',
    '/instrumental/conocimiento' => 'instrumental_conocimiento',
    '/instrumental/conocimiento/contratos_extraccion' => 'instrumental_conocimiento_con_5495ad87d7',
    '/instrumental/conocimiento/contratos_extraccion/RESPUESTA_INSTITUCIONAL' => 'instrumental_conocimiento_con_817a29185b',
    '/instrumental/conocimiento/contratos_extraccion/RESPUESTA_ORIGINAL' => 'instrumental_conocimiento_con_41f021bf19',
    '/instrumental/conocimiento/contratos_relaciones' => 'instrumental_conocimiento_con_224bc4d292',
    '/habilita_participacion' => 'habilita_participacion',
    '/modalidades' => 'modalidades',
    '/tipo_destino' => 'tipo_destino',
    '/tipo_origen' => 'tipo_origen',
    '/tipo_relacion' => 'tipo_relacion',
    '/instrumental/conocimiento/solo_pruebas_tecnicas' => 'instrumental_conocimiento_sol_092bac0c2b',
    '/instrumental/conocimiento/tablas' => 'instrumental_conocimiento_tablas',
    '/instrumental/conocimiento/tablas/condiciones_regla_experta' => 'instrumental_conocimiento_tab_b3e8a9143b',
    '/cod_condicion_regla' => 'cod_condicion_regla',
    '/cod_regla_experta' => 'cod_regla_experta',
    '/cod_valor_semantico' => 'cod_valor_semantico',
    '/cod_variable_experta' => 'cod_variable_experta',
    '/instrumental/conocimiento/tablas/consecuencias_regla_experta' => 'instrumental_conocimiento_tab_42f32615ef',
    '/cod_consecuencia_regla' => 'cod_consecuencia_regla',
    '/cod_criterio_dominio_resultado' => 'cod_criterio_dominio_resultado',
    '/instrumental/conocimiento/tablas/criterios_dominios_resultado' => 'instrumental_conocimiento_tab_65538363b1',
    '/cod_dominio_valores' => 'cod_dominio_valores',
    '/cod_nodo_criterio' => 'cod_nodo_criterio',
    '/cod_usuario_creacion' => 'cod_usuario_creacion',
    '/fecha_hora_creacion' => 'fecha_hora_creacion',
    '/instrumental/conocimiento/tablas/dominios_valores_expertos' => 'instrumental_conocimiento_tab_d712a36b91',
    '/cod_version_modelo' => 'cod_version_modelo',
    '/codigo_dominio' => 'codigo_dominio',
    '/descripcion' => 'descripcion',
    '/nombre' => 'nombre',
    '/tipo_dominio' => 'tipo_dominio',
    '/instrumental/conocimiento/tablas/fuentes_datos_expertas' => 'instrumental_conocimiento_tab_ae7bd93fc7',
    '/campo_estado' => 'campo_estado',
    '/campo_personal' => 'campo_personal',
    '/campo_pk_raiz' => 'campo_pk_raiz',
    '/campo_residente' => 'campo_residente',
    '/campo_temporal' => 'campo_temporal',
    '/clave_adaptador' => 'clave_adaptador',
    '/cod_fuente_dato_experta' => 'cod_fuente_dato_experta',
    '/codigo_fuente' => 'codigo_fuente',
    '/tabla_raiz' => 'tabla_raiz',
    '/tipo_fuente' => 'tipo_fuente',
    '/instrumental/conocimiento/tablas/mapeos_valores_fuente' => 'instrumental_conocimiento_tab_cfa5292fd5',
    '/cod_mapeo_valor_fuente' => 'cod_mapeo_valor_fuente',
    '/cod_mapeo_variable_fuente' => 'cod_mapeo_variable_fuente',
    '/estado_aprobacion' => 'estado_aprobacion',
    '/fecha_hora_vigencia_desde' => 'fecha_hora_vigencia_desde',
    '/fecha_hora_vigencia_hasta' => 'fecha_hora_vigencia_hasta',
    '/tipo_valor_fuente' => 'tipo_valor_fuente',
    '/valor_fuente_exacto' => 'valor_fuente_exacto',
    '/instrumental/conocimiento/tablas/mapeos_variables_fuente' => 'instrumental_conocimiento_tab_2968a98540',
    '/campo_valor' => 'campo_valor',
    '/clave_selector' => 'clave_selector',
    '/tipo_extraccion' => 'tipo_extraccion',
    '/instrumental/conocimiento/tablas/nodos_semanticos' => 'instrumental_conocimiento_tab_5baf563f9b',
    '/cod_nodo_semantico' => 'cod_nodo_semantico',
    '/codigo_semantico' => 'codigo_semantico',
    '/definicion' => 'definicion',
    '/tipo_nodo' => 'tipo_nodo',
    '/instrumental/conocimiento/tablas/reglas_expertas' => 'instrumental_conocimiento_tab_c2aa0e145c',
    '/cod_regla_anterior' => 'cod_regla_anterior',
    '/cod_usuario_revision' => 'cod_usuario_revision',
    '/codigo_regla' => 'codigo_regla',
    '/estado_revision' => 'estado_revision',
    '/fecha_hora_revision' => 'fecha_hora_revision',
    '/justificacion_revision' => 'justificacion_revision',
    '/tipo_regla' => 'tipo_regla',
    '/instrumental/conocimiento/tablas/relaciones_semanticas' => 'instrumental_conocimiento_tab_4d3d622da8',
    '/cod_nodo_destino' => 'cod_nodo_destino',
    '/cod_nodo_origen' => 'cod_nodo_origen',
    '/cod_relacion_semantica' => 'cod_relacion_semantica',
    '/modalidad_relacion' => 'modalidad_relacion',
    '/significado' => 'significado',
    '/instrumental/conocimiento/tablas/valores_semanticos' => 'instrumental_conocimiento_tab_e5daeaeda7',
    '/codigo_valor' => 'codigo_valor',
    '/definicion_semantica' => 'definicion_semantica',
    '/instrumental/conocimiento/tablas/variables_expertas' => 'instrumental_conocimiento_tab_e298251428',
    '/cod_nodo_propietario_primario' => 'cod_nodo_propietario_primario',
    '/papel_inferencial' => 'papel_inferencial',
    '/tipo_semantico' => 'tipo_semantico',
    '/instrumental/conocimiento/tipo_regla_resultado' => 'instrumental_conocimiento_tip_306b630bbe',
    '/instrumental/conocimiento/version' => 'instrumental_conocimiento_version',
    '/instrumental/contexto' => 'instrumental_contexto',
    '/instrumental/contexto/evaluacion' => 'instrumental_contexto_evaluacion',
    '/instrumental/contexto/fecha_corte' => 'instrumental_contexto_fecha_corte',
    '/instrumental/contexto/fecha_corte/corte_offset' => 'instrumental_contexto_fecha_c_f8d4ea4324',
    '/instrumental/contexto/fecha_corte/corte_utc' => 'instrumental_contexto_fecha_c_b0e4b6f789',
    '/instrumental/contexto/fecha_corte/corte_zona' => 'instrumental_contexto_fecha_c_767f259569',
    '/instrumental/contexto/residente' => 'instrumental_contexto_residente',
    '/instrumental/contexto/usuario_ejecucion' => 'instrumental_contexto_usuario_ejecucion',
    '/instrumental/contexto/version' => 'instrumental_contexto_version',
    '/instrumental/contrato' => 'instrumental_contrato',
    '/instrumental/vinculos' => 'instrumental_vinculos',
    '/componente' => 'componente',
    '/perfil_interpretativo' => 'perfil_interpretativo',
    '/perfil_interpretativo/codigo' => 'perfil_interpretativo_codigo',
    '/perfil_interpretativo/componente' => 'perfil_interpretativo_componente',
    '/perfil_interpretativo/fuente' => 'perfil_interpretativo_fuente',
    '/perfil_interpretativo/instrumento' => 'perfil_interpretativo_instrumento',
    '/perfil_interpretativo/instrumento/codigo' => 'perfil_interpretativo_instrumento_codigo',
    '/perfil_interpretativo/instrumento/version' => 'perfil_interpretativo_instrum_3311aa54d2',
    '/perfil_interpretativo/mediciones_requeridas' => 'perfil_interpretativo_medicio_aee933edbe',
    '/perfil_interpretativo/metodo' => 'perfil_interpretativo_metodo',
    '/perfil_interpretativo/predicados' => 'perfil_interpretativo_predicados',
    '/perfil_interpretativo/version' => 'perfil_interpretativo_version',
    '/petersen' => 'petersen',
    '/petersen/adquisicion' => 'petersen_adquisicion',
    '/petersen/adquisicion/conservacion_completa' => 'petersen_adquisicion_conserva_7b25788aa8',
    '/petersen/adquisicion/exclusiones' => 'petersen_adquisicion_exclusiones',
    '/elemento' => 'elemento',
    '/motivo' => 'motivo',
    '/valoracion' => 'valoracion',
    '/petersen/adquisicion/fuentes_originales_mutables' => 'petersen_adquisicion_fuentes__4111797f0d',
    '/petersen/adquisicion/valoraciones_al_corte' => 'petersen_adquisicion_valoraci_9e9393e6d2',
    '/petersen/constancias_documentales' => 'petersen_constancias_documentales',
    '/cod_documento_clinico' => 'cod_documento_clinico',
    '/hash_archivo' => 'hash_archivo',
    '/localizador_fuente' => 'localizador_fuente',
    '/papel' => 'papel',
    '/verificado' => 'verificado',
    '/petersen/contexto' => 'petersen_contexto',
    '/petersen/contexto/evaluacion' => 'petersen_contexto_evaluacion',
    '/petersen/contexto/fecha_corte' => 'petersen_contexto_fecha_corte',
    '/petersen/contexto/fecha_corte/corte_offset' => 'petersen_contexto_fecha_corte_a1ab98b117',
    '/petersen/contexto/fecha_corte/corte_utc' => 'petersen_contexto_fecha_corte_corte_utc',
    '/petersen/contexto/fecha_corte/corte_zona' => 'petersen_contexto_fecha_corte_corte_zona',
    '/petersen/contexto/residente' => 'petersen_contexto_residente',
    '/petersen/contexto/usuario_ejecucion' => 'petersen_contexto_usuario_ejecucion',
    '/petersen/contexto/version' => 'petersen_contexto_version',
    '/petersen/contrato' => 'petersen_contrato',
    '/petersen/gobierno' => 'petersen_gobierno',
    '/petersen/gobierno/cabecera' => 'petersen_gobierno_cabecera',
    '/petersen/gobierno/cabecera/cod_usuario_creacion' => 'petersen_gobierno_cabecera_co_20107d29fe',
    '/petersen/gobierno/cabecera/cod_version_anterior' => 'petersen_gobierno_cabecera_co_b2eeee922a',
    '/petersen/gobierno/cabecera/cod_version_modelo' => 'petersen_gobierno_cabecera_co_65607d4c80',
    '/petersen/gobierno/cabecera/codigo_version' => 'petersen_gobierno_cabecera_co_1e028368fb',
    '/petersen/gobierno/cabecera/descripcion' => 'petersen_gobierno_cabecera_descripcion',
    '/petersen/gobierno/cabecera/estado' => 'petersen_gobierno_cabecera_estado',
    '/petersen/gobierno/cabecera/fecha_hora_creacion' => 'petersen_gobierno_cabecera_fe_2e9e9696b6',
    '/petersen/gobierno/cabecera/fecha_hora_retiro' => 'petersen_gobierno_cabecera_fe_9a3b3cde8f',
    '/petersen/gobierno/cabecera/fecha_hora_vigencia' => 'petersen_gobierno_cabecera_fe_0793cfc2ff',
    '/petersen/gobierno/cabecera/motivo_cambio' => 'petersen_gobierno_cabecera_motivo_cambio',
    '/petersen/gobierno/cabecera/nombre' => 'petersen_gobierno_cabecera_nombre',
    '/petersen/gobierno/cabecera/observacion' => 'petersen_gobierno_cabecera_observacion',
    '/petersen/gobierno/conocimiento' => 'petersen_gobierno_conocimiento',
    '/petersen/gobierno/conocimiento/contratos_extraccion' => 'petersen_gobierno_conocimient_962cd1efb2',
    '/petersen/gobierno/conocimiento/contratos_extraccion/RESPUESTA_INSTITUCIONAL' => 'petersen_gobierno_conocimient_69fa47c2da',
    '/petersen/gobierno/conocimiento/contratos_extraccion/RESPUESTA_ORIGINAL' => 'petersen_gobierno_conocimient_768845c9e7',
    '/petersen/gobierno/conocimiento/contratos_relaciones' => 'petersen_gobierno_conocimient_d5e9553f19',
    '/petersen/gobierno/conocimiento/solo_pruebas_tecnicas' => 'petersen_gobierno_conocimient_f86307b600',
    '/petersen/gobierno/conocimiento/tablas' => 'petersen_gobierno_conocimiento_tablas',
    '/petersen/gobierno/conocimiento/tablas/condiciones_regla_experta' => 'petersen_gobierno_conocimient_a4a6f9a38c',
    '/petersen/gobierno/conocimiento/tablas/consecuencias_regla_experta' => 'petersen_gobierno_conocimient_9b66fa6f28',
    '/petersen/gobierno/conocimiento/tablas/criterios_dominios_resultado' => 'petersen_gobierno_conocimient_49a778ea4e',
    '/petersen/gobierno/conocimiento/tablas/dominios_valores_expertos' => 'petersen_gobierno_conocimient_01286026cf',
    '/petersen/gobierno/conocimiento/tablas/fuentes_datos_expertas' => 'petersen_gobierno_conocimient_9d8389c18e',
    '/petersen/gobierno/conocimiento/tablas/mapeos_valores_fuente' => 'petersen_gobierno_conocimient_5db3c11a03',
    '/petersen/gobierno/conocimiento/tablas/mapeos_variables_fuente' => 'petersen_gobierno_conocimient_505e386ef2',
    '/petersen/gobierno/conocimiento/tablas/nodos_semanticos' => 'petersen_gobierno_conocimient_9c7dbe5237',
    '/petersen/gobierno/conocimiento/tablas/reglas_expertas' => 'petersen_gobierno_conocimient_ed95cc27d1',
    '/petersen/gobierno/conocimiento/tablas/relaciones_semanticas' => 'petersen_gobierno_conocimient_f5cfa3f3bf',
    '/petersen/gobierno/conocimiento/tablas/valores_semanticos' => 'petersen_gobierno_conocimient_88dec39267',
    '/petersen/gobierno/conocimiento/tablas/variables_expertas' => 'petersen_gobierno_conocimient_7b8e129270',
    '/petersen/gobierno/conocimiento/tipo_regla_resultado' => 'petersen_gobierno_conocimient_672923ef09',
    '/petersen/gobierno/conocimiento/version' => 'petersen_gobierno_conocimiento_version',
    '/petersen/limites' => 'petersen_limites',
    '/petersen/limites/persistido' => 'petersen_limites_persistido',
    '/petersen/limites/reproduce_adquisicion_sql' => 'petersen_limites_reproduce_ad_1f02193bec',
    '/petersen/limites/revalida_bytes_documentales' => 'petersen_limites_revalida_byt_3e6a6c0597',
    '/petersen/limites/revalida_permisos_historicos' => 'petersen_limites_revalida_per_7dbdcad815',
    '/petersen/membresias_soportes' => 'petersen_membresias_soportes',
    '/soportes' => 'soportes',
    '/petersen/procedencia_valoraciones' => 'petersen_procedencia_valoraciones',
    '/cod_usuario_registro' => 'cod_usuario_registro',
    '/cod_valoracion_anterior' => 'cod_valoracion_anterior',
    '/cod_valoracion_petersen' => 'cod_valoracion_petersen',
    '/registrado_en' => 'registrado_en',
    '/tipo_registro' => 'tipo_registro',
    '/petersen/valoraciones' => 'petersen_valoraciones',
    '/comprobaciones' => 'comprobaciones',
    '/comprobaciones/alcance_dominio' => 'comprobaciones_alcance_dominio',
    '/comprobaciones/atribucion_revisada' => 'comprobaciones_atribucion_revisada',
    '/comprobaciones/cambio_respecto_previo' => 'comprobaciones_cambio_respecto_previo',
    '/comprobaciones/condiciones_revisadas' => 'comprobaciones_condiciones_revisadas',
    '/comprobaciones/contexto_interpretado' => 'comprobaciones_contexto_interpretado',
    '/comprobaciones/corroboracion_independiente' => 'comprobaciones_corroboracion__c7524d170e',
    '/comprobaciones/exclusiones_revisadas' => 'comprobaciones_exclusiones_revisadas',
    '/comprobaciones/factores_pertinentes_revisados' => 'comprobaciones_factores_perti_5a388b1c73',
    '/comprobaciones/impedimento_no_resuelto' => 'comprobaciones_impedimento_no_resuelto',
    '/comprobaciones/oportunidad_adecuada' => 'comprobaciones_oportunidad_adecuada',
    '/comprobaciones/revision_explicita' => 'comprobaciones_revision_explicita',
    '/comprobaciones/revision_integrada' => 'comprobaciones_revision_integrada',
    '/comprobaciones/suficiencia_conjunto' => 'comprobaciones_suficiencia_conjunto',
    '/dominios_revisados' => 'dominios_revisados',
    '/justificacion' => 'justificacion',
    '/origen' => 'origen',
    '/origen/autor' => 'origen_autor',
    '/origen/datos_originales' => 'origen_datos_originales',
    '/origen/episodio' => 'origen_episodio',
    '/origen/fecha' => 'origen_fecha',
    '/origen/registro' => 'origen_registro',
    '/origen/residente' => 'origen_residente',
    '/origen/tabla' => 'origen_tabla',
    '/origen/tipo' => 'origen_tipo',
    '/origen/vigente' => 'origen_vigente',
    '/datos_originales' => 'datos_originales',
    '/datos_originales/actividad' => 'datos_originales_actividad',
    '/datos_originales/captura_original' => 'datos_originales_captura_original',
    '/datos_originales/captura_original/aplicacion_actual' => 'datos_originales_captura_orig_5c00877c15',
    '/datos_originales/captura_original/aplicacion_actual/clasificacion' => 'datos_originales_captura_orig_1cd87d1f21',
    '/datos_originales/captura_original/aplicacion_actual/cod_aplicacion' => 'datos_originales_captura_orig_eaf72a2295',
    '/datos_originales/captura_original/aplicacion_actual/cod_aplicacion_anterior' => 'datos_originales_captura_orig_d513cd1028',
    '/datos_originales/captura_original/aplicacion_actual/cod_atencion' => 'datos_originales_captura_orig_1fbea0bd56',
    '/datos_originales/captura_original/aplicacion_actual/cod_documento_clinico' => 'datos_originales_captura_orig_f6bb2757b7',
    '/datos_originales/captura_original/aplicacion_actual/cod_instrumento' => 'datos_originales_captura_orig_aac43a70e7',
    '/datos_originales/captura_original/aplicacion_actual/cod_personal' => 'datos_originales_captura_orig_083079a46c',
    '/datos_originales/captura_original/aplicacion_actual/cod_residente' => 'datos_originales_captura_orig_45fcd87ac6',
    '/datos_originales/captura_original/aplicacion_actual/cod_usuario_registro' => 'datos_originales_captura_orig_f17c197a8d',
    '/datos_originales/captura_original/aplicacion_actual/estado' => 'datos_originales_captura_orig_3fc38647ec',
    '/datos_originales/captura_original/aplicacion_actual/fecha_hora' => 'datos_originales_captura_orig_b7e1ea8e20',
    '/datos_originales/captura_original/aplicacion_actual/interpretacion' => 'datos_originales_captura_orig_1ea3ddb4ae',
    '/datos_originales/captura_original/aplicacion_actual/localizador_fuente' => 'datos_originales_captura_orig_6f53f6054e',
    '/datos_originales/captura_original/aplicacion_actual/observacion' => 'datos_originales_captura_orig_88449e8ee4',
    '/datos_originales/captura_original/aplicacion_actual/puntaje_maximo' => 'datos_originales_captura_orig_8aee06e623',
    '/datos_originales/captura_original/aplicacion_actual/puntaje_total' => 'datos_originales_captura_orig_1a178a221b',
    '/datos_originales/captura_original/aplicacion_actual/registrado_en' => 'datos_originales_captura_orig_1ccd87eb85',
    '/datos_originales/captura_original/atencion_original' => 'datos_originales_captura_orig_f22b3da1b8',
    '/datos_originales/captura_original/atencion_original/cod_atencion' => 'datos_originales_captura_orig_6e60e51f14',
    '/datos_originales/captura_original/atencion_original/cod_residente' => 'datos_originales_captura_orig_3675b1c461',
    '/datos_originales/captura_original/autor' => 'datos_originales_captura_original_autor',
    '/datos_originales/captura_original/cod_aplicacion' => 'datos_originales_captura_orig_85e8080bcd',
    '/datos_originales/captura_original/cod_instrumento' => 'datos_originales_captura_orig_088ad86786',
    '/datos_originales/captura_original/cod_personal' => 'datos_originales_captura_orig_efe7cb06d3',
    '/datos_originales/captura_original/cod_residente' => 'datos_originales_captura_orig_60146368b8',
    '/datos_originales/captura_original/codigo_instrumento' => 'datos_originales_captura_orig_9ad09177eb',
    '/datos_originales/captura_original/documento_original' => 'datos_originales_captura_orig_244640a406',
    '/datos_originales/captura_original/documento_original/cod_documento_clinico' => 'datos_originales_captura_orig_da8088f306',
    '/datos_originales/captura_original/documento_original/cod_personal' => 'datos_originales_captura_orig_2fd3c6d19d',
    '/datos_originales/captura_original/documento_original/cod_residente' => 'datos_originales_captura_orig_ace2f0c459',
    '/datos_originales/captura_original/documento_original/estado' => 'datos_originales_captura_orig_c1ffbd3a81',
    '/datos_originales/captura_original/documento_original/fecha_hora' => 'datos_originales_captura_orig_6fe00c15db',
    '/datos_originales/captura_original/documento_original/hash_archivo' => 'datos_originales_captura_orig_331ce03a42',
    '/datos_originales/captura_original/edad_en_aplicacion' => 'datos_originales_captura_orig_c52b0aac2f',
    '/datos_originales/captura_original/estado_original' => 'datos_originales_captura_orig_46b6d7c468',
    '/datos_originales/captura_original/extraccion_adquirida' => 'datos_originales_captura_orig_7319d6c5e8',
    '/datos_originales/captura_original/fecha_hora' => 'datos_originales_captura_orig_90d9f5e79f',
    '/datos_originales/captura_original/fuente_edad' => 'datos_originales_captura_orig_0e2b183373',
    '/datos_originales/captura_original/fuente_edad/campo' => 'datos_originales_captura_orig_ee500456be',
    '/datos_originales/captura_original/fuente_edad/fecha_aplicacion' => 'datos_originales_captura_orig_82e346d044',
    '/datos_originales/captura_original/fuente_edad/fecha_nacimiento' => 'datos_originales_captura_orig_f48d694962',
    '/datos_originales/captura_original/fuente_edad/registro' => 'datos_originales_captura_orig_723b7daf7e',
    '/datos_originales/captura_original/fuente_edad/tabla' => 'datos_originales_captura_orig_dd39875eaf',
    '/datos_originales/captura_original/instrumento_actual' => 'datos_originales_captura_orig_892501f830',
    '/datos_originales/captura_original/instrumento_actual/cod_instrumento' => 'datos_originales_captura_orig_fc319a2de9',
    '/datos_originales/captura_original/instrumento_actual/codigo' => 'datos_originales_captura_orig_8b110701d8',
    '/datos_originales/captura_original/instrumento_actual/descripcion' => 'datos_originales_captura_orig_d1215eaa27',
    '/datos_originales/captura_original/instrumento_actual/estado' => 'datos_originales_captura_orig_f0933be9fa',
    '/datos_originales/captura_original/instrumento_actual/nombre' => 'datos_originales_captura_orig_62c506a36a',
    '/datos_originales/captura_original/instrumento_actual/puntaje_maximo' => 'datos_originales_captura_orig_8f05c4183e',
    '/datos_originales/captura_original/instrumento_actual/tipo' => 'datos_originales_captura_orig_6ccd899ff0',
    '/datos_originales/captura_original/instrumento_actual/version' => 'datos_originales_captura_orig_fcbdd60362',
    '/datos_originales/captura_original/motivos' => 'datos_originales_captura_orig_40f70870c5',
    '/datos_originales/captura_original/nombre_instrumento' => 'datos_originales_captura_orig_86314ba786',
    '/datos_originales/captura_original/perfil_captura_original' => 'datos_originales_captura_orig_bb6ef4d3c9',
    '/datos_originales/captura_original/perfil_captura_original/cod_instrumento' => 'datos_originales_captura_orig_90a4af9bc4',
    '/datos_originales/captura_original/perfil_captura_original/codigo_instrumento' => 'datos_originales_captura_orig_8cb823fc88',
    '/datos_originales/captura_original/perfil_captura_original/id' => 'datos_originales_captura_orig_b1c47834f0',
    '/datos_originales/captura_original/perfil_captura_original/identidad_instrumento' => 'datos_originales_captura_orig_87b3b6bd12',
    '/datos_originales/captura_original/perfil_captura_original/identidad_instrumento/edicion_protocolo' => 'datos_originales_captura_orig_7790587a5e',
    '/datos_originales/captura_original/perfil_captura_original/identidad_instrumento/forma' => 'datos_originales_captura_orig_1beac91015',
    '/datos_originales/captura_original/perfil_captura_original/identidad_instrumento/idioma' => 'datos_originales_captura_orig_7dd06eed05',
    '/datos_originales/captura_original/perfil_captura_original/identidad_instrumento/instrumento' => 'datos_originales_captura_orig_16eff0003a',
    '/datos_originales/captura_original/perfil_captura_original/metodo' => 'datos_originales_captura_orig_ee23ac4371',
    '/datos_originales/captura_original/perfil_captura_original/selectores' => 'datos_originales_captura_orig_2f6504d85f',
    '/datos_originales/captura_original/perfil_captura_original/version_instrumento' => 'datos_originales_captura_orig_c6c68daf75',
    '/datos_originales/captura_original/preguntas_declarativas_originales' => 'datos_originales_captura_orig_78f1a55d9d',
    '/datos_originales/captura_original/procedencia' => 'datos_originales_captura_orig_e8960474c3',
    '/datos_originales/captura_original/procedencia/cod_aplicacion_anterior' => 'datos_originales_captura_orig_ff0827229e',
    '/datos_originales/captura_original/procedencia/cod_documento_clinico' => 'datos_originales_captura_orig_448a95cad4',
    '/datos_originales/captura_original/procedencia/cod_personal_aplicador' => 'datos_originales_captura_orig_e837d21daa',
    '/datos_originales/captura_original/procedencia/cod_usuario_registro' => 'datos_originales_captura_orig_1394449630',
    '/datos_originales/captura_original/procedencia/estado_documento' => 'datos_originales_captura_orig_f243fd2bd3',
    '/datos_originales/captura_original/procedencia/localizador_fuente' => 'datos_originales_captura_orig_60d702ea02',
    '/datos_originales/captura_original/procedencia/registrado_en' => 'datos_originales_captura_orig_fbb7a3758c',
    '/datos_originales/captura_original/respuestas_originales' => 'datos_originales_captura_orig_e392bbd0dd',
    '/datos_originales/captura_original/version_instrumento' => 'datos_originales_captura_orig_cf71fa5cb2',
    '/datos_originales/captura_original/vinculos_preguntas_actuales' => 'datos_originales_captura_orig_584101441a',
    '/datos_originales/causa_ayuda' => 'datos_originales_causa_ayuda',
    '/datos_originales/descripcion' => 'datos_originales_descripcion',
    '/datos_originales/desempeno' => 'datos_originales_desempeno',
    '/datos_originales/dominio' => 'datos_originales_dominio',
    '/datos_originales/fuente' => 'datos_originales_fuente',
    '/datos_originales/nivel_previo' => 'datos_originales_nivel_previo',
    '/datos_originales/oportunidad' => 'datos_originales_oportunidad',
    '/datos_originales/tipo_ayuda' => 'datos_originales_tipo_ayuda',
    '/episodio' => 'episodio',
    '/fecha' => 'fecha',
    '/registro' => 'registro',
    '/residente' => 'residente',
    '/tabla' => 'tabla',
    '/tipo' => 'tipo',
    '/vigente' => 'vigente',
  ),
);
