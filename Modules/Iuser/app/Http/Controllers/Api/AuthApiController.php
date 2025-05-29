<?php

namespace Modules\Iuser\Http\Controllers\Api;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Imagina\Icore\Http\Controllers\CoreApiController;

use Modules\Iuser\Models\User;
use Modules\Iuser\Repositories\UserRepository;
use Modules\Iuser\Services\AuthService;

class AuthApiController extends CoreApiController
{

    protected $authService;

    public function __construct(User $model, UserRepository $modelRepository,AuthService $authService)
    {
        parent::__construct($model, $modelRepository);
        $this->authService = $authService;
    }

    /**
     * Login
     */
    public function login(Request $request)
    {
        try {

            $credentials = $request->validate([
                'email' => ['required', 'email'],
                'password' => ['required'],
            ]);

            //Validations Credentials and Login
            if (!Auth::attempt($credentials))
                throw new \Exception('Unauthorized', 401);

            //TODO: Esta es una opcion , no se si sea necesaria
            //El middleware web debe estar habilitado en la ruta del login API.
            /* if (Auth::guard('web')->attempt($credentials)) {
                $request->session()->regenerate();
            } */

            //Authentication passed
            $user = auth()->user();
            $tokenResult = $user->createToken('authToken');

            //TODO: No generó error pero habria que probar en frontend
            //Session in Blade
            $this->authService->logUserIn($user);

            $response = ['data' => [
                'userData' => $user,
                'userToken' => $tokenResult->accessToken,
                'expiresDate' => $tokenResult->token->expires_at
            ]];

        } catch (\Exception $e) {
            $status = $this->getStatusError($e->getCode());
            $response = ['errors' => $this->getErrorMessage($e)];
        }

        //Return response
        return response()->json($response ?? ['data' => 'Request successful'], $status ?? 200);
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        try {

            $user = auth()->user();
            $user->token()->revoke(); //Revoke the token

            //Session in Blade
            $this->authService->logUserOut($user);

            $response = ['data' => 'Logout successful'];

        } catch (\Exception $e) {
            $status = $this->getStatusError($e->getCode());
            $response = ['errors' => $this->getErrorMessage($e)];
        }

        //Return response
        return response()->json($response ?? ['data' => 'Request successful'], $status ?? 200);
    }

}
