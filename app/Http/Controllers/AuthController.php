<?php

namespace App\Http\Controllers;

use App\Role;
use Validator;
use App\User;
use App\Users;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Firebase\JWT\ExpiredException;
use Illuminate\Support\Facades\Hash;
use Laravel\Lumen\Routing\Controller as BaseController;

class AuthController extends BaseController
{
    /**
     * The request instance.
     *
     * @var \Illuminate\Http\Request
     */
    private $request;
    /**
     * Create a new controller instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }
    /**
     * Create a new token.
     *
     * @param  \App\Users   $user
     * @return string
     */
    protected function jwt(Users $user)
    {
        $payload = [
            'iss' => "lumen-jwt", // Issuer of the token
            'sub' => $user->id, // Subject of the token
            'iat' => time(), // Time when JWT was issued.
            'exp' => time() + 60 * 100000 // Expiration time
        ];
        return JWT::encode($payload, env('JWT_SECRET'));
    }
    /**
     * Authenticate a user and return the token if the provided credentials are correct.
     *
     * @param  \App\Users   $user
     * @return mixed
     */
    public function authenticate(Users $user)
    {
        $this->validate($this->request, [
            'username'     => 'required',
            'password'  => 'required'
        ]);
        $user = Users::where('username', $this->request->input('username'))->first();
        if (!$user) {
            return response()->json([
                'error' => 'username does not exist.'
            ], 400);
        }
        if (Hash::check($this->request->input('password'), $user->password)) {
            $jwt = $this->jwt($user);
            $role = Role::find($user['role']);
            $user['role'] = $role;
            return response()->json([
                'token' => $jwt,
                'user' => $user
            ], 200);
        }
        return response()->json([
            'error' => 'username or password is wrong.'
        ], 400);
    }
}
