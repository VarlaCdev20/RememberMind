<?php

namespace App\Livewire\Admin\PersonalInstitucional\Partials;

use Livewire\Component;
use App\Models\User;
use App\Models\DocumentoUsuario;
use App\Models\TipoDocumentoUsuario;
use Livewire\WithFileUploads;

class PersonalInstitucionalDocumentos extends Component
{
    use WithFileUploads;

    public $usuarioId;
    public $documentos = [];
    public $tiposDocumento = [];

    // Form
    public $cod_tipo_doc, $archivo, $observaciones;

    public function mount($usuarioId)
    {
        $this->usuarioId = $usuarioId;
        $this->tiposDocumento = TipoDocumentoUsuario::all();
        $this->cargarDocumentos();
    }

    public function cargarDocumentos()
    {
        $this->documentos = DocumentoUsuario::with('tipoDocumento')
            ->where('cod_usu', $this->usuarioId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function subirDocumento()
    {
        $this->validate([
            'cod_tipo_doc' => 'required',
            'archivo' => 'required|file|max:5120', // max 5MB
        ]);

        $usuario = User::findOrFail($this->usuarioId);
        
        $path = $this->archivo->store('documentos_personal', 'public');

        DocumentoUsuario::create([
            'fecha_subida' => now(),
            'ruta_archivo' => $path,
            'estado_validacion' => 'PENDIENTE',
            'observaciones' => $this->observaciones,
            'cod_usu' => $this->usuarioId,
            'cod_tipo_doc' => $this->cod_tipo_doc,
        ]);

        $this->reset(['cod_tipo_doc', 'archivo', 'observaciones']);
        $this->cargarDocumentos();

        $this->dispatch('mostrarAlerta', [
            'type' => 'success',
            'title' => 'Documento Subido',
            'message' => 'El documento ha sido anexado al expediente del personal.'
        ]);
    }

    public function eliminarDocumento($id)
    {
        $doc = DocumentoUsuario::findOrFail($id);
        $doc->delete(); // Soft delete as requested
        
        $this->cargarDocumentos();
        $this->dispatch('mostrarAlerta', [
            'type' => 'success',
            'title' => 'Eliminado',
            'message' => 'Documento eliminado del expediente.'
        ]);
    }

    public function validarDocumento($id)
    {
        $doc = DocumentoUsuario::findOrFail($id);
        $doc->estado_validacion = 'VÁLIDO';
        $doc->save();
        $this->cargarDocumentos();
    }

    public function render()
    {
        return view('livewire.admin.personal-institucional.partials.personal-institucional-documentos');
    }
}
