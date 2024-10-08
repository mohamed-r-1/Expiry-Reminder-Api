<?php

namespace App\Http\Controllers;

use App\Http\Requests\Reset;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\ResetPasswordRequest;
use App\Models\User;
use App\Notifications\RestPasswordVerificationNotification;
use Illuminate\Http\Request;
use Ichtrojan\Otp\Otp;
use Illuminate\Support\Facades\Hash;

class ResetPasswordController extends Controller
{
    private $otp;
    public function __construct()
    {
        $this->otp = new Otp;
    }
    public function passwordReset(ResetPasswordRequest $request)
    {
        $code = $this->otp->validate($request->email, $request->code);
        if (!$code->status) {
            $successs['error'] = $code;
            $successs['status'] = '401';
            return response()->json($successs, 401);
        }
        $user = User::where('email', $request->email)->first();
        $user->update(['password' => Hash::make($request->password)]);
        $user->tokens()->delete();
        $successs['success'] = true;
        // $successs['status'] = '200';
        return response()->json($successs, 200);
    }
}
