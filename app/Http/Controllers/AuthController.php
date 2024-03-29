<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Traits\HasRoles;

class AuthController extends Controller
{
    use HasRoles;

    /**
     *  @param Request $request
     *  ajouter un utilisateur
     */
    public function createUser(Request $request)
    {
        $data = $request->all();

        $request->validate([
            "firstname" => ["string", "min:2"],
            "lastname" => ["string", "min:2"],
            "username" => ["required", "string", "min:2", "unique:users"],
            "unique:users",
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

        $path = null;
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


        if (User::where('email', $data['email'])->exists()) {
            return response()->json([
                'status' => 422,
                'message' => 'Un utilisateur avec cet e-mail existe déjà.',
            ], 422);
        }

        if (User::where('username', $data['username'])->exists()) {
            return response()->json([
                'status' => 422,
                'message' => "Un utilisateur avec ce nom d'utilisateur existe déjà.",
            ], 422);
        }


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

    /**
     * @param Request $request
     * mettre à jour son compte utilisateurs
     */
    public function updateUser(Request $request)
    {
        $data = $request->all();
        $user = $request->user();
        $request->validate([
            "firstname" => ["nullable", "string", "min:2"],
            "lastname" => ["nullable", "string", "min:2"],
            "username" => ["nullable", "string", "min:2",],
            "picture" => ["nullable", "image", "mimes:jpeg,png,jpg,gif,svg"],
            "email" => [
                "nullable",
                "regex:/^[\w\-\.]+@([\w\-]+\.)+[\w\-]{2,4}$/",
            ],
        ]);

        if (!$user) {
            return response()->json([
                'status' => 403,
                'message' => "Vous n'êtes pas autorisé à modifier ce compte.",
            ], 403);
        }

        $path = null;

        if ($request->hasFile("picture")) {
            $path = $request->file("picture")->store('user_pictures', 'public');
        }

        $user->update([
            "firstname" => $data["firstname"],
            "lastname" => $data["lastname"],
            "username" => $data["username"],
            "picture" => asset(Storage::url($path)),
            "email" => $data["email"],
        ]);

        return response()->json([
            'status' => 200,
            'message' => 'Votre compte a été bien mise à jour avec succès',
            'user' => $user,
        ], 200);
    }


    /**
     * @param Request $request
     * Validation du compte (vérification du code reçu par mail)
     */
    public function verifyEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'verification_code' => 'required',
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


    /**
     * @param Request $request
     * se connecter
     */
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

        $roles = $user->getRoleNames();


        return response()->json([
            'status' => 200,
            'message' => 'Connexion réussie',
            'user' => $user,
            'token' => $token,
            'roles' => $roles,
        ], 200);
    }


    /**
     * @param Request $request
     * Récupérer l'utilisateur connecté
     */

    public function me(Request $request)
    {
        $user = $request->user();
        $roles = $user->getRoleNames();
        if ($user) {
            return response()->json([
                "user" => $user
            ]);
        } else {
            return response()->json(
                [
                    "message" => "Utilisateur non authentifié",
                    "roles" => $roles,
                ],
                401
            );
        }
    }



    /**
     * @param Request $request
     * Déconnexion
     */
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


    /**
     * @param Request $request
     * mot de passe oublié
     */
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


    /**
     * @param Request $request
     * supprimer utilisateur
     */
    public function deleteUser(Request $request)
    {

        $user = $request->user();

        if (!$user) {
            return response()->json([
                'status' => 404,
                'message' => "Vous n'êtes pas connecté, veuillez vous connectez avant de supprimer votre compte",
            ], 404);
        }


        $user->announcement()->notes()->delete();

        $user->announcement()->delete();

        $user->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Utilisateur et annonces associées supprimés avec succès',
        ], 200);
    }

    /**
     * @param Request $request
     * Récupérer tous les utilisateurs 
     */
    public function getAllUsers(Request $request)
    {
        $user = $request->user();

        if (!$user || !$user->hasRole('admin')) {
            return response()->json([
                'status' => 403,
                'message' => "Vous n'avez pas les autorisations nécessaires.",
            ], 403);
        }
        // $users = User::with('roles')->get();

        $users = User::all();
        $users->load('roles');

        return response()->json([
            'status' => 200,
            'users' => $users,

        ], 200);
    }

}
