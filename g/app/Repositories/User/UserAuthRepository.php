<?php
namespace App\Repositories\User;

use App\Models\User;
use Illuminate\Http\Request;

class UserAuthRepository
{
    protected $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Reset user password
     */
    public function sendPasswordResetEmail(Request $request)
    {
        $user = $this->user->where('email', $request->input('email'))->first();

        if (!$user) {
            return response()->json(['error' => 'Aucun utilisateur trouvé avec cette adresse e-mail'], 404);
        }
    }
}