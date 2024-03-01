<?php

namespace App\Http\Controllers\Api\Crm;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use ParasolCRM\Services\CRM\Facades\Prsl;

class DocumentController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'filename' => 'required|file',
        ]);

        return $this->save($request, new Document());
    }

    public function update(Request $request, Document $document)
    {
        $request->validate([
            'filename' => 'required',
        ]);

        if ($request->hasFile('filename')) {
            $path = trim(Document::getFilePath('filename'), '/');
            Storage::delete($path.'/original/'.$document->filename);
            return $this->save($request, $document);
        }

        return $document->save() ? redirect('api/crm/document/'.$document->id) : abort(404);
    }

    public function destroy(Document $document)
    {
        if ($document->type) {
            abort(404);
        }

        $path = trim(Document::getFilePath('filename'), '/');
        Storage::delete($path.'/original/'.$document->filename);

        $document->delete() ? Prsl::responseSuccess() : Prsl::responseError('Not deleted', 500);
    }

    private function save(Request $request, Document $document)
    {
        $path = trim(Document::getFilePath('filename'), '/');
        Storage::makeDirectory($path.'/original');
        $file = $request->file('filename');
        $file->getBasename();
        $file->getExtension();
        $originalName = $document->original_name ?? $file->getClientOriginalName();
        $fileName = \Str::slug(pathinfo($originalName, PATHINFO_FILENAME));
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $i = 0;
        $fileNamePostfix = '';

        while (!$document->original_name && Storage::exists($path.'/original/'.$fileName.$fileNamePostfix.'.'.$extension)) {
            $i++;
            $fileNamePostfix = '-'.$i;
        }

        $fileName .= $fileNamePostfix.'.'.$extension;

        if (Storage::putFileAs($path.'/original/', $file, $fileName)) {
            $document->filename = $fileName;
            $document->original_name = $originalName;

            if ($document->save()) {
                return redirect('api/crm/document/'.$document->id);
            }
        }
        abort(404);
    }
}
