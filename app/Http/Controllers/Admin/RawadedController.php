<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\RawadedResource;
use App\Models\Rawaded;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class RawadedController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('permission:rawadeds.read|rawadeds.write|rawadeds.delete')->only('index', 'show', 'export');
        $this->middleware('permission:rawadeds.write')->only('store', 'update');
        $this->middleware('permission:rawadeds.delete')->only('destroy');
    }

    /**
     * @OA\Get(
     * path="/admin/rawadeds",
     * description="Get all rawadeds",
     * operationId="get_all_rawadeds",
     * tags={"Admin - Rawadeds"},
     *   security={{"bearer_token": {} }},
     * @OA\Parameter(
     *     in="query",
     *     name="with_paginate",
     *     required=false,
     *     @OA\Schema(type="integer",enum={0, 1})
     *   ),
     * @OA\Parameter(
     *     in="query",
     *     name="status",
     *     required=false,
     *     @OA\Schema(type="integer",enum={0, 1})
     *   ),
     * @OA\Parameter(
     *     in="query",
     *     name="featured",
     *     required=false,
     *     @OA\Schema(type="integer",enum={0, 1})
     *   ),
     * @OA\Parameter(
     *    in="query",
     *    name="per_page",
     *    required=false,
     *    @OA\Schema(type="integer"),
     * ),
     * @OA\Parameter(
     *    in="query",
     *    name="q",
     *    required=false,
     *    @OA\Schema(type="string"),
     * ),
     *   @OA\Response(
     *     response=200,
     *     description="Success",
     *  )
     *  )
    */
    public function index(Request $request)
    {
        $request->validate([
            'with_paginate'      => ['integer', 'in:0,1'],
            'per_page'           => ['integer', 'min:1'],
            'status'             => ['integer', 'in:1,0'],
            'featured'           => ['integer', 'in:1,0'],
            'q'                  => ['string']
        ]);

        $q = Rawaded::query()->latest();

        if($request->q)
        {
            $q->where(function($query) use ($request) {
                if (is_numeric($request->q))
                    $query->where('id', $request->q);
        
                $query->orWhere('name', 'LIKE', '%'.$request->q.'%');
            });
        }

        if($request->status === '0'){
            $q->where('status', false);
        }else if ($request->status === '1') {
            $q->where('status', true);
        }

        if($request->featured === '0'){
            $q->where('featured', false);
        }else if ($request->featured === '1') {
            $q->where('featured', true);
        }

        if($request->with_paginate === '0')
            $rawadeds = $q->get();
        else
            $rawadeds = $q->paginate($request->per_page ?? 10);

        return RawadedResource::collection($rawadeds);
    }

    /**
     * @OA\Post(
     * path="/admin/rawadeds",
     * description="Add new rawaded.",
     * tags={"Admin - Rawadeds"},
     * security={{"bearer_token": {} }},
     *   @OA\RequestBody(
     *       required=true,
     *       @OA\MediaType(
     *           mediaType="multipart/form-data",
     *           @OA\Schema(
     *              required={"name", "image", "featured"},
     *              @OA\Property(property="name", type="string"),
     *              @OA\Property(property="featured", type="integer", enum={"0","1"}),
     *              @OA\Property(property="image", type="file"),
     *          )
     *       )
     *   ),
     * @OA\Response(
     *    response=200,
     *    description="successful operation",
     *     ),
     * )
     * )
    */
    public function store(Request $request)
    {
        $request->validate([
            'name'           => ['required', 'string'],
            'featured'       => ['required', 'boolean'],
            'image'          => ['required', 'image'],
        ]);

        if($request->featured){
            $featured_rawadeds_count = Rawaded::where('featured', true)->count();
            if($featured_rawadeds_count == 6)
                throw new BadRequestException(__('error_messages.The max count of featured rawadeds is 6 and you got it!'));        
        }

        $image = upload_file($request->image, 'rawadeds', 'rawaded');

        $rawaded = Rawaded::create([
            'name'          => $request->name,
            'featured'      => $request->featured,
            'status'        => 1,
            'image'         => $image,
        ]);

        return response()->json(new RawadedResource($rawaded), 200);
    }

    /**
     * @OA\Get(
     * path="/admin/rawadeds/{id}",
     * description="Get rawaded information.",
     *     @OA\Parameter(
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(type="string"),
     *      ),
     * operationId="show_rawaded",
     * tags={"Admin - Rawadeds"},
     * security={{"bearer_token": {} }},
     * @OA\Response(
     *    response=200,
     *    description="successful operation"
     * ),
     * )
     *)
    */
    public function show(Rawaded $rawaded)
    {
        return response()->json(new RawadedResource($rawaded), 200);
    }

    /**
     * @OA\Post(
     * path="/admin/rawadeds/{id}",
     * description="Edit rawaded.",
     *   @OA\Parameter(
     *     in="path",
     *     name="id",
     *     required=true,
     *     @OA\Schema(type="string"),
     *   ),
     *  tags={"Admin - Rawadeds"},
     *  security={{"bearer_token": {} }},
     *   @OA\RequestBody(
     *       required=true,
     *       @OA\MediaType(
     *           mediaType="multipart/form-data",
     *           @OA\Schema(
     *              @OA\Property(property="name", type="string"),
     *              @OA\Property(property="image", type="file"),
     *              @OA\Property(property="_method", type="string", format="string", example="PUT"),
     *           )
     *       )
     *   ),
     * @OA\Response(
     *         response="200",
     *         description="successful operation",
     *     ),
     * )
     * )
    */
    public function update(Request $request, Rawaded $rawaded)
    {
        $request->validate([
            'name'           => ['required', 'string'],
            'image'          => ['required'],
        ]);

        $image = $rawaded->image;
        if($request->image == $rawaded->image){
            $image = $rawaded->image;
        }else{
            if(!is_file($request->image))
                throw ValidationException::withMessages(['image' => __('Image should be a file')]);
            $image = upload_file($request->image, 'rawadeds', 'rawaded');
        }

        $rawaded->update([
            'name'         => $request->name,
            'image'        => $image,
        ]);

        return response()->json(new RawadedResource($rawaded), 200);
    }

    /**
     * @OA\Delete(
     * path="/admin/rawadeds/{id}",
     * description="Delete entered rawaded.",
     *     @OA\Parameter(
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         @OA\Examples(example="int", value="1", summary="An int value."),
     *      ),
     * operationId="delete_rawaded",
     * tags={"Admin - Rawadeds"},
     * security={{"bearer_token":{}}},
     * @OA\Response(
     *    response=200,
     *    description="successful operation"
     * ),
     * )
     *)
    */
    public function destroy(Rawaded $rawaded)
    {
        //TODO: Deleting a radod will delete all audios

        $rawaded->delete();
        return response()->json(null, 204);
    }

    /**
     * @OA\Post(
     * path="/admin/rawadeds/{id}/featured",
     * description="featured the rawaded.",
     *   @OA\Parameter(
     *     in="path",
     *     name="id",
     *     required=true,
     *     @OA\Schema(type="string"),
     *   ),
     * tags={"Admin - Rawadeds"},
     * security={{"bearer_token": {} }},
     * @OA\Response(
     *    response=200,
     *    description="successful operation",
     *     ),
     * )
     * )
    */
    public function rawaded_feature_toggle(Rawaded $rawaded)
    {
        if(!$rawaded->featured){
            $featured_rawadeds_count = Rawaded::where('featured', true)->count();
            if($featured_rawadeds_count == 6)
                throw new BadRequestException(__('error_messages.This max count of featured rawadeds is 6 and you got it!'));
        }

        $rawaded->update(['featured' => !$rawaded->featured]);
        return response()->json(new RawadedResource($rawaded), 200);
    }

    /**
     * @OA\Post(
     * path="/admin/rawadeds/{id}/activate",
     * description="activate the rawaded.",
     *   @OA\Parameter(
     *     in="path",
     *     name="id",
     *     required=true,
     *     @OA\Schema(type="string"),
     *   ),
     * tags={"Admin - Rawadeds"},
     * security={{"bearer_token": {} }},
     * @OA\Response(
     *    response=200,
     *    description="successful operation",
     *     ),
     * )
     * )
    */
    public function rawaded_status_toggle(Rawaded $rawaded)
    {
        $rawaded->update(['status' => !$rawaded->status]);
        return response()->json(new RawadedResource($rawaded), 200);
    }

    // public function export(Request $request, $type)
    // {
    //     $q = Rawaded::query()->latest();

    //     if($request->q)
    //     {
    //         $q->where(function($query) use ($request) {
    //             if (is_numeric($request->q))
    //                 $query->where('id', $request->q);
        
    //             $query->orWhere('name', 'LIKE', '%'.$request->q.'%');
    //         });
    //     }

    //     if($request->status === '0'){
    //         $q->where('status', false);
    //     }else if ($request->status === '1') {
    //         $q->where('status', true);
    //     }

    //     if($request->featured === '0'){
    //         $q->where('featured', false);
    //     }else if ($request->featured === '1') {
    //         $q->where('featured', true);
    //     }

    //     $rawadeds = $q->get();

    //     if ($type == 'xlsx') {
    //         return Excel::download(new RawadedExport($rawadeds), 'rawaded.xlsx', \Maatwebsite\Excel\Excel::XLSX);
    //     } else if ($type == 'csv') {
    //         return Excel::download(new RawadedExport($rawadeds), 'rawaded.csv', \Maatwebsite\Excel\Excel::CSV);
    //     } else
    //         return Excel::download(new RawadedExport($rawadeds), 'rawaded.pdf', \Maatwebsite\Excel\Excel::DOMPDF);
    // }
}
