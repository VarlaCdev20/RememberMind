const fs = require('node:fs');
const cp = require('node:child_process');
const path = require('node:path');
const files = cp.execFileSync('rg', ['-l', '^<<<<<<< ', 'app', 'resources', 'tests', 'docs'], {encoding:'utf8'}).trim().split(/\r?\n/);
const backup = path.resolve('output/resolucion-integracion-20261023/originales');
const remoteWhole = new Set(['FichaPsicologicaResidente.php', 'ficha-psicologica-residente.blade.php', 'FichaPsicologicaResidenteTest.php']);
const remoteBlocks = new Set(['EvaluacionesAreaPanel.php', 'DashboardPsicologo.php', 'evaluacion-geriatrica-modal.blade.php', 'ConocimientoPanel.php', 'conocimiento-panel.blade.php', 'explorador.blade.php', 'gobierno.blade.php', 'red-semantica.blade.php', 'expert-resident-workspace.js', 'expert-resident-workspace.test.js', 'experto-workspace-e2e.test.js', 'expert-laboratory.css', 'conexiones-residente.blade.php', 'resultados-experto-residente.blade.php', 'dominios.blade.php', 'residentes.blade.php']);
const decisions = [];
for (const f of files) {
  const original = fs.readFileSync(f,'utf8');
  const copy = path.join(backup, f);
  fs.mkdirSync(path.dirname(copy), {recursive:true});
  if (fs.existsSync(copy)) throw new Error('No sobrescribir respaldo: '+copy);
  fs.writeFileSync(copy, original);
  let count = 0;
  let result;
  if (remoteWhole.has(path.basename(f))) {
    result = cp.execFileSync('git',['show','HEAD^2:'+f.replaceAll('\\','/')],{encoding:'utf8'});
    decisions.push({file:f, resolution:'versión remota completa: formulario, autorización y pruebas coherentes'});
  } else {
    result = original.replace(/^<<<<<<< .*\r?\n([\s\S]*?)^=======\r?\n([\s\S]*?)^>>>>>>> .*\r?\n?/gm, (_,ours,theirs) => {
      count++;
      let resolution = remoteBlocks.has(path.basename(f)) ? 'theirs' : 'ours';
      if (path.basename(f) === 'SidebarService.php') resolution = count === 1 ? 'theirs' : 'ours';
      if (['modal-livewire.blade.php','evaluaciones-area-panel.blade.php'].includes(path.basename(f))) resolution = 'both';
      if (path.basename(f) === 'ServidorExpertoSintetico.php' && count === 4) resolution = 'theirs';
      decisions.push({file:f, block:count, resolution});
      return resolution === 'both' ? ours+theirs : resolution === 'ours' ? ours : theirs;
    });
  }
  if (/^<<<<<<< |^=======\s*$|^>>>>>>> /m.test(result)) throw new Error('Marcadores pendientes: '+f);
  fs.writeFileSync(f,result);
}
fs.writeFileSync(path.resolve('output/resolucion-integracion-20261023/decisiones.json'),JSON.stringify(decisions,null,2));
console.log('Archivos reparados: '+files.length+'; decisiones: '+decisions.length+'; originales respaldados.');
