<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Share;
use App\Models\Directory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\ShareResource;
use App\Notifications\ShareCreatedNotification;

class ShareController extends Controller {
    public function index() {
        try {
            return ShareResource::collection(
                Share::where('recipient_id', Auth::id())->where('accepted', false)->get()
            );
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur lors de la récupération des partages', 'error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request) {
        try {
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
            $recipient = $share->recipient;
            $directory = $share->directory;

            // Envoie de la notification
            $recipient->notify(new ShareCreatedNotification($directory));

            return new ShareResource($share);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur lors de la création du partage', 'error' => $e->getMessage()], 500);
        }
    }

    public function show($id) {
        try {
            $share = Share::where('id', $id)->where('recipient_id', Auth::id())->firstOrFail();
            return new ShareResource($share);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur lors de la récupération du partage', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id) {
        try {
            $share = Share::where('id', $id)->where('recipient_id', Auth::id())->firstOrFail();
            $share->update(['accepted' => true]);

            return new ShareResource($share);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur lors de la mise à jour du partage', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id) {
        try {
            $share = Share::where('id', $id)
                          ->where(function ($query) {
                              $query->where('owner_id', Auth::id())
                                    ->orWhere('recipient_id', Auth::id());
                          })
                          ->firstOrFail();

            $share->delete();
            return response()->json(['message' => 'Partage supprimé']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur lors de la suppression du partage', 'error' => $e->getMessage()], 500);
        }
    }

    public function getPendingSharesByUser($userId) {
        try {
            return ShareResource::collection(
                Share::where('recipient_id', $userId)->where('accepted', false)->get()
            );
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur lors de la récupération des partages en attente', 'error' => $e->getMessage()], 500);
        }
    }

    public function getAllPendingShares() {
        try {
            $shares = Share::where('accepted', false)->get();
            return ShareResource::collection($shares);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur lors de la récupération des partages en attente', 'error' => $e->getMessage()], 500);
        }
    }

    public function getUserNotifications()
{
    try {
        $notifications = Auth::user()->notifications;

        return response()->json($notifications);
    } catch (\Exception $e) {
        return response()->json(['message' => 'Erreur lors de la récupération des notifications', 'error' => $e->getMessage()], 500);
    }
}

    
}
