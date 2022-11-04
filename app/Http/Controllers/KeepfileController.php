<?php

namespace App\Http\Controllers;

use App\Http\Resources\KeepfileResource;
use App\Models\Keepfile;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;


class KeepfileController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            //Fetch data
            $keepfiles = Keepfile::latest()->get();

            //Return message and data
            return response()->json([
                'status' => true,
                'message' => 'Keep fetch successfully!',
                'data' => KeepfileResource::collection($keepfiles)
            ], 200);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {

            //check validator
            $validator = Validator::make($request->all(),[
                'name' => 'required|string|max:50',
                'image' => 'image|mimes:jpg,png,jpeg,gif,svg|max:2048|nullable',
                'desc' =>'string|nullable'
            ]);


            //if validate fails
            if($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 401);
            }

            if($request->image != ''){
                $imageName = Str::random() . '.' . $request->image->getClientOriginalExtension();
                Storage::disk('public')->putFileAs('keepfile/image', $request->image, $imageName);



                //Create data
                $keepfile = Keepfile::create([
                    'name' => $request->name,
                    'image' => $imageName,
                    'desc' => $request->desc
                ]);
            }else {
                $keepfile = Keepfile::create(
                    $request->post()
                );
            }

            //return data
            return response()->json([
                'status' => true,
                'message' => 'Keepfile has been created successfully!',
                'success' => $keepfile
            ], 200);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Keepfile  $keepfile
     * @return \Illuminate\Http\Response
     */
    public function show(Keepfile $keepfile)
    {
        //
        return response()->json([
            'status' => true,
            'data' => $keepfile
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Keepfile  $keepfile
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Keepfile $keepfile)
    {
        try {
            //check validator
            $validator = Validator::make($request->all(),[
                'name' => 'required|string|max:50',
                'image' => 'image|mimes:jpg,png,jpeg,gif,svg|max:2048|nullable',
                'desc' =>'string|nullable'
            ]);


            //if validate fails
            if($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 401);
            }


            $keepfile->fill($request->post())->update();

            if ($request->hasFile('image')) {
                // remove old image
                if ($request->image) {
                    $exists = Storage::disk('public')->exists("keepfile/image/{$keepfile->image}");
                    if ($exists) {
                        Storage::disk('public')->delete("keepfile/image/{$keepfile->image}");
                    }
                }

                $imageName = Str::random() . '.' . $request->image->getClientOriginalExtension();
                Storage::disk('public')->putFileAs('keepfile/image', $request->image, $imageName);

                $keepfile->image = $imageName;

                $keepfile->save();

                return response()->json([
                    'status' => true,
                    'message' => 'File updated successfully!',
                    'dataImage' => $keepfile
                ], 200);
            }else {


                return response()->json([
                    'status' => true,
                    'message' => 'File updated successfully!',
                    'data' => $keepfile
                ], 200);
            }
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Keepfile  $keepfile
     * @return \Illuminate\Http\Response
     */
    public function destroy(Keepfile $keepfile)
    {
        try {
            if ($keepfile->image) {
                $exists = Storage::disk('public')->exists("keepfile/image/{$keepfile->image}");
                if ($exists) {
                    Storage::disk('public')->delete("keepfile/image/{$keepfile->image}");
                }
            }

            $keepfile->delete();

            return response()->json([
                'status' => true,
                'message' => 'File deleted successfully!'
            ]);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
