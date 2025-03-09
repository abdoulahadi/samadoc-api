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
        try {
            $documents = Document::where('user_id', Auth::id())->paginate(30);
            return DocumentResource::collection($documents);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur lors de la récupération des documents', 'error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request) {
        try {
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
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur lors de l\'enregistrement du document', 'error' => $e->getMessage()], 500);
        }
    }

    public function show($id) {
        try {
            $document = Document::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
            return new DocumentResource($document);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Document not found or access denied'], 404);
        }
    }

    public function download($id) {
        try {
            $document = Document::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
            return Storage::download('public/' . $document->path, $document->filename);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Document not found or access denied'], 404);
        }
    }

    public function destroy($id) {
        try {
            $document = Document::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
            
            // Supprimer le fichier
            Storage::delete('public/' . $document->path);

            $document->delete();
            return response()->json(['message' => 'Document supprimé']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Document not found or access denied'], 404);
        }
    }

    public function searchDocumentByName(Request $request) {
        try {
            $request->validate([
                'name' => 'required|string',
            ]);

            $documents = Document::where('user_id', Auth::id())
                ->where('filename', 'like', '%' . $request->name . '%')
                ->paginate(30);

            return DocumentResource::collection($documents);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur lors de la recherche des documents', 'error' => $e->getMessage()], 500);
        }
    }

    public function getDocumentsByDirectoryAndFolder($rep_id, $folder) {
        try {
            $directory = Directory::findOrFail($rep_id);
            if ($directory->user_id !== Auth::id()) {
                return response()->json(['message' => 'Accès interdit'], 403);
            }

            $documents = Document::where('user_id', Auth::id())
                ->where('rep_id', $rep_id)
                ->where('folder', $folder)
                ->paginate(30);

            return DocumentResource::collection($documents);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur lors de la récupération des documents', 'error' => $e->getMessage()], 500);
        }
    }

    public function getVideos() {
        try {
            $videos = Document::where('user_id', Auth::id())
                ->whereIn('file_extension', ['mp4', 'mkv'])
                ->limit(20)
                ->get();

            return DocumentResource::collection($videos);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur lors de la récupération des vidéos', 'error' => $e->getMessage()], 500);
        }
    }
}
