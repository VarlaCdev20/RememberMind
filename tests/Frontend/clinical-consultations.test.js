import test from 'node:test';
import assert from 'node:assert/strict';
import { consultationPlacement } from '../../resources/frontend/scripts/components/clinical-consultations.js';

test('captura y consulta comparten escritorio por el lado con espacio, sin solaparse', () => {
    for (const width of [1280,1440,1920]) {
        for (const bounds of [{left:width-456,right:width-16},{left:16,right:456}]) {
            const result=consultationPlacement(width,bounds);
            assert.ok(result.width>=600&&result.width<=1160);
            if(result.side==='left') assert.ok(16+result.width+16<=bounds.left);
            else assert.ok(width-16-result.width>=bounds.right+16);
        }
    }
});

test('tablet, móvil y arrastre al centro conservan la consulta superpuesta antes de estrechar la captura', () => {
    for(const width of [390,768,1024,1279]) assert.equal(consultationPlacement(width,{left:8,right:width-8}),null);
    assert.equal(consultationPlacement(1280,{left:420,right:860}),null);
});
