<?php
namespace App\Http\Controllers;
use App\Services\FasterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Mtn\GenerateMtnToken;
use Illuminate\Support\Facades\Hash;
use App\Services\TwilioService;
use Illuminate\Support\Facades\Validator;

use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
// use Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use OpenApi\Annotations as OA;
// use Illuminate\Support\Str;
/**
 * @OA\Tag(
 *     name="Authentication",
 *     description="Operations related to Authentication"
 * )
 */
class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    // protected $fasterService, $mtnToken;
    // protected $credential;
    // public function __construct() {
    //     // public function __construct(FasterService $fasterServive) {
    //     // // $this->middleware('auth:api', ['except' => ['login', 'register']]);
    //     // $this->fasterService = $fasterServive;
    //     // $this->mtnToken = $generateMtnToken;
    // }
    protected $twilioService;

    public function __construct(TwilioService $twilioService)
    {
        $this->twilioService = $twilioService;
    }

    /**
     * Register a User.
     *
     * @OA\Post(
     *     path="/api/register",
     *     summary="Register a new user",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"first_name", "last_name", "email", "phone", "password"},
     *             @OA\Property(property="first_name", type="string", description="First name of the user"),
     *             @OA\Property(property="last_name", type="string", description="Last name of the user"),
     *             @OA\Property(property="email", type="string", format="email", description="Email address of the user"),
     *             @OA\Property(property="phone", type="string", format="numeric", description="Phone number of the user"),
     *             @OA\Property(property="password", type="string", format="password", description="Password for the user")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User registered successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example="1"),
     *             @OA\Property(property="first_name", type="string", example="John"),
     *             @OA\Property(property="last_name", type="string", example="Doe"),
     *             @OA\Property(property="email", type="string", example="john@example.com"),
     *             @OA\Property(property="phone", type="string", example="1234567890"),
     *             @OA\Property(property="code", type="integer", example="1234"),
     *             @OA\Property(property="is_verified", type="boolean", example="false"),
     *             @OA\Property(property="created_at", type="string", format="date-time", example="2024-02-26T10:15:00Z"),
     *             @OA\Property(property="updated_at", type="string", format="date-time", example="2024-02-26T10:15:00Z")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request, validation failed",
     *         @OA\JsonContent(type="object", @OA\Property(property="errors", type="array", @OA\Items(type="string")))
     *     )
     * )
     */   
    public function register(Request $request) {
        $donne = $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|string|email|max:100|unique:users',
            'phone' => 'required|numeric|unique:users',
            'password' => 'required|string|min:6'
        ]);

            $user = User::create([
                "first_name" => $donne["first_name"],
                "last_name" => $donne["last_name"],
                "email" => $donne["email"],
                "phone" => $donne["phone"],
                "code" => null,
                "is_verified" => false,
                "password" => bcrypt($donne["password"])
            ]);
            $verificationCode = random_int(1000, 9999);
     
            // Enregistrer le code de vérification dans l'utilisateur
            $user->code = $verificationCode;
            $user->save();
        
            return response($user, 201);
        // }
    }

    /**
     * Verify user's verification code.
     *
     * @OA\Post(
     *     path="/api/verify-code",
     *     summary="Verify user's verification code",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "code"},
     *             @OA\Property(property="email", type="string", format="email", description="Email address of the user"),
     *             @OA\Property(property="code", type="string", description="Verification code")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Verification successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Verification code correct. User verified.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User not found.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Incorrect verification code.")
     *         )
     *     )
     * )
     */
    public function verifyCode(Request $request) {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|string',
        ]);
    
        $user = User::where('email', $request->email)->first();
    
        if (!$user) {
            return response()->json(['message' => 'Utilisateur non trouvé.'], 404);
        }
    
        if ($user->code === $request->code) {
            // Mettre à jour le champ is_verified à true
            $user->is_verified = true;
            $user->save();
    
            return response()->json(['message' => 'Code de vérification correct. Utilisateur vérifié.'], 200);
        }
    
        return response()->json(['message' => 'Code de vérification incorrect.'], 422);
    }

    /**
     * @OA\Schema(
     *     schema="User",
     *     required={"id", "name", "email"},
     *     @OA\Property(property="id", type="integer", format="int64", example=1),
     *     @OA\Property(property="name", type="string", example="John Doe"),
     *     @OA\Property(property="email", type="string", format="email", example="john@example.com")
     * )
     */

    /**
     * Authenticate user and generate JWT token.
     *
     * @OA\Post(
     *     path="/api/login",
     *     summary="Authenticate user and generate JWT token",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"login", "password"},
     *             @OA\Property(property="login", type="string", description="User email or phone number", example="user@example.com or phone number"),
     *             @OA\Property(property="password", type="string", description="User password")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="user"),
     *             @OA\Property(property="token", type="string", description="JWT token")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Invalid credentials")
     *         )
     *     )
     * )
     */
    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);
    
        $user = User::where(function ($query) use ($request) {
            $query->where('email', $request->login)
                ->orWhere('phone', $request->login);
        })->first();
    
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response(['message' => 'Identifiants invalides'], 401);
        }
    
        // Générer le jeton JWT
        $token = JWTAuth::fromUser($user);
    
        return response([
            'user' => $user,
            'token' => $token
        ]);
    }
      
    // /**
    //  * Update user password
    //  */
    // /**
    //  * Update user password
    //  *
    //  * @OA\Post(
    //  *     path="/api/change-password",
    //  *     summary="Update user password",
    //  *     tags={"Authentication"},
    //  *     @OA\RequestBody(
    //  *         required=true,
    //  *         @OA\JsonContent(
    //  *             required={"current_password", "new_password", "confirm_password"},
    //  *             @OA\Property(property="current_password", type="string", format="password", description="Current password"),
    //  *             @OA\Property(property="new_password", type="string", format="password", description="New password"),
    //  *             @OA\Property(property="confirm_password", type="string", format="password", description="Password confirmation")
    //  *         )
    //  *     ),
    //  *     @OA\Response(
    //  *         response=200,
    //  *         description="Password updated successfully"
    //  *     ),
    //  *     @OA\Response(
    //  *         response=400,
    //  *         description="Bad request, validation failed",
    //  *         @OA\JsonContent(type="object", @OA\Property(property="errors", type="array", @OA\Items(type="string")))
    //  *     ),
    //  *     @OA\Response(
    //  *         response=401,
    //  *         description="Unauthorized, invalid current password",
    //  *         @OA\JsonContent(type="object", @OA\Property(property="error", type="string"))
    //  *     )
    //  * )
    //  */
    public function updatePassword(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'current_password' => 'required',
            'new_password' => 'required|min:6',
            'confirm_password' => 'required|same:new_password',
        ]);

        if ($validation->fails()) {
            $errors = $validation->errors()->all();
            return response()->json(['errors' => $errors], 400);
        }
        // Vérifiez le mot de passe actuel de l'utilisateur
        if (!Hash::check($request->input('current_password'), auth()->user()->password)) {
            return response()->json(['error' => 'Le mot de passe actuel est invalide.'], 401);
        }

        // Mettez à jour le mot de passe
        $user = auth()->user();
        $user->password = bcrypt($request->input('new_password'));
        $user->save();
        $this->logout();
        return response()->json(['message' => 'Mot de passe mise à jour avec succès.']);
    }


    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     * Log the user out (Invalidate the token).
     *
     * @OA\Post(
     *     path="/api/logout",
     *     summary="Logout user",
     *     tags={"Authentication"},
     *     @OA\Response(
     *         response=200,
     *         description="User logged out successfully"
     *     ),
     *     @OA\Response(
     *         response=405,
     *         description="Method Not Allowed"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(type="object", @OA\Property(property="error", type="string"))
     *     )
     * )
     */
    public function logout() {
        try {
            auth()->check();
            auth()->logout();
            return response()->json(['message' => 'Utilisateur déconnecté avec succès.']);
        } catch (MethodNotAllowedHttpException $e) {
            return response()->json(['error' => 'Méthode non autorisée.'], 405);
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            return response()->json(['error' => $errorMessage], 500);
        }
    }
    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */

    /**
     * Refresh a token.
     *
     * @OA\Post(
     *     path="/api/refresh",
     *     summary="Refresh JWT token",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Token refreshed successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="token", type="string", description="Refreshed JWT token")
     *         )
     *     )
     * )
     */
    public function refresh($user) {
        $token = JWTAuth::fromUser($user);
        return $token;
    }
    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */

     /**
     * Get the authenticated User.
     *
     * @OA\Get(
     *     path="/api/user-profile",
     *     summary="Get authenticated user profile",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="User profile retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="user", type="object", description="User profile")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized, user not logged in",
     *         @OA\JsonContent(type="object", @OA\Property(property="error", type="string", description="Error message"))
     *     )
     * )
 */
    public function userProfile() {
        return auth()->user() ? response()->json(auth()->user()): response()->json(['error' => 'Veuillez-vous connecter.'], 401);
    }

    /**
     * Updated the authenticate user information
     */

    /**
     * Update the authenticated user information.
     *
     * @OA\Post(
     *     path="/api/update-user-profile/{id}",
     *     summary="Update authenticated user profile",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="User ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", description="New name for the user")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User profile updated successfully",
     *         @OA\JsonContent(type="object", @OA\Property(property="message", type="string", description="Success message"))
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized, user not logged in",
     *         @OA\JsonContent(type="object", @OA\Property(property="error", type="string", description="Error message"))
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden, user not authorized to update profile",
     *         @OA\JsonContent(type="object", @OA\Property(property="error", type="string", description="Error message"))
     *     )
     * )
     */
    public function updateUserProfile(Request $request, $id)
    {
        $user = User::find($id);
        if ($request->has('name') && !$request->has('password') && !$request->has('email')) {
            $user->update([
                "name"=>$request->name
            ]);
            return response()->json(["message"=>"Le nom d'utilisateur mis à jour avec succès."],201);
        }
        return response()->json(["message"=>"Vous n'êtes pas authorisé."]);
    }

}