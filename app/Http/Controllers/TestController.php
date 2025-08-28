<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TestController extends Controller
{
    public function showLogin()
    {
        return view('auth.login-simple');
    }
    
    public function testAuth(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'Laravel backend is working!',
            'data' => $request->all()
        ]);
    }
}