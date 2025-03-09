<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Directory;
use Illuminate\Http\Request;
use App\Http\Resources\DirectoryResource;
use Illuminate\Support\Facades\Auth;

class DirectoryController extends Controller {
    public function index() {
        try {
            $directories = Directory::all();
            return DirectoryResource::collection($directories);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while fetching directories'], 500);
        }
    }

    public function userDirectories() {
        try {
            $directories = Directory::where('user_id', Auth::id())->get();
            return DirectoryResource::collection($directories);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while fetching user directories'], 500);
        }
    }

    public function store(Request $request) {
        try {
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
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while creating the directory'], 500);
        }
    }

    public function show($id) {
        try {
            $directory = Directory::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
            return new DirectoryResource($directory);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while fetching the directory'], 500);
        }
    }

    public function update(Request $request, $id) {
        try {
            $directory = Directory::where('id', $id)->where('user_id', Auth::id())->firstOrFail();

            $request->validate([
                'rep_name' => 'required|string',
                'level' => 'nullable|string',
                'description' => 'nullable|string',
                'shared' => 'boolean',
            ]);

            $directory->update($request->only('rep_name', 'level', 'description', 'shared'));

            return new DirectoryResource($directory);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while updating the directory'], 500);
        }
    }

    public function destroy($id) {
        try {
            $directory = Directory::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
            $directory->delete();

            return response()->json(['message' => 'Répertoire supprimé']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while deleting the directory'], 500);
        }
    }

    public function sharedDirectories() {
        try {
            $directories = Directory::whereHas('shares', function ($query) {
                $query->where('accepted', true)->where('user_id', Auth::id());
            })->get();

            return DirectoryResource::collection($directories);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while fetching shared directories'], 500);
        }
    }

    public function getLatestOpenedDirectoriesByUser() {
        try {
            $directories = Directory::where('user_id', Auth::id())
                ->orderBy('updated_at', 'desc')
                ->take(5)
                ->get();

            return DirectoryResource::collection($directories);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while fetching the latest opened directories'], 500);
        }
    }

    public function searchDirectoryByName(Request $request) {
        try {
            $request->validate([
                'name' => 'required|string',
            ]);

            $directories = Directory::where('user_id', Auth::id())
                ->where('rep_name', 'like', '%' . $request->name . '%')
                ->get();

            return DirectoryResource::collection($directories);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while searching for directories'], 500);
        }
    }
}
