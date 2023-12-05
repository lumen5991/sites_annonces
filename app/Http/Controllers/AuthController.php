<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;



class AuthController extends Controller
{

    // Ajout d'un nouveau utilisateur
    public function createUser(Request $request)
    {
        $data = $request->all();

        $request->validate([
            "firstname" => ["string", "min:2"],
            "lastname" => ["string", "min:2"],
            "username" => ["required", "string", "min:2"],
            "picture" => ["string"],
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
            $path = $request->file("picture")->store('user_pictures');
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

        Mail::send('mail', ['username' => $data['username'],'verificationCode' => $verificationCode,], 
        function ($message) use ($data) {
            $config = config('mail');
            $message->subject('Vérification de la création de votre compte!')
                ->from($config['from']['address'], $config['from']['name'])
                ->to($data['email'], $data['username']);
        });

        return response()->json([
            'status' => 200,
            'message' => 'Utilisateur créé avec succès',
        ], 200);
    }

    //Validation du compte (vérification du code reçu par mail)
    public function verifyEmail(Request $request, $email)
{
    $request->validate([
        'verification_code' => 'required',
        'email' => 'required|email',
    ]);

    $user = User::where('email', $email)->first();

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

        if(is_null($user)) {
            return response()->json([
                'status' => 422,
                'message' => "Utilisateur inexistant !",
            ], 422);
        }

        if(is_null($user->email_verify_at)) {
            return response()->json([
                'status' => 403,
                'message' => 'Veuillez vérifier votre compte avant de vous connecter.',
            ], 403);
        }

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 401,
                'message' => 'Les informations d\'identification ne sont pas valides.',
            ], 401);
        }


        $token = $user->createToken('API_TOKEN')->plainTextToken;

        return response()->json([
            'status' => 200,
            'message' => 'Connexion réussie',
            'token' => $token
        ], 200);
    }


    //Récupérer l'utilisateur connecté

    public function me(Request $request)
    {
        $user = $request->user();
        if($user){
            return response()->json([
                "infos"=> $user
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

    Mail::send('forgetPassword', ['username' => $user['username']], 
        function ($message) use ($user) {
            $config = config('mail');
            $message->subject('Notification de réinitialisation du mot de passe!')
                ->from($config['from']['address'], $config['from']['name'])
                ->to($user['email'], $user['username']);
        });

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

    

}
