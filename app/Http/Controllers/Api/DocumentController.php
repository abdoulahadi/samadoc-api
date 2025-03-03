<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Directory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\DocumentResource;

class DocumentController extends Controller {
    public function index() {
        return DocumentResource::collection(
            Document::where('user_id', Auth::id())->paginate(30)
        );
    }

    public function store(Request $request) {
        $request->validate([
            'file' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,jpg,png,mp4,mkv|max:10240',
            'folder' => 'required|string',
            'rep_id' => 'required|exists:directories,id',
            'level' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $directory = Directory::findOrFail($request->rep_id);
        if ($directory->user_id !== Auth::id()) {
            return response()->json(['message' => 'Accès interdit'], 403);
        }

        // Stockage du fichier
        $filePath = $request->file('file')->store('documents', 'public');

        $document = Document::create([
            'filename' => $request->file('file')->getClientOriginalName(),
            'path' => $filePath,
            'folder' => $request->folder,
            'level' => $request->level,
            'description' => $request->description,
            'rep_id' => $request->rep_id,
            'user_id' => Auth::id(),
        ]);

        return new DocumentResource($document);
    }

    public function show($id) {
        $document = Document::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        return new DocumentResource($document);
    }

    public function download($id) {
        $document = Document::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        return Storage::download('public/' . $document->path, $document->filename);
    }

    public function destroy($id) {
        $document = Document::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        
        // Supprimer le fichier
        Storage::delete('public/' . $document->path);

        $document->delete();
        return response()->json(['message' => 'Document supprimé']);
    }
}
