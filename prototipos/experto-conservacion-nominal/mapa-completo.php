<?php

declare(strict_types=1);

// Referencia cerrada compilada OFFLINE. No contiene valores clínicos. No instalable.
return array (
  'contrato' => 'REFERENCIA-COMPLETA-NOMINAL-1',
  'raiz' => 'c001',
  'modelos' => 
  array (
    'c001' => 
    array (
      'tabla' => 'entradas_institucionales',
      'padre' => NULL,
      'ruta' => '',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'contrato' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'contrato',
          ),
          'ejecutor' => 
          array (
            'clase' => 'objeto',
            'campos' => 
            array (
              'cantidad_archivos' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'INT',
                ),
                'columna' => 'ejecutor_cantidad_archivos',
              ),
              'huella_src' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'ejecutor_huella_src',
              ),
            ),
            'columna' => 'ejecutor',
          ),
          'instrumental' => 
          array (
            'clase' => 'objeto',
            'campos' => 
            array (
              'contenido' => 
              array (
                'clase' => 'objeto',
                'campos' => 
                array (
                  'cabecera_conocimiento' => 
                  array (
                    'clase' => 'objeto',
                    'campos' => 
                    array (
                      'cod_version_modelo' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'STRING',
                        ),
                        'columna' => 'i_cabecera_cod_version_modelo',
                      ),
                    ),
                    'columna' => 'i_cabecera',
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
                        'columna' => 'i_capturas_capturas',
                        'modelo' => 'c002',
                        'identidad' => 'COLECCION',
                      ),
                      'disponible' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'BOOL',
                        ),
                        'columna' => 'i_capturas_disponible',
                      ),
                      'fecha_corte' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'STRING',
                        ),
                        'columna' => 'i_capturas_fecha_corte',
                      ),
                      'persistido' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'BOOL',
                        ),
                        'columna' => 'i_capturas_persistido',
                      ),
                    ),
                    'columna' => 'i_capturas',
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
                            'tipos' => 
                            array (
                              0 => 'STRING',
                            ),
                            'columna' => 'i_k_contratos_extraccion_respuesta_institucional',
                          ),
                        ),
                        'columna' => 'i_k_contratos_extraccion',
                      ),
                      'contratos_relaciones' => 
                      array (
                        'clase' => 'coleccion',
                        'modo' => 'lista',
                        'columna' => 'i_k_contratos_relaciones',
                        'modelo' => 'c013',
                        'identidad' => 'COLECCION',
                      ),
                      'solo_pruebas_tecnicas' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'BOOL',
                        ),
                        'columna' => 'i_k_solo_pruebas_tecnicas',
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
                            'columna' => 'i_k_tablas_condiciones_regla_experta',
                            'modelo' => 'c015',
                            'identidad' => 'COLECCION',
                          ),
                          'consecuencias_regla_experta' => 
                          array (
                            'clase' => 'coleccion',
                            'modo' => 'lista',
                            'columna' => 'i_k_tablas_consecuencias_regla_experta',
                            'modelo' => 'c016',
                            'identidad' => 'COLECCION',
                          ),
                          'criterios_dominios_resultado' => 
                          array (
                            'clase' => 'coleccion',
                            'modo' => 'lista',
                            'columna' => 'i_k_tablas_criterios_dominios_r',
                            'modelo' => 'c017',
                            'identidad' => 'COLECCION',
                          ),
                          'dominios_valores_expertos' => 
                          array (
                            'clase' => 'coleccion',
                            'modo' => 'lista',
                            'columna' => 'i_k_tablas_dominios_valores_expertos',
                            'modelo' => 'c018',
                            'identidad' => 'COLECCION',
                          ),
                          'fuentes_datos_expertas' => 
                          array (
                            'clase' => 'coleccion',
                            'modo' => 'lista',
                            'columna' => 'i_k_tablas_fuentes_datos_expertas',
                            'modelo' => 'c019',
                            'identidad' => 'COLECCION',
                          ),
                          'mapeos_valores_fuente' => 
                          array (
                            'clase' => 'coleccion',
                            'modo' => 'lista',
                            'columna' => 'i_k_tablas_mapeos_valores_fuente',
                            'modelo' => 'c020',
                            'identidad' => 'COLECCION',
                          ),
                          'mapeos_variables_fuente' => 
                          array (
                            'clase' => 'coleccion',
                            'modo' => 'lista',
                            'columna' => 'i_k_tablas_mapeos_variables_fuente',
                            'modelo' => 'c021',
                            'identidad' => 'COLECCION',
                          ),
                          'nodos_semanticos' => 
                          array (
                            'clase' => 'coleccion',
                            'modo' => 'lista',
                            'columna' => 'i_k_tablas_nodos_semanticos',
                            'modelo' => 'c022',
                            'identidad' => 'COLECCION',
                          ),
                          'reglas_expertas' => 
                          array (
                            'clase' => 'coleccion',
                            'modo' => 'lista',
                            'columna' => 'i_k_tablas_reglas_expertas',
                            'modelo' => 'c023',
                            'identidad' => 'COLECCION',
                          ),
                          'relaciones_semanticas' => 
                          array (
                            'clase' => 'coleccion',
                            'modo' => 'lista',
                            'columna' => 'i_k_tablas_relaciones_semanticas',
                            'modelo' => 'c024',
                            'identidad' => 'COLECCION',
                          ),
                          'valores_semanticos' => 
                          array (
                            'clase' => 'coleccion',
                            'modo' => 'lista',
                            'columna' => 'i_k_tablas_valores_semanticos',
                            'modelo' => 'c025',
                            'identidad' => 'COLECCION',
                          ),
                          'variables_expertas' => 
                          array (
                            'clase' => 'coleccion',
                            'modo' => 'lista',
                            'columna' => 'i_k_tablas_variables_expertas',
                            'modelo' => 'c026',
                            'identidad' => 'COLECCION',
                          ),
                        ),
                        'columna' => 'i_k_tablas',
                      ),
                      'tipo_regla_resultado' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'STRING',
                        ),
                        'columna' => 'i_k_tipo_regla_r',
                      ),
                      'version' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'STRING',
                        ),
                        'columna' => 'i_k_version',
                      ),
                    ),
                    'columna' => 'i_k',
                  ),
                  'contexto' => 
                  array (
                    'clase' => 'objeto',
                    'campos' => 
                    array (
                      'evaluacion' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'STRING',
                        ),
                        'columna' => 'i_contexto_evaluacion',
                      ),
                      'fecha_corte' => 
                      array (
                        'clase' => 'objeto',
                        'campos' => 
                        array (
                          'corte_offset' => 
                          array (
                            'clase' => 'escalar',
                            'tipos' => 
                            array (
                              0 => 'INT',
                            ),
                            'columna' => 'i_contexto_fecha_corte_corte_offset',
                          ),
                          'corte_utc' => 
                          array (
                            'clase' => 'escalar',
                            'tipos' => 
                            array (
                              0 => 'STRING',
                            ),
                            'columna' => 'i_contexto_fecha_corte_corte_utc',
                          ),
                          'corte_zona' => 
                          array (
                            'clase' => 'escalar',
                            'tipos' => 
                            array (
                              0 => 'STRING',
                            ),
                            'columna' => 'i_contexto_fecha_corte_corte_zona',
                          ),
                        ),
                        'columna' => 'i_contexto_fecha_corte',
                      ),
                      'residente' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'STRING',
                        ),
                        'columna' => 'i_contexto_residente',
                      ),
                      'usuario_ejecucion' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'STRING',
                        ),
                        'columna' => 'i_contexto_usuario_ejecucion',
                      ),
                      'version' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'STRING',
                        ),
                        'columna' => 'i_contexto_version',
                      ),
                    ),
                    'columna' => 'i_contexto',
                  ),
                  'contrato' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'i_contrato',
                  ),
                  'fuente_conceptual' => 
                  array (
                    'clase' => 'objeto',
                    'campos' => 
                    array (
                      'documento' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'STRING',
                        ),
                        'columna' => 'i_fuente_conceptual_documento',
                      ),
                      'documento_maestro_sha256' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'STRING',
                        ),
                        'columna' => 'i_fuente_conceptual_documento_maestro_sha256',
                      ),
                      'documento_url' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'STRING',
                        ),
                        'columna' => 'i_fuente_conceptual_documento_url',
                      ),
                      'fecha_consulta' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'STRING',
                        ),
                        'columna' => 'i_fuente_conceptual_fecha_consulta',
                      ),
                      'limite' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'STRING',
                        ),
                        'columna' => 'i_fuente_conceptual_limite',
                      ),
                      'matrices_sha256' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'STRING',
                        ),
                        'columna' => 'i_fuente_conceptual_matrices_sha256',
                      ),
                      'matrices_url' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'STRING',
                        ),
                        'columna' => 'i_fuente_conceptual_matrices_url',
                      ),
                      'nodos' => 
                      array (
                        'clase' => 'coleccion',
                        'modo' => 'lista',
                        'columna' => 'i_fuente_conceptual_nodos',
                        'modelo' => 'c027',
                        'identidad' => 'COLECCION',
                      ),
                      'relaciones' => 
                      array (
                        'clase' => 'coleccion',
                        'modo' => 'lista',
                        'columna' => 'i_fuente_conceptual_relaciones',
                        'modelo' => 'c029',
                        'identidad' => 'COLECCION',
                      ),
                      'relaciones_documentales_no_materializadas' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'INT',
                        ),
                        'columna' => 'i_fue_con_rel_doc_no_mat',
                      ),
                    ),
                    'columna' => 'i_fuente_conceptual',
                  ),
                  'salida' => 
                  array (
                    'clase' => 'objeto',
                    'campos' => 
                    array (
                      'fuentes_excluidas' => 
                      array (
                        'clase' => 'objeto',
                        'campos' => 
                        array (
                        ),
                        'columna' => 'i_s_fuentes_excluidas',
                      ),
                      'perfil_instrumental' => 
                      array (
                        'clase' => 'objeto',
                        'campos' => 
                        array (
                          'comprobacion_adquisicion' => 
                          array (
                            'clase' => 'objeto',
                            'campos' => 
                            array (
                              'aplicaciones' => 
                              array (
                                'clase' => 'coleccion',
                                'modo' => 'lista',
                                'columna' => 'i_s_perfil_adquisicion_aplicaciones',
                                'modelo' => 'c030',
                                'identidad' => 'COLECCION',
                              ),
                              'autoriza_interpretacion_clinica' => 
                              array (
                                'clase' => 'escalar',
                                'tipos' => 
                                array (
                                  0 => 'BOOL',
                                ),
                                'columna' => 'i_s_per_adq_aut_int_cli',
                              ),
                              'contrato' => 
                              array (
                                'clase' => 'escalar',
                                'tipos' => 
                                array (
                                  0 => 'STRING',
                                ),
                                'columna' => 'i_s_perfil_adquisicion_contrato',
                              ),
                              'metricas_entradas_60_o_mas' => 
                              array (
                                'clase' => 'objeto',
                                'campos' => 
                                array (
                                  'aplicaciones_observacionales_sin_normalizacion' => 
                                  array (
                                    'clase' => 'escalar',
                                    'tipos' => 
                                    array (
                                      0 => 'INT',
                                    ),
                                    'columna' => 'i_s_per_adq_met_ent_60_o_mas_apl_obs_sin_nor',
                                  ),
                                  'aplicaciones_sin_extraccion' => 
                                  array (
                                    'clase' => 'escalar',
                                    'tipos' => 
                                    array (
                                      0 => 'INT',
                                    ),
                                    'columna' => 'i_s_per_adq_met_ent_60_o_mas_apl_sin_ext',
                                  ),
                                  'denominador' => 
                                  array (
                                    'clase' => 'escalar',
                                    'tipos' => 
                                    array (
                                      0 => 'STRING',
                                    ),
                                    'columna' => 'i_s_per_adq_met_ent_60_o_mas_den',
                                  ),
                                  'entradas_con_dato_tipado' => 
                                  array (
                                    'clase' => 'escalar',
                                    'tipos' => 
                                    array (
                                      0 => 'INT',
                                    ),
                                    'columna' => 'i_s_per_adq_met_ent_60_o_mas_ent_con_dat_tip',
                                  ),
                                  'entradas_faltantes_o_invalidas' => 
                                  array (
                                    'clase' => 'escalar',
                                    'tipos' => 
                                    array (
                                      0 => 'INT',
                                    ),
                                    'columna' => 'i_s_per_adq_met_ent_60_o_mas_ent_fal_o_inv',
                                  ),
                                  'entradas_requeridas' => 
                                  array (
                                    'clase' => 'escalar',
                                    'tipos' => 
                                    array (
                                      0 => 'INT',
                                    ),
                                    'columna' => 'i_s_per_adq_met_ent_60_o_mas_ent_req',
                                  ),
                                  'limite' => 
                                  array (
                                    'clase' => 'escalar',
                                    'tipos' => 
                                    array (
                                      0 => 'STRING',
                                    ),
                                    'columna' => 'i_s_per_adq_met_ent_60_o_mas_lim',
                                  ),
                                  'porcentaje_completitud' => 
                                  array (
                                    'clase' => 'escalar',
                                    'tipos' => 
                                    array (
                                      0 => 'FLOAT',
                                    ),
                                    'columna' => 'i_s_per_adq_met_ent_60_o_mas_por_com',
                                  ),
                                ),
                                'columna' => 'i_s_perfil_adquisicion_metricas_entradas_60_o_mas',
                              ),
                            ),
                            'columna' => 'i_s_perfil_adquisicion',
                          ),
                          'contrato' => 
                          array (
                            'clase' => 'escalar',
                            'tipos' => 
                            array (
                              0 => 'STRING',
                            ),
                            'columna' => 'i_s_perfil_contrato',
                          ),
                          'diagnostico' => 
                          array (
                            'clase' => 'escalar',
                            'tipos' => 
                            array (
                              0 => 'STRING',
                            ),
                            'columna' => 'i_s_perfil_diagnostico',
                          ),
                          'dominios' => 
                          array (
                            'clase' => 'coleccion',
                            'modo' => 'mapa',
                            'columna' => 'i_s_perfil_dominios',
                            'modelo' => 'c035',
                            'identidad' => 'COLECCION',
                          ),
                          'metricas_60_o_mas' => 
                          array (
                            'clase' => 'objeto',
                            'campos' => 
                            array (
                              'aplicaciones_60_o_mas' => 
                              array (
                                'clase' => 'escalar',
                                'tipos' => 
                                array (
                                  0 => 'INT',
                                ),
                                'columna' => 'i_s_per_met_60_o_mas_apl_60_o_mas',
                              ),
                              'aplicaciones_edad_desconocida' => 
                              array (
                                'clase' => 'escalar',
                                'tipos' => 
                                array (
                                  0 => 'INT',
                                ),
                                'columna' => 'i_s_per_met_60_o_mas_apl_eda_des',
                              ),
                              'aplicaciones_examinadas' => 
                              array (
                                'clase' => 'escalar',
                                'tipos' => 
                                array (
                                  0 => 'INT',
                                ),
                                'columna' => 'i_s_per_met_60_o_mas_apl_exa',
                              ),
                              'aplicaciones_menores_60' => 
                              array (
                                'clase' => 'escalar',
                                'tipos' => 
                                array (
                                  0 => 'INT',
                                ),
                                'columna' => 'i_s_per_met_60_o_mas_apl_men_60',
                              ),
                              'componentes_con_comparacion' => 
                              array (
                                'clase' => 'escalar',
                                'tipos' => 
                                array (
                                  0 => 'INT',
                                ),
                                'columna' => 'i_s_per_met_60_o_mas_com_con_com',
                              ),
                              'componentes_del_perfil' => 
                              array (
                                'clase' => 'escalar',
                                'tipos' => 
                                array (
                                  0 => 'INT',
                                ),
                                'columna' => 'i_s_per_met_60_o_mas_com_del_per',
                              ),
                              'denominador' => 
                              array (
                                'clase' => 'escalar',
                                'tipos' => 
                                array (
                                  0 => 'STRING',
                                ),
                                'columna' => 'i_s_perfil_metricas_60_o_mas_denominador',
                              ),
                              'porcentaje_cobertura_comparacion' => 
                              array (
                                'clase' => 'escalar',
                                'tipos' => 
                                array (
                                  0 => 'FLOAT',
                                ),
                                'columna' => 'i_s_per_met_60_o_mas_por_cob_com',
                              ),
                              'tipo_metrica' => 
                              array (
                                'clase' => 'escalar',
                                'tipos' => 
                                array (
                                  0 => 'STRING',
                                ),
                                'columna' => 'i_s_perfil_metricas_60_o_mas_tipo_metrica',
                              ),
                            ),
                            'columna' => 'i_s_perfil_metricas_60_o_mas',
                          ),
                          'persistido' => 
                          array (
                            'clase' => 'escalar',
                            'tipos' => 
                            array (
                              0 => 'BOOL',
                            ),
                            'columna' => 'i_s_perfil_persistido',
                          ),
                          'red' => 
                          array (
                            'clase' => 'objeto',
                            'campos' => 
                            array (
                              'contratos' => 
                              array (
                                'clase' => 'coleccion',
                                'modo' => 'lista',
                                'columna' => 'i_s_perfil_red_contratos',
                                'modelo' => 'c046',
                                'identidad' => 'COLECCION',
                              ),
                              'documento_sha256' => 
                              array (
                                'clase' => 'escalar',
                                'tipos' => 
                                array (
                                  0 => 'STRING',
                                ),
                                'columna' => 'i_s_perfil_red_documento_sha256',
                              ),
                              'dominios' => 
                              array (
                                'clase' => 'coleccion',
                                'modo' => 'mapa',
                                'columna' => 'i_s_perfil_red_dominios',
                                'modelo' => 'c048',
                                'identidad' => 'COLECCION',
                              ),
                              'ejecutable_clinico' => 
                              array (
                                'clase' => 'escalar',
                                'tipos' => 
                                array (
                                  0 => 'BOOL',
                                ),
                                'columna' => 'i_s_perfil_red_ejecutable_clinico',
                              ),
                              'matrices_sha256' => 
                              array (
                                'clase' => 'escalar',
                                'tipos' => 
                                array (
                                  0 => 'STRING',
                                ),
                                'columna' => 'i_s_perfil_red_matrices_sha256',
                              ),
                              'nodos' => 
                              array (
                                'clase' => 'coleccion',
                                'modo' => 'lista',
                                'columna' => 'i_s_perfil_red_nodos',
                                'modelo' => 'c052',
                                'identidad' => 'COLECCION',
                              ),
                              'relaciones' => 
                              array (
                                'clase' => 'coleccion',
                                'modo' => 'lista',
                                'columna' => 'i_s_perfil_red_relaciones',
                                'modelo' => 'c053',
                                'identidad' => 'COLECCION',
                              ),
                              'version' => 
                              array (
                                'clase' => 'escalar',
                                'tipos' => 
                                array (
                                  0 => 'STRING',
                                ),
                                'columna' => 'i_s_perfil_red_version',
                              ),
                            ),
                            'columna' => 'i_s_perfil_red',
                          ),
                          'revision_profesional' => 
                          array (
                            'clase' => 'escalar',
                            'tipos' => 
                            array (
                              0 => 'STRING',
                            ),
                            'columna' => 'i_s_perfil_revision_profesional',
                          ),
                        ),
                        'columna' => 'i_s_perfil',
                      ),
                      'resultado' => 
                      array (
                        'clase' => 'objeto',
                        'campos' => 
                        array (
                          'alcance' => 
                          array (
                            'clase' => 'escalar',
                            'tipos' => 
                            array (
                              0 => 'STRING',
                            ),
                            'columna' => 'i_s_r_alcance',
                          ),
                          'clasificacion_dcl' => 
                          array (
                            'clase' => 'escalar',
                            'tipos' => 
                            array (
                              0 => 'STRING',
                            ),
                            'columna' => 'i_s_r_clasificacion_dcl',
                          ),
                          'contrato_institucional' => 
                          array (
                            'clase' => 'escalar',
                            'tipos' => 
                            array (
                              0 => 'STRING',
                            ),
                            'columna' => 'i_s_r_contrato_institucional',
                          ),
                          'evaluacion' => 
                          array (
                            'clase' => 'escalar',
                            'tipos' => 
                            array (
                              0 => 'STRING',
                            ),
                            'columna' => 'i_s_r_evaluacion',
                          ),
                          'fecha_corte' => 
                          array (
                            'clase' => 'escalar',
                            'tipos' => 
                            array (
                              0 => 'STRING',
                            ),
                            'columna' => 'i_s_r_fecha_corte',
                          ),
                          'observaciones_por_componente' => 
                          array (
                            'clase' => 'objeto',
                            'campos' => 
                            array (
                            ),
                            'columna' => 'i_s_r_observaciones_por_componente',
                          ),
                          'perfil' => 
                          array (
                            'clase' => 'coleccion',
                            'modo' => 'mapa',
                            'columna' => 'i_s_r_perfil',
                            'modelo' => 'c054',
                            'identidad' => 'COLECCION',
                          ),
                          'persistido' => 
                          array (
                            'clase' => 'escalar',
                            'tipos' => 
                            array (
                              0 => 'BOOL',
                            ),
                            'columna' => 'i_s_r_persistido',
                          ),
                          'red' => 
                          array (
                            'clase' => 'objeto',
                            'campos' => 
                            array (
                              'componentes' => 
                              array (
                                'clase' => 'coleccion',
                                'modo' => 'mapa',
                                'columna' => 'i_s_r_red_componentes',
                                'modelo' => 'c070',
                                'identidad' => 'COLECCION',
                              ),
                              'dominios' => 
                              array (
                                'clase' => 'coleccion',
                                'modo' => 'mapa',
                                'columna' => 'i_s_r_red_dominios',
                                'modelo' => 'c071',
                                'identidad' => 'COLECCION',
                              ),
                              'rutas_reglas' => 
                              array (
                                'clase' => 'coleccion',
                                'modo' => 'mapa',
                                'columna' => 'i_s_r_red_rutas_reglas',
                                'modelo' => 'c072',
                                'identidad' => 'COLECCION',
                              ),
                              'version' => 
                              array (
                                'clase' => 'escalar',
                                'tipos' => 
                                array (
                                  0 => 'STRING',
                                ),
                                'columna' => 'i_s_r_red_version',
                              ),
                            ),
                            'columna' => 'i_s_r_red',
                          ),
                          'residente' => 
                          array (
                            'clase' => 'escalar',
                            'tipos' => 
                            array (
                              0 => 'STRING',
                            ),
                            'columna' => 'i_s_r_residente',
                          ),
                          'version' => 
                          array (
                            'clase' => 'escalar',
                            'tipos' => 
                            array (
                              0 => 'STRING',
                            ),
                            'columna' => 'i_s_r_version',
                          ),
                        ),
                        'columna' => 'i_s_r',
                      ),
                    ),
                    'columna' => 'i_s',
                  ),
                  'vinculos' => 
                  array (
                    'clase' => 'coleccion',
                    'modo' => 'mapa',
                    'columna' => 'i_vinculos',
                    'modelo' => 'c074',
                    'identidad' => 'COLECCION',
                  ),
                ),
                'columna' => 'i',
              ),
              'huella' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'instrumental_huella',
              ),
            ),
            'columna' => 'instrumental',
          ),
          'persistido' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'BOOL',
            ),
            'columna' => 'persistido',
          ),
          'petersen' => 
          array (
            'clase' => 'objeto',
            'campos' => 
            array (
              'contenido' => 
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
                        'tipos' => 
                        array (
                          0 => 'BOOL',
                        ),
                        'columna' => 'p_adquisicion_conservacion_completa',
                      ),
                      'exclusiones' => 
                      array (
                        'clase' => 'coleccion',
                        'modo' => 'lista',
                        'columna' => 'p_adquisicion_exclusiones',
                        'modelo' => 'c077',
                        'identidad' => 'COLECCION',
                      ),
                      'fuentes_originales_mutables' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'BOOL',
                        ),
                        'columna' => 'p_adquisicion_fuentes_originales_mutables',
                      ),
                      'valoraciones_al_corte' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'INT',
                        ),
                        'columna' => 'p_adquisicion_valoraciones_al_corte',
                      ),
                    ),
                    'columna' => 'p_adquisicion',
                  ),
                  'constancias_documentales' => 
                  array (
                    'clase' => 'coleccion',
                    'modo' => 'lista',
                    'columna' => 'p_constancias_documentales',
                    'modelo' => 'c078',
                    'identidad' => 'COLECCION',
                  ),
                  'contexto' => 
                  array (
                    'clase' => 'objeto',
                    'campos' => 
                    array (
                      'evaluacion' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'STRING',
                        ),
                        'columna' => 'p_contexto_evaluacion',
                      ),
                      'fecha_corte' => 
                      array (
                        'clase' => 'objeto',
                        'campos' => 
                        array (
                          'corte_offset' => 
                          array (
                            'clase' => 'escalar',
                            'tipos' => 
                            array (
                              0 => 'INT',
                            ),
                            'columna' => 'p_contexto_fecha_corte_corte_offset',
                          ),
                          'corte_utc' => 
                          array (
                            'clase' => 'escalar',
                            'tipos' => 
                            array (
                              0 => 'STRING',
                            ),
                            'columna' => 'p_contexto_fecha_corte_corte_utc',
                          ),
                          'corte_zona' => 
                          array (
                            'clase' => 'escalar',
                            'tipos' => 
                            array (
                              0 => 'STRING',
                            ),
                            'columna' => 'p_contexto_fecha_corte_corte_zona',
                          ),
                        ),
                        'columna' => 'p_contexto_fecha_corte',
                      ),
                      'residente' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'STRING',
                        ),
                        'columna' => 'p_contexto_residente',
                      ),
                      'usuario_ejecucion' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'STRING',
                        ),
                        'columna' => 'p_contexto_usuario_ejecucion',
                      ),
                      'version' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'STRING',
                        ),
                        'columna' => 'p_contexto_version',
                      ),
                    ),
                    'columna' => 'p_contexto',
                  ),
                  'contrato' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'p_contrato',
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
                            'tipos' => 
                            array (
                              0 => 'STRING',
                            ),
                            'columna' => 'p_gobierno_cabecera_cod_usuario_creacion',
                          ),
                          'cod_version_anterior' => 
                          array (
                            'clase' => 'escalar',
                            'tipos' => 
                            array (
                              0 => 'STRING',
                            ),
                            'columna' => 'p_gobierno_cabecera_cod_version_anterior',
                          ),
                          'cod_version_modelo' => 
                          array (
                            'clase' => 'escalar',
                            'tipos' => 
                            array (
                              0 => 'STRING',
                            ),
                            'columna' => 'p_gobierno_cabecera_cod_version_modelo',
                          ),
                          'codigo_version' => 
                          array (
                            'clase' => 'escalar',
                            'tipos' => 
                            array (
                              0 => 'STRING',
                            ),
                            'columna' => 'p_gobierno_cabecera_clave_version',
                          ),
                          'descripcion' => 
                          array (
                            'clase' => 'escalar',
                            'tipos' => 
                            array (
                              0 => 'STRING',
                            ),
                            'columna' => 'p_gobierno_cabecera_descripcion',
                          ),
                          'estado' => 
                          array (
                            'clase' => 'escalar',
                            'tipos' => 
                            array (
                              0 => 'STRING',
                            ),
                            'columna' => 'p_gobierno_cabecera_estado',
                          ),
                          'fecha_hora_creacion' => 
                          array (
                            'clase' => 'escalar',
                            'tipos' => 
                            array (
                              0 => 'STRING',
                            ),
                            'columna' => 'p_gobierno_cabecera_fecha_creacion',
                          ),
                          'fecha_hora_retiro' => 
                          array (
                            'clase' => 'escalar',
                            'tipos' => 
                            array (
                              0 => 'STRING',
                            ),
                            'columna' => 'p_gobierno_cabecera_fecha_retiro',
                          ),
                          'fecha_hora_vigencia' => 
                          array (
                            'clase' => 'escalar',
                            'tipos' => 
                            array (
                              0 => 'STRING',
                            ),
                            'columna' => 'p_gobierno_cabecera_fecha_vigencia',
                          ),
                          'motivo_cambio' => 
                          array (
                            'clase' => 'escalar',
                            'tipos' => 
                            array (
                              0 => 'STRING',
                            ),
                            'columna' => 'p_gobierno_cabecera_motivo_cambio',
                          ),
                          'nombre' => 
                          array (
                            'clase' => 'escalar',
                            'tipos' => 
                            array (
                              0 => 'STRING',
                            ),
                            'columna' => 'p_gobierno_cabecera_nombre',
                          ),
                          'observacion' => 
                          array (
                            'clase' => 'escalar',
                            'tipos' => 
                            array (
                              0 => 'STRING',
                            ),
                            'columna' => 'p_gobierno_cabecera_observacion',
                          ),
                        ),
                        'columna' => 'p_gobierno_cabecera',
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
                            ),
                            'columna' => 'p_gobierno_k_contratos_extraccion',
                          ),
                          'contratos_relaciones' => 
                          array (
                            'clase' => 'coleccion',
                            'modo' => 'lista',
                            'columna' => 'p_gobierno_k_contratos_relaciones',
                            'modelo' => 'c079',
                            'identidad' => 'COLECCION',
                          ),
                          'solo_pruebas_tecnicas' => 
                          array (
                            'clase' => 'escalar',
                            'tipos' => 
                            array (
                              0 => 'BOOL',
                            ),
                            'columna' => 'p_gobierno_k_solo_pruebas_tecnicas',
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
                                'columna' => 'p_gobierno_k_tablas_condiciones_regla_experta',
                                'modelo' => 'c081',
                                'identidad' => 'COLECCION',
                              ),
                              'consecuencias_regla_experta' => 
                              array (
                                'clase' => 'coleccion',
                                'modo' => 'lista',
                                'columna' => 'p_gobierno_k_tablas_consecuencias_regla_experta',
                                'modelo' => 'c082',
                                'identidad' => 'COLECCION',
                              ),
                              'criterios_dominios_resultado' => 
                              array (
                                'clase' => 'coleccion',
                                'modo' => 'lista',
                                'columna' => 'p_gobierno_k_tablas_criterios_dominios_r',
                                'modelo' => 'c083',
                                'identidad' => 'COLECCION',
                              ),
                              'dominios_valores_expertos' => 
                              array (
                                'clase' => 'coleccion',
                                'modo' => 'lista',
                                'columna' => 'p_gobierno_k_tablas_dominios_valores_expertos',
                                'modelo' => 'c084',
                                'identidad' => 'COLECCION',
                              ),
                              'fuentes_datos_expertas' => 
                              array (
                                'clase' => 'objeto',
                                'campos' => 
                                array (
                                ),
                                'columna' => 'p_gobierno_k_tablas_fuentes_datos_expertas',
                              ),
                              'mapeos_valores_fuente' => 
                              array (
                                'clase' => 'objeto',
                                'campos' => 
                                array (
                                ),
                                'columna' => 'p_gobierno_k_tablas_mapeos_valores_fuente',
                              ),
                              'mapeos_variables_fuente' => 
                              array (
                                'clase' => 'objeto',
                                'campos' => 
                                array (
                                ),
                                'columna' => 'p_gobierno_k_tablas_mapeos_variables_fuente',
                              ),
                              'nodos_semanticos' => 
                              array (
                                'clase' => 'coleccion',
                                'modo' => 'lista',
                                'columna' => 'p_gobierno_k_tablas_nodos_semanticos',
                                'modelo' => 'c085',
                                'identidad' => 'COLECCION',
                              ),
                              'reglas_expertas' => 
                              array (
                                'clase' => 'coleccion',
                                'modo' => 'lista',
                                'columna' => 'p_gobierno_k_tablas_reglas_expertas',
                                'modelo' => 'c086',
                                'identidad' => 'COLECCION',
                              ),
                              'relaciones_semanticas' => 
                              array (
                                'clase' => 'coleccion',
                                'modo' => 'lista',
                                'columna' => 'p_gobierno_k_tablas_relaciones_semanticas',
                                'modelo' => 'c087',
                                'identidad' => 'COLECCION',
                              ),
                              'valores_semanticos' => 
                              array (
                                'clase' => 'coleccion',
                                'modo' => 'lista',
                                'columna' => 'p_gobierno_k_tablas_valores_semanticos',
                                'modelo' => 'c088',
                                'identidad' => 'COLECCION',
                              ),
                              'variables_expertas' => 
                              array (
                                'clase' => 'coleccion',
                                'modo' => 'lista',
                                'columna' => 'p_gobierno_k_tablas_variables_expertas',
                                'modelo' => 'c089',
                                'identidad' => 'COLECCION',
                              ),
                            ),
                            'columna' => 'p_gobierno_k_tablas',
                          ),
                          'tipo_regla_resultado' => 
                          array (
                            'clase' => 'escalar',
                            'tipos' => 
                            array (
                              0 => 'STRING',
                            ),
                            'columna' => 'p_gobierno_k_tipo_regla_r',
                          ),
                          'version' => 
                          array (
                            'clase' => 'escalar',
                            'tipos' => 
                            array (
                              0 => 'STRING',
                            ),
                            'columna' => 'p_gobierno_k_version',
                          ),
                        ),
                        'columna' => 'p_gobierno_k',
                      ),
                    ),
                    'columna' => 'p_gobierno',
                  ),
                  'limites' => 
                  array (
                    'clase' => 'objeto',
                    'campos' => 
                    array (
                      'persistido' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'BOOL',
                        ),
                        'columna' => 'p_limites_persistido',
                      ),
                      'reproduce_adquisicion_sql' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'BOOL',
                        ),
                        'columna' => 'p_limites_reproduce_adquisicion_sql',
                      ),
                      'revalida_bytes_documentales' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'BOOL',
                        ),
                        'columna' => 'p_limites_revalida_bytes_documentales',
                      ),
                      'revalida_permisos_historicos' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'BOOL',
                        ),
                        'columna' => 'p_limites_revalida_permisos_historicos',
                      ),
                    ),
                    'columna' => 'p_limites',
                  ),
                  'membresias_soportes' => 
                  array (
                    'clase' => 'coleccion',
                    'modo' => 'lista',
                    'columna' => 'p_membresias_soportes',
                    'modelo' => 'c090',
                    'identidad' => 'COLECCION',
                  ),
                  'procedencia_valoraciones' => 
                  array (
                    'clase' => 'coleccion',
                    'modo' => 'lista',
                    'columna' => 'p_procedencia_valoraciones',
                    'modelo' => 'c092',
                    'identidad' => 'COLECCION',
                  ),
                  'salida' => 
                  array (
                    'clase' => 'objeto',
                    'campos' => 
                    array (
                      'contrato' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'STRING',
                        ),
                        'columna' => 'p_s_contrato',
                      ),
                      'dominios' => 
                      array (
                        'clase' => 'coleccion',
                        'modo' => 'mapa',
                        'columna' => 'p_s_dominios',
                        'modelo' => 'c093',
                        'identidad' => 'COLECCION',
                      ),
                      'estado' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'STRING',
                        ),
                        'columna' => 'p_s_estado',
                      ),
                      'evaluacion' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'STRING',
                        ),
                        'columna' => 'p_s_evaluacion',
                      ),
                      'fecha_corte' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'STRING',
                        ),
                        'columna' => 'p_s_fecha_corte',
                      ),
                      'gobierno' => 
                      array (
                        'clase' => 'objeto',
                        'campos' => 
                        array (
                          'bloqueada_por' => 
                          array (
                            'clase' => 'coleccion',
                            'modo' => 'lista',
                            'columna' => 'p_s_gobierno_bloqueada_por',
                            'modelo' => 'c096',
                            'identidad' => 'COLECCION',
                          ),
                          'codigo_version' => 
                          array (
                            'clase' => 'escalar',
                            'tipos' => 
                            array (
                              0 => 'STRING',
                            ),
                            'columna' => 'p_s_gobierno_clave_version',
                          ),
                          'disponible' => 
                          array (
                            'clase' => 'escalar',
                            'tipos' => 
                            array (
                              0 => 'BOOL',
                            ),
                            'columna' => 'p_s_gobierno_disponible',
                          ),
                          'estados_derivados' => 
                          array (
                            'clase' => 'coleccion',
                            'modo' => 'mapa',
                            'columna' => 'p_s_gobierno_estados_derivados',
                            'modelo' => 'c097',
                            'identidad' => 'COLECCION',
                          ),
                          'origen' => 
                          array (
                            'clase' => 'escalar',
                            'tipos' => 
                            array (
                              0 => 'STRING',
                            ),
                            'columna' => 'p_s_gobierno_origen',
                          ),
                          'patron' => 
                          array (
                            'clase' => 'escalar',
                            'tipos' => 
                            array (
                              0 => 'STRING',
                            ),
                            'columna' => 'p_s_gobierno_patron',
                          ),
                          'persistido' => 
                          array (
                            'clase' => 'escalar',
                            'tipos' => 
                            array (
                              0 => 'BOOL',
                            ),
                            'columna' => 'p_s_gobierno_persistido',
                          ),
                          'reglas' => 
                          array (
                            'clase' => 'coleccion',
                            'modo' => 'lista',
                            'columna' => 'p_s_gobierno_reglas',
                            'modelo' => 'c098',
                            'identidad' => 'COLECCION',
                          ),
                          'version' => 
                          array (
                            'clase' => 'escalar',
                            'tipos' => 
                            array (
                              0 => 'STRING',
                            ),
                            'columna' => 'p_s_gobierno_version',
                          ),
                        ),
                        'columna' => 'p_s_gobierno',
                      ),
                      'impedimentos' => 
                      array (
                        'clase' => 'coleccion',
                        'modo' => 'lista',
                        'columna' => 'p_s_impedimentos',
                        'modelo' => 'c101',
                        'identidad' => 'COLECCION',
                      ),
                      'patron' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'STRING',
                        ),
                        'columna' => 'p_s_patron',
                      ),
                      'persistido' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'BOOL',
                        ),
                        'columna' => 'p_s_persistido',
                      ),
                      'requisitos' => 
                      array (
                        'clase' => 'objeto',
                        'campos' => 
                        array (
                          'C01' => 
                          array (
                            'clase' => 'objeto',
                            'campos' => 
                            array (
                              'estado' => 
                              array (
                                'clase' => 'escalar',
                                'tipos' => 
                                array (
                                  0 => 'STRING',
                                ),
                                'columna' => 'p_s_requisitos_c01_estado',
                              ),
                              'indices_valoraciones' => 
                              array (
                                'clase' => 'coleccion',
                                'modo' => 'lista',
                                'columna' => 'p_s_requisitos_c01_indices_valoraciones',
                                'modelo' => 'c102',
                                'identidad' => 'COLECCION',
                              ),
                              'motivos' => 
                              array (
                                'clase' => 'coleccion',
                                'modo' => 'lista',
                                'columna' => 'p_s_requisitos_c01_motivos',
                                'modelo' => 'c103',
                                'identidad' => 'COLECCION',
                              ),
                            ),
                            'columna' => 'p_s_requisitos_c01',
                          ),
                          'C02' => 
                          array (
                            'clase' => 'objeto',
                            'campos' => 
                            array (
                              'derivado_de' => 
                              array (
                                'clase' => 'coleccion',
                                'modo' => 'lista',
                                'columna' => 'p_s_requisitos_c02_derivado_de',
                                'modelo' => 'c104',
                                'identidad' => 'COLECCION',
                              ),
                              'estado' => 
                              array (
                                'clase' => 'escalar',
                                'tipos' => 
                                array (
                                  0 => 'STRING',
                                ),
                                'columna' => 'p_s_requisitos_c02_estado',
                              ),
                              'indices_valoraciones' => 
                              array (
                                'clase' => 'objeto',
                                'campos' => 
                                array (
                                ),
                                'columna' => 'p_s_requisitos_c02_indices_valoraciones',
                              ),
                              'motivos' => 
                              array (
                                'clase' => 'coleccion',
                                'modo' => 'lista',
                                'columna' => 'p_s_requisitos_c02_motivos',
                                'modelo' => 'c105',
                                'identidad' => 'COLECCION',
                              ),
                            ),
                            'columna' => 'p_s_requisitos_c02',
                          ),
                          'C03' => 
                          array (
                            'clase' => 'objeto',
                            'campos' => 
                            array (
                              'estado' => 
                              array (
                                'clase' => 'escalar',
                                'tipos' => 
                                array (
                                  0 => 'STRING',
                                ),
                                'columna' => 'p_s_requisitos_c03_estado',
                              ),
                              'indices_valoraciones' => 
                              array (
                                'clase' => 'coleccion',
                                'modo' => 'lista',
                                'columna' => 'p_s_requisitos_c03_indices_valoraciones',
                                'modelo' => 'c106',
                                'identidad' => 'COLECCION',
                              ),
                              'motivos' => 
                              array (
                                'clase' => 'coleccion',
                                'modo' => 'lista',
                                'columna' => 'p_s_requisitos_c03_motivos',
                                'modelo' => 'c107',
                                'identidad' => 'COLECCION',
                              ),
                            ),
                            'columna' => 'p_s_requisitos_c03',
                          ),
                          'C04' => 
                          array (
                            'clase' => 'objeto',
                            'campos' => 
                            array (
                              'estado' => 
                              array (
                                'clase' => 'escalar',
                                'tipos' => 
                                array (
                                  0 => 'STRING',
                                ),
                                'columna' => 'p_s_requisitos_c04_estado',
                              ),
                              'indices_valoraciones' => 
                              array (
                                'clase' => 'coleccion',
                                'modo' => 'lista',
                                'columna' => 'p_s_requisitos_c04_indices_valoraciones',
                                'modelo' => 'c108',
                                'identidad' => 'COLECCION',
                              ),
                              'motivos' => 
                              array (
                                'clase' => 'coleccion',
                                'modo' => 'lista',
                                'columna' => 'p_s_requisitos_c04_motivos',
                                'modelo' => 'c109',
                                'identidad' => 'COLECCION',
                              ),
                            ),
                            'columna' => 'p_s_requisitos_c04',
                          ),
                          'C05' => 
                          array (
                            'clase' => 'objeto',
                            'campos' => 
                            array (
                              'estado' => 
                              array (
                                'clase' => 'escalar',
                                'tipos' => 
                                array (
                                  0 => 'STRING',
                                ),
                                'columna' => 'p_s_requisitos_c05_estado',
                              ),
                              'indices_valoraciones' => 
                              array (
                                'clase' => 'coleccion',
                                'modo' => 'lista',
                                'columna' => 'p_s_requisitos_c05_indices_valoraciones',
                                'modelo' => 'c110',
                                'identidad' => 'COLECCION',
                              ),
                              'motivos' => 
                              array (
                                'clase' => 'coleccion',
                                'modo' => 'lista',
                                'columna' => 'p_s_requisitos_c05_motivos',
                                'modelo' => 'c111',
                                'identidad' => 'COLECCION',
                              ),
                            ),
                            'columna' => 'p_s_requisitos_c05',
                          ),
                        ),
                        'columna' => 'p_s_requisitos',
                      ),
                      'residente' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'STRING',
                        ),
                        'columna' => 'p_s_residente',
                      ),
                      'revision_profesional' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'STRING',
                        ),
                        'columna' => 'p_s_revision_profesional',
                      ),
                      'valoraciones_excluidas' => 
                      array (
                        'clase' => 'coleccion',
                        'modo' => 'lista',
                        'columna' => 'p_s_valoraciones_excluidas',
                        'modelo' => 'c112',
                        'identidad' => 'COLECCION',
                      ),
                      'version' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'STRING',
                        ),
                        'columna' => 'p_s_version',
                      ),
                    ),
                    'columna' => 'p_s',
                  ),
                  'valoraciones' => 
                  array (
                    'clase' => 'coleccion',
                    'modo' => 'lista',
                    'columna' => 'p_valoraciones',
                    'modelo' => 'c115',
                    'identidad' => 'COLECCION',
                  ),
                ),
                'columna' => 'p',
              ),
              'huella' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'petersen_huella',
              ),
            ),
            'columna' => 'petersen',
          ),
          'release' => 
          array (
            'clase' => 'objeto',
            'campos' => 
            array (
              'archivos' => 
              array (
                'clase' => 'coleccion',
                'modo' => 'mapa',
                'columna' => 'release_archivos',
                'modelo' => 'c127',
                'identidad' => 'COLECCION',
              ),
              'contrato' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'release_contrato',
              ),
              'huella' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'release_huella',
              ),
              'incluye_datos_clinicos' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'BOOL',
                ),
                'columna' => 'release_incluye_datos_clinicos',
              ),
              'php_requerido' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'release_php_requerido',
              ),
              'revision_base' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'release_revision_base',
              ),
            ),
            'columna' => 'release',
          ),
          'release_retenido' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'BOOL',
            ),
            'columna' => 'release_retenido',
          ),
        ),
        'columna' => 'dto',
      ),
    ),
    'c002' => 
    array (
      'tabla' => 'ret_contenido_capturas_capturas_c002',
      'padre' => 'c001',
      'ruta' => '/instrumental/contenido/capturas/capturas/*',
      'fk_propietario' => 'cod_propietario',
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
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'aplicacion_actual_clasificacion',
              ),
              'cod_aplicacion' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'aplicacion_actual_cod_aplicacion',
              ),
              'cod_aplicacion_anterior' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'aplicacion_actual_cod_aplicacion_anterior',
              ),
              'cod_atencion' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'aplicacion_actual_cod_atencion',
              ),
              'cod_documento_clinico' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'aplicacion_actual_cod_documento_clinico',
              ),
              'cod_instrumento' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'aplicacion_actual_cod_instrumento',
              ),
              'cod_personal' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'aplicacion_actual_cod_personal',
              ),
              'cod_residente' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'aplicacion_actual_cod_residente',
              ),
              'cod_usuario_registro' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'aplicacion_actual_cod_usuario_registro',
              ),
              'estado' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'aplicacion_actual_estado',
              ),
              'fecha_hora' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'aplicacion_actual_fecha',
              ),
              'interpretacion' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'aplicacion_actual_interpretacion',
              ),
              'localizador_fuente' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'aplicacion_actual_localizador_fuente',
              ),
              'observacion' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'aplicacion_actual_observacion',
              ),
              'puntaje_maximo' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'aplicacion_actual_puntaje_maximo',
              ),
              'puntaje_total' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'aplicacion_actual_puntaje_total',
              ),
              'registrado_en' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'aplicacion_actual_registrado_en',
              ),
            ),
            'columna' => 'aplicacion_actual',
          ),
          'atencion_original' => 
          array (
            'clase' => 'objeto',
            'campos' => 
            array (
              'cod_atencion' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'atencion_original_cod_atencion',
              ),
              'cod_residente' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'atencion_original_cod_residente',
              ),
            ),
            'columna' => 'atencion_original',
          ),
          'autor' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'autor',
          ),
          'cod_aplicacion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_aplicacion',
          ),
          'cod_instrumento' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_instrumento',
          ),
          'cod_personal' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_personal',
          ),
          'cod_residente' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_residente',
          ),
          'codigo_instrumento' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'clave_instrumento',
          ),
          'documento_original' => 
          array (
            'clase' => 'objeto',
            'campos' => 
            array (
              'cod_documento_clinico' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'documento_original_cod_documento_clinico',
              ),
              'cod_personal' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'documento_original_cod_personal',
              ),
              'cod_residente' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'documento_original_cod_residente',
              ),
              'estado' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'documento_original_estado',
              ),
              'fecha_hora' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'documento_original_fecha',
              ),
              'hash_archivo' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'documento_original_hash_archivo',
              ),
            ),
            'columna' => 'documento_original',
          ),
          'edad_en_aplicacion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'INT',
            ),
            'columna' => 'edad_en_aplicacion',
          ),
          'estado_original' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'estado_original',
          ),
          'extraccion' => 
          array (
            'clase' => 'objeto',
            'campos' => 
            array (
              'aplicabilidad_geriatrica' => 
              array (
                'clase' => 'objeto',
                'campos' => 
                array (
                  'alcance_etario_compatible' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'BOOL',
                    ),
                    'columna' => 'ext_apl_ger_alc_eta_com',
                  ),
                  'autoriza_interpretacion_clinica' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'BOOL',
                    ),
                    'columna' => 'ext_apl_ger_aut_int_cli',
                  ),
                  'edad_en_aplicacion' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'INT',
                    ),
                    'columna' => 'ext_apl_ger_eda_en_apl',
                  ),
                  'escolaridad_original' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'INT',
                    ),
                    'columna' => 'ext_apl_ger_esc_ori',
                  ),
                  'fuente' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'extraccion_aplicabilidad_geriatrica_fuente',
                  ),
                  'idioma_declarado' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'ext_apl_ger_idi_dec',
                  ),
                  'motivos' => 
                  array (
                    'clase' => 'objeto',
                    'campos' => 
                    array (
                    ),
                    'columna' => 'extraccion_aplicabilidad_geriatrica_motivos',
                  ),
                  'pertenece_a_poblacion_objetivo' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'BOOL',
                    ),
                    'columna' => 'ext_apl_ger_per_a_pob_obj',
                  ),
                  'poblacion_objetivo' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'ext_apl_ger_pob_obj',
                  ),
                  'rango_edad_referencia' => 
                  array (
                    'clase' => 'objeto',
                    'campos' => 
                    array (
                      'maxima' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'INT',
                        ),
                        'columna' => 'ext_apl_ger_ran_eda_ref_max',
                      ),
                      'minima' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'INT',
                        ),
                        'columna' => 'ext_apl_ger_ran_eda_ref_min',
                      ),
                    ),
                    'columna' => 'ext_apl_ger_ran_eda_ref',
                  ),
                  'requiere_verificar' => 
                  array (
                    'clase' => 'coleccion',
                    'modo' => 'lista',
                    'columna' => 'ext_apl_ger_req_ver',
                    'modelo' => 'c003',
                    'identidad' => 'COLECCION',
                  ),
                ),
                'columna' => 'extraccion_aplicabilidad_geriatrica',
              ),
              'captura' => 
              array (
                'clase' => 'objeto',
                'campos' => 
                array (
                  'aciertos_clave_semantica' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'INT',
                    ),
                    'columna' => 'extraccion_captura_aciertos_clave_semantica',
                  ),
                  'aciertos_espontaneos' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'INT',
                    ),
                    'columna' => 'extraccion_captura_aciertos_espontaneos',
                  ),
                  'administracion_estandarizada' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'BOOL',
                    ),
                    'columna' => 'extraccion_captura_administracion_estandarizada',
                  ),
                  'audicion_compatible' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'BOOL',
                    ),
                    'columna' => 'extraccion_captura_audicion_compatible',
                  ),
                  'condiciones_sensoriales_motoras_compatibles' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'BOOL',
                    ),
                    'columna' => 'extraccion_captura_condiciones_sensoriales',
                  ),
                  'demora_minutos' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'FLOAT',
                    ),
                    'columna' => 'extraccion_captura_demora_minutos',
                  ),
                  'edad_anios' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'INT',
                    ),
                    'columna' => 'extraccion_captura_edad_anios',
                  ),
                  'edicion_protocolo' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'extraccion_captura_edicion_protocolo',
                  ),
                  'elementos_revisados' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'INT',
                    ),
                    'columna' => 'extraccion_captura_elementos_revisados',
                  ),
                  'ensayo_1' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'INT',
                    ),
                    'columna' => 'extraccion_captura_ensayo_1',
                  ),
                  'ensayo_2' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'INT',
                    ),
                    'columna' => 'extraccion_captura_ensayo_2',
                  ),
                  'ensayo_3' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'INT',
                    ),
                    'columna' => 'extraccion_captura_ensayo_3',
                  ),
                  'ensayos' => 
                  array (
                    'clase' => 'coleccion',
                    'modo' => 'lista',
                    'columna' => 'extraccion_captura_ensayos',
                    'modelo' => 'c004',
                    'identidad' => 'COLECCION',
                  ),
                  'errores_observados' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'INT',
                    ),
                    'columna' => 'extraccion_captura_errores_observados',
                  ),
                  'escolaridad_anios' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'INT',
                    ),
                    'columna' => 'extraccion_captura_escolaridad_anios',
                  ),
                  'espanol_nativo' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'BOOL',
                    ),
                    'columna' => 'extraccion_captura_espanol_nativo',
                  ),
                  'finalizada' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'BOOL',
                    ),
                    'columna' => 'extraccion_captura_finalizada',
                  ),
                  'forma' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'INT',
                    ),
                    'columna' => 'extraccion_captura_forma',
                  ),
                  'forma_l_aciertos' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'INT',
                    ),
                    'columna' => 'extraccion_captura_forma_l_aciertos',
                  ),
                  'forma_n_aciertos' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'INT',
                    ),
                    'columna' => 'extraccion_captura_forma_n_aciertos',
                  ),
                  'idioma' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'extraccion_captura_idioma',
                  ),
                  'instrumento' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'extraccion_captura_instrumento',
                  ),
                  'lectoescritura' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'BOOL',
                    ),
                    'columna' => 'extraccion_captura_lectoescritura',
                  ),
                  'nacimiento_bolivia' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'BOOL',
                    ),
                    'columna' => 'extraccion_captura_nacimiento_bolivia',
                  ),
                  'protocolo' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'extraccion_captura_protocolo',
                  ),
                  'puntaje_copia' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'FLOAT',
                    ),
                    'columna' => 'extraccion_captura_puntaje_copia',
                  ),
                  'recuerdo_diferido' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'INT',
                    ),
                    'columna' => 'extraccion_captura_recuerdo_diferido',
                  ),
                  'residencia_bolivia' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'BOOL',
                    ),
                    'columna' => 'extraccion_captura_residencia_bolivia',
                  ),
                  'segundos_parte_b' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'FLOAT',
                    ),
                    'columna' => 'extraccion_captura_segundos_parte_b',
                  ),
                ),
                'columna' => 'extraccion_captura',
              ),
              'fuentes_entradas' => 
              array (
                'clase' => 'coleccion',
                'modo' => 'mapa',
                'columna' => 'extraccion_fuentes_entradas',
                'modelo' => 'c005',
                'identidad' => 'COLECCION',
              ),
              'identidad_declarada' => 
              array (
                'clase' => 'objeto',
                'campos' => 
                array (
                  'edicion_protocolo' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'extraccion_identidad_declarada_edicion_protocolo',
                  ),
                  'forma' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'INT',
                    ),
                    'columna' => 'extraccion_identidad_declarada_forma',
                  ),
                  'idioma' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'extraccion_identidad_declarada_idioma',
                  ),
                  'instrumento' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'extraccion_identidad_declarada_instrumento',
                  ),
                ),
                'columna' => 'extraccion_identidad_declarada',
              ),
              'identidad_requerida_metodo' => 
              array (
                'clase' => 'objeto',
                'campos' => 
                array (
                  'edicion_protocolo' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'ext_ide_req_met_edi_pro',
                  ),
                  'forma' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'INT',
                    ),
                    'columna' => 'extraccion_identidad_requerida_metodo_forma',
                  ),
                  'idioma' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'extraccion_identidad_requerida_metodo_idioma',
                  ),
                  'instrumento' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'extraccion_identidad_requerida_metodo_instrumento',
                  ),
                ),
                'columna' => 'extraccion_identidad_requerida_metodo',
              ),
              'metodo' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'extraccion_metodo',
              ),
              'motivos_captura' => 
              array (
                'clase' => 'objeto',
                'campos' => 
                array (
                ),
                'columna' => 'extraccion_motivos_captura',
              ),
              'normalizacion' => 
              array (
                'clase' => 'objeto',
                'campos' => 
                array (
                  'bruto' => 
                  array (
                    'clase' => 'objeto',
                    'campos' => 
                    array (
                      'aciertos_clave_semantica' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'INT',
                        ),
                        'columna' => 'ext_nor_bru_aci_cla_sem',
                      ),
                      'aciertos_espontaneos' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'INT',
                        ),
                        'columna' => 'ext_nor_bru_aci_esp',
                      ),
                      'administracion_estandarizada' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'BOOL',
                        ),
                        'columna' => 'ext_nor_bru_adm_est',
                      ),
                      'audicion_compatible' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'BOOL',
                        ),
                        'columna' => 'ext_nor_bru_aud_com',
                      ),
                      'condiciones_sensoriales_motoras_compatibles' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'BOOL',
                        ),
                        'columna' => 'ext_nor_bru_con_sen',
                      ),
                      'demora_minutos' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'FLOAT',
                        ),
                        'columna' => 'extraccion_normalizacion_bruto_demora_minutos',
                      ),
                      'elementos_revisados' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'INT',
                        ),
                        'columna' => 'ext_nor_bru_ele_rev',
                      ),
                      'ensayos' => 
                      array (
                        'clase' => 'coleccion',
                        'modo' => 'lista',
                        'columna' => 'extraccion_normalizacion_bruto_ensayos',
                        'modelo' => 'c006',
                        'identidad' => 'COLECCION',
                      ),
                      'errores_observados' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'INT',
                        ),
                        'columna' => 'extraccion_normalizacion_bruto_errores_observados',
                      ),
                      'escolaridad_anios' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'INT',
                        ),
                        'columna' => 'extraccion_normalizacion_bruto_escolaridad_anios',
                      ),
                      'espanol_nativo' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'BOOL',
                        ),
                        'columna' => 'extraccion_normalizacion_bruto_espanol_nativo',
                      ),
                      'finalizada' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'BOOL',
                        ),
                        'columna' => 'extraccion_normalizacion_bruto_finalizada',
                      ),
                      'forma_l_aciertos' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'INT',
                        ),
                        'columna' => 'extraccion_normalizacion_bruto_forma_l_aciertos',
                      ),
                      'forma_n_aciertos' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'INT',
                        ),
                        'columna' => 'extraccion_normalizacion_bruto_forma_n_aciertos',
                      ),
                      'lectoescritura' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'BOOL',
                        ),
                        'columna' => 'extraccion_normalizacion_bruto_lectoescritura',
                      ),
                      'nacimiento_bolivia' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'BOOL',
                        ),
                        'columna' => 'extraccion_normalizacion_bruto_nacimiento_bolivia',
                      ),
                      'puntaje_copia' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'FLOAT',
                        ),
                        'columna' => 'extraccion_normalizacion_bruto_puntaje_copia',
                      ),
                      'recuerdo_diferido' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'INT',
                        ),
                        'columna' => 'extraccion_normalizacion_bruto_recuerdo_diferido',
                      ),
                      'residencia_bolivia' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'BOOL',
                        ),
                        'columna' => 'extraccion_normalizacion_bruto_residencia_bolivia',
                      ),
                      'segundos_parte_b' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'FLOAT',
                        ),
                        'columna' => 'extraccion_normalizacion_bruto_segundos_parte_b',
                      ),
                    ),
                    'columna' => 'extraccion_normalizacion_bruto',
                  ),
                  'componentes' => 
                  array (
                    'clase' => 'coleccion',
                    'modo' => 'mapa',
                    'columna' => 'extraccion_normalizacion_componentes',
                    'modelo' => 'c007',
                    'identidad' => 'COLECCION',
                  ),
                  'contexto_aplicacion' => 
                  array (
                    'clase' => 'objeto',
                    'campos' => 
                    array (
                      'aciertos_clave_semantica' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'INT',
                        ),
                        'columna' => 'ext_nor_con_apl_aci_cla_sem',
                      ),
                      'aciertos_espontaneos' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'INT',
                        ),
                        'columna' => 'ext_nor_con_apl_aci_esp',
                      ),
                      'administracion_estandarizada' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'BOOL',
                        ),
                        'columna' => 'ext_nor_con_apl_adm_est',
                      ),
                      'audicion_compatible' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'BOOL',
                        ),
                        'columna' => 'ext_nor_con_apl_aud_com',
                      ),
                      'condiciones_sensoriales_motoras_compatibles' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'BOOL',
                        ),
                        'columna' => 'ext_nor_con_apl_con_sen',
                      ),
                      'edad_anios' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'INT',
                        ),
                        'columna' => 'ext_nor_con_apl_eda_ani',
                      ),
                      'edicion_protocolo' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'STRING',
                        ),
                        'columna' => 'ext_nor_con_apl_edi_pro',
                      ),
                      'elementos_revisados' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'INT',
                        ),
                        'columna' => 'ext_nor_con_apl_ele_rev',
                      ),
                      'errores_observados' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'INT',
                        ),
                        'columna' => 'ext_nor_con_apl_err_obs',
                      ),
                      'escolaridad_anios' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'INT',
                        ),
                        'columna' => 'ext_nor_con_apl_esc_ani',
                      ),
                      'espanol_nativo' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'BOOL',
                        ),
                        'columna' => 'ext_nor_con_apl_esp_nat',
                      ),
                      'finalizada' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'BOOL',
                        ),
                        'columna' => 'ext_nor_con_apl_fin',
                      ),
                      'forma' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'INT',
                        ),
                        'columna' => 'ext_nor_con_apl_for',
                      ),
                      'forma_l_aciertos' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'INT',
                        ),
                        'columna' => 'ext_nor_con_apl_for_l_aci',
                      ),
                      'forma_n_aciertos' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'INT',
                        ),
                        'columna' => 'ext_nor_con_apl_for_n_aci',
                      ),
                      'idioma' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'STRING',
                        ),
                        'columna' => 'ext_nor_con_apl_idi',
                      ),
                      'instrumento' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'STRING',
                        ),
                        'columna' => 'ext_nor_con_apl_ins',
                      ),
                      'lectoescritura' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'BOOL',
                        ),
                        'columna' => 'ext_nor_con_apl_lec',
                      ),
                      'nacimiento_bolivia' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'BOOL',
                        ),
                        'columna' => 'ext_nor_con_apl_nac_bol',
                      ),
                      'protocolo' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'STRING',
                        ),
                        'columna' => 'ext_nor_con_apl_pro',
                      ),
                      'puntaje_copia' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'FLOAT',
                        ),
                        'columna' => 'ext_nor_con_apl_pun_cop',
                      ),
                      'residencia_bolivia' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'BOOL',
                        ),
                        'columna' => 'ext_nor_con_apl_res_bol',
                      ),
                      'segundos_parte_b' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'FLOAT',
                        ),
                        'columna' => 'ext_nor_con_apl_seg_par_b',
                      ),
                    ),
                    'columna' => 'extraccion_normalizacion_contexto_aplicacion',
                  ),
                  'diagnostico' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'extraccion_normalizacion_diagnostico',
                  ),
                  'limite' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'extraccion_normalizacion_limite',
                  ),
                  'localizador' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'extraccion_normalizacion_localizador',
                  ),
                  'metodo' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'extraccion_normalizacion_metodo',
                  ),
                  'referencia' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'extraccion_normalizacion_referencia',
                  ),
                  'revision_profesional' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'extraccion_normalizacion_revision_profesional',
                  ),
                ),
                'columna' => 'extraccion_normalizacion',
              ),
              'perfil' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'extraccion_perfil',
              ),
            ),
            'columna' => 'extraccion',
          ),
          'fecha_hora' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'fecha',
          ),
          'fuente_edad' => 
          array (
            'clase' => 'objeto',
            'campos' => 
            array (
              'campo' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'fuente_edad_campo',
              ),
              'fecha_aplicacion' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'fuente_edad_fecha_aplicacion',
              ),
              'fecha_nacimiento' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'fuente_edad_fecha_nacimiento',
              ),
              'registro' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'fuente_edad_registro',
              ),
              'tabla' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'fuente_edad_tabla',
              ),
            ),
            'columna' => 'fuente_edad',
          ),
          'instrumento_actual' => 
          array (
            'clase' => 'objeto',
            'campos' => 
            array (
              'cod_instrumento' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'instrumento_actual_cod_instrumento',
              ),
              'codigo' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'instrumento_actual_codigo',
              ),
              'descripcion' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'instrumento_actual_descripcion',
              ),
              'estado' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'instrumento_actual_estado',
              ),
              'nombre' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'instrumento_actual_nombre',
              ),
              'puntaje_maximo' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'instrumento_actual_puntaje_maximo',
              ),
              'tipo' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'instrumento_actual_tipo',
              ),
              'version' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'instrumento_actual_version',
              ),
            ),
            'columna' => 'instrumento_actual',
          ),
          'motivos' => 
          array (
            'clase' => 'coleccion',
            'modo' => 'lista',
            'columna' => 'motivos',
            'modelo' => 'c008',
            'identidad' => 'COLECCION',
          ),
          'nombre_instrumento' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'nombre_instrumento',
          ),
          'perfil_captura_original' => 
          array (
            'clase' => 'objeto',
            'campos' => 
            array (
              'cod_instrumento' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'perfil_captura_original_cod_instrumento',
              ),
              'codigo_instrumento' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'perfil_captura_original_clave_instrumento',
              ),
              'id' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'perfil_captura_original_id',
              ),
              'identidad_instrumento' => 
              array (
                'clase' => 'objeto',
                'campos' => 
                array (
                  'edicion_protocolo' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'per_cap_ori_ide_ins_edi_pro',
                  ),
                  'forma' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'INT',
                    ),
                    'columna' => 'per_cap_ori_ide_ins_for',
                  ),
                  'idioma' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'per_cap_ori_ide_ins_idi',
                  ),
                  'instrumento' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'per_cap_ori_ide_ins_ins',
                  ),
                ),
                'columna' => 'perfil_captura_original_identidad_instrumento',
              ),
              'metodo' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'perfil_captura_original_metodo',
              ),
              'selectores' => 
              array (
                'clase' => 'coleccion',
                'modo' => 'mapa',
                'columna' => 'perfil_captura_original_selectores',
                'modelo' => 'c009',
                'identidad' => 'COLECCION',
              ),
              'version_instrumento' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'perfil_captura_original_version_instrumento',
              ),
            ),
            'columna' => 'perfil_captura_original',
          ),
          'preguntas_declarativas_originales' => 
          array (
            'clase' => 'coleccion',
            'modo' => 'mapa',
            'columna' => 'preguntas_declarativas_originales',
            'modelo' => 'c010',
            'identidad' => 'COLECCION',
          ),
          'procedencia' => 
          array (
            'clase' => 'objeto',
            'campos' => 
            array (
              'cod_aplicacion_anterior' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'procedencia_cod_aplicacion_anterior',
              ),
              'cod_documento_clinico' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'procedencia_cod_documento_clinico',
              ),
              'cod_usuario_registro' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'procedencia_cod_usuario_registro',
              ),
              'localizador_fuente' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'procedencia_localizador_fuente',
              ),
              'registrado_en' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'procedencia_registrado_en',
              ),
            ),
            'columna' => 'procedencia',
          ),
          'respuestas_originales' => 
          array (
            'clase' => 'coleccion',
            'modo' => 'lista',
            'columna' => 'respuestas_originales',
            'modelo' => 'c011',
            'identidad' => 'COLECCION',
          ),
          'version_instrumento' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'version_instrumento',
          ),
          'vinculos_preguntas_actuales' => 
          array (
            'clase' => 'coleccion',
            'modo' => 'mapa',
            'columna' => 'vinculos_preguntas_actuales',
            'modelo' => 'c012',
            'identidad' => 'COLECCION',
          ),
        ),
        'columna' => 'dto',
      ),
    ),
    'c003' => 
    array (
      'tabla' => 'ret_extraccion_aplicabilidad_geriatrica_re_c003',
      'padre' => 'c002',
      'ruta' => '/instrumental/contenido/capturas/capturas/*/extraccion/aplicabilidad_geriatrica/requiere_verificar/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'escalar',
        'tipos' => 
        array (
          0 => 'STRING',
        ),
        'columna' => 'dto',
      ),
    ),
    'c004' => 
    array (
      'tabla' => 'ret_extraccion_captura_ensayos_c004',
      'padre' => 'c002',
      'ruta' => '/instrumental/contenido/capturas/capturas/*/extraccion/captura/ensayos/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'escalar',
        'tipos' => 
        array (
          0 => 'INT',
        ),
        'columna' => 'dto',
      ),
    ),
    'c005' => 
    array (
      'tabla' => 'ret_capturas_extraccion_fuentes_entradas_c005',
      'padre' => 'c002',
      'ruta' => '/instrumental/contenido/capturas/capturas/*/extraccion/fuentes_entradas/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'aplicacion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'aplicacion',
          ),
          'bruto' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'BOOL',
              1 => 'STRING',
            ),
            'columna' => 'bruto',
          ),
          'campo' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'campo',
          ),
          'pregunta' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'pregunta',
          ),
          'respuesta' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'respuesta',
          ),
          'valor' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'BOOL',
              1 => 'FLOAT',
              2 => 'INT',
            ),
            'columna' => 'valor',
          ),
        ),
        'columna' => 'dto',
      ),
    ),
    'c006' => 
    array (
      'tabla' => 'ret_normalizacion_bruto_ensayos_c006',
      'padre' => 'c002',
      'ruta' => '/instrumental/contenido/capturas/capturas/*/extraccion/normalizacion/bruto/ensayos/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'escalar',
        'tipos' => 
        array (
          0 => 'INT',
        ),
        'columna' => 'dto',
      ),
    ),
    'c007' => 
    array (
      'tabla' => 'ret_extraccion_normalizacion_componentes_c007',
      'padre' => 'c002',
      'ruta' => '/instrumental/contenido/capturas/capturas/*/extraccion/normalizacion/componentes/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'clasificacion_clinica' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'clasificacion_clinica',
          ),
          'de_residual' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'FLOAT',
            ),
            'columna' => 'de_residual',
          ),
          'direccion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'direccion',
          ),
          'esperado' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'FLOAT',
            ),
            'columna' => 'esperado',
          ),
          'estado' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'estado',
          ),
          'interpretacion_matematica' => 
          array (
            'clase' => 'objeto',
            'campos' => 
            array (
              'clasificacion_clinica' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'interpretacion_matematica_clasificacion_clinica',
              ),
              'contrato' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'interpretacion_matematica_contrato',
              ),
              'estado' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'interpretacion_matematica_estado',
              ),
              'limite' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'interpretacion_matematica_limite',
              ),
              'posicion' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'interpretacion_matematica_posicion',
              ),
              'texto' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'interpretacion_matematica_texto',
              ),
            ),
            'columna' => 'interpretacion_matematica',
          ),
          'medida' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'medida',
          ),
          'motivos' => 
          array (
            'clase' => 'objeto',
            'campos' => 
            array (
            ),
            'columna' => 'motivos',
          ),
          'puntuacion_bruta' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'FLOAT',
              1 => 'INT',
            ),
            'columna' => 'puntuacion_bruta',
          ),
          'z' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'FLOAT',
            ),
            'columna' => 'z',
          ),
        ),
        'columna' => 'dto',
      ),
    ),
    'c008' => 
    array (
      'tabla' => 'ret_capturas_capturas_motivos_c008',
      'padre' => 'c002',
      'ruta' => '/instrumental/contenido/capturas/capturas/*/motivos/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'escalar',
        'tipos' => 
        array (
          0 => 'STRING',
        ),
        'columna' => 'dto',
      ),
    ),
    'c009' => 
    array (
      'tabla' => 'ret_capturas_perfil_captura_original_selec_c009',
      'padre' => 'c002',
      'ruta' => '/instrumental/contenido/capturas/capturas/*/perfil_captura_original/selectores/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'campo' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'campo',
          ),
          'cod_pregunta' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_pregunta',
          ),
        ),
        'columna' => 'dto',
      ),
    ),
    'c010' => 
    array (
      'tabla' => 'ret_capturas_capturas_preguntas_declarativ_c010',
      'padre' => 'c002',
      'ruta' => '/instrumental/contenido/capturas/capturas/*/preguntas_declarativas_originales/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'cod_instrumento' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_instrumento',
          ),
          'cod_pregunta' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_pregunta',
          ),
          'codigo' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'codigo',
          ),
          'dominio' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'dominio',
          ),
          'enunciado' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'enunciado',
          ),
          'estado' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'estado',
          ),
          'orden' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'INT',
            ),
            'columna' => 'orden',
          ),
          'puntaje_maximo' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'puntaje_maximo',
          ),
          'tipo_respuesta' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'tipo_respuesta',
          ),
        ),
        'columna' => 'dto',
      ),
    ),
    'c011' => 
    array (
      'tabla' => 'ret_capturas_capturas_respuestas_originale_c011',
      'padre' => 'c002',
      'ruta' => '/instrumental/contenido/capturas/capturas/*/respuestas_originales/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'cod_aplicacion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_aplicacion',
          ),
          'cod_opcion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_opcion',
          ),
          'cod_pregunta' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_pregunta',
          ),
          'cod_respuesta' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_respuesta',
          ),
          'codigo_pregunta' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'clave_pregunta',
          ),
          'coherente' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'BOOL',
            ),
            'columna' => 'coherente',
          ),
          'dominio_original' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'dominio_original',
          ),
          'estado_pregunta_actual' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'estado_pregunta_actual',
          ),
          'observacion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'observacion',
          ),
          'opcion_actual' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'opcion_actual',
          ),
          'pregunta' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'pregunta',
          ),
          'pregunta_actual' => 
          array (
            'clase' => 'objeto',
            'campos' => 
            array (
              'cod_instrumento' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'pregunta_actual_cod_instrumento',
              ),
              'cod_pregunta' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'pregunta_actual_cod_pregunta',
              ),
              'codigo' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'pregunta_actual_codigo',
              ),
              'dominio' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'pregunta_actual_dominio',
              ),
              'enunciado' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'pregunta_actual_enunciado',
              ),
              'estado' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'pregunta_actual_estado',
              ),
              'orden' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'INT',
                ),
                'columna' => 'pregunta_actual_orden',
              ),
              'puntaje_maximo' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'pregunta_actual_puntaje_maximo',
              ),
              'tipo_respuesta' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'pregunta_actual_tipo_respuesta',
              ),
            ),
            'columna' => 'pregunta_actual',
          ),
          'puntaje' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'puntaje',
          ),
          'tipo_respuesta' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'tipo_respuesta',
          ),
          'valor_logico' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'BOOL',
            ),
            'columna' => 'valor_logico',
          ),
          'valor_numero' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'valor_numero',
          ),
          'valor_texto' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'valor_texto',
          ),
        ),
        'columna' => 'dto',
      ),
    ),
    'c012' => 
    array (
      'tabla' => 'ret_capturas_capturas_vinculos_preguntas_a_c012',
      'padre' => 'c002',
      'ruta' => '/instrumental/contenido/capturas/capturas/*/vinculos_preguntas_actuales/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'pertenece_al_instrumento' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'BOOL',
            ),
            'columna' => 'pertenece_al_instrumento',
          ),
          'pregunta_actual' => 
          array (
            'clase' => 'objeto',
            'campos' => 
            array (
              'cod_instrumento' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'pregunta_actual_cod_instrumento',
              ),
              'cod_pregunta' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'pregunta_actual_cod_pregunta',
              ),
              'codigo' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'pregunta_actual_codigo',
              ),
              'dominio' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'pregunta_actual_dominio',
              ),
              'enunciado' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'pregunta_actual_enunciado',
              ),
              'estado' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'pregunta_actual_estado',
              ),
              'orden' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'INT',
                ),
                'columna' => 'pregunta_actual_orden',
              ),
              'puntaje_maximo' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'pregunta_actual_puntaje_maximo',
              ),
              'tipo_respuesta' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'pregunta_actual_tipo_respuesta',
              ),
            ),
            'columna' => 'pregunta_actual',
          ),
          'selector' => 
          array (
            'clase' => 'objeto',
            'campos' => 
            array (
              'campo' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'selector_campo',
              ),
              'cod_pregunta' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'selector_cod_pregunta',
              ),
            ),
            'columna' => 'selector',
          ),
          'tiene_respuesta' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'BOOL',
            ),
            'columna' => 'tiene_respuesta',
          ),
        ),
        'columna' => 'dto',
      ),
    ),
    'c013' => 
    array (
      'tabla' => 'ret_contenido_conocimiento_contratos_relac_c013',
      'padre' => 'c001',
      'ruta' => '/instrumental/contenido/conocimiento/contratos_relaciones/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'habilita_participacion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'BOOL',
            ),
            'columna' => 'habilita_participacion',
          ),
          'modalidades' => 
          array (
            'clase' => 'coleccion',
            'modo' => 'lista',
            'columna' => 'modalidades',
            'modelo' => 'c014',
            'identidad' => 'COLECCION',
          ),
          'tipo_destino' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'tipo_destino',
          ),
          'tipo_origen' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'tipo_origen',
          ),
          'tipo_relacion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'tipo_relacion',
          ),
        ),
        'columna' => 'dto',
      ),
    ),
    'c014' => 
    array (
      'tabla' => 'ret_conocimiento_contratos_relaciones_moda_c014',
      'padre' => 'c013',
      'ruta' => '/instrumental/contenido/conocimiento/contratos_relaciones/*/modalidades/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'escalar',
        'tipos' => 
        array (
          0 => 'STRING',
        ),
        'columna' => 'dto',
      ),
    ),
    'c015' => 
    array (
      'tabla' => 'ret_conocimiento_tablas_condiciones_regla__c015',
      'padre' => 'c001',
      'ruta' => '/instrumental/contenido/conocimiento/tablas/condiciones_regla_experta/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'cod_condicion_regla' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_condicion_regla',
          ),
          'cod_regla_experta' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_regla_experta',
          ),
          'cod_valor_semantico' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_valor_semantico',
          ),
          'cod_variable_experta' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_variable_experta',
          ),
        ),
        'columna' => 'dto',
      ),
    ),
    'c016' => 
    array (
      'tabla' => 'ret_conocimiento_tablas_consecuencias_regl_c016',
      'padre' => 'c001',
      'ruta' => '/instrumental/contenido/conocimiento/tablas/consecuencias_regla_experta/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'cod_consecuencia_regla' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_consecuencia_regla',
          ),
          'cod_criterio_dominio_resultado' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_criterio_dominio_r',
          ),
          'cod_regla_experta' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_regla_experta',
          ),
          'cod_valor_semantico' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_valor_semantico',
          ),
        ),
        'columna' => 'dto',
      ),
    ),
    'c017' => 
    array (
      'tabla' => 'ret_conocimiento_tablas_criterios_dominios_c017',
      'padre' => 'c001',
      'ruta' => '/instrumental/contenido/conocimiento/tablas/criterios_dominios_resultado/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'cod_criterio_dominio_resultado' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_criterio_dominio_r',
          ),
          'cod_dominio_valores' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_dominio_valores',
          ),
          'cod_nodo_criterio' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_nodo_criterio',
          ),
          'cod_usuario_creacion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_usuario_creacion',
          ),
          'estado' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'estado',
          ),
          'fecha_hora_creacion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'fecha_creacion',
          ),
          'observacion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'observacion',
          ),
        ),
        'columna' => 'dto',
      ),
    ),
    'c018' => 
    array (
      'tabla' => 'ret_conocimiento_tablas_dominios_valores_e_c018',
      'padre' => 'c001',
      'ruta' => '/instrumental/contenido/conocimiento/tablas/dominios_valores_expertos/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'cod_dominio_valores' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_dominio_valores',
          ),
          'cod_usuario_creacion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_usuario_creacion',
          ),
          'cod_version_modelo' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_version_modelo',
          ),
          'codigo_dominio' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'clave_dominio',
          ),
          'descripcion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'descripcion',
          ),
          'estado' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'estado',
          ),
          'fecha_hora_creacion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'fecha_creacion',
          ),
          'nombre' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'nombre',
          ),
          'observacion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'observacion',
          ),
          'tipo_dominio' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'tipo_dominio',
          ),
        ),
        'columna' => 'dto',
      ),
    ),
    'c019' => 
    array (
      'tabla' => 'ret_conocimiento_tablas_fuentes_datos_expe_c019',
      'padre' => 'c001',
      'ruta' => '/instrumental/contenido/conocimiento/tablas/fuentes_datos_expertas/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'campo_personal' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'campo_personal',
          ),
          'campo_pk_raiz' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'campo_pk_raiz',
          ),
          'campo_residente' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'campo_residente',
          ),
          'campo_temporal' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'campo_temporal',
          ),
          'cod_fuente_dato_experta' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_fuente_dato_experta',
          ),
          'cod_version_modelo' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_version_modelo',
          ),
          'codigo_fuente' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'clave_fuente',
          ),
          'estado' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'estado',
          ),
          'tabla_raiz' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'tabla_raiz',
          ),
        ),
        'columna' => 'dto',
      ),
    ),
    'c020' => 
    array (
      'tabla' => 'ret_conocimiento_tablas_mapeos_valores_fue_c020',
      'padre' => 'c001',
      'ruta' => '/instrumental/contenido/conocimiento/tablas/mapeos_valores_fuente/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'cod_mapeo_valor_fuente' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_mapeo_valor_fuente',
          ),
          'cod_mapeo_variable_fuente' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_mapeo_variable_fuente',
          ),
          'cod_valor_semantico' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_valor_semantico',
          ),
          'estado' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'estado',
          ),
          'estado_aprobacion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'estado_aprobacion',
          ),
          'fecha_hora_vigencia_desde' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'fecha_vigencia_desde',
          ),
          'fecha_hora_vigencia_hasta' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'fecha_vigencia_hasta',
          ),
          'tipo_valor_fuente' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'tipo_valor_fuente',
          ),
          'valor_fuente_exacto' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'valor_fuente_exacto',
          ),
        ),
        'columna' => 'dto',
      ),
    ),
    'c021' => 
    array (
      'tabla' => 'ret_conocimiento_tablas_mapeos_variables_f_c021',
      'padre' => 'c001',
      'ruta' => '/instrumental/contenido/conocimiento/tablas/mapeos_variables_fuente/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'campo_valor' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'campo_valor',
          ),
          'clave_selector' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'clave_selector',
          ),
          'cod_fuente_dato_experta' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_fuente_dato_experta',
          ),
          'cod_mapeo_variable_fuente' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_mapeo_variable_fuente',
          ),
          'cod_variable_experta' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_variable_experta',
          ),
          'cod_version_modelo' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_version_modelo',
          ),
          'estado' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'estado',
          ),
          'tipo_extraccion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'tipo_extraccion',
          ),
        ),
        'columna' => 'dto',
      ),
    ),
    'c022' => 
    array (
      'tabla' => 'ret_conocimiento_tablas_nodos_semanticos_c022',
      'padre' => 'c001',
      'ruta' => '/instrumental/contenido/conocimiento/tablas/nodos_semanticos/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'cod_nodo_semantico' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_nodo_semantico',
          ),
          'cod_usuario_creacion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_usuario_creacion',
          ),
          'cod_version_modelo' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_version_modelo',
          ),
          'codigo_semantico' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'clave_semantico',
          ),
          'definicion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'definicion',
          ),
          'estado' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'estado',
          ),
          'fecha_hora_creacion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'fecha_creacion',
          ),
          'nombre' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'nombre',
          ),
          'observacion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'observacion',
          ),
          'tipo_nodo' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'tipo_nodo',
          ),
        ),
        'columna' => 'dto',
      ),
    ),
    'c023' => 
    array (
      'tabla' => 'ret_conocimiento_tablas_reglas_expertas_c023',
      'padre' => 'c001',
      'ruta' => '/instrumental/contenido/conocimiento/tablas/reglas_expertas/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'cod_regla_anterior' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_regla_anterior',
          ),
          'cod_regla_experta' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_regla_experta',
          ),
          'cod_usuario_creacion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_usuario_creacion',
          ),
          'cod_usuario_revision' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_usuario_revision',
          ),
          'cod_version_modelo' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_version_modelo',
          ),
          'codigo_regla' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'clave_regla',
          ),
          'descripcion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'descripcion',
          ),
          'estado' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'estado',
          ),
          'estado_revision' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'estado_revision',
          ),
          'fecha_hora_creacion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'fecha_creacion',
          ),
          'fecha_hora_revision' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'fecha_revision',
          ),
          'justificacion_revision' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'justificacion_revision',
          ),
          'nombre' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'nombre',
          ),
          'observacion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'observacion',
          ),
          'tipo_regla' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'tipo_regla',
          ),
        ),
        'columna' => 'dto',
      ),
    ),
    'c024' => 
    array (
      'tabla' => 'ret_conocimiento_tablas_relaciones_semanti_c024',
      'padre' => 'c001',
      'ruta' => '/instrumental/contenido/conocimiento/tablas/relaciones_semanticas/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'cod_nodo_destino' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_nodo_destino',
          ),
          'cod_nodo_origen' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_nodo_origen',
          ),
          'cod_relacion_semantica' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_relacion_semantica',
          ),
          'cod_usuario_creacion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_usuario_creacion',
          ),
          'cod_version_modelo' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_version_modelo',
          ),
          'estado' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'estado',
          ),
          'fecha_hora_creacion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'fecha_creacion',
          ),
          'modalidad_relacion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'modalidad_relacion',
          ),
          'observacion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'observacion',
          ),
          'significado' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'significado',
          ),
          'tipo_relacion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'tipo_relacion',
          ),
        ),
        'columna' => 'dto',
      ),
    ),
    'c025' => 
    array (
      'tabla' => 'ret_conocimiento_tablas_valores_semanticos_c025',
      'padre' => 'c001',
      'ruta' => '/instrumental/contenido/conocimiento/tablas/valores_semanticos/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'cod_dominio_valores' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_dominio_valores',
          ),
          'cod_usuario_creacion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_usuario_creacion',
          ),
          'cod_valor_semantico' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_valor_semantico',
          ),
          'codigo_valor' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'clave_valor',
          ),
          'definicion_semantica' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'definicion_semantica',
          ),
          'estado' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'estado',
          ),
          'estado_aprobacion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'estado_aprobacion',
          ),
          'fecha_hora_creacion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'fecha_creacion',
          ),
          'nombre' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'nombre',
          ),
          'observacion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'observacion',
          ),
        ),
        'columna' => 'dto',
      ),
    ),
    'c026' => 
    array (
      'tabla' => 'ret_conocimiento_tablas_variables_expertas_c026',
      'padre' => 'c001',
      'ruta' => '/instrumental/contenido/conocimiento/tablas/variables_expertas/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'cod_dominio_valores' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_dominio_valores',
          ),
          'cod_nodo_propietario_primario' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_nodo_propietario_primario',
          ),
          'cod_nodo_semantico' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_nodo_semantico',
          ),
          'cod_usuario_creacion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_usuario_creacion',
          ),
          'cod_variable_experta' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_variable_experta',
          ),
          'cod_version_modelo' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_version_modelo',
          ),
          'estado' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'estado',
          ),
          'fecha_hora_creacion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'fecha_creacion',
          ),
          'observacion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'observacion',
          ),
          'papel_inferencial' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'papel_inferencial',
          ),
          'tipo_semantico' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'tipo_semantico',
          ),
        ),
        'columna' => 'dto',
      ),
    ),
    'c027' => 
    array (
      'tabla' => 'ret_contenido_fuente_conceptual_nodos_c027',
      'padre' => 'c001',
      'ruta' => '/instrumental/contenido/fuente_conceptual/nodos/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'codigo' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'codigo',
          ),
          'definicion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'definicion',
          ),
          'estado_fuente' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'estado_fuente',
          ),
          'limites' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'limites',
          ),
          'nombre' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'nombre',
          ),
          'procedencia' => 
          array (
            'clase' => 'coleccion',
            'modo' => 'lista',
            'columna' => 'procedencia',
            'modelo' => 'c028',
            'identidad' => 'COLECCION',
          ),
          'tipo' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'tipo',
          ),
        ),
        'columna' => 'dto',
      ),
    ),
    'c028' => 
    array (
      'tabla' => 'ret_fuente_conceptual_nodos_procedencia_c028',
      'padre' => 'c027',
      'ruta' => '/instrumental/contenido/fuente_conceptual/nodos/*/procedencia/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'escalar',
        'tipos' => 
        array (
          0 => 'INT',
        ),
        'columna' => 'dto',
      ),
    ),
    'c029' => 
    array (
      'tabla' => 'ret_contenido_fuente_conceptual_relaciones_c029',
      'padre' => 'c001',
      'ruta' => '/instrumental/contenido/fuente_conceptual/relaciones/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'codigo' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'codigo',
          ),
          'destino' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'destino',
          ),
          'estado_fuente' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'estado_fuente',
          ),
          'fila' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'INT',
            ),
            'columna' => 'fila',
          ),
          'limites' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'limites',
          ),
          'origen' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'origen',
          ),
          'relacion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'relacion',
          ),
          'significado' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'significado',
          ),
        ),
        'columna' => 'dto',
      ),
    ),
    'c030' => 
    array (
      'tabla' => 'ret_perfil_instrumental_comprobacion_adqui_c030',
      'padre' => 'c001',
      'ruta' => '/instrumental/contenido/salida/perfil_instrumental/comprobacion_adquisicion/aplicaciones/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'aplicabilidad' => 
          array (
            'clase' => 'objeto',
            'campos' => 
            array (
              'alcance_etario_compatible' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'BOOL',
                ),
                'columna' => 'aplicabilidad_alcance_etario_compatible',
              ),
              'autoriza_interpretacion_clinica' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'BOOL',
                ),
                'columna' => 'aplicabilidad_autoriza_interpretacion_clinica',
              ),
              'edad_en_aplicacion' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'INT',
                ),
                'columna' => 'aplicabilidad_edad_en_aplicacion',
              ),
              'escolaridad_original' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'INT',
                ),
                'columna' => 'aplicabilidad_escolaridad_original',
              ),
              'fuente' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'aplicabilidad_fuente',
              ),
              'idioma_declarado' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'aplicabilidad_idioma_declarado',
              ),
              'motivos' => 
              array (
                'clase' => 'objeto',
                'campos' => 
                array (
                ),
                'columna' => 'aplicabilidad_motivos',
              ),
              'pertenece_a_poblacion_objetivo' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'BOOL',
                ),
                'columna' => 'aplicabilidad_pertenece_a_poblacion_objetivo',
              ),
              'poblacion_objetivo' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'aplicabilidad_poblacion_objetivo',
              ),
              'rango_edad_referencia' => 
              array (
                'clase' => 'objeto',
                'campos' => 
                array (
                  'maxima' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'INT',
                    ),
                    'columna' => 'aplicabilidad_rango_edad_referencia_maxima',
                  ),
                  'minima' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'INT',
                    ),
                    'columna' => 'aplicabilidad_rango_edad_referencia_minima',
                  ),
                ),
                'columna' => 'aplicabilidad_rango_edad_referencia',
              ),
              'requiere_verificar' => 
              array (
                'clase' => 'coleccion',
                'modo' => 'lista',
                'columna' => 'aplicabilidad_requiere_verificar',
                'modelo' => 'c031',
                'identidad' => 'COLECCION',
              ),
            ),
            'columna' => 'aplicabilidad',
          ),
          'calculos' => 
          array (
            'clase' => 'coleccion',
            'modo' => 'mapa',
            'columna' => 'calculos',
            'modelo' => 'c032',
            'identidad' => 'COLECCION',
          ),
          'cod_aplicacion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_aplicacion',
          ),
          'cod_instrumento' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_instrumento',
          ),
          'edad_en_aplicacion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'INT',
            ),
            'columna' => 'edad_en_aplicacion',
          ),
          'entradas' => 
          array (
            'clase' => 'objeto',
            'campos' => 
            array (
              'aciertos_clave_semantica' => 
              array (
                'clase' => 'objeto',
                'campos' => 
                array (
                  'campo' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_aciertos_clave_semantica_campo',
                  ),
                  'cod_pregunta' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_aciertos_clave_semantica_cod_pregunta',
                  ),
                  'cod_respuesta' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_aciertos_clave_semantica_cod_respuesta',
                  ),
                  'estado' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_aciertos_clave_semantica_estado',
                  ),
                  'tipo' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_aciertos_clave_semantica_tipo',
                  ),
                ),
                'columna' => 'entradas_aciertos_clave_semantica',
              ),
              'aciertos_espontaneos' => 
              array (
                'clase' => 'objeto',
                'campos' => 
                array (
                  'campo' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_aciertos_espontaneos_campo',
                  ),
                  'cod_pregunta' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_aciertos_espontaneos_cod_pregunta',
                  ),
                  'cod_respuesta' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_aciertos_espontaneos_cod_respuesta',
                  ),
                  'estado' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_aciertos_espontaneos_estado',
                  ),
                  'tipo' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_aciertos_espontaneos_tipo',
                  ),
                ),
                'columna' => 'entradas_aciertos_espontaneos',
              ),
              'administracion_estandarizada' => 
              array (
                'clase' => 'objeto',
                'campos' => 
                array (
                  'campo' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_administracion_estandarizada_campo',
                  ),
                  'cod_pregunta' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'ent_adm_est_cod_pre',
                  ),
                  'cod_respuesta' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'ent_adm_est_cod_res',
                  ),
                  'estado' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_administracion_estandarizada_estado',
                  ),
                  'tipo' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_administracion_estandarizada_tipo',
                  ),
                ),
                'columna' => 'entradas_administracion_estandarizada',
              ),
              'audicion_compatible' => 
              array (
                'clase' => 'objeto',
                'campos' => 
                array (
                  'campo' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_audicion_compatible_campo',
                  ),
                  'cod_pregunta' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_audicion_compatible_cod_pregunta',
                  ),
                  'cod_respuesta' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_audicion_compatible_cod_respuesta',
                  ),
                  'estado' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_audicion_compatible_estado',
                  ),
                  'tipo' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_audicion_compatible_tipo',
                  ),
                ),
                'columna' => 'entradas_audicion_compatible',
              ),
              'condiciones_sensoriales_motoras_compatibles' => 
              array (
                'clase' => 'objeto',
                'campos' => 
                array (
                  'campo' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_condiciones_sensoriales_campo',
                  ),
                  'cod_pregunta' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_condiciones_sensoriales_cod_pregunta',
                  ),
                  'cod_respuesta' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_condiciones_sensoriales_cod_respuesta',
                  ),
                  'estado' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_condiciones_sensoriales_estado',
                  ),
                  'tipo' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_condiciones_sensoriales_tipo',
                  ),
                ),
                'columna' => 'entradas_condiciones_sensoriales',
              ),
              'demora_minutos' => 
              array (
                'clase' => 'objeto',
                'campos' => 
                array (
                  'campo' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_demora_minutos_campo',
                  ),
                  'cod_pregunta' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_demora_minutos_cod_pregunta',
                  ),
                  'cod_respuesta' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_demora_minutos_cod_respuesta',
                  ),
                  'estado' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_demora_minutos_estado',
                  ),
                  'tipo' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_demora_minutos_tipo',
                  ),
                ),
                'columna' => 'entradas_demora_minutos',
              ),
              'elementos_revisados' => 
              array (
                'clase' => 'objeto',
                'campos' => 
                array (
                  'campo' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_elementos_revisados_campo',
                  ),
                  'cod_pregunta' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_elementos_revisados_cod_pregunta',
                  ),
                  'cod_respuesta' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_elementos_revisados_cod_respuesta',
                  ),
                  'estado' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_elementos_revisados_estado',
                  ),
                  'tipo' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_elementos_revisados_tipo',
                  ),
                ),
                'columna' => 'entradas_elementos_revisados',
              ),
              'ensayo_1' => 
              array (
                'clase' => 'objeto',
                'campos' => 
                array (
                  'campo' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_ensayo_1_campo',
                  ),
                  'cod_pregunta' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_ensayo_1_cod_pregunta',
                  ),
                  'cod_respuesta' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_ensayo_1_cod_respuesta',
                  ),
                  'estado' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_ensayo_1_estado',
                  ),
                  'tipo' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_ensayo_1_tipo',
                  ),
                ),
                'columna' => 'entradas_ensayo_1',
              ),
              'ensayo_2' => 
              array (
                'clase' => 'objeto',
                'campos' => 
                array (
                  'campo' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_ensayo_2_campo',
                  ),
                  'cod_pregunta' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_ensayo_2_cod_pregunta',
                  ),
                  'cod_respuesta' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_ensayo_2_cod_respuesta',
                  ),
                  'estado' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_ensayo_2_estado',
                  ),
                  'tipo' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_ensayo_2_tipo',
                  ),
                ),
                'columna' => 'entradas_ensayo_2',
              ),
              'ensayo_3' => 
              array (
                'clase' => 'objeto',
                'campos' => 
                array (
                  'campo' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_ensayo_3_campo',
                  ),
                  'cod_pregunta' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_ensayo_3_cod_pregunta',
                  ),
                  'cod_respuesta' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_ensayo_3_cod_respuesta',
                  ),
                  'estado' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_ensayo_3_estado',
                  ),
                  'tipo' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_ensayo_3_tipo',
                  ),
                ),
                'columna' => 'entradas_ensayo_3',
              ),
              'errores_observados' => 
              array (
                'clase' => 'objeto',
                'campos' => 
                array (
                  'campo' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_errores_observados_campo',
                  ),
                  'cod_pregunta' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_errores_observados_cod_pregunta',
                  ),
                  'cod_respuesta' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_errores_observados_cod_respuesta',
                  ),
                  'estado' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_errores_observados_estado',
                  ),
                  'tipo' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_errores_observados_tipo',
                  ),
                ),
                'columna' => 'entradas_errores_observados',
              ),
              'escolaridad_anios' => 
              array (
                'clase' => 'objeto',
                'campos' => 
                array (
                  'campo' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_escolaridad_anios_campo',
                  ),
                  'cod_pregunta' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_escolaridad_anios_cod_pregunta',
                  ),
                  'cod_respuesta' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_escolaridad_anios_cod_respuesta',
                  ),
                  'estado' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_escolaridad_anios_estado',
                  ),
                  'tipo' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_escolaridad_anios_tipo',
                  ),
                ),
                'columna' => 'entradas_escolaridad_anios',
              ),
              'espanol_nativo' => 
              array (
                'clase' => 'objeto',
                'campos' => 
                array (
                  'campo' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_espanol_nativo_campo',
                  ),
                  'cod_pregunta' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_espanol_nativo_cod_pregunta',
                  ),
                  'cod_respuesta' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_espanol_nativo_cod_respuesta',
                  ),
                  'estado' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_espanol_nativo_estado',
                  ),
                  'tipo' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_espanol_nativo_tipo',
                  ),
                ),
                'columna' => 'entradas_espanol_nativo',
              ),
              'finalizada' => 
              array (
                'clase' => 'objeto',
                'campos' => 
                array (
                  'campo' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_finalizada_campo',
                  ),
                  'cod_pregunta' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_finalizada_cod_pregunta',
                  ),
                  'cod_respuesta' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_finalizada_cod_respuesta',
                  ),
                  'estado' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_finalizada_estado',
                  ),
                  'tipo' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_finalizada_tipo',
                  ),
                ),
                'columna' => 'entradas_finalizada',
              ),
              'forma_l_aciertos' => 
              array (
                'clase' => 'objeto',
                'campos' => 
                array (
                  'campo' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_forma_l_aciertos_campo',
                  ),
                  'cod_pregunta' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_forma_l_aciertos_cod_pregunta',
                  ),
                  'cod_respuesta' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_forma_l_aciertos_cod_respuesta',
                  ),
                  'estado' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_forma_l_aciertos_estado',
                  ),
                  'tipo' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_forma_l_aciertos_tipo',
                  ),
                ),
                'columna' => 'entradas_forma_l_aciertos',
              ),
              'forma_n_aciertos' => 
              array (
                'clase' => 'objeto',
                'campos' => 
                array (
                  'campo' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_forma_n_aciertos_campo',
                  ),
                  'cod_pregunta' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_forma_n_aciertos_cod_pregunta',
                  ),
                  'cod_respuesta' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_forma_n_aciertos_cod_respuesta',
                  ),
                  'estado' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_forma_n_aciertos_estado',
                  ),
                  'tipo' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_forma_n_aciertos_tipo',
                  ),
                ),
                'columna' => 'entradas_forma_n_aciertos',
              ),
              'lectoescritura' => 
              array (
                'clase' => 'objeto',
                'campos' => 
                array (
                  'campo' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_lectoescritura_campo',
                  ),
                  'cod_pregunta' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_lectoescritura_cod_pregunta',
                  ),
                  'cod_respuesta' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_lectoescritura_cod_respuesta',
                  ),
                  'estado' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_lectoescritura_estado',
                  ),
                  'tipo' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_lectoescritura_tipo',
                  ),
                ),
                'columna' => 'entradas_lectoescritura',
              ),
              'nacimiento_bolivia' => 
              array (
                'clase' => 'objeto',
                'campos' => 
                array (
                  'campo' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_nacimiento_bolivia_campo',
                  ),
                  'cod_pregunta' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_nacimiento_bolivia_cod_pregunta',
                  ),
                  'cod_respuesta' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_nacimiento_bolivia_cod_respuesta',
                  ),
                  'estado' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_nacimiento_bolivia_estado',
                  ),
                  'tipo' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_nacimiento_bolivia_tipo',
                  ),
                ),
                'columna' => 'entradas_nacimiento_bolivia',
              ),
              'puntaje_copia' => 
              array (
                'clase' => 'objeto',
                'campos' => 
                array (
                  'campo' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_puntaje_copia_campo',
                  ),
                  'cod_pregunta' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_puntaje_copia_cod_pregunta',
                  ),
                  'cod_respuesta' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_puntaje_copia_cod_respuesta',
                  ),
                  'estado' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_puntaje_copia_estado',
                  ),
                  'tipo' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_puntaje_copia_tipo',
                  ),
                ),
                'columna' => 'entradas_puntaje_copia',
              ),
              'recuerdo_diferido' => 
              array (
                'clase' => 'objeto',
                'campos' => 
                array (
                  'campo' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_recuerdo_diferido_campo',
                  ),
                  'cod_pregunta' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_recuerdo_diferido_cod_pregunta',
                  ),
                  'cod_respuesta' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_recuerdo_diferido_cod_respuesta',
                  ),
                  'estado' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_recuerdo_diferido_estado',
                  ),
                  'tipo' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_recuerdo_diferido_tipo',
                  ),
                ),
                'columna' => 'entradas_recuerdo_diferido',
              ),
              'residencia_bolivia' => 
              array (
                'clase' => 'objeto',
                'campos' => 
                array (
                  'campo' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_residencia_bolivia_campo',
                  ),
                  'cod_pregunta' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_residencia_bolivia_cod_pregunta',
                  ),
                  'cod_respuesta' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_residencia_bolivia_cod_respuesta',
                  ),
                  'estado' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_residencia_bolivia_estado',
                  ),
                  'tipo' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_residencia_bolivia_tipo',
                  ),
                ),
                'columna' => 'entradas_residencia_bolivia',
              ),
              'segundos_parte_b' => 
              array (
                'clase' => 'objeto',
                'campos' => 
                array (
                  'campo' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_segundos_parte_b_campo',
                  ),
                  'cod_pregunta' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_segundos_parte_b_cod_pregunta',
                  ),
                  'cod_respuesta' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_segundos_parte_b_cod_respuesta',
                  ),
                  'estado' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_segundos_parte_b_estado',
                  ),
                  'tipo' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'entradas_segundos_parte_b_tipo',
                  ),
                ),
                'columna' => 'entradas_segundos_parte_b',
              ),
            ),
            'columna' => 'entradas',
          ),
          'estado_normalizacion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'estado_normalizacion',
          ),
          'fuente_metodo' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'fuente_metodo',
          ),
          'identidad' => 
          array (
            'clase' => 'objeto',
            'campos' => 
            array (
              'edicion_protocolo' => 
              array (
                'clase' => 'objeto',
                'campos' => 
                array (
                  'declarado' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'identidad_edicion_protocolo_declarado',
                  ),
                  'estado' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'identidad_edicion_protocolo_estado',
                  ),
                  'requerido_por_metodo' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'identidad_edicion_protocolo_requerido_por_metodo',
                  ),
                ),
                'columna' => 'identidad_edicion_protocolo',
              ),
              'forma' => 
              array (
                'clase' => 'objeto',
                'campos' => 
                array (
                  'declarado' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'INT',
                    ),
                    'columna' => 'identidad_forma_declarado',
                  ),
                  'estado' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'identidad_forma_estado',
                  ),
                  'requerido_por_metodo' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'INT',
                    ),
                    'columna' => 'identidad_forma_requerido_por_metodo',
                  ),
                ),
                'columna' => 'identidad_forma',
              ),
              'idioma' => 
              array (
                'clase' => 'objeto',
                'campos' => 
                array (
                  'declarado' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'identidad_idioma_declarado',
                  ),
                  'estado' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'identidad_idioma_estado',
                  ),
                  'requerido_por_metodo' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'identidad_idioma_requerido_por_metodo',
                  ),
                ),
                'columna' => 'identidad_idioma',
              ),
              'instrumento' => 
              array (
                'clase' => 'objeto',
                'campos' => 
                array (
                  'declarado' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'identidad_instrumento_declarado',
                  ),
                  'estado' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'identidad_instrumento_estado',
                  ),
                  'requerido_por_metodo' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'identidad_instrumento_requerido_por_metodo',
                  ),
                ),
                'columna' => 'identidad_instrumento',
              ),
            ),
            'columna' => 'identidad',
          ),
          'metodo' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'metodo',
          ),
          'motivos_captura' => 
          array (
            'clase' => 'objeto',
            'campos' => 
            array (
            ),
            'columna' => 'motivos_captura',
          ),
          'motivos_lectura' => 
          array (
            'clase' => 'coleccion',
            'modo' => 'lista',
            'columna' => 'motivos_lectura',
            'modelo' => 'c034',
            'identidad' => 'COLECCION',
          ),
          'naturaleza_fuente' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'naturaleza_fuente',
          ),
          'perfil' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'perfil',
          ),
          'pertenece_a_poblacion_60_o_mas' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'BOOL',
            ),
            'columna' => 'pertenece_a_poblacion_60_o_mas',
          ),
          'procedencia' => 
          array (
            'clase' => 'objeto',
            'campos' => 
            array (
              'cod_documento_clinico' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'procedencia_cod_documento_clinico',
              ),
              'cod_personal_aplicador' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'procedencia_cod_personal_aplicador',
              ),
              'cod_usuario_registro' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'procedencia_cod_usuario_registro',
              ),
              'estado_documento' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'procedencia_estado_documento',
              ),
              'fecha_hora_clinica' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'procedencia_fecha_clinica',
              ),
              'registrado_en' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'procedencia_registrado_en',
              ),
              'tiene_localizador' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'BOOL',
                ),
                'columna' => 'procedencia_tiene_localizador',
              ),
            ),
            'columna' => 'procedencia',
          ),
          'version_instrumento' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'version_instrumento',
          ),
        ),
        'columna' => 'dto',
      ),
    ),
    'c031' => 
    array (
      'tabla' => 'ret_aplicaciones_aplicabilidad_requiere_ve_c031',
      'padre' => 'c030',
      'ruta' => '/instrumental/contenido/salida/perfil_instrumental/comprobacion_adquisicion/aplicaciones/*/aplicabilidad/requiere_verificar/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'escalar',
        'tipos' => 
        array (
          0 => 'STRING',
        ),
        'columna' => 'dto',
      ),
    ),
    'c032' => 
    array (
      'tabla' => 'ret_comprobacion_adquisicion_aplicaciones__c032',
      'padre' => 'c030',
      'ruta' => '/instrumental/contenido/salida/perfil_instrumental/comprobacion_adquisicion/aplicaciones/*/calculos/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'coleccion',
        'modo' => 'lista',
        'columna' => 'dto',
        'modelo' => 'c033',
        'identidad' => 'COLECCION',
      ),
    ),
    'c033' => 
    array (
      'tabla' => 'ret_comprobacion_adquisicion_aplicaciones__c033',
      'padre' => 'c032',
      'ruta' => '/instrumental/contenido/salida/perfil_instrumental/comprobacion_adquisicion/aplicaciones/*/calculos/*/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'estado' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'estado',
          ),
          'motivos' => 
          array (
            'clase' => 'objeto',
            'campos' => 
            array (
            ),
            'columna' => 'motivos',
          ),
        ),
        'columna' => 'dto',
      ),
    ),
    'c034' => 
    array (
      'tabla' => 'ret_comprobacion_adquisicion_aplicaciones__c034',
      'padre' => 'c030',
      'ruta' => '/instrumental/contenido/salida/perfil_instrumental/comprobacion_adquisicion/aplicaciones/*/motivos_lectura/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'escalar',
        'tipos' => 
        array (
          0 => 'STRING',
        ),
        'columna' => 'dto',
      ),
    ),
    'c035' => 
    array (
      'tabla' => 'ret_salida_perfil_instrumental_dominios_c035',
      'padre' => 'c001',
      'ruta' => '/instrumental/contenido/salida/perfil_instrumental/dominios/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'componentes' => 
          array (
            'clase' => 'coleccion',
            'modo' => 'mapa',
            'columna' => 'componentes',
            'modelo' => 'c036',
            'identidad' => 'COLECCION',
          ),
          'estado' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'estado',
          ),
          'motivos' => 
          array (
            'clase' => 'coleccion',
            'modo' => 'lista',
            'columna' => 'motivos',
            'modelo' => 'c045',
            'identidad' => 'COLECCION',
          ),
          'nombre' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'nombre',
          ),
          'resultado_clinico' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'r_clinico',
          ),
        ),
        'columna' => 'dto',
      ),
    ),
    'c036' => 
    array (
      'tabla' => 'ret_perfil_instrumental_dominios_component_c036',
      'padre' => 'c035',
      'ruta' => '/instrumental/contenido/salida/perfil_instrumental/dominios/*/componentes/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'captura_requerida' => 
          array (
            'clase' => 'objeto',
            'campos' => 
            array (
              'calculos_descriptivos_disponibles' => 
              array (
                'clase' => 'coleccion',
                'modo' => 'lista',
                'columna' => 'captura_requerida_calculos',
                'modelo' => 'c037',
                'identidad' => 'COLECCION',
              ),
              'campos' => 
              array (
                'clase' => 'objeto',
                'campos' => 
                array (
                  'a_segundos' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'captura_requerida_campos_a_segundos',
                  ),
                  'a_validas' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'captura_requerida_campos_a_validas',
                  ),
                  'aciertos_clave_semantica' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'captura_requerida_campos_aciertos_clave_semantica',
                  ),
                  'aciertos_espontaneos' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'captura_requerida_campos_aciertos_espontaneos',
                  ),
                  'aciertos_reconocimiento' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'captura_requerida_campos_aciertos_rek',
                  ),
                  'adaptacion_nacional' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'captura_requerida_campos_adaptacion_nacional',
                  ),
                  'adicional_claves_1' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'captura_requerida_campos_adicional_claves_1',
                  ),
                  'adicional_claves_2' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'captura_requerida_campos_adicional_claves_2',
                  ),
                  'adicional_claves_3' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'captura_requerida_campos_adicional_claves_3',
                  ),
                  'adicional_claves_diferido' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'cap_req_cam_adi_cla_dif',
                  ),
                  'administracion_estandarizada' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'cap_req_cam_adm_est',
                  ),
                  'administrados' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'captura_requerida_campos_administrados',
                  ),
                  'animales_segundos' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'captura_requerida_campos_animales_segundos',
                  ),
                  'animales_validas' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'captura_requerida_campos_animales_validas',
                  ),
                  'audicion_compatible' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'captura_requerida_campos_audicion_compatible',
                  ),
                  'audicion_vision' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'captura_requerida_campos_audicion_vision',
                  ),
                  'ayudas_fonemicas' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'captura_requerida_campos_ayudas_fonemicas',
                  ),
                  'bruto_subprueba' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'captura_requerida_campos_bruto_subprueba',
                  ),
                  'codificacion_verificada' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'captura_requerida_campos_codificacion_verificada',
                  ),
                  'colores_correctos' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'captura_requerida_campos_colores_correctos',
                  ),
                  'condicion' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'captura_requerida_campos_condicion',
                  ),
                  'contenido_informacion' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'captura_requerida_campos_dto_informacion',
                  ),
                  'control_visual' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'captura_requerida_campos_control_visual',
                  ),
                  'correctos' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'captura_requerida_campos_correctos',
                  ),
                  'demora_minutos' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'captura_requerida_campos_demora_minutos',
                  ),
                  'elementos_revisados' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'captura_requerida_campos_elementos_revisados',
                  ),
                  'ensayo_1' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'captura_requerida_campos_ensayo_1',
                  ),
                  'ensayo_2' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'captura_requerida_campos_ensayo_2',
                  ),
                  'ensayo_3' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'captura_requerida_campos_ensayo_3',
                  ),
                  'ensayos' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'captura_requerida_campos_ensayos',
                  ),
                  'errores_observados' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'captura_requerida_campos_errores_observados',
                  ),
                  'f_segundos' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'captura_requerida_campos_f_segundos',
                  ),
                  'f_validas' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'captura_requerida_campos_f_validas',
                  ),
                  'falsas_alarmas_no_relacionadas' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'cap_req_cam_fal_ala_no_rel',
                  ),
                  'falsas_alarmas_relacionadas' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'cap_req_cam_fal_ala_rel',
                  ),
                  'finalizada' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'captura_requerida_campos_finalizada',
                  ),
                  'fluidez' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'captura_requerida_campos_fluidez',
                  ),
                  'forma' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'captura_requerida_campos_forma',
                  ),
                  'forma_l_aciertos' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'captura_requerida_campos_forma_l_aciertos',
                  ),
                  'forma_n_aciertos' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'captura_requerida_campos_forma_n_aciertos',
                  ),
                  'libre_1' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'captura_requerida_campos_libre_1',
                  ),
                  'libre_2' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'captura_requerida_campos_libre_2',
                  ),
                  'libre_3' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'captura_requerida_campos_libre_3',
                  ),
                  'libre_diferido' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'captura_requerida_campos_libre_diferido',
                  ),
                  'longitud_maxima' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'captura_requerida_campos_longitud_maxima',
                  ),
                  'movimientos_por_problema' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'captura_requerida_campos_movimientos_por_problema',
                  ),
                  'muestra_discurso_documentada' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'cap_req_cam_mue_dis_doc',
                  ),
                  'palabra_color_correctas' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'captura_requerida_campos_palabra_color_correctas',
                  ),
                  'palabras_correctas' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'captura_requerida_campos_palabras_correctas',
                  ),
                  'presentaciones_por_elemento' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'cap_req_cam_pre_por_ele',
                  ),
                  'puntaje_copia' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'captura_requerida_campos_puntaje_copia',
                  ),
                  'puntaje_total' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'captura_requerida_campos_puntaje_total',
                  ),
                  'puntajes_por_elemento' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'captura_requerida_campos_puntajes_por_elemento',
                  ),
                  'puntajes_por_ensayo' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'captura_requerida_campos_puntajes_por_ensayo',
                  ),
                  'recuerdo_diferido' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'captura_requerida_campos_recuerdo_diferido',
                  ),
                  'referencia_muestra' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'captura_requerida_campos_referencia_muestra',
                  ),
                  'referencia_produccion' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'captura_requerida_campos_referencia_produccion',
                  ),
                  'repeticion_total' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'captura_requerida_campos_repeticion_total',
                  ),
                  'repeticiones_intrusiones' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'captura_requerida_campos_repeticiones_intrusiones',
                  ),
                  'resueltos' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'captura_requerida_campos_resueltos',
                  ),
                  's_segundos' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'captura_requerida_campos_s_segundos',
                  ),
                  's_validas' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'captura_requerida_campos_s_validas',
                  ),
                  'segundos_colores' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'captura_requerida_campos_segundos_colores',
                  ),
                  'segundos_palabra_color' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'captura_requerida_campos_segundos_palabra_color',
                  ),
                  'segundos_palabras' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'captura_requerida_campos_segundos_palabras',
                  ),
                  'segundos_parte_b' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'captura_requerida_campos_segundos_parte_b',
                  ),
                  'subprueba' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'captura_requerida_campos_subprueba',
                  ),
                  'subprueba_forma' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'captura_requerida_campos_subprueba_forma',
                  ),
                  'tarea' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'captura_requerida_campos_tarea',
                  ),
                  'tiempos_por_problema' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'captura_requerida_campos_tiempos_por_problema',
                  ),
                  'violaciones_regla' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'captura_requerida_campos_violaciones_regla',
                  ),
                  'violaciones_tiempo' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'captura_requerida_campos_violaciones_tiempo',
                  ),
                ),
                'columna' => 'captura_requerida_campos',
              ),
              'estado_metodo' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'captura_requerida_estado_metodo',
              ),
              'fuente' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'captura_requerida_fuente',
              ),
              'instrumento_administrado' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'captura_requerida_instrumento_administrado',
              ),
              'instrumento_referencia' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'captura_requerida_instrumento_referencia',
              ),
              'limite' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'captura_requerida_limite',
              ),
              'mediciones_residente' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'captura_requerida_mediciones_residente',
              ),
              'metodos_disponibles' => 
              array (
                'clase' => 'coleccion',
                'modo' => 'lista',
                'columna' => 'captura_requerida_metodos_disponibles',
                'modelo' => 'c038',
                'identidad' => 'COLECCION',
              ),
              'normalizadores_referencia_disponibles' => 
              array (
                'clase' => 'coleccion',
                'modo' => 'lista',
                'columna' => 'captura_requerida_normalizadores',
                'modelo' => 'c039',
                'identidad' => 'COLECCION',
              ),
              'version' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'captura_requerida_version',
              ),
            ),
            'columna' => 'captura_requerida',
          ),
          'concepto' => 
          array (
            'clase' => 'objeto',
            'campos' => 
            array (
              'codigo' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'concepto_codigo',
              ),
              'definicion' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'concepto_definicion',
              ),
              'estado_fuente' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'concepto_estado_fuente',
              ),
              'limites' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'concepto_limites',
              ),
              'nombre' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'concepto_nombre',
              ),
              'procedencia' => 
              array (
                'clase' => 'coleccion',
                'modo' => 'lista',
                'columna' => 'concepto_procedencia',
                'modelo' => 'c040',
                'identidad' => 'COLECCION',
              ),
              'tipo' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'concepto_tipo',
              ),
            ),
            'columna' => 'concepto',
          ),
          'dominio' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'dominio',
          ),
          'estado_medicion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'estado_medicion',
          ),
          'mediciones' => 
          array (
            'clase' => 'coleccion',
            'modo' => 'lista',
            'columna' => 'mediciones',
            'modelo' => 'c041',
            'identidad' => 'COLECCION',
          ),
          'motivos' => 
          array (
            'clase' => 'coleccion',
            'modo' => 'lista',
            'columna' => 'motivos',
            'modelo' => 'c044',
            'identidad' => 'COLECCION',
          ),
          'nombre' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'nombre',
          ),
          'relacion_original' => 
          array (
            'clase' => 'objeto',
            'campos' => 
            array (
              'codigo' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'relacion_original_codigo',
              ),
              'destino' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'relacion_original_destino',
              ),
              'estado_fuente' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'relacion_original_estado_fuente',
              ),
              'fila' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'INT',
                ),
                'columna' => 'relacion_original_fila',
              ),
              'limites' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'relacion_original_limites',
              ),
              'origen' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'relacion_original_origen',
              ),
              'relacion' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'relacion_original_relacion',
              ),
              'significado' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'relacion_original_significado',
              ),
            ),
            'columna' => 'relacion_original',
          ),
          'resultado_clinico' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'r_clinico',
          ),
        ),
        'columna' => 'dto',
      ),
    ),
    'c037' => 
    array (
      'tabla' => 'ret_componentes_captura_requerida_calculos_c037',
      'padre' => 'c036',
      'ruta' => '/instrumental/contenido/salida/perfil_instrumental/dominios/*/componentes/*/captura_requerida/calculos_descriptivos_disponibles/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'escalar',
        'tipos' => 
        array (
          0 => 'STRING',
        ),
        'columna' => 'dto',
      ),
    ),
    'c038' => 
    array (
      'tabla' => 'ret_componentes_captura_requerida_metodos__c038',
      'padre' => 'c036',
      'ruta' => '/instrumental/contenido/salida/perfil_instrumental/dominios/*/componentes/*/captura_requerida/metodos_disponibles/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'escalar',
        'tipos' => 
        array (
          0 => 'STRING',
        ),
        'columna' => 'dto',
      ),
    ),
    'c039' => 
    array (
      'tabla' => 'ret_componentes_captura_requerida_normaliz_c039',
      'padre' => 'c036',
      'ruta' => '/instrumental/contenido/salida/perfil_instrumental/dominios/*/componentes/*/captura_requerida/normalizadores_referencia_disponibles/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'escalar',
        'tipos' => 
        array (
          0 => 'STRING',
        ),
        'columna' => 'dto',
      ),
    ),
    'c040' => 
    array (
      'tabla' => 'ret_componentes_concepto_procedencia_c040',
      'padre' => 'c036',
      'ruta' => '/instrumental/contenido/salida/perfil_instrumental/dominios/*/componentes/*/concepto/procedencia/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'escalar',
        'tipos' => 
        array (
          0 => 'INT',
        ),
        'columna' => 'dto',
      ),
    ),
    'c041' => 
    array (
      'tabla' => 'ret_dominios_componentes_mediciones_c041',
      'padre' => 'c036',
      'ruta' => '/instrumental/contenido/salida/perfil_instrumental/dominios/*/componentes/*/mediciones/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'aplicacion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'aplicacion',
          ),
          'autor' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'autor',
          ),
          'captura' => 
          array (
            'clase' => 'objeto',
            'campos' => 
            array (
              'aciertos_clave_semantica' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'INT',
                ),
                'columna' => 'captura_aciertos_clave_semantica',
              ),
              'aciertos_espontaneos' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'INT',
                ),
                'columna' => 'captura_aciertos_espontaneos',
              ),
              'administracion_estandarizada' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'BOOL',
                ),
                'columna' => 'captura_administracion_estandarizada',
              ),
              'audicion_compatible' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'BOOL',
                ),
                'columna' => 'captura_audicion_compatible',
              ),
              'condiciones_sensoriales_motoras_compatibles' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'BOOL',
                ),
                'columna' => 'captura_condiciones_sensoriales',
              ),
              'demora_minutos' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'FLOAT',
                ),
                'columna' => 'captura_demora_minutos',
              ),
              'edad_anios' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'INT',
                ),
                'columna' => 'captura_edad_anios',
              ),
              'edicion_protocolo' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'captura_edicion_protocolo',
              ),
              'elementos_revisados' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'INT',
                ),
                'columna' => 'captura_elementos_revisados',
              ),
              'ensayo_1' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'INT',
                ),
                'columna' => 'captura_ensayo_1',
              ),
              'ensayo_2' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'INT',
                ),
                'columna' => 'captura_ensayo_2',
              ),
              'ensayo_3' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'INT',
                ),
                'columna' => 'captura_ensayo_3',
              ),
              'ensayos' => 
              array (
                'clase' => 'coleccion',
                'modo' => 'lista',
                'columna' => 'captura_ensayos',
                'modelo' => 'c042',
                'identidad' => 'COLECCION',
              ),
              'errores_observados' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'INT',
                ),
                'columna' => 'captura_errores_observados',
              ),
              'escolaridad_anios' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'INT',
                ),
                'columna' => 'captura_escolaridad_anios',
              ),
              'espanol_nativo' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'BOOL',
                ),
                'columna' => 'captura_espanol_nativo',
              ),
              'finalizada' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'BOOL',
                ),
                'columna' => 'captura_finalizada',
              ),
              'forma' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'INT',
                ),
                'columna' => 'captura_forma',
              ),
              'forma_l_aciertos' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'INT',
                ),
                'columna' => 'captura_forma_l_aciertos',
              ),
              'forma_n_aciertos' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'INT',
                ),
                'columna' => 'captura_forma_n_aciertos',
              ),
              'idioma' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'captura_idioma',
              ),
              'instrumento' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'captura_instrumento',
              ),
              'lectoescritura' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'BOOL',
                ),
                'columna' => 'captura_lectoescritura',
              ),
              'nacimiento_bolivia' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'BOOL',
                ),
                'columna' => 'captura_nacimiento_bolivia',
              ),
              'protocolo' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'captura_protocolo',
              ),
              'puntaje_copia' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'FLOAT',
                ),
                'columna' => 'captura_puntaje_copia',
              ),
              'recuerdo_diferido' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'INT',
                ),
                'columna' => 'captura_recuerdo_diferido',
              ),
              'residencia_bolivia' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'BOOL',
                ),
                'columna' => 'captura_residencia_bolivia',
              ),
              'segundos_parte_b' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'FLOAT',
                ),
                'columna' => 'captura_segundos_parte_b',
              ),
            ),
            'columna' => 'captura',
          ),
          'fecha' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'fecha',
          ),
          'fuentes_entradas' => 
          array (
            'clase' => 'coleccion',
            'modo' => 'mapa',
            'columna' => 'fuentes_entradas',
            'modelo' => 'c043',
            'identidad' => 'COLECCION',
          ),
          'metodo' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'metodo',
          ),
          'perfil' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'perfil',
          ),
          'referencia' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'referencia',
          ),
          'resultado' => 
          array (
            'clase' => 'objeto',
            'campos' => 
            array (
              'clasificacion_clinica' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'r_clasificacion_clinica',
              ),
              'de_residual' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'FLOAT',
                ),
                'columna' => 'r_de_residual',
              ),
              'direccion' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'r_direccion',
              ),
              'esperado' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'FLOAT',
                ),
                'columna' => 'r_esperado',
              ),
              'estado' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'r_estado',
              ),
              'interpretacion_matematica' => 
              array (
                'clase' => 'objeto',
                'campos' => 
                array (
                  'clasificacion_clinica' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'r_interpretacion_matematica_clasificacion_clinica',
                  ),
                  'contrato' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'r_interpretacion_matematica_contrato',
                  ),
                  'estado' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'r_interpretacion_matematica_estado',
                  ),
                  'limite' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'r_interpretacion_matematica_limite',
                  ),
                  'posicion' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'r_interpretacion_matematica_posicion',
                  ),
                  'texto' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'r_interpretacion_matematica_texto',
                  ),
                ),
                'columna' => 'r_interpretacion_matematica',
              ),
              'medida' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'r_medida',
              ),
              'motivos' => 
              array (
                'clase' => 'objeto',
                'campos' => 
                array (
                ),
                'columna' => 'r_motivos',
              ),
              'puntuacion_bruta' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'FLOAT',
                  1 => 'INT',
                ),
                'columna' => 'r_puntuacion_bruta',
              ),
              'z' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'FLOAT',
                ),
                'columna' => 'r_z',
              ),
            ),
            'columna' => 'r',
          ),
        ),
        'columna' => 'dto',
      ),
    ),
    'c042' => 
    array (
      'tabla' => 'ret_mediciones_captura_ensayos_c042',
      'padre' => 'c041',
      'ruta' => '/instrumental/contenido/salida/perfil_instrumental/dominios/*/componentes/*/mediciones/*/captura/ensayos/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'escalar',
        'tipos' => 
        array (
          0 => 'INT',
        ),
        'columna' => 'dto',
      ),
    ),
    'c043' => 
    array (
      'tabla' => 'ret_componentes_mediciones_fuentes_entrada_c043',
      'padre' => 'c041',
      'ruta' => '/instrumental/contenido/salida/perfil_instrumental/dominios/*/componentes/*/mediciones/*/fuentes_entradas/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'aplicacion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'aplicacion',
          ),
          'bruto' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'BOOL',
              1 => 'STRING',
            ),
            'columna' => 'bruto',
          ),
          'campo' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'campo',
          ),
          'pregunta' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'pregunta',
          ),
          'respuesta' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'respuesta',
          ),
          'valor' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'BOOL',
              1 => 'FLOAT',
              2 => 'INT',
            ),
            'columna' => 'valor',
          ),
        ),
        'columna' => 'dto',
      ),
    ),
    'c044' => 
    array (
      'tabla' => 'ret_dominios_componentes_motivos_c044',
      'padre' => 'c036',
      'ruta' => '/instrumental/contenido/salida/perfil_instrumental/dominios/*/componentes/*/motivos/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'escalar',
        'tipos' => 
        array (
          0 => 'STRING',
        ),
        'columna' => 'dto',
      ),
    ),
    'c045' => 
    array (
      'tabla' => 'ret_perfil_instrumental_dominios_motivos_c045',
      'padre' => 'c035',
      'ruta' => '/instrumental/contenido/salida/perfil_instrumental/dominios/*/motivos/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'escalar',
        'tipos' => 
        array (
          0 => 'STRING',
        ),
        'columna' => 'dto',
      ),
    ),
    'c046' => 
    array (
      'tabla' => 'ret_perfil_instrumental_red_contratos_c046',
      'padre' => 'c001',
      'ruta' => '/instrumental/contenido/salida/perfil_instrumental/red/contratos/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'modalidades' => 
          array (
            'clase' => 'coleccion',
            'modo' => 'lista',
            'columna' => 'modalidades',
            'modelo' => 'c047',
            'identidad' => 'COLECCION',
          ),
          'tipo_destino' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'tipo_destino',
          ),
          'tipo_origen' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'tipo_origen',
          ),
          'tipo_relacion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'tipo_relacion',
          ),
        ),
        'columna' => 'dto',
      ),
    ),
    'c047' => 
    array (
      'tabla' => 'ret_red_contratos_modalidades_c047',
      'padre' => 'c046',
      'ruta' => '/instrumental/contenido/salida/perfil_instrumental/red/contratos/*/modalidades/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'escalar',
        'tipos' => 
        array (
          0 => 'STRING',
        ),
        'columna' => 'dto',
      ),
    ),
    'c048' => 
    array (
      'tabla' => 'ret_perfil_instrumental_red_dominios_c048',
      'padre' => 'c001',
      'ruta' => '/instrumental/contenido/salida/perfil_instrumental/red/dominios/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'componentes' => 
          array (
            'clase' => 'coleccion',
            'modo' => 'mapa',
            'columna' => 'componentes',
            'modelo' => 'c049',
            'identidad' => 'COLECCION',
          ),
          'concepto' => 
          array (
            'clase' => 'objeto',
            'campos' => 
            array (
              'codigo' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'concepto_codigo',
              ),
              'definicion' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'concepto_definicion',
              ),
              'estado_fuente' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'concepto_estado_fuente',
              ),
              'limites' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'concepto_limites',
              ),
              'nombre' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'concepto_nombre',
              ),
              'procedencia' => 
              array (
                'clase' => 'coleccion',
                'modo' => 'lista',
                'columna' => 'concepto_procedencia',
                'modelo' => 'c051',
                'identidad' => 'COLECCION',
              ),
              'tipo' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'concepto_tipo',
              ),
            ),
            'columna' => 'concepto',
          ),
          'nombre' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'nombre',
          ),
        ),
        'columna' => 'dto',
      ),
    ),
    'c049' => 
    array (
      'tabla' => 'ret_red_dominios_componentes_c049',
      'padre' => 'c048',
      'ruta' => '/instrumental/contenido/salida/perfil_instrumental/red/dominios/*/componentes/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'concepto' => 
          array (
            'clase' => 'objeto',
            'campos' => 
            array (
              'codigo' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'concepto_codigo',
              ),
              'definicion' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'concepto_definicion',
              ),
              'estado_fuente' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'concepto_estado_fuente',
              ),
              'limites' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'concepto_limites',
              ),
              'nombre' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'concepto_nombre',
              ),
              'procedencia' => 
              array (
                'clase' => 'coleccion',
                'modo' => 'lista',
                'columna' => 'concepto_procedencia',
                'modelo' => 'c050',
                'identidad' => 'COLECCION',
              ),
              'tipo' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'concepto_tipo',
              ),
            ),
            'columna' => 'concepto',
          ),
          'dominio' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'dominio',
          ),
          'nombre' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'nombre',
          ),
          'relacion_original' => 
          array (
            'clase' => 'objeto',
            'campos' => 
            array (
              'codigo' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'relacion_original_codigo',
              ),
              'destino' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'relacion_original_destino',
              ),
              'estado_fuente' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'relacion_original_estado_fuente',
              ),
              'fila' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'INT',
                ),
                'columna' => 'relacion_original_fila',
              ),
              'limites' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'relacion_original_limites',
              ),
              'origen' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'relacion_original_origen',
              ),
              'relacion' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'relacion_original_relacion',
              ),
              'significado' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'relacion_original_significado',
              ),
            ),
            'columna' => 'relacion_original',
          ),
        ),
        'columna' => 'dto',
      ),
    ),
    'c050' => 
    array (
      'tabla' => 'ret_componentes_concepto_procedencia_c050',
      'padre' => 'c049',
      'ruta' => '/instrumental/contenido/salida/perfil_instrumental/red/dominios/*/componentes/*/concepto/procedencia/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'escalar',
        'tipos' => 
        array (
          0 => 'INT',
        ),
        'columna' => 'dto',
      ),
    ),
    'c051' => 
    array (
      'tabla' => 'ret_dominios_concepto_procedencia_c051',
      'padre' => 'c048',
      'ruta' => '/instrumental/contenido/salida/perfil_instrumental/red/dominios/*/concepto/procedencia/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'escalar',
        'tipos' => 
        array (
          0 => 'INT',
        ),
        'columna' => 'dto',
      ),
    ),
    'c052' => 
    array (
      'tabla' => 'ret_perfil_instrumental_red_nodos_c052',
      'padre' => 'c001',
      'ruta' => '/instrumental/contenido/salida/perfil_instrumental/red/nodos/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'cod_nodo_semantico' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_nodo_semantico',
          ),
          'cod_version_modelo' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_version_modelo',
          ),
          'codigo_semantico' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'clave_semantico',
          ),
          'estado' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'estado',
          ),
          'nombre' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'nombre',
          ),
          'tipo_nodo' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'tipo_nodo',
          ),
        ),
        'columna' => 'dto',
      ),
    ),
    'c053' => 
    array (
      'tabla' => 'ret_perfil_instrumental_red_relaciones_c053',
      'padre' => 'c001',
      'ruta' => '/instrumental/contenido/salida/perfil_instrumental/red/relaciones/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'cod_nodo_destino' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_nodo_destino',
          ),
          'cod_nodo_origen' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_nodo_origen',
          ),
          'cod_relacion_semantica' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_relacion_semantica',
          ),
          'cod_version_modelo' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_version_modelo',
          ),
          'estado' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'estado',
          ),
          'modalidad_relacion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'modalidad_relacion',
          ),
          'tipo_relacion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'tipo_relacion',
          ),
        ),
        'columna' => 'dto',
      ),
    ),
    'c054' => 
    array (
      'tabla' => 'ret_salida_resultado_perfil_c054',
      'padre' => 'c001',
      'ruta' => '/instrumental/contenido/salida/resultado/perfil/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'antecedentes_descartados_por_contexto' => 
          array (
            'clase' => 'objeto',
            'campos' => 
            array (
            ),
            'columna' => 'antecedentes_descartados_por_contexto',
          ),
          'compuerta_institucional' => 
          array (
            'clase' => 'objeto',
            'campos' => 
            array (
              'contexto' => 
              array (
                'clase' => 'objeto',
                'campos' => 
                array (
                  'basal_disponible' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'BOOL',
                    ),
                    'columna' => 'compuerta_institucional_contexto_basal_disponible',
                  ),
                  'faltantes' => 
                  array (
                    'clase' => 'coleccion',
                    'modo' => 'lista',
                    'columna' => 'compuerta_institucional_contexto_faltantes',
                    'modelo' => 'c055',
                    'identidad' => 'COLECCION',
                  ),
                  'invalidacion_necesaria' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'BOOL',
                    ),
                    'columna' => 'com_ins_con_inv_nec',
                  ),
                  'modificador' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'compuerta_institucional_contexto_modificador',
                  ),
                  'suficiente' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'BOOL',
                    ),
                    'columna' => 'compuerta_institucional_contexto_suficiente',
                  ),
                ),
                'columna' => 'compuerta_institucional_contexto',
              ),
              'estado' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'compuerta_institucional_estado',
              ),
              'habilita_resultado' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'BOOL',
                ),
                'columna' => 'compuerta_institucional_habilita_r',
              ),
              'modificador' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'compuerta_institucional_modificador',
              ),
            ),
            'columna' => 'compuerta_institucional',
          ),
          'conclusiones_candidatas' => 
          array (
            'clase' => 'objeto',
            'campos' => 
            array (
            ),
            'columna' => 'conclusiones_candidatas',
          ),
          'condiciones_pendientes' => 
          array (
            'clase' => 'coleccion',
            'modo' => 'lista',
            'columna' => 'condiciones_pendientes',
            'modelo' => 'c056',
            'identidad' => 'COLECCION',
          ),
          'contradicciones' => 
          array (
            'clase' => 'objeto',
            'campos' => 
            array (
            ),
            'columna' => 'contradicciones',
          ),
          'criterio' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'criterio',
          ),
          'estado_institucional' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'estado_institucional',
          ),
          'motivos_no_emision' => 
          array (
            'clase' => 'objeto',
            'campos' => 
            array (
            ),
            'columna' => 'motivos_no_emision',
          ),
          'resultado_institucional' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'r_institucional',
          ),
          'traza' => 
          array (
            'clase' => 'objeto',
            'campos' => 
            array (
              'compuerta' => 
              array (
                'clase' => 'objeto',
                'campos' => 
                array (
                  'alcance' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'traza_compuerta_alcance',
                  ),
                  'sin_interpretacion_A_P' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'BOOL',
                    ),
                    'columna' => 'traza_compuerta_sin_interpretacion_a_p',
                  ),
                ),
                'columna' => 'traza_compuerta',
              ),
              'criterio' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'traza_criterio',
              ),
              'evaluacion' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'traza_evaluacion',
              ),
              'evidencias' => 
              array (
                'clase' => 'coleccion',
                'modo' => 'lista',
                'columna' => 'traza_evidencias',
                'modelo' => 'c057',
                'identidad' => 'COLECCION',
              ),
              'fecha_corte' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'traza_fecha_corte',
              ),
              'reglas' => 
              array (
                'clase' => 'coleccion',
                'modo' => 'lista',
                'columna' => 'traza_reglas',
                'modelo' => 'c060',
                'identidad' => 'COLECCION',
              ),
              'relaciones_evidencias' => 
              array (
                'clase' => 'objeto',
                'campos' => 
                array (
                ),
                'columna' => 'traza_relaciones_evidencias',
              ),
              'residente' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'traza_residente',
              ),
              'resultado' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'traza_r',
              ),
              'rutas' => 
              array (
                'clase' => 'coleccion',
                'modo' => 'lista',
                'columna' => 'traza_rutas',
                'modelo' => 'c067',
                'identidad' => 'COLECCION',
              ),
              'rutas_hallazgos' => 
              array (
                'clase' => 'objeto',
                'campos' => 
                array (
                ),
                'columna' => 'traza_rutas_hallazgos',
              ),
              'sin_emision' => 
              array (
                'clase' => 'objeto',
                'campos' => 
                array (
                ),
                'columna' => 'traza_sin_emision',
              ),
              'version' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'traza_version',
              ),
            ),
            'columna' => 'traza',
          ),
        ),
        'columna' => 'dto',
      ),
    ),
    'c055' => 
    array (
      'tabla' => 'ret_compuerta_institucional_contexto_falta_c055',
      'padre' => 'c054',
      'ruta' => '/instrumental/contenido/salida/resultado/perfil/*/compuerta_institucional/contexto/faltantes/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'escalar',
        'tipos' => 
        array (
          0 => 'STRING',
        ),
        'columna' => 'dto',
      ),
    ),
    'c056' => 
    array (
      'tabla' => 'ret_resultado_perfil_condiciones_pendiente_c056',
      'padre' => 'c054',
      'ruta' => '/instrumental/contenido/salida/resultado/perfil/*/condiciones_pendientes/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'codigo_regla' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'clave_regla',
          ),
          'condicion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'condicion',
          ),
          'regla' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'regla',
          ),
          'valor' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'valor',
          ),
          'valor_codigo' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'valor_codigo',
          ),
          'variable' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'variable',
          ),
          'variable_codigo' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'variable_codigo',
          ),
        ),
        'columna' => 'dto',
      ),
    ),
    'c057' => 
    array (
      'tabla' => 'ret_perfil_traza_evidencias_c057',
      'padre' => 'c054',
      'ruta' => '/instrumental/contenido/salida/resultado/perfil/*/traza/evidencias/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'admisibilidad' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'admisibilidad',
          ),
          'campo' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'campo',
          ),
          'fecha_fuente' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'fecha_fuente',
          ),
          'fuente' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'fuente',
          ),
          'id' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'id',
          ),
          'mapeo_valor' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'mapeo_valor',
          ),
          'mapeo_variable' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'mapeo_variable',
          ),
          'motivos' => 
          array (
            'clase' => 'objeto',
            'campos' => 
            array (
            ),
            'columna' => 'motivos',
          ),
          'procedencia_instrumental' => 
          array (
            'clase' => 'objeto',
            'campos' => 
            array (
              'campo_original' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'procedencia_instrumental_campo_original',
              ),
              'cod_aplicacion' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'procedencia_instrumental_cod_aplicacion',
              ),
              'cod_personal_original' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'procedencia_instrumental_cod_personal_original',
              ),
              'cod_pregunta' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'procedencia_instrumental_cod_pregunta',
              ),
              'cod_respuesta' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'procedencia_instrumental_cod_respuesta',
              ),
              'episodio_original' => 
              array (
                'clase' => 'objeto',
                'campos' => 
                array (
                  'cod_atencion' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'pro_ins_epi_ori_cod_ate',
                  ),
                  'grupo_soporte' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'pro_ins_epi_ori_gru_sop',
                  ),
                  'independencia_clinica' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'pro_ins_epi_ori_ind_cli',
                  ),
                  'unidad_conjuncion' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'pro_ins_epi_ori_uni_con',
                  ),
                ),
                'columna' => 'procedencia_instrumental_episodio_original',
              ),
              'fuente_edad' => 
              array (
                'clase' => 'objeto',
                'campos' => 
                array (
                  'campo' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'procedencia_instrumental_fuente_edad_campo',
                  ),
                  'fecha_aplicacion' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'pro_ins_fue_eda_fec_apl',
                  ),
                  'fecha_nacimiento' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'pro_ins_fue_eda_fec_nac',
                  ),
                  'registro' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'procedencia_instrumental_fuente_edad_registro',
                  ),
                  'tabla' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'procedencia_instrumental_fuente_edad_tabla',
                  ),
                ),
                'columna' => 'procedencia_instrumental_fuente_edad',
              ),
              'instrumento' => 
              array (
                'clase' => 'objeto',
                'campos' => 
                array (
                  'cod_instrumento' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'pro_ins_ins_cod_ins',
                  ),
                  'codigo' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'procedencia_instrumental_instrumento_codigo',
                  ),
                  'estado' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'procedencia_instrumental_instrumento_estado',
                  ),
                  'nombre' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'procedencia_instrumental_instrumento_nombre',
                  ),
                  'tipo' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'procedencia_instrumental_instrumento_tipo',
                  ),
                  'version' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'procedencia_instrumental_instrumento_version',
                  ),
                ),
                'columna' => 'procedencia_instrumental_instrumento',
              ),
              'mediciones_interpretacion' => 
              array (
                'clase' => 'objeto',
                'campos' => 
                array (
                  'aciertos' => 
                  array (
                    'clase' => 'objeto',
                    'campos' => 
                    array (
                      'campo' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'STRING',
                        ),
                        'columna' => 'pro_ins_med_int_aci_cam',
                      ),
                      'cod_aplicacion' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'STRING',
                        ),
                        'columna' => 'pro_ins_med_int_aci_cod_apl',
                      ),
                      'cod_pregunta' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'STRING',
                        ),
                        'columna' => 'pro_ins_med_int_aci_cod_pre',
                      ),
                      'cod_respuesta' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'STRING',
                        ),
                        'columna' => 'pro_ins_med_int_aci_cod_res',
                      ),
                      'valor_original' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'STRING',
                        ),
                        'columna' => 'pro_ins_med_int_aci_val_ori',
                      ),
                    ),
                    'columna' => 'pro_ins_med_int_aci',
                  ),
                ),
                'columna' => 'pro_ins_med_int',
              ),
              'perfil_interpretativo' => 
              array (
                'clase' => 'objeto',
                'campos' => 
                array (
                  'codigo' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'pro_ins_per_int_cod',
                  ),
                  'componente' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'pro_ins_per_int_com',
                  ),
                  'fuente' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'pro_ins_per_int_fue',
                  ),
                  'instrumento' => 
                  array (
                    'clase' => 'objeto',
                    'campos' => 
                    array (
                      'codigo' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'STRING',
                        ),
                        'columna' => 'pro_ins_per_int_ins_cod',
                      ),
                      'version' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'STRING',
                        ),
                        'columna' => 'pro_ins_per_int_ins_ver',
                      ),
                    ),
                    'columna' => 'pro_ins_per_int_ins',
                  ),
                  'mediciones_requeridas' => 
                  array (
                    'clase' => 'coleccion',
                    'modo' => 'mapa',
                    'columna' => 'pro_ins_per_int_med_req',
                    'modelo' => 'c058',
                    'identidad' => 'COLECCION',
                  ),
                  'metodo' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'pro_ins_per_int_met',
                  ),
                  'predicados' => 
                  array (
                    'clase' => 'coleccion',
                    'modo' => 'lista',
                    'columna' => 'pro_ins_per_int_pre',
                    'modelo' => 'c059',
                    'identidad' => 'COLECCION',
                  ),
                  'version' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'pro_ins_per_int_ver',
                  ),
                ),
                'columna' => 'procedencia_instrumental_perfil_interpretativo',
              ),
              'pregunta_original' => 
              array (
                'clase' => 'objeto',
                'campos' => 
                array (
                  'cod_instrumento' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'pro_ins_pre_ori_cod_ins',
                  ),
                  'cod_pregunta' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'pro_ins_pre_ori_cod_pre',
                  ),
                  'tipo_respuesta' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'pro_ins_pre_ori_tip_res',
                  ),
                ),
                'columna' => 'procedencia_instrumental_pregunta_original',
              ),
              'registro_original' => 
              array (
                'clase' => 'objeto',
                'campos' => 
                array (
                  'cod_aplicacion_anterior' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'pro_ins_reg_ori_cod_apl_ant',
                  ),
                  'cod_documento_clinico' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'pro_ins_reg_ori_cod_doc_cli',
                  ),
                  'cod_usuario_registro' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'pro_ins_reg_ori_cod_usu_reg',
                  ),
                  'localizador_fuente' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'pro_ins_reg_ori_loc_fue',
                  ),
                  'registrado_en' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'pro_ins_reg_ori_reg_en',
                  ),
                ),
                'columna' => 'procedencia_instrumental_registro_original',
              ),
            ),
            'columna' => 'procedencia_instrumental',
          ),
          'registro' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'registro',
          ),
          'representacion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'representacion',
          ),
          'tabla' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'tabla',
          ),
          'valor' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'valor',
          ),
          'valor_original' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'BOOL',
            ),
            'columna' => 'valor_original',
          ),
          'variable' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'variable',
          ),
        ),
        'columna' => 'dto',
      ),
    ),
    'c058' => 
    array (
      'tabla' => 'ret_procedencia_instrumental_perfil_interp_c058',
      'padre' => 'c057',
      'ruta' => '/instrumental/contenido/salida/resultado/perfil/*/traza/evidencias/*/procedencia_instrumental/perfil_interpretativo/mediciones_requeridas/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'campo' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'campo',
          ),
          'cod_pregunta' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_pregunta',
          ),
        ),
        'columna' => 'dto',
      ),
    ),
    'c059' => 
    array (
      'tabla' => 'ret_procedencia_instrumental_perfil_interp_c059',
      'padre' => 'c057',
      'ruta' => '/instrumental/contenido/salida/resultado/perfil/*/traza/evidencias/*/procedencia_instrumental/perfil_interpretativo/predicados/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'escalar',
        'tipos' => 
        array (
          0 => 'STRING',
        ),
        'columna' => 'dto',
      ),
    ),
    'c060' => 
    array (
      'tabla' => 'ret_perfil_traza_reglas_c060',
      'padre' => 'c054',
      'ruta' => '/instrumental/contenido/salida/resultado/perfil/*/traza/reglas/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'alcance' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'alcance',
          ),
          'codigo_regla' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'clave_regla',
          ),
          'condiciones' => 
          array (
            'clase' => 'coleccion',
            'modo' => 'lista',
            'columna' => 'condiciones',
            'modelo' => 'c061',
            'identidad' => 'COLECCION',
          ),
          'consecuencia' => 
          array (
            'clase' => 'objeto',
            'campos' => 
            array (
              'cod_consecuencia_regla' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'consecuencia_cod_consecuencia_regla',
              ),
              'cod_criterio_dominio_resultado' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'consecuencia_cod_criterio_dominio_r',
              ),
              'cod_regla_experta' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'consecuencia_cod_regla_experta',
              ),
              'cod_valor_semantico' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'consecuencia_cod_valor_semantico',
              ),
            ),
            'columna' => 'consecuencia',
          ),
          'estado' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'estado',
          ),
          'evaluaciones_unidad' => 
          array (
            'clase' => 'coleccion',
            'modo' => 'mapa',
            'columna' => 'evaluaciones_unidad',
            'modelo' => 'c063',
            'identidad' => 'COLECCION',
          ),
          'regla' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'regla',
          ),
          'unidades_por_componente' => 
          array (
            'clase' => 'coleccion',
            'modo' => 'mapa',
            'columna' => 'unidades_por_componente',
            'modelo' => 'c066',
            'identidad' => 'COLECCION',
          ),
        ),
        'columna' => 'dto',
      ),
    ),
    'c061' => 
    array (
      'tabla' => 'ret_traza_reglas_condiciones_c061',
      'padre' => 'c060',
      'ruta' => '/instrumental/contenido/salida/resultado/perfil/*/traza/reglas/*/condiciones/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'condicion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'condicion',
          ),
          'estado' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'estado',
          ),
          'soportes' => 
          array (
            'clase' => 'coleccion',
            'modo' => 'lista',
            'columna' => 'soportes',
            'modelo' => 'c062',
            'identidad' => 'COLECCION',
          ),
          'valor' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'valor',
          ),
          'valor_codigo' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'valor_codigo',
          ),
          'variable' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'variable',
          ),
          'variable_codigo' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'variable_codigo',
          ),
        ),
        'columna' => 'dto',
      ),
    ),
    'c062' => 
    array (
      'tabla' => 'ret_reglas_condiciones_soportes_c062',
      'padre' => 'c061',
      'ruta' => '/instrumental/contenido/salida/resultado/perfil/*/traza/reglas/*/condiciones/*/soportes/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'escalar',
        'tipos' => 
        array (
          0 => 'STRING',
        ),
        'columna' => 'dto',
      ),
    ),
    'c063' => 
    array (
      'tabla' => 'ret_traza_reglas_evaluaciones_unidad_c063',
      'padre' => 'c060',
      'ruta' => '/instrumental/contenido/salida/resultado/perfil/*/traza/reglas/*/evaluaciones_unidad/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'condiciones' => 
          array (
            'clase' => 'coleccion',
            'modo' => 'lista',
            'columna' => 'condiciones',
            'modelo' => 'c064',
            'identidad' => 'COLECCION',
          ),
          'consecuencia' => 
          array (
            'clase' => 'objeto',
            'campos' => 
            array (
              'cod_consecuencia_regla' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'consecuencia_cod_consecuencia_regla',
              ),
              'cod_criterio_dominio_resultado' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'consecuencia_cod_criterio_dominio_r',
              ),
              'cod_regla_experta' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'consecuencia_cod_regla_experta',
              ),
              'cod_valor_semantico' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'consecuencia_cod_valor_semantico',
              ),
            ),
            'columna' => 'consecuencia',
          ),
          'estado' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'estado',
          ),
          'regla' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'regla',
          ),
        ),
        'columna' => 'dto',
      ),
    ),
    'c064' => 
    array (
      'tabla' => 'ret_reglas_evaluaciones_unidad_condiciones_c064',
      'padre' => 'c063',
      'ruta' => '/instrumental/contenido/salida/resultado/perfil/*/traza/reglas/*/evaluaciones_unidad/*/condiciones/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'condicion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'condicion',
          ),
          'estado' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'estado',
          ),
          'soportes' => 
          array (
            'clase' => 'coleccion',
            'modo' => 'lista',
            'columna' => 'soportes',
            'modelo' => 'c065',
            'identidad' => 'COLECCION',
          ),
          'valor' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'valor',
          ),
          'variable' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'variable',
          ),
        ),
        'columna' => 'dto',
      ),
    ),
    'c065' => 
    array (
      'tabla' => 'ret_evaluaciones_unidad_condiciones_soport_c065',
      'padre' => 'c064',
      'ruta' => '/instrumental/contenido/salida/resultado/perfil/*/traza/reglas/*/evaluaciones_unidad/*/condiciones/*/soportes/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'escalar',
        'tipos' => 
        array (
          0 => 'STRING',
        ),
        'columna' => 'dto',
      ),
    ),
    'c066' => 
    array (
      'tabla' => 'ret_traza_reglas_unidades_por_componente_c066',
      'padre' => 'c060',
      'ruta' => '/instrumental/contenido/salida/resultado/perfil/*/traza/reglas/*/unidades_por_componente/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
        ),
        'columna' => 'dto',
      ),
    ),
    'c067' => 
    array (
      'tabla' => 'ret_perfil_traza_rutas_c067',
      'padre' => 'c054',
      'ruta' => '/instrumental/contenido/salida/resultado/perfil/*/traza/rutas/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'campo' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'campo',
          ),
          'condicion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'condicion',
          ),
          'criterio' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'criterio',
          ),
          'estado_regla' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'estado_regla',
          ),
          'evaluacion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'evaluacion',
          ),
          'evidencia' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'evidencia',
          ),
          'fecha_fuente' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'fecha_fuente',
          ),
          'fuente' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'fuente',
          ),
          'mapeo_valor' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'mapeo_valor',
          ),
          'mapeo_variable' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'mapeo_variable',
          ),
          'personal' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'personal',
          ),
          'procedencia_instrumental' => 
          array (
            'clase' => 'objeto',
            'campos' => 
            array (
              'campo_original' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'procedencia_instrumental_campo_original',
              ),
              'cod_aplicacion' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'procedencia_instrumental_cod_aplicacion',
              ),
              'cod_personal_original' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'procedencia_instrumental_cod_personal_original',
              ),
              'cod_pregunta' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'procedencia_instrumental_cod_pregunta',
              ),
              'cod_respuesta' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'procedencia_instrumental_cod_respuesta',
              ),
              'episodio_original' => 
              array (
                'clase' => 'objeto',
                'campos' => 
                array (
                  'cod_atencion' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'pro_ins_epi_ori_cod_ate',
                  ),
                  'grupo_soporte' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'pro_ins_epi_ori_gru_sop',
                  ),
                  'independencia_clinica' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'pro_ins_epi_ori_ind_cli',
                  ),
                  'unidad_conjuncion' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'pro_ins_epi_ori_uni_con',
                  ),
                ),
                'columna' => 'procedencia_instrumental_episodio_original',
              ),
              'fuente_edad' => 
              array (
                'clase' => 'objeto',
                'campos' => 
                array (
                  'campo' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'procedencia_instrumental_fuente_edad_campo',
                  ),
                  'fecha_aplicacion' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'pro_ins_fue_eda_fec_apl',
                  ),
                  'fecha_nacimiento' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'pro_ins_fue_eda_fec_nac',
                  ),
                  'registro' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'procedencia_instrumental_fuente_edad_registro',
                  ),
                  'tabla' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'procedencia_instrumental_fuente_edad_tabla',
                  ),
                ),
                'columna' => 'procedencia_instrumental_fuente_edad',
              ),
              'instrumento' => 
              array (
                'clase' => 'objeto',
                'campos' => 
                array (
                  'cod_instrumento' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'pro_ins_ins_cod_ins',
                  ),
                  'codigo' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'procedencia_instrumental_instrumento_codigo',
                  ),
                  'estado' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'procedencia_instrumental_instrumento_estado',
                  ),
                  'nombre' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'procedencia_instrumental_instrumento_nombre',
                  ),
                  'tipo' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'procedencia_instrumental_instrumento_tipo',
                  ),
                  'version' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'procedencia_instrumental_instrumento_version',
                  ),
                ),
                'columna' => 'procedencia_instrumental_instrumento',
              ),
              'mediciones_interpretacion' => 
              array (
                'clase' => 'objeto',
                'campos' => 
                array (
                  'aciertos' => 
                  array (
                    'clase' => 'objeto',
                    'campos' => 
                    array (
                      'campo' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'STRING',
                        ),
                        'columna' => 'pro_ins_med_int_aci_cam',
                      ),
                      'cod_aplicacion' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'STRING',
                        ),
                        'columna' => 'pro_ins_med_int_aci_cod_apl',
                      ),
                      'cod_pregunta' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'STRING',
                        ),
                        'columna' => 'pro_ins_med_int_aci_cod_pre',
                      ),
                      'cod_respuesta' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'STRING',
                        ),
                        'columna' => 'pro_ins_med_int_aci_cod_res',
                      ),
                      'valor_original' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'STRING',
                        ),
                        'columna' => 'pro_ins_med_int_aci_val_ori',
                      ),
                    ),
                    'columna' => 'pro_ins_med_int_aci',
                  ),
                ),
                'columna' => 'pro_ins_med_int',
              ),
              'perfil_interpretativo' => 
              array (
                'clase' => 'objeto',
                'campos' => 
                array (
                  'codigo' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'pro_ins_per_int_cod',
                  ),
                  'componente' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'pro_ins_per_int_com',
                  ),
                  'fuente' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'pro_ins_per_int_fue',
                  ),
                  'instrumento' => 
                  array (
                    'clase' => 'objeto',
                    'campos' => 
                    array (
                      'codigo' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'STRING',
                        ),
                        'columna' => 'pro_ins_per_int_ins_cod',
                      ),
                      'version' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'STRING',
                        ),
                        'columna' => 'pro_ins_per_int_ins_ver',
                      ),
                    ),
                    'columna' => 'pro_ins_per_int_ins',
                  ),
                  'mediciones_requeridas' => 
                  array (
                    'clase' => 'coleccion',
                    'modo' => 'mapa',
                    'columna' => 'pro_ins_per_int_med_req',
                    'modelo' => 'c068',
                    'identidad' => 'COLECCION',
                  ),
                  'metodo' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'pro_ins_per_int_met',
                  ),
                  'predicados' => 
                  array (
                    'clase' => 'coleccion',
                    'modo' => 'lista',
                    'columna' => 'pro_ins_per_int_pre',
                    'modelo' => 'c069',
                    'identidad' => 'COLECCION',
                  ),
                  'version' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'pro_ins_per_int_ver',
                  ),
                ),
                'columna' => 'procedencia_instrumental_perfil_interpretativo',
              ),
              'pregunta_original' => 
              array (
                'clase' => 'objeto',
                'campos' => 
                array (
                  'cod_instrumento' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'pro_ins_pre_ori_cod_ins',
                  ),
                  'cod_pregunta' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'pro_ins_pre_ori_cod_pre',
                  ),
                  'tipo_respuesta' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'pro_ins_pre_ori_tip_res',
                  ),
                ),
                'columna' => 'procedencia_instrumental_pregunta_original',
              ),
              'registro_original' => 
              array (
                'clase' => 'objeto',
                'campos' => 
                array (
                  'cod_aplicacion_anterior' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'pro_ins_reg_ori_cod_apl_ant',
                  ),
                  'cod_documento_clinico' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'pro_ins_reg_ori_cod_doc_cli',
                  ),
                  'cod_usuario_registro' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'pro_ins_reg_ori_cod_usu_reg',
                  ),
                  'localizador_fuente' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'pro_ins_reg_ori_loc_fue',
                  ),
                  'registrado_en' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'pro_ins_reg_ori_reg_en',
                  ),
                ),
                'columna' => 'procedencia_instrumental_registro_original',
              ),
            ),
            'columna' => 'procedencia_instrumental',
          ),
          'registro' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'registro',
          ),
          'regla' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'regla',
          ),
          'residente' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'residente',
          ),
          'tabla' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'tabla',
          ),
          'valor' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'valor',
          ),
          'valor_original' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'BOOL',
            ),
            'columna' => 'valor_original',
          ),
          'variable' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'variable',
          ),
          'version' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'version',
          ),
        ),
        'columna' => 'dto',
      ),
    ),
    'c068' => 
    array (
      'tabla' => 'ret_procedencia_instrumental_perfil_interp_c068',
      'padre' => 'c067',
      'ruta' => '/instrumental/contenido/salida/resultado/perfil/*/traza/rutas/*/procedencia_instrumental/perfil_interpretativo/mediciones_requeridas/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'campo' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'campo',
          ),
          'cod_pregunta' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_pregunta',
          ),
        ),
        'columna' => 'dto',
      ),
    ),
    'c069' => 
    array (
      'tabla' => 'ret_procedencia_instrumental_perfil_interp_c069',
      'padre' => 'c067',
      'ruta' => '/instrumental/contenido/salida/resultado/perfil/*/traza/rutas/*/procedencia_instrumental/perfil_interpretativo/predicados/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'escalar',
        'tipos' => 
        array (
          0 => 'STRING',
        ),
        'columna' => 'dto',
      ),
    ),
    'c070' => 
    array (
      'tabla' => 'ret_resultado_red_componentes_c070',
      'padre' => 'c001',
      'ruta' => '/instrumental/contenido/salida/resultado/red/componentes/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'dominio' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'dominio',
          ),
          'estado' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'estado',
          ),
          'nodo' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'nodo',
          ),
          'relacion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'relacion',
          ),
        ),
        'columna' => 'dto',
      ),
    ),
    'c071' => 
    array (
      'tabla' => 'ret_resultado_red_dominios_c071',
      'padre' => 'c001',
      'ruta' => '/instrumental/contenido/salida/resultado/red/dominios/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'escalar',
        'tipos' => 
        array (
          0 => 'STRING',
        ),
        'columna' => 'dto',
      ),
    ),
    'c072' => 
    array (
      'tabla' => 'ret_resultado_red_rutas_reglas_c072',
      'padre' => 'c001',
      'ruta' => '/instrumental/contenido/salida/resultado/red/rutas_reglas/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'coleccion',
        'modo' => 'lista',
        'columna' => 'dto',
        'modelo' => 'c073',
        'identidad' => 'COLECCION',
      ),
    ),
    'c073' => 
    array (
      'tabla' => 'ret_resultado_red_rutas_reglas_c073',
      'padre' => 'c072',
      'ruta' => '/instrumental/contenido/salida/resultado/red/rutas_reglas/*/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'condicion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'condicion',
          ),
          'consecuencia' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'consecuencia',
          ),
          'criterio' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'criterio',
          ),
          'pertenencia' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'pertenencia',
          ),
          'propietario' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'propietario',
          ),
          'variable' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'variable',
          ),
        ),
        'columna' => 'dto',
      ),
    ),
    'c074' => 
    array (
      'tabla' => 'ret_instrumental_contenido_vinculos_c074',
      'padre' => 'c001',
      'ruta' => '/instrumental/contenido/vinculos/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'campo' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'campo',
          ),
          'cod_instrumento' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_instrumento',
          ),
          'cod_pregunta' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_pregunta',
          ),
          'codigo_instrumento' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'clave_instrumento',
          ),
          'componente' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'componente',
          ),
          'perfil_interpretativo' => 
          array (
            'clase' => 'objeto',
            'campos' => 
            array (
              'codigo' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'perfil_interpretativo_codigo',
              ),
              'componente' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'perfil_interpretativo_componente',
              ),
              'fuente' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'perfil_interpretativo_fuente',
              ),
              'instrumento' => 
              array (
                'clase' => 'objeto',
                'campos' => 
                array (
                  'codigo' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'perfil_interpretativo_instrumento_codigo',
                  ),
                  'version' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'perfil_interpretativo_instrumento_version',
                  ),
                ),
                'columna' => 'perfil_interpretativo_instrumento',
              ),
              'mediciones_requeridas' => 
              array (
                'clase' => 'coleccion',
                'modo' => 'mapa',
                'columna' => 'perfil_interpretativo_mediciones_requeridas',
                'modelo' => 'c075',
                'identidad' => 'COLECCION',
              ),
              'metodo' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'perfil_interpretativo_metodo',
              ),
              'predicados' => 
              array (
                'clase' => 'coleccion',
                'modo' => 'lista',
                'columna' => 'perfil_interpretativo_predicados',
                'modelo' => 'c076',
                'identidad' => 'COLECCION',
              ),
              'version' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'perfil_interpretativo_version',
              ),
            ),
            'columna' => 'perfil_interpretativo',
          ),
          'version_instrumento' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'version_instrumento',
          ),
        ),
        'columna' => 'dto',
      ),
    ),
    'c075' => 
    array (
      'tabla' => 'ret_vinculos_perfil_interpretativo_medicio_c075',
      'padre' => 'c074',
      'ruta' => '/instrumental/contenido/vinculos/*/perfil_interpretativo/mediciones_requeridas/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'campo' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'campo',
          ),
          'cod_pregunta' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_pregunta',
          ),
        ),
        'columna' => 'dto',
      ),
    ),
    'c076' => 
    array (
      'tabla' => 'ret_vinculos_perfil_interpretativo_predica_c076',
      'padre' => 'c074',
      'ruta' => '/instrumental/contenido/vinculos/*/perfil_interpretativo/predicados/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'escalar',
        'tipos' => 
        array (
          0 => 'STRING',
        ),
        'columna' => 'dto',
      ),
    ),
    'c077' => 
    array (
      'tabla' => 'ret_contenido_adquisicion_exclusiones_c077',
      'padre' => 'c001',
      'ruta' => '/petersen/contenido/adquisicion/exclusiones/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'elemento' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'elemento',
          ),
          'motivo' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'motivo',
          ),
          'valoracion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'valoracion',
          ),
        ),
        'columna' => 'dto',
      ),
    ),
    'c078' => 
    array (
      'tabla' => 'ret_petersen_contenido_constancias_documen_c078',
      'padre' => 'c001',
      'ruta' => '/petersen/contenido/constancias_documentales/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'cod_documento_clinico' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_documento_clinico',
          ),
          'cod_personal' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_personal',
          ),
          'cod_residente' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_residente',
          ),
          'estado' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'estado',
          ),
          'fecha_hora' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'fecha',
          ),
          'hash_archivo' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'hash_archivo',
          ),
          'localizador_fuente' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'localizador_fuente',
          ),
          'papel' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'papel',
          ),
          'valoracion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'valoracion',
          ),
          'verificado' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'BOOL',
            ),
            'columna' => 'verificado',
          ),
        ),
        'columna' => 'dto',
      ),
    ),
    'c079' => 
    array (
      'tabla' => 'ret_gobierno_conocimiento_contratos_relaci_c079',
      'padre' => 'c001',
      'ruta' => '/petersen/contenido/gobierno/conocimiento/contratos_relaciones/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'habilita_participacion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'BOOL',
            ),
            'columna' => 'habilita_participacion',
          ),
          'modalidades' => 
          array (
            'clase' => 'coleccion',
            'modo' => 'lista',
            'columna' => 'modalidades',
            'modelo' => 'c080',
            'identidad' => 'COLECCION',
          ),
          'tipo_destino' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'tipo_destino',
          ),
          'tipo_origen' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'tipo_origen',
          ),
          'tipo_relacion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'tipo_relacion',
          ),
        ),
        'columna' => 'dto',
      ),
    ),
    'c080' => 
    array (
      'tabla' => 'ret_conocimiento_contratos_relaciones_moda_c080',
      'padre' => 'c079',
      'ruta' => '/petersen/contenido/gobierno/conocimiento/contratos_relaciones/*/modalidades/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'escalar',
        'tipos' => 
        array (
          0 => 'STRING',
        ),
        'columna' => 'dto',
      ),
    ),
    'c081' => 
    array (
      'tabla' => 'ret_conocimiento_tablas_condiciones_regla__c081',
      'padre' => 'c001',
      'ruta' => '/petersen/contenido/gobierno/conocimiento/tablas/condiciones_regla_experta/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'cod_condicion_regla' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_condicion_regla',
          ),
          'cod_regla_experta' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_regla_experta',
          ),
          'cod_valor_semantico' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_valor_semantico',
          ),
          'cod_variable_experta' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_variable_experta',
          ),
        ),
        'columna' => 'dto',
      ),
    ),
    'c082' => 
    array (
      'tabla' => 'ret_conocimiento_tablas_consecuencias_regl_c082',
      'padre' => 'c001',
      'ruta' => '/petersen/contenido/gobierno/conocimiento/tablas/consecuencias_regla_experta/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'cod_consecuencia_regla' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_consecuencia_regla',
          ),
          'cod_criterio_dominio_resultado' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_criterio_dominio_r',
          ),
          'cod_regla_experta' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_regla_experta',
          ),
          'cod_valor_semantico' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_valor_semantico',
          ),
        ),
        'columna' => 'dto',
      ),
    ),
    'c083' => 
    array (
      'tabla' => 'ret_conocimiento_tablas_criterios_dominios_c083',
      'padre' => 'c001',
      'ruta' => '/petersen/contenido/gobierno/conocimiento/tablas/criterios_dominios_resultado/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'cod_criterio_dominio_resultado' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_criterio_dominio_r',
          ),
          'cod_dominio_valores' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_dominio_valores',
          ),
          'cod_nodo_criterio' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_nodo_criterio',
          ),
          'cod_usuario_creacion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_usuario_creacion',
          ),
          'estado' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'estado',
          ),
          'fecha_hora_creacion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'fecha_creacion',
          ),
          'observacion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'observacion',
          ),
        ),
        'columna' => 'dto',
      ),
    ),
    'c084' => 
    array (
      'tabla' => 'ret_conocimiento_tablas_dominios_valores_e_c084',
      'padre' => 'c001',
      'ruta' => '/petersen/contenido/gobierno/conocimiento/tablas/dominios_valores_expertos/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'cod_dominio_valores' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_dominio_valores',
          ),
          'cod_usuario_creacion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_usuario_creacion',
          ),
          'cod_version_modelo' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_version_modelo',
          ),
          'codigo_dominio' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'clave_dominio',
          ),
          'descripcion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'descripcion',
          ),
          'estado' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'estado',
          ),
          'fecha_hora_creacion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'fecha_creacion',
          ),
          'nombre' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'nombre',
          ),
          'observacion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'observacion',
          ),
          'tipo_dominio' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'tipo_dominio',
          ),
        ),
        'columna' => 'dto',
      ),
    ),
    'c085' => 
    array (
      'tabla' => 'ret_conocimiento_tablas_nodos_semanticos_c085',
      'padre' => 'c001',
      'ruta' => '/petersen/contenido/gobierno/conocimiento/tablas/nodos_semanticos/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'cod_nodo_semantico' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_nodo_semantico',
          ),
          'cod_usuario_creacion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_usuario_creacion',
          ),
          'cod_version_modelo' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_version_modelo',
          ),
          'codigo_semantico' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'clave_semantico',
          ),
          'definicion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'definicion',
          ),
          'estado' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'estado',
          ),
          'fecha_hora_creacion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'fecha_creacion',
          ),
          'nombre' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'nombre',
          ),
          'observacion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'observacion',
          ),
          'tipo_nodo' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'tipo_nodo',
          ),
        ),
        'columna' => 'dto',
      ),
    ),
    'c086' => 
    array (
      'tabla' => 'ret_conocimiento_tablas_reglas_expertas_c086',
      'padre' => 'c001',
      'ruta' => '/petersen/contenido/gobierno/conocimiento/tablas/reglas_expertas/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'cod_regla_anterior' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_regla_anterior',
          ),
          'cod_regla_experta' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_regla_experta',
          ),
          'cod_usuario_creacion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_usuario_creacion',
          ),
          'cod_usuario_revision' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_usuario_revision',
          ),
          'cod_version_modelo' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_version_modelo',
          ),
          'codigo_regla' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'clave_regla',
          ),
          'descripcion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'descripcion',
          ),
          'estado' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'estado',
          ),
          'estado_revision' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'estado_revision',
          ),
          'fecha_hora_creacion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'fecha_creacion',
          ),
          'fecha_hora_revision' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'fecha_revision',
          ),
          'justificacion_revision' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'justificacion_revision',
          ),
          'nombre' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'nombre',
          ),
          'observacion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'observacion',
          ),
          'tipo_regla' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'tipo_regla',
          ),
        ),
        'columna' => 'dto',
      ),
    ),
    'c087' => 
    array (
      'tabla' => 'ret_conocimiento_tablas_relaciones_semanti_c087',
      'padre' => 'c001',
      'ruta' => '/petersen/contenido/gobierno/conocimiento/tablas/relaciones_semanticas/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'cod_nodo_destino' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_nodo_destino',
          ),
          'cod_nodo_origen' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_nodo_origen',
          ),
          'cod_relacion_semantica' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_relacion_semantica',
          ),
          'cod_usuario_creacion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_usuario_creacion',
          ),
          'cod_version_modelo' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_version_modelo',
          ),
          'estado' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'estado',
          ),
          'fecha_hora_creacion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'fecha_creacion',
          ),
          'modalidad_relacion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'modalidad_relacion',
          ),
          'observacion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'observacion',
          ),
          'significado' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'significado',
          ),
          'tipo_relacion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'tipo_relacion',
          ),
        ),
        'columna' => 'dto',
      ),
    ),
    'c088' => 
    array (
      'tabla' => 'ret_conocimiento_tablas_valores_semanticos_c088',
      'padre' => 'c001',
      'ruta' => '/petersen/contenido/gobierno/conocimiento/tablas/valores_semanticos/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'cod_dominio_valores' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_dominio_valores',
          ),
          'cod_usuario_creacion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_usuario_creacion',
          ),
          'cod_valor_semantico' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_valor_semantico',
          ),
          'codigo_valor' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'clave_valor',
          ),
          'definicion_semantica' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'definicion_semantica',
          ),
          'estado' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'estado',
          ),
          'estado_aprobacion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'estado_aprobacion',
          ),
          'fecha_hora_creacion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'fecha_creacion',
          ),
          'nombre' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'nombre',
          ),
          'observacion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'observacion',
          ),
        ),
        'columna' => 'dto',
      ),
    ),
    'c089' => 
    array (
      'tabla' => 'ret_conocimiento_tablas_variables_expertas_c089',
      'padre' => 'c001',
      'ruta' => '/petersen/contenido/gobierno/conocimiento/tablas/variables_expertas/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'cod_dominio_valores' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_dominio_valores',
          ),
          'cod_nodo_propietario_primario' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_nodo_propietario_primario',
          ),
          'cod_nodo_semantico' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_nodo_semantico',
          ),
          'cod_usuario_creacion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_usuario_creacion',
          ),
          'cod_variable_experta' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_variable_experta',
          ),
          'cod_version_modelo' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_version_modelo',
          ),
          'estado' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'estado',
          ),
          'fecha_hora_creacion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'fecha_creacion',
          ),
          'observacion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'observacion',
          ),
          'papel_inferencial' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'papel_inferencial',
          ),
          'tipo_semantico' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'tipo_semantico',
          ),
        ),
        'columna' => 'dto',
      ),
    ),
    'c090' => 
    array (
      'tabla' => 'ret_petersen_contenido_membresias_soportes_c090',
      'padre' => 'c001',
      'ruta' => '/petersen/contenido/membresias_soportes/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'soportes' => 
          array (
            'clase' => 'coleccion',
            'modo' => 'lista',
            'columna' => 'soportes',
            'modelo' => 'c091',
            'identidad' => 'COLECCION',
          ),
          'valoracion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'valoracion',
          ),
        ),
        'columna' => 'dto',
      ),
    ),
    'c091' => 
    array (
      'tabla' => 'ret_contenido_membresias_soportes_soportes_c091',
      'padre' => 'c090',
      'ruta' => '/petersen/contenido/membresias_soportes/*/soportes/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'escalar',
        'tipos' => 
        array (
          0 => 'STRING',
        ),
        'columna' => 'dto',
      ),
    ),
    'c092' => 
    array (
      'tabla' => 'ret_petersen_contenido_procedencia_valorac_c092',
      'padre' => 'c001',
      'ruta' => '/petersen/contenido/procedencia_valoraciones/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'cod_documento_clinico' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_documento_clinico',
          ),
          'cod_personal' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_personal',
          ),
          'cod_residente' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_residente',
          ),
          'cod_usuario_registro' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_usuario_registro',
          ),
          'cod_valoracion_anterior' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_valoracion_anterior',
          ),
          'cod_valoracion_petersen' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_valoracion_petersen',
          ),
          'elemento' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'elemento',
          ),
          'fecha_hora' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'fecha',
          ),
          'localizador_fuente' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'localizador_fuente',
          ),
          'registrado_en' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'registrado_en',
          ),
          'tipo_registro' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'tipo_registro',
          ),
        ),
        'columna' => 'dto',
      ),
    ),
    'c093' => 
    array (
      'tabla' => 'ret_contenido_salida_dominios_c093',
      'padre' => 'c001',
      'ruta' => '/petersen/contenido/salida/dominios/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'estado' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'estado',
          ),
          'indices_valoraciones' => 
          array (
            'clase' => 'coleccion',
            'modo' => 'lista',
            'columna' => 'indices_valoraciones',
            'modelo' => 'c094',
            'identidad' => 'COLECCION',
          ),
          'motivos' => 
          array (
            'clase' => 'coleccion',
            'modo' => 'lista',
            'columna' => 'motivos',
            'modelo' => 'c095',
            'identidad' => 'COLECCION',
          ),
        ),
        'columna' => 'dto',
      ),
    ),
    'c094' => 
    array (
      'tabla' => 'ret_salida_dominios_indices_valoraciones_c094',
      'padre' => 'c093',
      'ruta' => '/petersen/contenido/salida/dominios/*/indices_valoraciones/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'escalar',
        'tipos' => 
        array (
          0 => 'INT',
        ),
        'columna' => 'dto',
      ),
    ),
    'c095' => 
    array (
      'tabla' => 'ret_salida_dominios_motivos_c095',
      'padre' => 'c093',
      'ruta' => '/petersen/contenido/salida/dominios/*/motivos/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'escalar',
        'tipos' => 
        array (
          0 => 'STRING',
        ),
        'columna' => 'dto',
      ),
    ),
    'c096' => 
    array (
      'tabla' => 'ret_salida_gobierno_bloqueada_por_c096',
      'padre' => 'c001',
      'ruta' => '/petersen/contenido/salida/gobierno/bloqueada_por/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'escalar',
        'tipos' => 
        array (
          0 => 'STRING',
        ),
        'columna' => 'dto',
      ),
    ),
    'c097' => 
    array (
      'tabla' => 'ret_salida_gobierno_estados_derivados_c097',
      'padre' => 'c001',
      'ruta' => '/petersen/contenido/salida/gobierno/estados_derivados/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'escalar',
        'tipos' => 
        array (
          0 => 'STRING',
        ),
        'columna' => 'dto',
      ),
    ),
    'c098' => 
    array (
      'tabla' => 'ret_salida_gobierno_reglas_c098',
      'padre' => 'c001',
      'ruta' => '/petersen/contenido/salida/gobierno/reglas/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'codigo' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'codigo',
          ),
          'condiciones' => 
          array (
            'clase' => 'coleccion',
            'modo' => 'lista',
            'columna' => 'condiciones',
            'modelo' => 'c099',
            'identidad' => 'COLECCION',
          ),
          'cumple' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'BOOL',
            ),
            'columna' => 'cumple',
          ),
          'estado' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'estado',
          ),
          'estado_revision' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'estado_revision',
          ),
          'habilitada' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'BOOL',
            ),
            'columna' => 'habilitada',
          ),
          'regla' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'regla',
          ),
        ),
        'columna' => 'dto',
      ),
    ),
    'c099' => 
    array (
      'tabla' => 'ret_gobierno_reglas_condiciones_c099',
      'padre' => 'c098',
      'ruta' => '/petersen/contenido/salida/gobierno/reglas/*/condiciones/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'condicion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'condicion',
          ),
          'estado' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'estado',
          ),
          'soportes' => 
          array (
            'clase' => 'coleccion',
            'modo' => 'lista',
            'columna' => 'soportes',
            'modelo' => 'c100',
            'identidad' => 'COLECCION',
          ),
          'valor' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'valor',
          ),
          'variable' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'variable',
          ),
        ),
        'columna' => 'dto',
      ),
    ),
    'c100' => 
    array (
      'tabla' => 'ret_reglas_condiciones_soportes_c100',
      'padre' => 'c099',
      'ruta' => '/petersen/contenido/salida/gobierno/reglas/*/condiciones/*/soportes/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'escalar',
        'tipos' => 
        array (
          0 => 'STRING',
        ),
        'columna' => 'dto',
      ),
    ),
    'c101' => 
    array (
      'tabla' => 'ret_contenido_salida_impedimentos_c101',
      'padre' => 'c001',
      'ruta' => '/petersen/contenido/salida/impedimentos/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'escalar',
        'tipos' => 
        array (
          0 => 'STRING',
        ),
        'columna' => 'dto',
      ),
    ),
    'c102' => 
    array (
      'tabla' => 'ret_requisitos_c01_indices_valoraciones_c102',
      'padre' => 'c001',
      'ruta' => '/petersen/contenido/salida/requisitos/C01/indices_valoraciones/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'escalar',
        'tipos' => 
        array (
          0 => 'INT',
        ),
        'columna' => 'dto',
      ),
    ),
    'c103' => 
    array (
      'tabla' => 'ret_requisitos_c01_motivos_c103',
      'padre' => 'c001',
      'ruta' => '/petersen/contenido/salida/requisitos/C01/motivos/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'escalar',
        'tipos' => 
        array (
          0 => 'STRING',
        ),
        'columna' => 'dto',
      ),
    ),
    'c104' => 
    array (
      'tabla' => 'ret_requisitos_c02_derivado_de_c104',
      'padre' => 'c001',
      'ruta' => '/petersen/contenido/salida/requisitos/C02/derivado_de/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'escalar',
        'tipos' => 
        array (
          0 => 'STRING',
        ),
        'columna' => 'dto',
      ),
    ),
    'c105' => 
    array (
      'tabla' => 'ret_requisitos_c02_motivos_c105',
      'padre' => 'c001',
      'ruta' => '/petersen/contenido/salida/requisitos/C02/motivos/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'escalar',
        'tipos' => 
        array (
          0 => 'STRING',
        ),
        'columna' => 'dto',
      ),
    ),
    'c106' => 
    array (
      'tabla' => 'ret_requisitos_c03_indices_valoraciones_c106',
      'padre' => 'c001',
      'ruta' => '/petersen/contenido/salida/requisitos/C03/indices_valoraciones/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'escalar',
        'tipos' => 
        array (
          0 => 'INT',
        ),
        'columna' => 'dto',
      ),
    ),
    'c107' => 
    array (
      'tabla' => 'ret_requisitos_c03_motivos_c107',
      'padre' => 'c001',
      'ruta' => '/petersen/contenido/salida/requisitos/C03/motivos/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'escalar',
        'tipos' => 
        array (
          0 => 'STRING',
        ),
        'columna' => 'dto',
      ),
    ),
    'c108' => 
    array (
      'tabla' => 'ret_requisitos_c04_indices_valoraciones_c108',
      'padre' => 'c001',
      'ruta' => '/petersen/contenido/salida/requisitos/C04/indices_valoraciones/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'escalar',
        'tipos' => 
        array (
          0 => 'INT',
        ),
        'columna' => 'dto',
      ),
    ),
    'c109' => 
    array (
      'tabla' => 'ret_requisitos_c04_motivos_c109',
      'padre' => 'c001',
      'ruta' => '/petersen/contenido/salida/requisitos/C04/motivos/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'escalar',
        'tipos' => 
        array (
          0 => 'STRING',
        ),
        'columna' => 'dto',
      ),
    ),
    'c110' => 
    array (
      'tabla' => 'ret_requisitos_c05_indices_valoraciones_c110',
      'padre' => 'c001',
      'ruta' => '/petersen/contenido/salida/requisitos/C05/indices_valoraciones/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'escalar',
        'tipos' => 
        array (
          0 => 'INT',
        ),
        'columna' => 'dto',
      ),
    ),
    'c111' => 
    array (
      'tabla' => 'ret_requisitos_c05_motivos_c111',
      'padre' => 'c001',
      'ruta' => '/petersen/contenido/salida/requisitos/C05/motivos/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'escalar',
        'tipos' => 
        array (
          0 => 'STRING',
        ),
        'columna' => 'dto',
      ),
    ),
    'c112' => 
    array (
      'tabla' => 'ret_contenido_salida_valoraciones_excluida_c112',
      'padre' => 'c001',
      'ruta' => '/petersen/contenido/salida/valoraciones_excluidas/*',
      'fk_propietario' => 'cod_propietario',
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
              'factores_pertinentes_revisados' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'BOOL',
                ),
                'columna' => 'comprobaciones_factores_pertinentes_revisados',
              ),
              'impedimento_no_resuelto' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'BOOL',
                ),
                'columna' => 'comprobaciones_impedimento_no_resuelto',
              ),
              'revision_explicita' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'BOOL',
                ),
                'columna' => 'comprobaciones_revision_explicita',
              ),
            ),
            'columna' => 'comprobaciones',
          ),
          'dominios_revisados' => 
          array (
            'clase' => 'objeto',
            'campos' => 
            array (
            ),
            'columna' => 'dominios_revisados',
          ),
          'elemento' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'elemento',
          ),
          'estado_original' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'estado_original',
          ),
          'justificacion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'justificacion',
          ),
          'motivos' => 
          array (
            'clase' => 'coleccion',
            'modo' => 'lista',
            'columna' => 'motivos',
            'modelo' => 'c113',
            'identidad' => 'COLECCION',
          ),
          'origen' => 
          array (
            'clase' => 'objeto',
            'campos' => 
            array (
              'autor' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'origen_autor',
              ),
              'datos_originales' => 
              array (
                'clase' => 'objeto',
                'campos' => 
                array (
                ),
                'columna' => 'origen_datos_originales',
              ),
              'episodio' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'origen_episodio',
              ),
              'fecha' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'origen_fecha',
              ),
              'registro' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'origen_registro',
              ),
              'residente' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'origen_residente',
              ),
              'tabla' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'origen_tabla',
              ),
              'tipo' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'origen_tipo',
              ),
              'vigente' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'BOOL',
                ),
                'columna' => 'origen_vigente',
              ),
            ),
            'columna' => 'origen',
          ),
          'soportes' => 
          array (
            'clase' => 'coleccion',
            'modo' => 'lista',
            'columna' => 'soportes',
            'modelo' => 'c114',
            'identidad' => 'COLECCION',
          ),
        ),
        'columna' => 'dto',
      ),
    ),
    'c113' => 
    array (
      'tabla' => 'ret_salida_valoraciones_excluidas_motivos_c113',
      'padre' => 'c112',
      'ruta' => '/petersen/contenido/salida/valoraciones_excluidas/*/motivos/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'escalar',
        'tipos' => 
        array (
          0 => 'STRING',
        ),
        'columna' => 'dto',
      ),
    ),
    'c114' => 
    array (
      'tabla' => 'ret_salida_valoraciones_excluidas_soportes_c114',
      'padre' => 'c112',
      'ruta' => '/petersen/contenido/salida/valoraciones_excluidas/*/soportes/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'autor' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'autor',
          ),
          'datos_originales' => 
          array (
            'clase' => 'objeto',
            'campos' => 
            array (
              'descripcion' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'datos_originales_descripcion',
              ),
            ),
            'columna' => 'datos_originales',
          ),
          'episodio' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'episodio',
          ),
          'fecha' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'fecha',
          ),
          'registro' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'registro',
          ),
          'residente' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'residente',
          ),
          'tabla' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'tabla',
          ),
          'tipo' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'tipo',
          ),
          'vigente' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'BOOL',
            ),
            'columna' => 'vigente',
          ),
        ),
        'columna' => 'dto',
      ),
    ),
    'c115' => 
    array (
      'tabla' => 'ret_petersen_contenido_valoraciones_c115',
      'padre' => 'c001',
      'ruta' => '/petersen/contenido/valoraciones/*',
      'fk_propietario' => 'cod_propietario',
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
                'tipos' => 
                array (
                  0 => 'BOOL',
                ),
                'columna' => 'comprobaciones_alcance_dominio',
              ),
              'atribucion_revisada' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'BOOL',
                ),
                'columna' => 'comprobaciones_atribucion_revisada',
              ),
              'cambio_respecto_previo' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'BOOL',
                ),
                'columna' => 'comprobaciones_cambio_respecto_previo',
              ),
              'condiciones_revisadas' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'BOOL',
                ),
                'columna' => 'comprobaciones_condiciones_revisadas',
              ),
              'contexto_interpretado' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'BOOL',
                ),
                'columna' => 'comprobaciones_contexto_interpretado',
              ),
              'corroboracion_independiente' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'BOOL',
                ),
                'columna' => 'comprobaciones_corroboracion_independiente',
              ),
              'exclusiones_revisadas' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'BOOL',
                ),
                'columna' => 'comprobaciones_exclusiones_revisadas',
              ),
              'factores_pertinentes_revisados' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'BOOL',
                ),
                'columna' => 'comprobaciones_factores_pertinentes_revisados',
              ),
              'impedimento_no_resuelto' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'BOOL',
                ),
                'columna' => 'comprobaciones_impedimento_no_resuelto',
              ),
              'oportunidad_adecuada' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'BOOL',
                ),
                'columna' => 'comprobaciones_oportunidad_adecuada',
              ),
              'revision_explicita' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'BOOL',
                ),
                'columna' => 'comprobaciones_revision_explicita',
              ),
              'revision_integrada' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'BOOL',
                ),
                'columna' => 'comprobaciones_revision_integrada',
              ),
              'suficiencia_conjunto' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'BOOL',
                ),
                'columna' => 'comprobaciones_suficiencia_conjunto',
              ),
            ),
            'columna' => 'comprobaciones',
          ),
          'dominios_revisados' => 
          array (
            'clase' => 'coleccion',
            'modo' => 'mapa',
            'columna' => 'dominios_revisados',
            'modelo' => 'c116',
            'identidad' => 'COLECCION',
          ),
          'elemento' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'elemento',
          ),
          'estado' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'estado',
          ),
          'justificacion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'justificacion',
          ),
          'origen' => 
          array (
            'clase' => 'objeto',
            'campos' => 
            array (
              'autor' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'origen_autor',
              ),
              'datos_originales' => 
              array (
                'clase' => 'objeto',
                'campos' => 
                array (
                ),
                'columna' => 'origen_datos_originales',
              ),
              'episodio' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'origen_episodio',
              ),
              'fecha' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'origen_fecha',
              ),
              'registro' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'origen_registro',
              ),
              'residente' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'origen_residente',
              ),
              'tabla' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'origen_tabla',
              ),
              'tipo' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'origen_tipo',
              ),
              'vigente' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'BOOL',
                ),
                'columna' => 'origen_vigente',
              ),
            ),
            'columna' => 'origen',
          ),
          'soportes' => 
          array (
            'clase' => 'coleccion',
            'modo' => 'lista',
            'columna' => 'soportes',
            'modelo' => 'c117',
            'identidad' => 'COLECCION',
          ),
        ),
        'columna' => 'dto',
      ),
    ),
    'c116' => 
    array (
      'tabla' => 'ret_contenido_valoraciones_dominios_revisa_c116',
      'padre' => 'c115',
      'ruta' => '/petersen/contenido/valoraciones/*/dominios_revisados/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'escalar',
        'tipos' => 
        array (
          0 => 'STRING',
        ),
        'columna' => 'dto',
      ),
    ),
    'c117' => 
    array (
      'tabla' => 'ret_contenido_valoraciones_soportes_c117',
      'padre' => 'c115',
      'ruta' => '/petersen/contenido/valoraciones/*/soportes/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'autor' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'autor',
          ),
          'datos_originales' => 
          array (
            'clase' => 'objeto',
            'campos' => 
            array (
              'actividad' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'datos_originales_actividad',
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
                        'tipos' => 
                        array (
                          0 => 'STRING',
                        ),
                        'columna' => 'dat_ori_cap_ori_apl_act_cla',
                      ),
                      'cod_aplicacion' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'STRING',
                        ),
                        'columna' => 'dat_ori_cap_ori_apl_act_cod_apl',
                      ),
                      'cod_aplicacion_anterior' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'STRING',
                        ),
                        'columna' => 'dat_ori_cap_ori_apl_act_cod_apl_ant',
                      ),
                      'cod_atencion' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'STRING',
                        ),
                        'columna' => 'dat_ori_cap_ori_apl_act_cod_ate',
                      ),
                      'cod_documento_clinico' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'STRING',
                        ),
                        'columna' => 'dat_ori_cap_ori_apl_act_cod_doc_cli',
                      ),
                      'cod_instrumento' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'STRING',
                        ),
                        'columna' => 'dat_ori_cap_ori_apl_act_cod_ins',
                      ),
                      'cod_personal' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'STRING',
                        ),
                        'columna' => 'dat_ori_cap_ori_apl_act_cod_per',
                      ),
                      'cod_residente' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'STRING',
                        ),
                        'columna' => 'dat_ori_cap_ori_apl_act_cod_res',
                      ),
                      'cod_usuario_registro' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'STRING',
                        ),
                        'columna' => 'dat_ori_cap_ori_apl_act_cod_usu_reg',
                      ),
                      'estado' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'STRING',
                        ),
                        'columna' => 'dat_ori_cap_ori_apl_act_est',
                      ),
                      'fecha_hora' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'STRING',
                        ),
                        'columna' => 'dat_ori_cap_ori_apl_act_fec',
                      ),
                      'interpretacion' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'STRING',
                        ),
                        'columna' => 'dat_ori_cap_ori_apl_act_int',
                      ),
                      'localizador_fuente' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'STRING',
                        ),
                        'columna' => 'dat_ori_cap_ori_apl_act_loc_fue',
                      ),
                      'observacion' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'STRING',
                        ),
                        'columna' => 'dat_ori_cap_ori_apl_act_obs',
                      ),
                      'puntaje_maximo' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'STRING',
                        ),
                        'columna' => 'dat_ori_cap_ori_apl_act_pun_max',
                      ),
                      'puntaje_total' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'STRING',
                        ),
                        'columna' => 'dat_ori_cap_ori_apl_act_pun_tot',
                      ),
                      'registrado_en' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'STRING',
                        ),
                        'columna' => 'dat_ori_cap_ori_apl_act_reg_en',
                      ),
                    ),
                    'columna' => 'dat_ori_cap_ori_apl_act',
                  ),
                  'atencion_original' => 
                  array (
                    'clase' => 'objeto',
                    'campos' => 
                    array (
                      'cod_atencion' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'STRING',
                        ),
                        'columna' => 'dat_ori_cap_ori_ate_ori_cod_ate',
                      ),
                      'cod_residente' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'STRING',
                        ),
                        'columna' => 'dat_ori_cap_ori_ate_ori_cod_res',
                      ),
                    ),
                    'columna' => 'dat_ori_cap_ori_ate_ori',
                  ),
                  'autor' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'datos_originales_captura_original_autor',
                  ),
                  'cod_aplicacion' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'datos_originales_captura_original_cod_aplicacion',
                  ),
                  'cod_instrumento' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'datos_originales_captura_original_cod_instrumento',
                  ),
                  'cod_personal' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'datos_originales_captura_original_cod_personal',
                  ),
                  'cod_residente' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'datos_originales_captura_original_cod_residente',
                  ),
                  'codigo_instrumento' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'dat_ori_cap_ori_cla_ins',
                  ),
                  'documento_original' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'dat_ori_cap_ori_doc_ori',
                  ),
                  'edad_en_aplicacion' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'INT',
                    ),
                    'columna' => 'dat_ori_cap_ori_eda_en_apl',
                  ),
                  'estado_original' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'datos_originales_captura_original_estado_original',
                  ),
                  'extraccion' => 
                  array (
                    'clase' => 'objeto',
                    'campos' => 
                    array (
                      'aplicabilidad_geriatrica' => 
                      array (
                        'clase' => 'objeto',
                        'campos' => 
                        array (
                          'alcance_etario_compatible' => 
                          array (
                            'clase' => 'escalar',
                            'tipos' => 
                            array (
                              0 => 'BOOL',
                            ),
                            'columna' => 'dat_ori_cap_ori_ext_apl_ger_alc_eta_com',
                          ),
                          'autoriza_interpretacion_clinica' => 
                          array (
                            'clase' => 'escalar',
                            'tipos' => 
                            array (
                              0 => 'BOOL',
                            ),
                            'columna' => 'dat_ori_cap_ori_ext_apl_ger_aut_int_cli',
                          ),
                          'edad_en_aplicacion' => 
                          array (
                            'clase' => 'escalar',
                            'tipos' => 
                            array (
                              0 => 'INT',
                            ),
                            'columna' => 'dat_ori_cap_ori_ext_apl_ger_eda_en_apl',
                          ),
                          'escolaridad_original' => 
                          array (
                            'clase' => 'escalar',
                            'tipos' => 
                            array (
                              0 => 'INT',
                            ),
                            'columna' => 'dat_ori_cap_ori_ext_apl_ger_esc_ori',
                          ),
                          'fuente' => 
                          array (
                            'clase' => 'escalar',
                            'tipos' => 
                            array (
                              0 => 'STRING',
                            ),
                            'columna' => 'dat_ori_cap_ori_ext_apl_ger_fue',
                          ),
                          'idioma_declarado' => 
                          array (
                            'clase' => 'escalar',
                            'tipos' => 
                            array (
                              0 => 'STRING',
                            ),
                            'columna' => 'dat_ori_cap_ori_ext_apl_ger_idi_dec',
                          ),
                          'motivos' => 
                          array (
                            'clase' => 'objeto',
                            'campos' => 
                            array (
                            ),
                            'columna' => 'dat_ori_cap_ori_ext_apl_ger_mot',
                          ),
                          'pertenece_a_poblacion_objetivo' => 
                          array (
                            'clase' => 'escalar',
                            'tipos' => 
                            array (
                              0 => 'BOOL',
                            ),
                            'columna' => 'dat_ori_cap_ori_ext_apl_ger_per_a_pob_obj',
                          ),
                          'poblacion_objetivo' => 
                          array (
                            'clase' => 'escalar',
                            'tipos' => 
                            array (
                              0 => 'STRING',
                            ),
                            'columna' => 'dat_ori_cap_ori_ext_apl_ger_pob_obj',
                          ),
                          'rango_edad_referencia' => 
                          array (
                            'clase' => 'objeto',
                            'campos' => 
                            array (
                              'maxima' => 
                              array (
                                'clase' => 'escalar',
                                'tipos' => 
                                array (
                                  0 => 'INT',
                                ),
                                'columna' => 'dat_ori_cap_ori_ext_apl_ger_ran_eda_ref_max',
                              ),
                              'minima' => 
                              array (
                                'clase' => 'escalar',
                                'tipos' => 
                                array (
                                  0 => 'INT',
                                ),
                                'columna' => 'dat_ori_cap_ori_ext_apl_ger_ran_eda_ref_min',
                              ),
                            ),
                            'columna' => 'dat_ori_cap_ori_ext_apl_ger_ran_eda_ref',
                          ),
                          'requiere_verificar' => 
                          array (
                            'clase' => 'coleccion',
                            'modo' => 'lista',
                            'columna' => 'dat_ori_cap_ori_ext_apl_ger_req_ver',
                            'modelo' => 'c118',
                            'identidad' => 'COLECCION',
                          ),
                        ),
                        'columna' => 'dat_ori_cap_ori_ext_apl_ger',
                      ),
                      'captura' => 
                      array (
                        'clase' => 'objeto',
                        'campos' => 
                        array (
                          'aciertos_clave_semantica' => 
                          array (
                            'clase' => 'escalar',
                            'tipos' => 
                            array (
                              0 => 'INT',
                            ),
                            'columna' => 'dat_ori_cap_ori_ext_cap_aci_cla_sem',
                          ),
                          'aciertos_espontaneos' => 
                          array (
                            'clase' => 'escalar',
                            'tipos' => 
                            array (
                              0 => 'INT',
                            ),
                            'columna' => 'dat_ori_cap_ori_ext_cap_aci_esp',
                          ),
                          'administracion_estandarizada' => 
                          array (
                            'clase' => 'escalar',
                            'tipos' => 
                            array (
                              0 => 'BOOL',
                            ),
                            'columna' => 'dat_ori_cap_ori_ext_cap_adm_est',
                          ),
                          'audicion_compatible' => 
                          array (
                            'clase' => 'escalar',
                            'tipos' => 
                            array (
                              0 => 'BOOL',
                            ),
                            'columna' => 'dat_ori_cap_ori_ext_cap_aud_com',
                          ),
                          'condiciones_sensoriales_motoras_compatibles' => 
                          array (
                            'clase' => 'escalar',
                            'tipos' => 
                            array (
                              0 => 'BOOL',
                            ),
                            'columna' => 'dat_ori_cap_ori_ext_cap_con_sen',
                          ),
                          'demora_minutos' => 
                          array (
                            'clase' => 'escalar',
                            'tipos' => 
                            array (
                              0 => 'FLOAT',
                            ),
                            'columna' => 'dat_ori_cap_ori_ext_cap_dem_min',
                          ),
                          'edad_anios' => 
                          array (
                            'clase' => 'escalar',
                            'tipos' => 
                            array (
                              0 => 'INT',
                            ),
                            'columna' => 'dat_ori_cap_ori_ext_cap_eda_ani',
                          ),
                          'edicion_protocolo' => 
                          array (
                            'clase' => 'escalar',
                            'tipos' => 
                            array (
                              0 => 'STRING',
                            ),
                            'columna' => 'dat_ori_cap_ori_ext_cap_edi_pro',
                          ),
                          'elementos_revisados' => 
                          array (
                            'clase' => 'escalar',
                            'tipos' => 
                            array (
                              0 => 'INT',
                            ),
                            'columna' => 'dat_ori_cap_ori_ext_cap_ele_rev',
                          ),
                          'ensayo_1' => 
                          array (
                            'clase' => 'escalar',
                            'tipos' => 
                            array (
                              0 => 'INT',
                            ),
                            'columna' => 'dat_ori_cap_ori_ext_cap_ens_1',
                          ),
                          'ensayo_2' => 
                          array (
                            'clase' => 'escalar',
                            'tipos' => 
                            array (
                              0 => 'INT',
                            ),
                            'columna' => 'dat_ori_cap_ori_ext_cap_ens_2',
                          ),
                          'ensayo_3' => 
                          array (
                            'clase' => 'escalar',
                            'tipos' => 
                            array (
                              0 => 'INT',
                            ),
                            'columna' => 'dat_ori_cap_ori_ext_cap_ens_3',
                          ),
                          'ensayos' => 
                          array (
                            'clase' => 'coleccion',
                            'modo' => 'lista',
                            'columna' => 'dat_ori_cap_ori_ext_cap_ens',
                            'modelo' => 'c119',
                            'identidad' => 'COLECCION',
                          ),
                          'errores_observados' => 
                          array (
                            'clase' => 'escalar',
                            'tipos' => 
                            array (
                              0 => 'INT',
                            ),
                            'columna' => 'dat_ori_cap_ori_ext_cap_err_obs',
                          ),
                          'escolaridad_anios' => 
                          array (
                            'clase' => 'escalar',
                            'tipos' => 
                            array (
                              0 => 'INT',
                            ),
                            'columna' => 'dat_ori_cap_ori_ext_cap_esc_ani',
                          ),
                          'espanol_nativo' => 
                          array (
                            'clase' => 'escalar',
                            'tipos' => 
                            array (
                              0 => 'BOOL',
                            ),
                            'columna' => 'dat_ori_cap_ori_ext_cap_esp_nat',
                          ),
                          'finalizada' => 
                          array (
                            'clase' => 'escalar',
                            'tipos' => 
                            array (
                              0 => 'BOOL',
                            ),
                            'columna' => 'dat_ori_cap_ori_ext_cap_fin',
                          ),
                          'forma' => 
                          array (
                            'clase' => 'escalar',
                            'tipos' => 
                            array (
                              0 => 'INT',
                            ),
                            'columna' => 'dat_ori_cap_ori_ext_cap_for',
                          ),
                          'forma_l_aciertos' => 
                          array (
                            'clase' => 'escalar',
                            'tipos' => 
                            array (
                              0 => 'INT',
                            ),
                            'columna' => 'dat_ori_cap_ori_ext_cap_for_l_aci',
                          ),
                          'forma_n_aciertos' => 
                          array (
                            'clase' => 'escalar',
                            'tipos' => 
                            array (
                              0 => 'INT',
                            ),
                            'columna' => 'dat_ori_cap_ori_ext_cap_for_n_aci',
                          ),
                          'idioma' => 
                          array (
                            'clase' => 'escalar',
                            'tipos' => 
                            array (
                              0 => 'STRING',
                            ),
                            'columna' => 'dat_ori_cap_ori_ext_cap_idi',
                          ),
                          'instrumento' => 
                          array (
                            'clase' => 'escalar',
                            'tipos' => 
                            array (
                              0 => 'STRING',
                            ),
                            'columna' => 'dat_ori_cap_ori_ext_cap_ins',
                          ),
                          'lectoescritura' => 
                          array (
                            'clase' => 'escalar',
                            'tipos' => 
                            array (
                              0 => 'BOOL',
                            ),
                            'columna' => 'dat_ori_cap_ori_ext_cap_lec',
                          ),
                          'nacimiento_bolivia' => 
                          array (
                            'clase' => 'escalar',
                            'tipos' => 
                            array (
                              0 => 'BOOL',
                            ),
                            'columna' => 'dat_ori_cap_ori_ext_cap_nac_bol',
                          ),
                          'protocolo' => 
                          array (
                            'clase' => 'escalar',
                            'tipos' => 
                            array (
                              0 => 'STRING',
                            ),
                            'columna' => 'dat_ori_cap_ori_ext_cap_pro',
                          ),
                          'puntaje_copia' => 
                          array (
                            'clase' => 'escalar',
                            'tipos' => 
                            array (
                              0 => 'FLOAT',
                            ),
                            'columna' => 'dat_ori_cap_ori_ext_cap_pun_cop',
                          ),
                          'recuerdo_diferido' => 
                          array (
                            'clase' => 'escalar',
                            'tipos' => 
                            array (
                              0 => 'INT',
                            ),
                            'columna' => 'dat_ori_cap_ori_ext_cap_rec_dif',
                          ),
                          'residencia_bolivia' => 
                          array (
                            'clase' => 'escalar',
                            'tipos' => 
                            array (
                              0 => 'BOOL',
                            ),
                            'columna' => 'dat_ori_cap_ori_ext_cap_res_bol',
                          ),
                          'segundos_parte_b' => 
                          array (
                            'clase' => 'escalar',
                            'tipos' => 
                            array (
                              0 => 'FLOAT',
                            ),
                            'columna' => 'dat_ori_cap_ori_ext_cap_seg_par_b',
                          ),
                        ),
                        'columna' => 'dat_ori_cap_ori_ext_cap',
                      ),
                      'fuentes_entradas' => 
                      array (
                        'clase' => 'coleccion',
                        'modo' => 'mapa',
                        'columna' => 'dat_ori_cap_ori_ext_fue_ent',
                        'modelo' => 'c120',
                        'identidad' => 'COLECCION',
                      ),
                      'identidad_declarada' => 
                      array (
                        'clase' => 'objeto',
                        'campos' => 
                        array (
                          'edicion_protocolo' => 
                          array (
                            'clase' => 'escalar',
                            'tipos' => 
                            array (
                              0 => 'STRING',
                            ),
                            'columna' => 'dat_ori_cap_ori_ext_ide_dec_edi_pro',
                          ),
                          'forma' => 
                          array (
                            'clase' => 'escalar',
                            'tipos' => 
                            array (
                              0 => 'INT',
                            ),
                            'columna' => 'dat_ori_cap_ori_ext_ide_dec_for',
                          ),
                          'idioma' => 
                          array (
                            'clase' => 'escalar',
                            'tipos' => 
                            array (
                              0 => 'STRING',
                            ),
                            'columna' => 'dat_ori_cap_ori_ext_ide_dec_idi',
                          ),
                          'instrumento' => 
                          array (
                            'clase' => 'escalar',
                            'tipos' => 
                            array (
                              0 => 'STRING',
                            ),
                            'columna' => 'dat_ori_cap_ori_ext_ide_dec_ins',
                          ),
                        ),
                        'columna' => 'dat_ori_cap_ori_ext_ide_dec',
                      ),
                      'identidad_requerida_metodo' => 
                      array (
                        'clase' => 'objeto',
                        'campos' => 
                        array (
                          'edicion_protocolo' => 
                          array (
                            'clase' => 'escalar',
                            'tipos' => 
                            array (
                              0 => 'STRING',
                            ),
                            'columna' => 'dat_ori_cap_ori_ext_ide_req_met_edi_pro',
                          ),
                          'forma' => 
                          array (
                            'clase' => 'escalar',
                            'tipos' => 
                            array (
                              0 => 'INT',
                            ),
                            'columna' => 'dat_ori_cap_ori_ext_ide_req_met_for',
                          ),
                          'idioma' => 
                          array (
                            'clase' => 'escalar',
                            'tipos' => 
                            array (
                              0 => 'STRING',
                            ),
                            'columna' => 'dat_ori_cap_ori_ext_ide_req_met_idi',
                          ),
                          'instrumento' => 
                          array (
                            'clase' => 'escalar',
                            'tipos' => 
                            array (
                              0 => 'STRING',
                            ),
                            'columna' => 'dat_ori_cap_ori_ext_ide_req_met_ins',
                          ),
                        ),
                        'columna' => 'dat_ori_cap_ori_ext_ide_req_met',
                      ),
                      'metodo' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'STRING',
                        ),
                        'columna' => 'dat_ori_cap_ori_ext_met',
                      ),
                      'motivos_captura' => 
                      array (
                        'clase' => 'objeto',
                        'campos' => 
                        array (
                        ),
                        'columna' => 'dat_ori_cap_ori_ext_mot_cap',
                      ),
                      'normalizacion' => 
                      array (
                        'clase' => 'objeto',
                        'campos' => 
                        array (
                          'bruto' => 
                          array (
                            'clase' => 'objeto',
                            'campos' => 
                            array (
                              'aciertos_clave_semantica' => 
                              array (
                                'clase' => 'escalar',
                                'tipos' => 
                                array (
                                  0 => 'INT',
                                ),
                                'columna' => 'dat_ori_cap_ori_ext_nor_bru_aci_cla_sem',
                              ),
                              'aciertos_espontaneos' => 
                              array (
                                'clase' => 'escalar',
                                'tipos' => 
                                array (
                                  0 => 'INT',
                                ),
                                'columna' => 'dat_ori_cap_ori_ext_nor_bru_aci_esp',
                              ),
                              'administracion_estandarizada' => 
                              array (
                                'clase' => 'escalar',
                                'tipos' => 
                                array (
                                  0 => 'BOOL',
                                ),
                                'columna' => 'dat_ori_cap_ori_ext_nor_bru_adm_est',
                              ),
                              'audicion_compatible' => 
                              array (
                                'clase' => 'escalar',
                                'tipos' => 
                                array (
                                  0 => 'BOOL',
                                ),
                                'columna' => 'dat_ori_cap_ori_ext_nor_bru_aud_com',
                              ),
                              'condiciones_sensoriales_motoras_compatibles' => 
                              array (
                                'clase' => 'escalar',
                                'tipos' => 
                                array (
                                  0 => 'BOOL',
                                ),
                                'columna' => 'dat_ori_cap_ori_ext_nor_bru_con_sen',
                              ),
                              'demora_minutos' => 
                              array (
                                'clase' => 'escalar',
                                'tipos' => 
                                array (
                                  0 => 'FLOAT',
                                ),
                                'columna' => 'dat_ori_cap_ori_ext_nor_bru_dem_min',
                              ),
                              'elementos_revisados' => 
                              array (
                                'clase' => 'escalar',
                                'tipos' => 
                                array (
                                  0 => 'INT',
                                ),
                                'columna' => 'dat_ori_cap_ori_ext_nor_bru_ele_rev',
                              ),
                              'ensayos' => 
                              array (
                                'clase' => 'coleccion',
                                'modo' => 'lista',
                                'columna' => 'dat_ori_cap_ori_ext_nor_bru_ens',
                                'modelo' => 'c121',
                                'identidad' => 'COLECCION',
                              ),
                              'errores_observados' => 
                              array (
                                'clase' => 'escalar',
                                'tipos' => 
                                array (
                                  0 => 'INT',
                                ),
                                'columna' => 'dat_ori_cap_ori_ext_nor_bru_err_obs',
                              ),
                              'escolaridad_anios' => 
                              array (
                                'clase' => 'escalar',
                                'tipos' => 
                                array (
                                  0 => 'INT',
                                ),
                                'columna' => 'dat_ori_cap_ori_ext_nor_bru_esc_ani',
                              ),
                              'espanol_nativo' => 
                              array (
                                'clase' => 'escalar',
                                'tipos' => 
                                array (
                                  0 => 'BOOL',
                                ),
                                'columna' => 'dat_ori_cap_ori_ext_nor_bru_esp_nat',
                              ),
                              'finalizada' => 
                              array (
                                'clase' => 'escalar',
                                'tipos' => 
                                array (
                                  0 => 'BOOL',
                                ),
                                'columna' => 'dat_ori_cap_ori_ext_nor_bru_fin',
                              ),
                              'forma_l_aciertos' => 
                              array (
                                'clase' => 'escalar',
                                'tipos' => 
                                array (
                                  0 => 'INT',
                                ),
                                'columna' => 'dat_ori_cap_ori_ext_nor_bru_for_l_aci',
                              ),
                              'forma_n_aciertos' => 
                              array (
                                'clase' => 'escalar',
                                'tipos' => 
                                array (
                                  0 => 'INT',
                                ),
                                'columna' => 'dat_ori_cap_ori_ext_nor_bru_for_n_aci',
                              ),
                              'lectoescritura' => 
                              array (
                                'clase' => 'escalar',
                                'tipos' => 
                                array (
                                  0 => 'BOOL',
                                ),
                                'columna' => 'dat_ori_cap_ori_ext_nor_bru_lec',
                              ),
                              'nacimiento_bolivia' => 
                              array (
                                'clase' => 'escalar',
                                'tipos' => 
                                array (
                                  0 => 'BOOL',
                                ),
                                'columna' => 'dat_ori_cap_ori_ext_nor_bru_nac_bol',
                              ),
                              'puntaje_copia' => 
                              array (
                                'clase' => 'escalar',
                                'tipos' => 
                                array (
                                  0 => 'FLOAT',
                                ),
                                'columna' => 'dat_ori_cap_ori_ext_nor_bru_pun_cop',
                              ),
                              'recuerdo_diferido' => 
                              array (
                                'clase' => 'escalar',
                                'tipos' => 
                                array (
                                  0 => 'INT',
                                ),
                                'columna' => 'dat_ori_cap_ori_ext_nor_bru_rec_dif',
                              ),
                              'residencia_bolivia' => 
                              array (
                                'clase' => 'escalar',
                                'tipos' => 
                                array (
                                  0 => 'BOOL',
                                ),
                                'columna' => 'dat_ori_cap_ori_ext_nor_bru_res_bol',
                              ),
                              'segundos_parte_b' => 
                              array (
                                'clase' => 'escalar',
                                'tipos' => 
                                array (
                                  0 => 'FLOAT',
                                ),
                                'columna' => 'dat_ori_cap_ori_ext_nor_bru_seg_par_b',
                              ),
                            ),
                            'columna' => 'dat_ori_cap_ori_ext_nor_bru',
                          ),
                          'componentes' => 
                          array (
                            'clase' => 'coleccion',
                            'modo' => 'mapa',
                            'columna' => 'dat_ori_cap_ori_ext_nor_com',
                            'modelo' => 'c122',
                            'identidad' => 'COLECCION',
                          ),
                          'contexto_aplicacion' => 
                          array (
                            'clase' => 'objeto',
                            'campos' => 
                            array (
                              'aciertos_clave_semantica' => 
                              array (
                                'clase' => 'escalar',
                                'tipos' => 
                                array (
                                  0 => 'INT',
                                ),
                                'columna' => 'dat_ori_cap_ori_ext_nor_con_apl_aci_cla_sem',
                              ),
                              'aciertos_espontaneos' => 
                              array (
                                'clase' => 'escalar',
                                'tipos' => 
                                array (
                                  0 => 'INT',
                                ),
                                'columna' => 'dat_ori_cap_ori_ext_nor_con_apl_aci_esp',
                              ),
                              'administracion_estandarizada' => 
                              array (
                                'clase' => 'escalar',
                                'tipos' => 
                                array (
                                  0 => 'BOOL',
                                ),
                                'columna' => 'dat_ori_cap_ori_ext_nor_con_apl_adm_est',
                              ),
                              'audicion_compatible' => 
                              array (
                                'clase' => 'escalar',
                                'tipos' => 
                                array (
                                  0 => 'BOOL',
                                ),
                                'columna' => 'dat_ori_cap_ori_ext_nor_con_apl_aud_com',
                              ),
                              'condiciones_sensoriales_motoras_compatibles' => 
                              array (
                                'clase' => 'escalar',
                                'tipos' => 
                                array (
                                  0 => 'BOOL',
                                ),
                                'columna' => 'dat_ori_cap_ori_ext_nor_con_apl_con_sen',
                              ),
                              'edad_anios' => 
                              array (
                                'clase' => 'escalar',
                                'tipos' => 
                                array (
                                  0 => 'INT',
                                ),
                                'columna' => 'dat_ori_cap_ori_ext_nor_con_apl_eda_ani',
                              ),
                              'edicion_protocolo' => 
                              array (
                                'clase' => 'escalar',
                                'tipos' => 
                                array (
                                  0 => 'STRING',
                                ),
                                'columna' => 'dat_ori_cap_ori_ext_nor_con_apl_edi_pro',
                              ),
                              'elementos_revisados' => 
                              array (
                                'clase' => 'escalar',
                                'tipos' => 
                                array (
                                  0 => 'INT',
                                ),
                                'columna' => 'dat_ori_cap_ori_ext_nor_con_apl_ele_rev',
                              ),
                              'errores_observados' => 
                              array (
                                'clase' => 'escalar',
                                'tipos' => 
                                array (
                                  0 => 'INT',
                                ),
                                'columna' => 'dat_ori_cap_ori_ext_nor_con_apl_err_obs',
                              ),
                              'escolaridad_anios' => 
                              array (
                                'clase' => 'escalar',
                                'tipos' => 
                                array (
                                  0 => 'INT',
                                ),
                                'columna' => 'dat_ori_cap_ori_ext_nor_con_apl_esc_ani',
                              ),
                              'espanol_nativo' => 
                              array (
                                'clase' => 'escalar',
                                'tipos' => 
                                array (
                                  0 => 'BOOL',
                                ),
                                'columna' => 'dat_ori_cap_ori_ext_nor_con_apl_esp_nat',
                              ),
                              'finalizada' => 
                              array (
                                'clase' => 'escalar',
                                'tipos' => 
                                array (
                                  0 => 'BOOL',
                                ),
                                'columna' => 'dat_ori_cap_ori_ext_nor_con_apl_fin',
                              ),
                              'forma' => 
                              array (
                                'clase' => 'escalar',
                                'tipos' => 
                                array (
                                  0 => 'INT',
                                ),
                                'columna' => 'dat_ori_cap_ori_ext_nor_con_apl_for',
                              ),
                              'forma_l_aciertos' => 
                              array (
                                'clase' => 'escalar',
                                'tipos' => 
                                array (
                                  0 => 'INT',
                                ),
                                'columna' => 'dat_ori_cap_ori_ext_nor_con_apl_for_l_aci',
                              ),
                              'forma_n_aciertos' => 
                              array (
                                'clase' => 'escalar',
                                'tipos' => 
                                array (
                                  0 => 'INT',
                                ),
                                'columna' => 'dat_ori_cap_ori_ext_nor_con_apl_for_n_aci',
                              ),
                              'idioma' => 
                              array (
                                'clase' => 'escalar',
                                'tipos' => 
                                array (
                                  0 => 'STRING',
                                ),
                                'columna' => 'dat_ori_cap_ori_ext_nor_con_apl_idi',
                              ),
                              'instrumento' => 
                              array (
                                'clase' => 'escalar',
                                'tipos' => 
                                array (
                                  0 => 'STRING',
                                ),
                                'columna' => 'dat_ori_cap_ori_ext_nor_con_apl_ins',
                              ),
                              'lectoescritura' => 
                              array (
                                'clase' => 'escalar',
                                'tipos' => 
                                array (
                                  0 => 'BOOL',
                                ),
                                'columna' => 'dat_ori_cap_ori_ext_nor_con_apl_lec',
                              ),
                              'nacimiento_bolivia' => 
                              array (
                                'clase' => 'escalar',
                                'tipos' => 
                                array (
                                  0 => 'BOOL',
                                ),
                                'columna' => 'dat_ori_cap_ori_ext_nor_con_apl_nac_bol',
                              ),
                              'protocolo' => 
                              array (
                                'clase' => 'escalar',
                                'tipos' => 
                                array (
                                  0 => 'STRING',
                                ),
                                'columna' => 'dat_ori_cap_ori_ext_nor_con_apl_pro',
                              ),
                              'puntaje_copia' => 
                              array (
                                'clase' => 'escalar',
                                'tipos' => 
                                array (
                                  0 => 'FLOAT',
                                ),
                                'columna' => 'dat_ori_cap_ori_ext_nor_con_apl_pun_cop',
                              ),
                              'residencia_bolivia' => 
                              array (
                                'clase' => 'escalar',
                                'tipos' => 
                                array (
                                  0 => 'BOOL',
                                ),
                                'columna' => 'dat_ori_cap_ori_ext_nor_con_apl_res_bol',
                              ),
                              'segundos_parte_b' => 
                              array (
                                'clase' => 'escalar',
                                'tipos' => 
                                array (
                                  0 => 'FLOAT',
                                ),
                                'columna' => 'dat_ori_cap_ori_ext_nor_con_apl_seg_par_b',
                              ),
                            ),
                            'columna' => 'dat_ori_cap_ori_ext_nor_con_apl',
                          ),
                          'diagnostico' => 
                          array (
                            'clase' => 'escalar',
                            'tipos' => 
                            array (
                              0 => 'STRING',
                            ),
                            'columna' => 'dat_ori_cap_ori_ext_nor_dia',
                          ),
                          'limite' => 
                          array (
                            'clase' => 'escalar',
                            'tipos' => 
                            array (
                              0 => 'STRING',
                            ),
                            'columna' => 'dat_ori_cap_ori_ext_nor_lim',
                          ),
                          'localizador' => 
                          array (
                            'clase' => 'escalar',
                            'tipos' => 
                            array (
                              0 => 'STRING',
                            ),
                            'columna' => 'dat_ori_cap_ori_ext_nor_loc',
                          ),
                          'metodo' => 
                          array (
                            'clase' => 'escalar',
                            'tipos' => 
                            array (
                              0 => 'STRING',
                            ),
                            'columna' => 'dat_ori_cap_ori_ext_nor_met',
                          ),
                          'referencia' => 
                          array (
                            'clase' => 'escalar',
                            'tipos' => 
                            array (
                              0 => 'STRING',
                            ),
                            'columna' => 'dat_ori_cap_ori_ext_nor_ref',
                          ),
                          'revision_profesional' => 
                          array (
                            'clase' => 'escalar',
                            'tipos' => 
                            array (
                              0 => 'STRING',
                            ),
                            'columna' => 'dat_ori_cap_ori_ext_nor_rev_pro',
                          ),
                        ),
                        'columna' => 'dat_ori_cap_ori_ext_nor',
                      ),
                      'perfil' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'STRING',
                        ),
                        'columna' => 'dat_ori_cap_ori_ext_per',
                      ),
                    ),
                    'columna' => 'datos_originales_captura_original_extraccion',
                  ),
                  'fecha_hora' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'datos_originales_captura_original_fecha',
                  ),
                  'fuente_edad' => 
                  array (
                    'clase' => 'objeto',
                    'campos' => 
                    array (
                      'campo' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'STRING',
                        ),
                        'columna' => 'dat_ori_cap_ori_fue_eda_cam',
                      ),
                      'fecha_aplicacion' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'STRING',
                        ),
                        'columna' => 'dat_ori_cap_ori_fue_eda_fec_apl',
                      ),
                      'fecha_nacimiento' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'STRING',
                        ),
                        'columna' => 'dat_ori_cap_ori_fue_eda_fec_nac',
                      ),
                      'registro' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'STRING',
                        ),
                        'columna' => 'dat_ori_cap_ori_fue_eda_reg',
                      ),
                      'tabla' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'STRING',
                        ),
                        'columna' => 'dat_ori_cap_ori_fue_eda_tab',
                      ),
                    ),
                    'columna' => 'datos_originales_captura_original_fuente_edad',
                  ),
                  'instrumento_actual' => 
                  array (
                    'clase' => 'objeto',
                    'campos' => 
                    array (
                      'cod_instrumento' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'STRING',
                        ),
                        'columna' => 'dat_ori_cap_ori_ins_act_cod_ins',
                      ),
                      'codigo' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'STRING',
                        ),
                        'columna' => 'dat_ori_cap_ori_ins_act_cod',
                      ),
                      'descripcion' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'STRING',
                        ),
                        'columna' => 'dat_ori_cap_ori_ins_act_des',
                      ),
                      'estado' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'STRING',
                        ),
                        'columna' => 'dat_ori_cap_ori_ins_act_est',
                      ),
                      'nombre' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'STRING',
                        ),
                        'columna' => 'dat_ori_cap_ori_ins_act_nom',
                      ),
                      'puntaje_maximo' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'STRING',
                        ),
                        'columna' => 'dat_ori_cap_ori_ins_act_pun_max',
                      ),
                      'tipo' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'STRING',
                        ),
                        'columna' => 'dat_ori_cap_ori_ins_act_tip',
                      ),
                      'version' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'STRING',
                        ),
                        'columna' => 'dat_ori_cap_ori_ins_act_ver',
                      ),
                    ),
                    'columna' => 'dat_ori_cap_ori_ins_act',
                  ),
                  'motivos' => 
                  array (
                    'clase' => 'objeto',
                    'campos' => 
                    array (
                    ),
                    'columna' => 'datos_originales_captura_original_motivos',
                  ),
                  'nombre_instrumento' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'dat_ori_cap_ori_nom_ins',
                  ),
                  'perfil_captura_original' => 
                  array (
                    'clase' => 'objeto',
                    'campos' => 
                    array (
                      'cod_instrumento' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'STRING',
                        ),
                        'columna' => 'dat_ori_cap_ori_per_cap_ori_cod_ins',
                      ),
                      'codigo_instrumento' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'STRING',
                        ),
                        'columna' => 'dat_ori_cap_ori_per_cap_ori_cla_ins',
                      ),
                      'id' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'STRING',
                        ),
                        'columna' => 'dat_ori_cap_ori_per_cap_ori_id',
                      ),
                      'identidad_instrumento' => 
                      array (
                        'clase' => 'objeto',
                        'campos' => 
                        array (
                          'edicion_protocolo' => 
                          array (
                            'clase' => 'escalar',
                            'tipos' => 
                            array (
                              0 => 'STRING',
                            ),
                            'columna' => 'dat_ori_cap_ori_per_cap_ori_ide_ins_edi_pro',
                          ),
                          'forma' => 
                          array (
                            'clase' => 'escalar',
                            'tipos' => 
                            array (
                              0 => 'INT',
                            ),
                            'columna' => 'dat_ori_cap_ori_per_cap_ori_ide_ins_for',
                          ),
                          'idioma' => 
                          array (
                            'clase' => 'escalar',
                            'tipos' => 
                            array (
                              0 => 'STRING',
                            ),
                            'columna' => 'dat_ori_cap_ori_per_cap_ori_ide_ins_idi',
                          ),
                          'instrumento' => 
                          array (
                            'clase' => 'escalar',
                            'tipos' => 
                            array (
                              0 => 'STRING',
                            ),
                            'columna' => 'dat_ori_cap_ori_per_cap_ori_ide_ins_ins',
                          ),
                        ),
                        'columna' => 'dat_ori_cap_ori_per_cap_ori_ide_ins',
                      ),
                      'metodo' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'STRING',
                        ),
                        'columna' => 'dat_ori_cap_ori_per_cap_ori_met',
                      ),
                      'selectores' => 
                      array (
                        'clase' => 'coleccion',
                        'modo' => 'mapa',
                        'columna' => 'dat_ori_cap_ori_per_cap_ori_sel',
                        'modelo' => 'c123',
                        'identidad' => 'COLECCION',
                      ),
                      'version_instrumento' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'STRING',
                        ),
                        'columna' => 'dat_ori_cap_ori_per_cap_ori_ver_ins',
                      ),
                    ),
                    'columna' => 'dat_ori_cap_ori_per_cap_ori',
                  ),
                  'preguntas_declarativas_originales' => 
                  array (
                    'clase' => 'coleccion',
                    'modo' => 'mapa',
                    'columna' => 'dat_ori_cap_ori_pre_dec_ori',
                    'modelo' => 'c124',
                    'identidad' => 'COLECCION',
                  ),
                  'procedencia' => 
                  array (
                    'clase' => 'objeto',
                    'campos' => 
                    array (
                      'cod_aplicacion_anterior' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'STRING',
                        ),
                        'columna' => 'dat_ori_cap_ori_pro_cod_apl_ant',
                      ),
                      'cod_documento_clinico' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'STRING',
                        ),
                        'columna' => 'dat_ori_cap_ori_pro_cod_doc_cli',
                      ),
                      'cod_usuario_registro' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'STRING',
                        ),
                        'columna' => 'dat_ori_cap_ori_pro_cod_usu_reg',
                      ),
                      'localizador_fuente' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'STRING',
                        ),
                        'columna' => 'dat_ori_cap_ori_pro_loc_fue',
                      ),
                      'registrado_en' => 
                      array (
                        'clase' => 'escalar',
                        'tipos' => 
                        array (
                          0 => 'STRING',
                        ),
                        'columna' => 'dat_ori_cap_ori_pro_reg_en',
                      ),
                    ),
                    'columna' => 'datos_originales_captura_original_procedencia',
                  ),
                  'respuestas_originales' => 
                  array (
                    'clase' => 'coleccion',
                    'modo' => 'lista',
                    'columna' => 'dat_ori_cap_ori_res_ori',
                    'modelo' => 'c125',
                    'identidad' => 'COLECCION',
                  ),
                  'version_instrumento' => 
                  array (
                    'clase' => 'escalar',
                    'tipos' => 
                    array (
                      0 => 'STRING',
                    ),
                    'columna' => 'dat_ori_cap_ori_ver_ins',
                  ),
                  'vinculos_preguntas_actuales' => 
                  array (
                    'clase' => 'coleccion',
                    'modo' => 'mapa',
                    'columna' => 'dat_ori_cap_ori_vin_pre_act',
                    'modelo' => 'c126',
                    'identidad' => 'COLECCION',
                  ),
                ),
                'columna' => 'datos_originales_captura_original',
              ),
              'causa_ayuda' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'datos_originales_causa_ayuda',
              ),
              'descripcion' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'datos_originales_descripcion',
              ),
              'desempeno' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'datos_originales_desempeno',
              ),
              'dominio' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'datos_originales_dominio',
              ),
              'fuente' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'datos_originales_fuente',
              ),
              'nivel_previo' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'datos_originales_nivel_previo',
              ),
              'oportunidad' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'datos_originales_oportunidad',
              ),
              'tipo_ayuda' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'datos_originales_tipo_ayuda',
              ),
            ),
            'columna' => 'datos_originales',
          ),
          'episodio' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'episodio',
          ),
          'fecha' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'fecha',
          ),
          'registro' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'registro',
          ),
          'residente' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'residente',
          ),
          'tabla' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'tabla',
          ),
          'tipo' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'tipo',
          ),
          'vigente' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'BOOL',
            ),
            'columna' => 'vigente',
          ),
        ),
        'columna' => 'dto',
      ),
    ),
    'c118' => 
    array (
      'tabla' => 'ret_extraccion_aplicabilidad_geriatrica_re_c118',
      'padre' => 'c117',
      'ruta' => '/petersen/contenido/valoraciones/*/soportes/*/datos_originales/captura_original/extraccion/aplicabilidad_geriatrica/requiere_verificar/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'escalar',
        'tipos' => 
        array (
          0 => 'STRING',
        ),
        'columna' => 'dto',
      ),
    ),
    'c119' => 
    array (
      'tabla' => 'ret_extraccion_captura_ensayos_c119',
      'padre' => 'c117',
      'ruta' => '/petersen/contenido/valoraciones/*/soportes/*/datos_originales/captura_original/extraccion/captura/ensayos/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'escalar',
        'tipos' => 
        array (
          0 => 'INT',
        ),
        'columna' => 'dto',
      ),
    ),
    'c120' => 
    array (
      'tabla' => 'ret_captura_original_extraccion_fuentes_en_c120',
      'padre' => 'c117',
      'ruta' => '/petersen/contenido/valoraciones/*/soportes/*/datos_originales/captura_original/extraccion/fuentes_entradas/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'aplicacion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'aplicacion',
          ),
          'bruto' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'BOOL',
              1 => 'FLOAT',
              2 => 'INT',
            ),
            'columna' => 'bruto',
          ),
          'campo' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'campo',
          ),
          'pregunta' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'pregunta',
          ),
          'respuesta' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'respuesta',
          ),
          'valor' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'BOOL',
              1 => 'FLOAT',
              2 => 'INT',
            ),
            'columna' => 'valor',
          ),
        ),
        'columna' => 'dto',
      ),
    ),
    'c121' => 
    array (
      'tabla' => 'ret_normalizacion_bruto_ensayos_c121',
      'padre' => 'c117',
      'ruta' => '/petersen/contenido/valoraciones/*/soportes/*/datos_originales/captura_original/extraccion/normalizacion/bruto/ensayos/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'escalar',
        'tipos' => 
        array (
          0 => 'INT',
        ),
        'columna' => 'dto',
      ),
    ),
    'c122' => 
    array (
      'tabla' => 'ret_extraccion_normalizacion_componentes_c122',
      'padre' => 'c117',
      'ruta' => '/petersen/contenido/valoraciones/*/soportes/*/datos_originales/captura_original/extraccion/normalizacion/componentes/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'clasificacion_clinica' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'clasificacion_clinica',
          ),
          'de_residual' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'FLOAT',
            ),
            'columna' => 'de_residual',
          ),
          'direccion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'direccion',
          ),
          'esperado' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'FLOAT',
            ),
            'columna' => 'esperado',
          ),
          'estado' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'estado',
          ),
          'interpretacion_matematica' => 
          array (
            'clase' => 'objeto',
            'campos' => 
            array (
              'clasificacion_clinica' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'interpretacion_matematica_clasificacion_clinica',
              ),
              'contrato' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'interpretacion_matematica_contrato',
              ),
              'estado' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'interpretacion_matematica_estado',
              ),
              'limite' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'interpretacion_matematica_limite',
              ),
              'posicion' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'interpretacion_matematica_posicion',
              ),
              'texto' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'interpretacion_matematica_texto',
              ),
            ),
            'columna' => 'interpretacion_matematica',
          ),
          'medida' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'medida',
          ),
          'motivos' => 
          array (
            'clase' => 'objeto',
            'campos' => 
            array (
            ),
            'columna' => 'motivos',
          ),
          'puntuacion_bruta' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'FLOAT',
              1 => 'INT',
            ),
            'columna' => 'puntuacion_bruta',
          ),
          'z' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'FLOAT',
            ),
            'columna' => 'z',
          ),
        ),
        'columna' => 'dto',
      ),
    ),
    'c123' => 
    array (
      'tabla' => 'ret_captura_original_perfil_captura_origin_c123',
      'padre' => 'c117',
      'ruta' => '/petersen/contenido/valoraciones/*/soportes/*/datos_originales/captura_original/perfil_captura_original/selectores/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'campo' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'campo',
          ),
          'cod_pregunta' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_pregunta',
          ),
        ),
        'columna' => 'dto',
      ),
    ),
    'c124' => 
    array (
      'tabla' => 'ret_datos_originales_captura_original_preg_c124',
      'padre' => 'c117',
      'ruta' => '/petersen/contenido/valoraciones/*/soportes/*/datos_originales/captura_original/preguntas_declarativas_originales/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'cod_instrumento' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_instrumento',
          ),
          'cod_pregunta' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_pregunta',
          ),
          'codigo' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'codigo',
          ),
          'dominio' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'dominio',
          ),
          'enunciado' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'enunciado',
          ),
          'estado' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'estado',
          ),
          'orden' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'INT',
            ),
            'columna' => 'orden',
          ),
          'puntaje_maximo' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'puntaje_maximo',
          ),
          'tipo_respuesta' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'tipo_respuesta',
          ),
        ),
        'columna' => 'dto',
      ),
    ),
    'c125' => 
    array (
      'tabla' => 'ret_datos_originales_captura_original_resp_c125',
      'padre' => 'c117',
      'ruta' => '/petersen/contenido/valoraciones/*/soportes/*/datos_originales/captura_original/respuestas_originales/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'cod_aplicacion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_aplicacion',
          ),
          'cod_opcion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_opcion',
          ),
          'cod_pregunta' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_pregunta',
          ),
          'cod_respuesta' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'cod_respuesta',
          ),
          'codigo_pregunta' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'clave_pregunta',
          ),
          'coherente' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'BOOL',
            ),
            'columna' => 'coherente',
          ),
          'dominio_original' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'dominio_original',
          ),
          'estado_pregunta_actual' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'estado_pregunta_actual',
          ),
          'observacion' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'observacion',
          ),
          'opcion_actual' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'opcion_actual',
          ),
          'pregunta' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'pregunta',
          ),
          'pregunta_actual' => 
          array (
            'clase' => 'objeto',
            'campos' => 
            array (
              'cod_instrumento' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'pregunta_actual_cod_instrumento',
              ),
              'cod_pregunta' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'pregunta_actual_cod_pregunta',
              ),
              'codigo' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'pregunta_actual_codigo',
              ),
              'dominio' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'pregunta_actual_dominio',
              ),
              'enunciado' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'pregunta_actual_enunciado',
              ),
              'estado' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'pregunta_actual_estado',
              ),
              'orden' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'INT',
                ),
                'columna' => 'pregunta_actual_orden',
              ),
              'puntaje_maximo' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'pregunta_actual_puntaje_maximo',
              ),
              'tipo_respuesta' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'pregunta_actual_tipo_respuesta',
              ),
            ),
            'columna' => 'pregunta_actual',
          ),
          'puntaje' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'puntaje',
          ),
          'tipo_respuesta' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'tipo_respuesta',
          ),
          'valor_logico' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'BOOL',
            ),
            'columna' => 'valor_logico',
          ),
          'valor_numero' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'FLOAT',
              1 => 'INT',
            ),
            'columna' => 'valor_numero',
          ),
          'valor_texto' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'STRING',
            ),
            'columna' => 'valor_texto',
          ),
        ),
        'columna' => 'dto',
      ),
    ),
    'c126' => 
    array (
      'tabla' => 'ret_datos_originales_captura_original_vinc_c126',
      'padre' => 'c117',
      'ruta' => '/petersen/contenido/valoraciones/*/soportes/*/datos_originales/captura_original/vinculos_preguntas_actuales/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'objeto',
        'campos' => 
        array (
          'pertenece_al_instrumento' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'BOOL',
            ),
            'columna' => 'pertenece_al_instrumento',
          ),
          'pregunta_actual' => 
          array (
            'clase' => 'objeto',
            'campos' => 
            array (
              'cod_instrumento' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'pregunta_actual_cod_instrumento',
              ),
              'cod_pregunta' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'pregunta_actual_cod_pregunta',
              ),
              'codigo' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'pregunta_actual_codigo',
              ),
              'dominio' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'pregunta_actual_dominio',
              ),
              'enunciado' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'pregunta_actual_enunciado',
              ),
              'estado' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'pregunta_actual_estado',
              ),
              'orden' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'INT',
                ),
                'columna' => 'pregunta_actual_orden',
              ),
              'puntaje_maximo' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'pregunta_actual_puntaje_maximo',
              ),
              'tipo_respuesta' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'pregunta_actual_tipo_respuesta',
              ),
            ),
            'columna' => 'pregunta_actual',
          ),
          'selector' => 
          array (
            'clase' => 'objeto',
            'campos' => 
            array (
              'campo' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'selector_campo',
              ),
              'cod_pregunta' => 
              array (
                'clase' => 'escalar',
                'tipos' => 
                array (
                  0 => 'STRING',
                ),
                'columna' => 'selector_cod_pregunta',
              ),
            ),
            'columna' => 'selector',
          ),
          'tiene_respuesta' => 
          array (
            'clase' => 'escalar',
            'tipos' => 
            array (
              0 => 'BOOL',
            ),
            'columna' => 'tiene_respuesta',
          ),
        ),
        'columna' => 'dto',
      ),
    ),
    'c127' => 
    array (
      'tabla' => 'ret_release_archivos_c127',
      'padre' => 'c001',
      'ruta' => '/release/archivos/*',
      'fk_propietario' => 'cod_propietario',
      'estructura' => 
      array (
        'clase' => 'escalar',
        'tipos' => 
        array (
          0 => 'STRING',
        ),
        'columna' => 'dto',
      ),
    ),
  ),
  'contrato_sql' => 'REFERENCIA-COMPLETA-NOMINAL-1',
  'contenido_completo' => true,
  'instalable' => false,
  'alcance' => 'CORPUS_SINTETICO_F02_Y_TRES_NEGATIVOS_CERRADO',
);
