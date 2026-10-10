const geometryKeys = ['trendOpen','trendBounds','trendViewportCompact','trendViewportWidth','trendPointer','trendReturnFocus','restoringTrendFocus','closeTrend','fitTrend','trendStyle','moveTrend','resizeTrend','resizeTrendEdge','resetTrend','startTrendPointer','updateTrendPointer','endTrendPointer','openTrend'];
const legacyFields = {marcha:'movMarcha',traslado:'movTraslado',tipo_apoyo:'movTipoApoyo',equilibrio:'movEquilibrio',fatiga:'movFatiga',riesgo_caida:'movRiesgoCaida',observacion:'movObservacion'};
const newFields = ['motivo_registro','actividad_realizada','dispositivo','distancia_metros','tolerancia_movilidad','dolor_movilidad','mareo','disnea','debilidad','cambio_habitual','motivo_otro','dispositivo_otro'];
const text = value => typeof value === 'string' && !/^(null|undefined|NaN|Infinity|\[object Object\])$/i.test(value.trim()) ? value : '';
export const normalizeMobilityHistory = history => (Array.isArray(history) ? history : []).filter(row=>row && text(row.codigo) && text(row.fecha) && text(row.hora)).map(row=>({codigo:row.codigo,fecha:row.fecha,hora:row.hora,grupo:text(row.grupo)||row.fecha,actividad:text(row.actividad)||'Movilidad registrada',categoria:['DEAMBULACION','TRANSFERENCIAS','OTROS'].includes(row.categoria)?row.categoria:'OTROS',observacion:text(row.observacion),campos:(Array.isArray(row.campos)?row.campos:[]).filter(field=>text(field?.nombre)&&text(field?.valor)).map(field=>({nombre:field.nombre,valor:field.valor}))}));
export function createMobilityCapture(history=[],initial={},persisted=false,baseline=null) {
    const geometry=window.rmDolorRegistro([],{},'');
    const initialValues={...initial,...(baseline?.movDatos??{})};
    if (baseline) for (const [field,property] of Object.entries(legacyFields)) initialValues[field]=baseline[property];
    return {...Object.fromEntries(geometryKeys.map(key=>[key,geometry[key]])),values:{...initial},initialValues,history:normalizeMobilityHistory(history),historyFilter:'TODOS',persisted,
        init() {
            this.captureShell=this.$el.closest('.rm-modal-shell');
            this.beforeUnloadHandler=event=>{if(!this.captureShell?.classList.contains('is-open')||!this.isDirty())return;event.preventDefault();event.returnValue='';};
            this.savingGuard=event=>{if(!this.captureShell?.querySelector('[wire\\:click="guardarCuidado"]:disabled'))return;if((event.type==='keydown'&&event.key==='Escape'&&!this.trendOpen)||(event.type==='click'&&event.target.closest('.rm-modal-panel__close,.rm-modal-panel__back,.rm-modal-shell__overlay,[wire\\:click="cerrarSelectorRegistro"]'))){event.preventDefault();event.stopImmediatePropagation();}};
            this.historyHandler=event=>{this.history=normalizeMobilityHistory(event.detail.historial);};window.addEventListener('movilidad-historial-actualizado',this.historyHandler);
            this.clearFocusHandler=()=>this.$nextTick(()=>this.$el.querySelector('[role="radio"]')?.focus());
            window.addEventListener('beforeunload',this.beforeUnloadHandler);window.addEventListener('keydown',this.savingGuard,true);this.captureShell?.addEventListener('click',this.savingGuard,true);window.addEventListener('registro-campos-limpiados',this.clearFocusHandler);
        },
        destroy(){window.removeEventListener('movilidad-historial-actualizado',this.historyHandler);if(this.$refs?.trendDialog?.matches(':popover-open'))this.$refs.trendDialog.hidePopover();window.removeEventListener('beforeunload',this.beforeUnloadHandler);window.removeEventListener('keydown',this.savingGuard,true);this.captureShell?.removeEventListener('click',this.savingGuard,true);window.removeEventListener('registro-campos-limpiados',this.clearFocusHandler);},
        isDirty(){return !this.persisted&&Object.keys(this.initialValues).some(key=>this.values[key]!==this.initialValues[key]);},
        canWalk(){return ['CAMINAR_HABITACION','CAMINAR_PASILLO'].includes(this.values.actividad_realizada);},
        setValue(field,value){
            if(!Object.hasOwn(legacyFields,field)&&!newFields.includes(field))return;
            this.values[field]=value;this.$wire?.$set(legacyFields[field]??'movDatos.'+field,value,false);
            const detail={motivo_registro:'motivo_otro',dispositivo:'dispositivo_otro'}[field];
            if(detail&&value!=='OTRO'){this.values[detail]='';this.$wire?.$set('movDatos.'+detail,'',false);}
            if(field==='actividad_realizada'&&!this.canWalk()){this.values.distancia_metros='';this.$wire?.$set('movDatos.distancia_metros','',false);}
        },
        chooseNext(event,field,options,step){const buttons=[...event.currentTarget.parentElement.querySelectorAll('[role="radio"]')];const next=(buttons.indexOf(event.currentTarget)+step+buttons.length)%buttons.length;this.setValue(field,options[next].value);buttons[next].focus();},
        filteredHistory(){return this.history.filter(row=>this.historyFilter==='TODOS'||row.categoria===this.historyFilter);},
        groups(){const result=[];for(const row of this.filteredHistory()){let group=result.find(item=>item.label===row.grupo);if(!group){group={label:row.grupo,rows:[]};result.push(group);}group.rows.push(row);}return result;},
    };
}
window.rmMovilidadRegistro=createMobilityCapture;
