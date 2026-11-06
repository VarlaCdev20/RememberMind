import { test } from 'node:test';
import assert from 'node:assert/strict';
import { crearMapaDireccion } from '../../resources/frontend/scripts/modules/personal-address-map.js';

test('cambiar una zona del catálogo centra el mapa abierto sin buscar mientras escribe otra zona', async () => {
    let aperturas = 0;
    const form = crearMapaDireccion({}, 'tiles');
    form.abrir = async () => { aperturas++; };
    form.cambiarZona(null);
    form.abierto = true;
    await form.cambiarZona('ZONA_2');
    assert.equal(aperturas, 1);
    await form.cambiarZona('ZONA_2');
    await form.cambiarZona('OTRA');
    assert.equal(aperturas, 1);
    form.abierto = false;
    await form.cambiarZona('ZONA_3');
    assert.equal(aperturas, 1);
});

test('mapa descarta una apertura si cambió la región mientras esperaba', async () => {
    let resolver;
    const form=crearMapaDireccion({centrarMapaDireccion:()=>new Promise(resolve=>{resolver=resolve;})},'https://tile.openstreetmap.org/{z}/{x}/{y}.png');
    form.cambiarContexto('LPZ');const apertura=form.abrir();form.cambiarContexto('CBB');resolver({lat:-17,lon:-66,zoom:8});await apertura;
    assert.equal(form.abierto,false);assert.equal(form.cargando,false);assert.equal(form.propuesta,'');
});

test('desmontar impide abrir un mapa tardío y aplicar requiere confirmación explícita', async () => {
    let resolver,aplicadas=[];
    const form=crearMapaDireccion({centrarMapaDireccion:()=>new Promise(resolve=>{resolver=resolve;}),usarDireccionMapa:async d=>{aplicadas.push(d);}},'tiles');
    const apertura=form.abrir();form.destroy();resolver({lat:1,lon:2,zoom:8});await apertura;assert.equal(form.abierto,false);
    const otro=crearMapaDireccion({usarDireccionMapa:async d=>{aplicadas.push(d);}},'tiles');
    await otro.usar();assert.deepEqual(aplicadas,[]);otro.propuesta='calle de prueba';await otro.usar();assert.deepEqual(aplicadas,['CALLE DE PRUEBA']);
});

test('fallos no fingen apertura o guardado y permiten reintentar', async () => {
    const form=crearMapaDireccion({centrarMapaDireccion:async()=>{throw Error('sin conexión');},usarDireccionMapa:async()=>{throw Error('validación');}},'tiles');
    await form.abrir();assert.equal(form.cargando,false);assert.equal(form.abierto,false);assert.match(form.mensaje,/No se pudo abrir/);
    form.propuesta='DIRECCIÓN';await form.usar();assert.match(form.mensaje,/No se pudo aplicar/);assert.equal(form.consultando,false);assert.equal(form.propuesta,'DIRECCIÓN');
});
