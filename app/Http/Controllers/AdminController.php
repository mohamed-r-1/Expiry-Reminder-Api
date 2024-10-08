<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Category;
use App\Models\Items;
use App\Models\User;
use App\Traits\GeneralTrait;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{

    use GeneralTrait;

    public function login(Request $request)
    {

        try {
            $rules = [
                "email" => "required|email",
                "password" => "required"

            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                $code = $this->returnCodeAccordingToInput($validator);
                return $this->returnValidationError($code, $validator);
            }

            //login

            $credentials = $request->only(['email', 'password']);

            $token = Auth::guard('admin-api')->attempt($credentials);

            if (!$token)
                return response()->json([
                'status' => false,
                'errNum' => '401',
                'msg' => 'Invalid Admin Information'
            ], 401);

            $admin = Auth::guard('admin-api')->user();
            $admin->api_token = $token;
            //return token
            return $this->returnData('admin', $admin);
        } catch (\Exception $ex) {
            return response()->json([
            'status' => false,
            'errNum' => $ex->getCode() ?: '400',
            'msg' => $ex->getMessage()
        ], $ex->getCode() ?: 400);
        }
    }

    public function logout(Request $request)
    {

        $token = $request->header('auth_token');

        if ($token) {

            try {

                JWTAuth::setToken($token)->invalidate(); //logout

            } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {

                return  $this->returnError('', 'some thing went wrongs');
            }

            return $this->returnSuccessMessage('Logged out successfully');
        } else {

            $this->returnError('', 'some thing went wrongs');
        }
    }

    public function Profile()
    {
        $admin = Auth::user();

        if(!$admin) {
            return response()->json([
                'success' => false,
                'message' => 'Admin not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'admin' => $admin
        ], 200);
    }

    public function deleteUser($id)
    {
        $user = User::find($id);
        if ($user) {
            if ($user->quantity > 1) {
                $user->quantity = $user->quantity - 1;
                $user->save();
                $data['msg'] = 'Record has been deleted one';
                $data['status'] = '200';
                $data['data'] = null;
                return response()->json($data);
            } else {
                if (File::exists(public_path('../public_html/users/image/' . $user->user_image))) {
                    File::delete(public_path('../public_html/users/image/' . $user->user_image));
                } else {
                    dd('File does not exist.');
                }
                $user->delete();
                $data['msg'] = 'Record has been deleted';
                $data['status'] = '200';
                $data['data'] = null;
                return response()->json($data);
            }
        } else {
            $data['msg'] = 'No such Id';
            $data['status'] = '404';
            $data['data'] = null;
            return response()->json($data, 404);
        }
    }

    public function countUsers()
    {
        $userCount = User::count();
        return response()->json(['user_count' => $userCount]);
    }

    public function countItems()
    {
        $itemCount = Items::count();
        return response()->json(['item_count' => $itemCount]);
    }

    public function countCategories()
    {
        $categoryCount = Category::count();
        return response()->json(['category_count' => $categoryCount]);
    }

    public function getAllUsers()
    {
        $users = User::all();
        return response()->json($users);
    }

    public function latestUsers()
    {
        $users = User::orderBy('created_at', 'desc')->take(5)->get();

        return response()->json($users);
    }

    public function latestItems()
    {
        $items = Items::orderBy('created_at', 'desc')->take(5)->get();

        return response()->json($items);
    }

    // public function index()
    // {
    //     $categories = Category::withCount('items')->get();
    //     return response()->json($categories);

    // }

    public function index()
    {
        // Get categories with the name and count of items
        $categories = Category::select('id', 'title')->withCount('items')->get();

        // Transform the data into the desired format
        $formattedCategories = $categories->mapWithKeys(function($category) {
            return [
                $category->title => [
                    'count' => $category->items_count
                ]
            ];
        });

        // Return the transformed data as JSON
        return response()->json($formattedCategories);
    }

}
