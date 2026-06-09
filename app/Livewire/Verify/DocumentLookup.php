<?php

namespace App\Livewire\Verify;

use App\Models\DocumentVerification;
use Livewire\Component;

class DocumentLookup extends Component
{
    public string $documentId = '';
    public ?DocumentVerification $document = null;
    public bool $searched = false;
    public bool $notFound = false;

    public function verify(): void
    {
        $this->validate([
            'documentId' => 'required|string|max:50',
        ]);

        $this->searched = true;
        $this->document = DocumentVerification::valid()
            ->where('document_id', $this->documentId)
            ->first();

        if ($this->document) {
            $this->document->markVerified();
            $this->notFound = false;
        } else {
            $this->document = null;
            $this->notFound = true;
        }
    }

    public function mount(string $id = ''): void
    {
        if (!empty($id)) {
            $this->documentId = $id;
            $this->verify();
        }
    }

    public function render()
    {
        return view('livewire.verify.document-lookup')->layout('layouts.guest');
    }
}
