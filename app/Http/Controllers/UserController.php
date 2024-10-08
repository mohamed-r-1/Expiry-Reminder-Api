<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class UserController extends Controller
{
    public function Register(Request $request)
    {
        $validateData = Validator::make($request->all(), [
            'image' => 'required|image|mimes:png,jpeg,jpg,gif|max:2048',
            'name' => 'required|string|min:5|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'confirm_password' => 'required|string|min:8|same:password',

        ]);

        if ($validateData->fails()) {
            $response['status'] = '401';
            $response['error'] = $validateData->errors();
            return response()->json($response, 401);
        }
        // $image = $request->file('image')->store('user_images', 'public');
        $imageName = "";
        if ($request->hasFile('image')) {
            $image = $request->image;
            $imageName = time() . "_" . rand(0, 1000) . "." . $image->extension();   //324234_954.png
            $image->move(public_path('../public_html/users/image'), $imageName);
        }
        $status = 'active';
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'image' => $imageName,
            'status' => $status,
        ]);
        $user->email_verified_at = now();
        $user->save();
        $res['token'] = 'Bearer ' .  $user->createToken($request->email)->plainTextToken;
        $res['status'] = '200';
        return response()->json($res, 200);
    }

    ###########################  Login USER  #################################

    public function Login(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);
        if ($validate->fails()) {
            $response['status'] = '401';
            $response['error'] = $validate->errors();
            return response()->json($response, 401);
        }

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();
            $user->email_verified_at = now();
            $response['token'] = 'Bearer ' . $user->createToken($request->email)->plainTextToken;
            $response['status'] = '200';
            return response()->json($response, 200);
        } else {
            $response['error'] = 'invalid user information';
            $response['status'] = '401';
            return response()->json($response, 401);
        }
    }


    public function Logout()
    {
        auth('sanctum')->user()->tokens()->delete();
        $response['status'] = '200';
        $response['message'] = "Logged out successfully.";
        return response()->json($response,  200);
    }
    public function Profile()
    {
        $user = Auth::guard('sanctum')->user();

        if (is_null($user) || is_null($user->email_verified_at)) {
            return response([
                "error" => "Unauthorized"
            ], 401);
        }

        $user->image = url('users/image/' . $user->image);

        return response()->json([
            'success' => true,
            'user' => $user
        ], 200);

    }

    public function  UpdateProfile(Request $request)
    {
        $user = Auth::guard('sanctum')->user();

        if(!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'image' => 'sometimes|image|mimes:png,jpeg,jpg,gif|max:2048',
            'name' => 'sometimes|string|min:5|max:255',
            'email' => 'sometimes|email',
            'password' => 'sometimes|string|min:8',
            'confirm_password' => 'sometimes|string|min:8|same:password',
        ]);

        if ($validator->fails()) {
            $data["error"] = $validator->errors();
            $data['status'] = '400';
            return response()->json($data);
        }

        if ($request->hasFile('image')) {
            $image = $request->image;
            $imageName = time() . "_" . rand(0, 1000) . "." . $image->extension();   //324234_954.png
            $image->move(public_path('../public_html/users/image'), $imageName);
        } else {
            $imageName = $user->image;
        }

        $user->update([
            "image" => $imageName,
            "name" => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);

        $user = $user->refresh();

        $user->image = url('users/image/' . $user->image);

        $success['user'] = $user;
        $success['status'] = "200";

        return response()->json($success, 200);

    }
}

