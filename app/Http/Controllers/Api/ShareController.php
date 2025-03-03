<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Share;
use App\Models\Directory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\ShareResource;

class ShareController extends Controller {
    public function index() {
        return ShareResource::collection(
            Share::where('recipient_id', Auth::id())->where('accepted', false)->get()
        );
    }

    public function store(Request $request) {
        $request->validate([
            'rep_id' => 'required|exists:directories,id',
            'recipient_id' => 'required|exists:users,id',
        ]);

        $directory = Directory::findOrFail($request->rep_id);

        // Vérifie si l'utilisateur connecté est bien le propriétaire du répertoire
        if ($directory->user_id !== Auth::id()) {
            return response()->json(['message' => 'Accès interdit'], 403);
        }

        $share = Share::create([
            'rep_id' => $request->rep_id,
            'owner_id' => Auth::id(),
            'recipient_id' => $request->recipient_id,
            'accepted' => false,
        ]);

        return new ShareResource($share);
    }

    public function show($id) {
        $share = Share::where('id', $id)->where('recipient_id', Auth::id())->firstOrFail();
        return new ShareResource($share);
    }

    public function update(Request $request, $id) {
        $share = Share::where('id', $id)->where('recipient_id', Auth::id())->firstOrFail();
        $share->update(['accepted' => true]);

        return new ShareResource($share);
    }

    public function destroy($id) {
        $share = Share::where('id', $id)
                      ->where(function ($query) {
                          $query->where('owner_id', Auth::id())
                                ->orWhere('recipient_id', Auth::id());
                      })
                      ->firstOrFail();

        $share->delete();
        return response()->json(['message' => 'Partage supprimé']);
    }
}
