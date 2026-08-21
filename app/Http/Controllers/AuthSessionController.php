<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthSessionController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'user' => 'required|array',
        ]);

        $user = $request->input('user');

        session([
            'logged_in' => true,
            'auth_user' => $user,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Session created successfully.',
        ], 200);
    }

    public function destroy(Request $request)
    {
        session()->forget([
            'logged_in',
            'auth_user',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Session destroyed successfully.',
        ], 200);
    }
}