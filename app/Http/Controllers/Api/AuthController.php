<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User; 
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Fungsi Register User Baru
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'phone_number' => 'required|unique:users',
            'password' => 'required|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::create([
            'name' => $request->name,
            'phone_number' => $request->phone_number,
            'password' => bcrypt($request->password),
            'balance' => 0,
        ]);

        return response()->json([
            'message' => 'User Created Successfully', 
            'data' => $user
        ], 201);
    }

    /**
     * Fungsi Login untuk mendapatkan Token JWT
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $credentials = $request->only('phone_number', 'password');

        if (!$token = auth()->guard('api')->attempt($credentials)) {
            return response()->json(['error' => 'Nomor HP atau Password salah'], 401);
        }

        return response()->json([
            'message' => 'Login Berhasil',
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->guard('api')->factory()->getTTL() * 60
        ]);
    }

    /**
     * Fungsi Update Profile User
     */
    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'phone_number' => 'sometimes|required|unique:users,phone_number,' . $user->id,
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user->update($request->only('name', 'phone_number'));

        return response()->json([
            'message' => 'Profil berhasil diperbarui',
            'data' => $user
        ]);
    }
}