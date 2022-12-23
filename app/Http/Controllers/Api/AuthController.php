<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Auth\LoginService;
use App\Services\Auth\RegisterService;
use Illuminate\Cache\RedisStore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    protected $loginService;
    protected $registerService;

    public function __construct(LoginService $loginService, RegisterService $registerService)
    {
        $this->loginService = $loginService;
        $this->registerService = $registerService;
    }

    /**
     * Get a JWT via given credentials.
     * @param object $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        try {
            $credentials = $request->only(['email', 'password']);

            $auth = $this->loginService->execute($credentials);

            return response()->json([
                'status' => 'ok',
                'data' => $auth
            ]);
        } catch (\Throwable $th) {
            // return error
            return response()->json([
                'error' => "Unauthorized"
            ], 401);
        }
    }

    /**
     * Register a new user
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        try {

            // validating user before registering
            $validator = Validator::make($request->all(), [
                'name' => 'required|max:255',
                'email' =>'required|email|max:255|unique:users',
                'password' =>'required|min:6'
            ]);

            // return validation errors
            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors()
                ], 401);
            }

            // create user
            $credentials = $request->only(['name', 'email', 'password']);
            $request['password'] = Hash::make($request->password);
            $user = User::create($request->all());

            // return token
            if ($user) {
                $auth = $this->registerService->execute($credentials);

                return response()->json([
                    'data' => $auth,
                ], 200);
            }

            return response()->json([
                'error' => "Something went wrong"
            ], 401);

        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 401);
        }
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        try {
            return response()->json(auth()->user());
        } catch (\Throwable $th) {
            return response()->json([
                'error' => $th->getMessage()
            ], $th->getCode());
        }
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        try {
            auth()->logout();

            return response()->json(['message' => 'Successfully logged out']);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => $th->getMessage()
            ], $th->getCode());
        }

    }
}
