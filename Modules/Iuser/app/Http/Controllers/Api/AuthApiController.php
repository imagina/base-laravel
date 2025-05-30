<?php

namespace Modules\Iuser\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Imagina\Icore\Http\Controllers\CoreApiController;

use Modules\Iuser\Models\User;
use Modules\Iuser\Repositories\UserRepository;
use Modules\Iuser\Services\AuthService;

use Illuminate\Support\Facades\Password;

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

            //Validate request
            $data = $request->input('attributes') ?? [];
            $this->validateWithModelRules($data, 'login');

            //Validations Credentials and Login
            if (!Auth::attempt($data))
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
            $status = $this->getHttpStatusCode($e);
            $response = $this->getErrorResponse($e);
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
            $status = $this->getHttpStatusCode($e);
            $response = $this->getErrorResponse($e);
        }

        //Return response
        return response()->json($response ?? ['data' => 'Request successful'], $status ?? 200);
    }

    /**
     * Reset Password
     */
    public function reset(Request $request)
    {
        try {

            //Validate request
            $data = $request->input('attributes') ?? [];
            $this->validateWithModelRules($data, 'resetPassword');

            //Process reset password
            $result = Password::sendResetLink(['email'=>$data['email']]);

            //TODO: Traducciones
            if($result === Password::ResetLinkSent){
                //status = passwords.sent
                $message = "We have emailed your password reset link";
            }else{
                //status = passwords.throttled
                $message = "Please wait before retrying";
            }

            $response = ['data' => $message];

        } catch (\Exception $e) {
            $status = $this->getHttpStatusCode($e);
            $response = $this->getErrorResponse($e);
        }

        //Return response
        return response()->json($response ?? ['data' => 'Request successful'], $status ?? 200);
    }

    /**
     * Reset Password Complete
     */
    public function resetComplete(Request $request)
    {
        try {

            //Validate request
            $data = $request->input('attributes') ?? [];
            $this->validateWithModelRules($data, 'resetPasswordComplete');

            //Process reset password complete
            $result = Password::reset(
                $data,
                function ($model, string $password) {
                    $model->forceFill([
                        'password' => \Hash::make($password)
                    ])->setRememberToken(\Str::random(60));

                    $model->save();
                }
            );

            //TODO: Traducciones
            if($result === Password::PasswordReset){
                //status = passwords.reset
                $message = "Password reset successfully.";
            }else{
                //status = passwords.token
                $message = "Invalid information";
            }

            $response = ['data' => $message];

         } catch (\Exception $e) {
            $status = $this->getHttpStatusCode($e);
            $response = $this->getErrorResponse($e);
        }

        //Return response
        return response()->json($response ?? ['data' => 'Request successful'], $status ?? 200);
    }

}
