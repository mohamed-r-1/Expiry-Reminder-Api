<?php

namespace App\Http\Controllers;

use App\Http\Requests\ForgetPasswordRequest;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Notifications\CodeNotification;
use App\Notifications\RestPasswordVerificationNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ForgetPasswordController extends Controller
{
    public function emailExists(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->getMessageBag()], 401);
        } else {
            $user = User::where('email', $request->email)->first();

            if ($user) {
                $user->email_verified_at = null;
                $token = 'Bearer ' . $user->createToken($request->email)->plainTextToken;
                $code = rand(10000, 99999);
                $codeExpireAt = date('Y-m-d H:i:s', strtotime('+2 minutes'));

                $user->code = $code;
                $user->code_expired_at = $codeExpireAt;
                $user->save();
                $user->token = $token;
                $user->notify(new CodeNotification($code));
                $res['status'] = true;
                $res['token'] = $token;

                return response()->json($res, 200);
            } else {
                return response()->json(['message' => 'Email does not exist'], 401);
            }
        }
    }

    public function sendCode(Request $request)
    {
        $token = $request->header('Authorization');
        $authenticatedUser = Auth::guard('sanctum')->user();
        $code = rand(10000, 99999);
        $codeExpireAt = date('Y-m-d H:i:s', strtotime('+2 minutes'));

        $user = User::find($authenticatedUser->id);
        $user->code = $code;
        $user->code_expired_at = $codeExpireAt;
        $user->save();

        $user->token = $token;

        // Send notification
        $user->notify(new CodeNotification($code));
        $res['status'] = true;
        $res['token'] = $token;

        return response()->json($res, 200);
    }


    public function checkCode(Request $request)
    {
        $token = $request->header('Authorization');
        $validateData = Validator::make($request->all(), [
            "code" => 'required|integer|digits:5'

        ]);

        if ($validateData->fails()) {
            $data["error"] = $validateData->errors();
            $data['status'] = '400';
            return response()->json($data);
        }

        $authenticatedUser = Auth::guard('sanctum')->user();

        $user = User::find($authenticatedUser->id);
        $now = date('Y-m-d H:i:s');
        if ($user->code == $request->code && $user->code_expired_at > $now) {
            $user->email_verified_at = now();
            $user->save();

            $user->token = $token;
            return response()->json([
                // 'data' => $user,
                "token" => $token,
                'message' => 'Your account has been verified.'
            ], 200);
        } else {
            return response()->json([
                'error' => 'Invalid code or the code is expired.'
            ], 406);
        }
    }

    public function resetPassword(Request $request)
    {
        $validateData = Validator::make($request->all(), [
            'password' => 'required|string|min:8',

        ]);

        if ($validateData->fails()) {
            $data["error"] = $validateData->errors();
            $data['status'] = '400';
            return response()->json($data);
        }
        $authenticatedUser = Auth::guard('sanctum')->user();

        $user = User::find($authenticatedUser->id);
        $user->update([
            'password' => Hash::make($request->password)

        ]);
        $user = $user->refresh();

        return response()->json(['message' => 'Password reset successfully']);
    }
}
