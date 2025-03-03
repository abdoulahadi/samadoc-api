<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Directory;
use Illuminate\Http\Request;
use App\Http\Resources\DirectoryResource;
use Illuminate\Support\Facades\Auth;

class DirectoryController extends Controller {
    public function index() {
        return DirectoryResource::collection(Directory::where('user_id', Auth::id())->get());
    }

    public function store(Request $request) {
        $request->validate([
            'rep_name' => 'required|string',
            'level' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $directory = Directory::create([
            'rep_name' => $request->rep_name,
            'level' => $request->level,
            'description' => $request->description,
            'shared' => false,
            'user_id' => Auth::id(),
        ]);

        return new DirectoryResource($directory);
    }

    public function show($id) {
        $directory = Directory::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        return new DirectoryResource($directory);
    }

    public function update(Request $request, $id) {
        $directory = Directory::where('id', $id)->where('user_id', Auth::id())->firstOrFail();

        $directory->update($request->only('rep_name', 'level', 'description', 'shared'));

        return new DirectoryResource($directory);
    }

    public function destroy($id) {
        $directory = Directory::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $directory->delete();

        return response()->json(['message' => 'Répertoire supprimé']);
    }
}
