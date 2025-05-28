<?php

namespace Modules\Iuser\Http\Controllers\Api;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Imagina\Icore\Http\Controllers\CoreApiController;

use Modules\Iuser\Models\User;
use Modules\Iuser\Repositories\UserRepository;

class AuthApiController extends CoreApiController
{

    public function __construct(User $model, UserRepository $modelRepository)
    {
        parent::__construct($model, $modelRepository);
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

            //Laravel 12 Documentacion |genero error: Session store not set on request.
            //$request->session()->regenerate();

            //Authentication passed
            $user = auth()->user();
            $tokenResult = $user->createToken('authToken');

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

            //Laravel 12 Documentacion | genero error: Session store not set on request.
            /*
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            */

            // EJEMPLO ICMS
            // El $this->auth que tienen es de este = use Modules\User\Contracts\Authentication;
            /*
            $token = $this->validateResponseApi($this->getRequestToken($request)); //Get Token
            if ($token) DB::table('oauth_access_tokens')->where('id', $token->id)->delete(); //Delete Token
            $this->auth->logout();
            */

            $response = ['data' => 'Logout successful'];

        } catch (\Exception $e) {
            $status = $this->getStatusError($e->getCode());
            $response = ['errors' => $this->getErrorMessage($e)];
        }

        //Return response
        return response()->json($response ?? ['data' => 'Request successful'], $status ?? 200);
    }

}
