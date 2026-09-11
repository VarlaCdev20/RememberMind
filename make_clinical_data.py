# -*- coding: utf-8 -*-
import json

data = {'residentes': {}, 'alertas': []}

def add_r(cod, ficha_dict, val_dict, meds_list, plan_tuple, tareas_list):
    data['residentes'][cod] = {
        'ficha': ficha_dict,
        'valoracion': val_dict,
        'medicaciones': meds_list,
        'plan_cuidado': {'resumen': plan_tuple[0], 'nivel_cuidado': plan_tuple[1]},
        'tareas': tareas_list
    }

def add_a(cod_am, origen, tipo, nivel, motivo, estado, min_ago, accion, hist, cierre=None, obs_cierre=None):
    data['alertas'].append({
        'cod_am': cod_am, 'origen': origen, 'tipo_alerta': tipo, 'nivel': nivel,
        'motivo': motivo, 'estado': estado, 'minutos_atras': min_ago,
        'accion_tomada': accion, 'cierre_minutos': cierre, 'observacion_cierre': obs_cierre,
        'hist': hist
    })
