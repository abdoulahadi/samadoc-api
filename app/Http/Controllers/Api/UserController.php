<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\Controller;

class UserController extends Controller {
    public function index() {
        return response()->json(User::all());
    }

    public function store(Request $request) {
        $request->validate([
            'username' => 'required|string|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
            'sexe' => 'required|in:Masculin,Féminin'
        ]);

        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => $request->password,
            'sexe' => $request->sexe,
            'activation_token' => Str::random(60)
        ]);

        Mail::raw("Cliquez ici pour activer votre compte : /api/activate/{$user->activation_token}", function ($message) use ($user) {
            $message->to($user->email)->subject('Activation de compte');
        });

        return response()->json(['message' => 'Utilisateur créé. Vérifiez votre e-mail.'], 201);
    }

    public function show($id) {
        $user = User::findOrFail($id);
        return response()->json($user);
    }

    public function update(Request $request, $id) {
        $user = User::findOrFail($id);
        $user->update($request->only('username', 'email', 'occupation', 'university', 'dbirth', 'pbirth', 'image'));
        return response()->json(['message' => 'Utilisateur mis à jour', 'user' => $user]);
    }

    public function destroy($id) {
        User::destroy($id);
        return response()->json(['message' => 'Utilisateur supprimé']);
    }

    public function activate($token) {
        $user = User::where('activation_token', $token)->first();
        if (!$user) {
            return response()->json(['message' => 'Token invalide'], 400);
        }

        $user->update(['state' => true, 'activation_token' => null]);
        return response()->json(['message' => 'Compte activé']);
    }

    public function login(Request $request) {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $credentials = $request->only('email', 'password');
        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => 'Identifiants invalides'], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('authToken')->plainTextToken;
        return response()->json(['token' => $token, 'user' => $user]);
    }

    public function logout() {
        Auth::user()->tokens()->delete();
        return response()->json(['message' => 'Déconnexion réussie']);
    }

    public function updatePassword(Request $request) {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed'
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'Mot de passe actuel incorrect'], 400);
        }

        $user->update(['password' => $request->new_password]);

        return response()->json(['message' => 'Mot de passe mis à jour']);
    }

    public function updateProfileImage(Request $request) {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $user = Auth::user();

        try {
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('images/profile'), $imageName);

                if ($user->image && $user->image !== 'default.png') {
                    $oldImagePath = public_path('images/profile/' . $user->image);
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }

                $user->update(['image' => $imageName]);
            }

            return response()->json(['message' => 'Photo de profil mise à jour', 'user' => $user]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur lors de la mise à jour de la photo de profil', 'error' => $e->getMessage()], 500);
        }
    }

    public function searchUsersByUsername(Request $request) {
        $request->validate([
            'query' => 'required|string|min:1'
        ]);

        $query = $request->query('query');
        $users = User::where('username', 'LIKE', '%' . $query . '%')
                     ->select('id', 'username', 'email', 'image')
                     ->limit(10)
                     ->get();

        return response()->json($users);
    }
    
}
