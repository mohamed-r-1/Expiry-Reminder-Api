<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\ItemsResource;
use App\Models\Category;
use App\Models\Items;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ItemsController extends Controller
{
    //show all data
    public function index()
    {
        $items = DB::table("items")->Join('categories', 'categories.id', '=', 'items.category_id')
            ->select(['items.*', 'categories.type'])->get();
            foreach ($items as $item) {
            $item->item_image = url('items/image/' . $item->item_image);
        }
        return response()->json(["data" => $items], 200);
    }

    public function create()
    {
        $category = Category::select('id', 'title')->get();
        return response()->json(["data" => $category], 200);
    }

    //show one Record Of Data
    public function show($id)
    {

        $item = Items::find($id);



        if ($item) {
            $item->item_image = url('items/image/' . $item->item_image);
            return response()->json(["data" =>  $item], 200);

        } else {
            $data["msg"] = "Not Found This Item";
            $data["status"] = 404;
            $data['data'] = null;
            return response()->json($data, 404);
        }
    }

    // create new item

    public function store(Request $request)
    {


        $validateData = Validator::make($request->all(), [
            // 'id' => 'required|max:11|unique:items',
            'title' => 'required|min:5|max:255',
            'description' => 'nullable|min:10|max:255',
            'item_image' => 'required|image|mimes:png,jpeg,jpg,gif|max:2048',
            "pro_date" => 'required|date',
            "exp_date" => 'required|date',
            "start_reminder" => 'required',
            "code" => 'required|integer|digits:5',
            "type" => 'required|integer|max:255|exists:categories,id',
            "quantity" => 'required|integer'

        ]);

        if ($validateData->fails()) {
            // $data["msg"] = "Error In The Inputs";
            $data["errors"] = $validateData->errors();
            $data["status"] = 403;
            return response()->json($data,  403);
        }
        $imageName = "";
        if ($request->hasFile('item_image')) {
            $image = $request->item_image;
            $imageName = time() . "_" . rand(0, 1000) . "." . $image->extension();   //324234_954.png
            $image->move(public_path('../public_html/items/image'), $imageName);
        }

        $final = Items::create([
            // "id" => $request->id,
            "title" => $request->title,
            "description" => $request->description,
            "item_image" => $imageName,
            "pro_date" => $request->pro_date,
            "exp_date" => $request->exp_date,
            "start_reminder" => $request->start_reminder,
            "code" => $request->code,
            "category_id" => $request->type,
            "quantity" => $request->quantity,

        ]);
        $final->item_image = url('items/image/' . $final->item_image);
        $data['data'] = $final;
        // $data['msg'] = "Item Added Successfully";
        $data['status'] = 200;
        return response()->json($data, 200);
    }


    //delete One Record Form DB

    public function delete($id)
    {
        $item = Items::find($id);
        if ($item) {
            if ($item->quantity > 1) {
                $item->quantity = $item->quantity - 1;
                $item->save();
                $data['msg'] = 'Record has been deleted one';
                $data['status'] = '200';
                $data['data'] = null;
                return response()->json($data);
            } else {
                if (File::exists(public_path('../public_html/items/image/' . $item->item_image))) {
                    File::delete(public_path('../public_html/items/image/' . $item->item_image));
                } else {
                    dd('File does not exist.');
                }
                $item->delete();
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



    //update one Record from db

    public function update(Request $request, $old_id)
    {


        $validateData = Validator::make($request->all(), [
            //   'id' => [
            //         'required',
            //         Rule::unique('items')->ignore($old_id),
            //     ],
            'title' => 'required|min:5|max:255',
            'description' => 'nullable|min:10|max:255',
            'item_image' => 'required|image|mimes:png,jpeg,jpg,gif|max:2048',
            "pro_date" => 'required|date',
            "exp_date" => 'required|date',
            "start_reminder" => 'required',
            "code" => 'required|integer|digits:5',
            "type" => 'required|integer|max:255|exists:categories,id',
            "quantity" => 'required|integer'


        ]);

        if ($validateData->fails()) {
            $data['errors'] = $validateData->errors();
            $data['status'] = '403';
            return response()->json($data,  403);
        }

        $update = Items::find($old_id);
        if ($update) {

            // $imageName = "";

            if ($request->hasFile('item_image')) {
                $image = $request->item_image;
                $imageName = time() . "_" . rand(0, 1000) . "." . $image->extension();   //324234_954.png
                $image->move(public_path('../public_html/items/image'), $imageName);
            } else {
                $imageName = $update->item_image;
            }
            $update->update([
                // "id" => $request->id,
                "title" => $request->title,
                "description" => $request->description,
                "item_image" => $imageName,
                "pro_date" => $request->pro_date,
                "exp_date" => $request->exp_date,
                "start_reminder" => $request->start_reminder,
                "code" => $request->code,
                "category_id" => $request->type,
                "quantity" => $request->quantity,


            ]);
            $update->item_image = url('items/image/' . $update->item_image);
            $data['data'] = $update;
            // $data["msg"] = "Item Updated Successfully";
            $data["status"] = "200";
            return response()->json($data, 200);
        } else {
            $data['msg'] = 'Item not found';
            $data['status'] = 404;
            $data['data'] = null;
            return response()->json($data, 404);
        }
    }


    public function expire()
    {
        $today = date('Y-m-d');

        $items = DB::table("categories")
            ->join('items', 'categories.id', '=', 'items.category_id')
            ->where('items.exp_date', '<=', $today)
            ->get(['items.*', 'categories.type']);
            foreach ($items as $item) {
            $item->item_image = url('items/image/' . $item->item_image);
        }
        return response()->json(["data" => $items], 200);
    }


    public function soon_expire()
    {
        $currentDate = date('Y-m-d H:i:s');
        $thirtyDaysAfterCurrent = date('Y-m-d H:i:s', strtotime('+1 months'));

        $items = DB::table("categories")
        ->join('items', 'categories.id', '=', 'items.category_id')
        ->whereBetween('items.exp_date', [$currentDate, $thirtyDaysAfterCurrent])
        ->select(['items.*', 'categories.type'])
        ->get();

        foreach ($items as $item) {
            $item->item_image = url('items/image/' . $item->item_image);
        }

        return response()->json(["data" => $items], 200);
    }

    public function sumQuantitiesByTitle()
    {
        $items = Items::groupBy('title')
            ->selectRaw('title, SUM(quantity) as total_quantity')
            ->get();

        $data['data'] = $items;
        $data['status'] = 200;
        return response()->json($data, 200);
    }

    ##############################################     Search all item     #############################

    public function search(Request $request)
    {
        // Get the search value from the request
        $search = $request->input('search');

        // Return an error if there is no search term in the request
        if (is_null($search)) {
            return response()->json([
                "message" => "No Search Term Provided!"
            ], 403);
        }

        // Construct a query to search for the provided search term in the items' title or code
        $query = DB::table('items')
            ->join('categories', 'categories.id', '=', 'items.category_id')
            ->select(['items.*', 'categories.type'])
            ->where('items.title', 'like', '%' . $search . '%')
            ->orWhere('items.code', 'like', '%' . $search . '%');

        // Run the query and get the results. If there are no results, return an error
        $results = $query->get();
        foreach ($results as $item) {
            $item->item_image = url('items/image/' . $item->item_image);
        }
        if ($results->isEmpty()) {
            return response()->json([
                "message" => "No Results Found!"
            ], 404);
        }

        // Otherwise, return the results with a successful status code
        $data['data'] = $results;
        $data['status'] = 200;
        return response()->json($data, 200);
    }

    ###############################################     serach expire item     ####################################

    public function searchExpire(Request $request)
    {
        // Get the search value from the request
        $search = $request->input('search');

        // Return an error if there is no search term in the request
        if (is_null($search)) {
            return response()->json([
                "message" => "No Search Term Provided!"
            ], 403);
        }

        // Construct a query to search for the provided search term in the items' title, code, and check for expired items
        $query = DB::table('items')
            ->join('categories', 'categories.id', '=', 'items.category_id')
            ->select(['items.*', 'categories.type'])
            ->Where('items.exp_date', '<=', date('Y-m-d'))
            ->where(function ($query) use ($search) {
                $query->where('items.title', 'like', '%' . $search . '%')
                    ->orWhere('items.code', 'like', '%' . $search . '%');
            });

        // Run the query and get the results. If there are no results, return an error
        $results = $query->get();
        foreach ($results as $item) {
            $item->item_image = url('items/image/' . $item->item_image);
        }
        if ($results->isEmpty()) {
            return response()->json([
                "message" => "No Results Found!"
            ], 404);
        } else {
            // Return the items as a JSON response
            return response()->json(["data" => $results], 200);
        }
    }

    ###############################################   search soonexpire  item     ####################################

    public function searchSoonExpire(Request $request)
    {
        // Get the search value from the request
        $search = $request->input('search');

        // Return an error if there is no search term in the request
        if (is_null($search)) {
            return response()->json([
                "error" => true,
                "code" => 403,
                "message" => "No Search Term Provided!"
            ], 403);
        }

        // Construct a query to search for the provided search term in the items' title, code, and check for items expiring within 30 days
        $currentDate = date('Y-m-d');
        $thirtyDaysAfterCurrent = date('Y-m-d', strtotime('+1 months'));

        $query = DB::table('items')
            ->join('categories', 'categories.id', '=', 'items.category_id')
            ->select(['items.*', 'categories.type'])
            ->whereBetween('items.exp_date', [$currentDate, $thirtyDaysAfterCurrent])
            ->where(function ($query) use ($search) {
                $query->where('items.title', 'like', '%' . $search . '%')
                    ->orWhere('items.code', 'like', '%' . $search . '%');
            });

        // Run the query and get the results. If there are no results, return an error
        $results = $query->get();
        foreach ($results as $item) {
            $item->item_image = url('items/image/' . $item->item_image);
        }
        if ($results->isEmpty()) {
            return response()->json([
                "message" => "No Results Found!"
            ], 404);
        } else {
            // Return the items as a JSON response
            return response()->json(["data" => $results], 200);
        }
    }

    ################################# search date  ###################################

    public function searchdate(Request $request)
    {
        // Get the search value from the request
        $search = $request->input('search');

        // Return an error if there is no search term in the request
        if (is_null($search)) {
            return response()->json([
                "message" => "No Search Term Provided!"
            ], 403);
        }

        // Construct a query to search for the provided search term in the items' title or code
        $query = DB::table('items')
            ->join('categories', 'categories.id', '=', 'items.category_id')
            ->select(['items.*', 'categories.type'])
            ->where('items.pro_date', 'like', '%' . $search . '%');

        // Run the query and get the results. If there are no results, return an error
        $results = $query->get();
        foreach ($results as $item) {
            $item->item_image = url('items/image/' . $item->item_image);
        }
        if ($results->isEmpty()) {
            return response()->json([
                "message" => "No Results Found!"
            ], 404);
        }

        // Otherwise, return the results with a successful status code
        $data['data'] = $results;
        $data['status'] = 200;
        return response()->json($data, 200);
    }


}
