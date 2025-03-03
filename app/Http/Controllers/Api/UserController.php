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
            'password' => bcrypt($request->password),
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
}
