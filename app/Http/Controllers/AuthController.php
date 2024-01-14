<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;



class AuthController extends Controller
{

    // Ajout d'un nouveau utilisateur
    public function createUser(Request $request)
    {
        $data = $request->all();
        $path = null;

        $request->validate([
            "firstname" => ["string", "min:2"],
            "lastname" => ["string", "min:2"],
            "username" => ["required", "string", "min:2"],
            "picture" => ["image", "mimes:jpeg,png,jpg,gif,svg"],
            "email" => [
                "required",
                "regex:/^[\w\-\.]+@([\w\-]+\.)+[\w\-]{2,4}$/",
                "unique:users"
            ],
            "password" => [
                "required",
                "regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%?&#^_;:,])[A-Za-z\d@$!%?&#^_;:,]{8,}$/",
                "confirmed:password_confirmation"
            ]
        ]);



        if ($request->hasFile("picture")) {

            $path = $request->file("picture")->store('user_pictures', 'public');

        }

        $user = User::create([
            "firstname" => $data["firstname"],
            "lastname" => $data["lastname"],
            "username" => $data["username"],
            "picture" => $path,
            "email" => $data["email"],
            "password" => Hash::make($data["password"]),
        ]);

        if (!$user) {
            return response()->json([
                'status' => 500,
                'message' => "Erreur lors de la création de l'utilisateur",
            ], 500);
        }

        $verificationCode = rand(100000, 999999);
        $user->update(['verification_code' => $verificationCode]);

        Mail::send(
            'mail',
            ['username' => $data['username'], 'verificationCode' => $verificationCode,],
            function ($message) use ($data) {
                $config = config('mail');
                $message->subject('Vérification de la création de votre compte!')
                    ->from($config['from']['address'], $config['from']['name'])
                    ->to($data['email'], $data['username']);
            }
        );

        return response()->json([
            'status' => 200,
            'message' => 'Utilisateur créé avec succès',
            'user' => $user,
        ], 200);
    }

    //mettre à jour utilisateur
    public function updateUser(Request $request, $id)
    {
        $data = $request->all();

        $request->validate([
            "firstname" => ["nullable", "string", "min:2"],
            "lastname" => ["nullable", "string", "min:2"],
            "username" => ["nullable", "string", "min:2"],
            "picture" => ["nullable", "image", "mimes:jpeg,png,jpg,gif,svg"],
            "email" => [
                "nullable",
                "regex:/^[\w\-\.]+@([\w\-]+\.)+[\w\-]{2,4}$/",
            ],
        ]);

        $user = User::find($id);

        if ($user->id !== Auth::user()->id) {
            return response()->json([
                'status' => 403,
                'message' => "Vous n'êtes pas autorisé à modifier ce compte.",
            ], 403);
        }

        if ($request->hasFile("picture")) {

            $path = $request->file("picture")->store('user_pictures', 'public');
        }

        $user->update([
            "firstname" => $data["firstname"],
            "lastname" => $data["lastname"],
            "username" => $data["username"],
            "picture" => $path,
            "email" => $data["email"],
        ]);


        return response()->json([
            'status' => 200,
            'message' => 'Votre compte a été bien mise à jour avec succès',
            'user' => $user,
        ], 200);
    }

    // TODO Ne plus passer le mail en paramètre de la route (très mauvaise pratique)
    // TODO Le mail étant passer dans le corps de la requête il n'est plus utile de le passer en paramètre

    //Validation du compte (vérification du code reçu par mail)
    public function verifyEmail(Request $request)
    {
        $request->validate([
            'verification_code' => 'required',
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || $request->verification_code !== $user->verification_code) {
            return response()->json([
                'status' => false,
                'message' => 'Code de vérification incorrect.',
            ], 401);
        }

        $user->update([
            'email_verify_at' => now(),
        ]);

        return response()->json([
            'status' => 200,
            'message' => 'Compte vérifié, vous pouvez vous connecter',
        ], 200);
    }


    //Connection
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (is_null($user)) {
            return response()->json([
                'status' => 422,
                'message' => "Utilisateur inexistant !",
            ], 422);
        }

        if (is_null($user->email_verify_at)) {
            return response()->json([
                'status' => 403,
                'message' => 'Veuillez vérifier votre compte avant de vous connecter.',
            ], 403);
        }

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 401,
                'message' => "Les informations d'identification ne sont pas valides.",
            ], 401);
        }

        $token = $user->createToken('API_TOKEN')->plainTextToken;

        return response()->json([
            'status' => 200,
            'message' => 'Connexion réussie',
            'user' => $user,
            'token' => $token
        ], 200);
    }


    //Récupérer l'utilisateur connecté

    public function me(Request $request)
    {
        $user = $request->user();
        if ($user) {
            return response()->json([
                "infos" => $user
            ]);
        }
    }

    //Déconnexion
    public function logout(Request $request)
    {
        $user = $request->user();

        if ($user) {
            $user->currentAccessToken()->delete();
            return response()->json([
                'message' => 'Déconnexion réussie.'
            ]);
        }

        return response()->json([
            'message' => 'Aucun utilisateur connecté.'
        ], 401);
    }

    //mot de passe oublié

    public function resetPassword(Request $request)
    {
        $request->validate([
            "email" => "required|email",
            "new_password" => [
                "required",
                "regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%?&#^_;:,])[A-Za-z\d@$!%?&#^_;:,]{8,}$/",
                "confirmed:new_password_confirmation"
            ]
        ]);

        $user = User::where('email', $request->email)->first();

        if (is_null($user)) {
            return response()->json([
                'status' => 422,
                'message' => "Utilisateur inexistant !",
            ], 422);
        }

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        Mail::send(
            'forgetPassword',
            ['username' => $user['username']],
            function ($message) use ($user) {
                $config = config('mail');
                $message->subject('Notification de réinitialisation du mot de passe!')
                    ->from($config['from']['address'], $config['from']['name'])
                    ->to($user['email'], $user['username']);
            }
        );

        return response()->json([
            'status' => 200,
            'message' => "Mot de passe modifié avec succès",
        ], 200);

        if ($user) {
            $user->currentAccessToken()->delete();
            return response()->json([
                'message' => 'Déconnexion réussie.'
            ]);
        }


    }

    //supprimer utilisateur
    public function deleteUser(Request $request)
    {

        $user = $request->user();

        if (!$user) {
            return response()->json([
                'status' => 404,
                'message' => "Vous n'êtes pas connecté, veuillez vous connectez avant de supprimer votre compte",
            ], 404);
        }

        $user->announcement()->delete();

        $user->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Utilisateur et annonces associées supprimés avec succès',
        ], 200);
    }


}
