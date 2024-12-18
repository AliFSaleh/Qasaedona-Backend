<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\PoetryCollectionResource;
use App\Models\PoetryCollection;
use Illuminate\Http\Request;

class PoetryCollectionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('permission:poetry_collections.read|poetry_collections.write|poetry_collections.delete')->only('index', 'show', 'export');
        $this->middleware('permission:poetry_collections.write')->only('store', 'update', 'poetry_collections_status_toggle');
        $this->middleware('permission:poetry_collections.delete')->only('destroy');
    }

    /**
     * @OA\Get(
     * path="/admin/poetry_collections",
     * description="Get all poetry_collections",
     * operationId="get_all_poetry_collections",
     * tags={"Admin - Poetry Collections"},
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
     *    in="query",
     *    name="poet_id",
     *    required=false,
     *    @OA\Schema(type="integer"),
     * ),
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
            'with_paginate'     => ['integer', 'in:0,1'],
            'poet_id'           => ['integer', 'exists:users,id'],
            'per_page'          => ['integer', 'min:1'],
            'status'            => ['integer', 'in:1,0'],
            'q'                 => ['string']
        ]);

        $q = PoetryCollection::query()->latest();

        if($request->q){
            $q->where(function($query) use ($request) {
                if (is_numeric($request->q))
                    $query->where('id', $request->q);
        
                $query->orWhere('title', 'LIKE', '%'.$request->q.'%')
                        ->orWhere('description', 'LIKE', '%'.$request->q.'%');
            });
        }

        if($request->status === '0'){
            $q->where('status', false);
        }else if ($request->status === '1') {
            $q->where('status', true);
        }

        if($request->poet_id)
            $q->where('poet_id', $request->poet_id);

        if($request->with_paginate === '0')
            $poetry_collections = $q->get();
        else
            $poetry_collections = $q->paginate($request->per_page ?? 10);

        return PoetryCollectionResource::collection($poetry_collections);
    }

    /**
     * @OA\Post(
     * path="/admin/poetry_collections",
     * description="Add new poetry_collections.",
     * tags={"Admin - Poetry Collections"},
     * security={{"bearer_token": {} }},
     *   @OA\RequestBody(
     *       required=true,
     *       @OA\MediaType(
     *           mediaType="multipart/form-data",
     *           @OA\Schema(
     *              required={"title","poet_id","description"},
     *              @OA\Property(property="poet_id", type="integer"),
     *              @OA\Property(property="title", type="string"),
     *              @OA\Property(property="description", type="string"),
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
            'poet_id'               => ['required', 'integer', 'exists:users,id'],
            'title'                 => ['required', 'string'],
            'description'           => ['required', 'string'],
        ]);

        $poetry_collection = PoetryCollection::create([
            'title'             => $request->title,
            'poet_id'           => $request->poet_id,
            'description'       => $request->description,
            'publish_status'    => 'approved',
            'status'            => 1,
        ]);

        return response()->json(new PoetryCollectionResource($poetry_collection), 200);
    }

    /**
     * @OA\Get(
     * path="/admin/poetry_collections/{id}",
     * description="Get poetry_collection information.",
     *     @OA\Parameter(
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(type="string"),
     *      ),
     * operationId="show_poetry_collection",
     * tags={"Admin - Poetry Collections"},
     * security={{"bearer_token": {} }},
     * @OA\Response(
     *    response=200,
     *    description="successful operation"
     * ),
     * )
     *)
    */
    public function show(PoetryCollection $poetry_collection)
    {
        $poetry_collection->load(['poet']);
        return response()->json(new PoetryCollectionResource($poetry_collection), 200);
    }

    /**
     * @OA\Post(
     * path="/admin/poetry_collections/{id}",
     * description="Edit poetry_collection.",
     *   @OA\Parameter(
     *     in="path",
     *     name="id",
     *     required=true,
     *     @OA\Schema(type="string"),
     *   ),
     *  tags={"Admin - Poetry Collections"},
     *  security={{"bearer_token": {} }},
     *   @OA\RequestBody(
     *       required=true,
     *       @OA\MediaType(
     *           mediaType="multipart/form-data",
     *           @OA\Schema(
     *              @OA\Property(property="title", type="string"),
     *              @OA\Property(property="poet_id", type="integer"),
     *              @OA\Property(property="description", type="string"),
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
    public function update(Request $request, PoetryCollection $poetry_collection)
    {
        $request->validate([
            'title'             => ['required', 'string'],
            'description'       => ['required', 'string'],
            'poet_id'           => ['required', 'integer', 'exists:users,id'],
        ]);

        $poetry_collection->update([
            'title'             => $request->title,
            'poet_id'           => $request->poet_id,
            'description'       => $request->description,
        ]);

        return response()->json(new PoetryCollectionResource($poetry_collection), 200);
    }

    /**
     * @OA\Delete(
     * path="/admin/poetry_collections/{id}",
     * description="Delete entered poetry_collection.",
     *     @OA\Parameter(
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         @OA\Examples(example="int", value="1", summary="An int value."),
     *      ),
     * operationId="delete_poetry_collection",
     * tags={"Admin - Poetry Collections"},
     * security={{"bearer_token":{}}},
     * @OA\Response(
     *    response=200,
     *    description="successful operation"
     * ),
     * )
     *)
    */
    public function destroy(PoetryCollection $poetry_collection)
    {
        // delete all related 

        $poetry_collection->delete();
        return response()->json(null, 204);
    }

    /**
     * @OA\Post(
     * path="/admin/poetry_collections/{id}/activate",
     * description="activate the PoetryCollection.",
     *   @OA\Parameter(
     *     in="path",
     *     name="id",
     *     required=true,
     *     @OA\Schema(type="string"),
     *   ),
     * tags={"Admin - Poetry Collections"},
     * security={{"bearer_token": {} }},
     * @OA\Response(
     *    response=200,
     *    description="successful operation",
     *     ),
     * )
     * )
    */
    public function poetry_collections_status_toggle(PoetryCollection $poetry_collection)
    {
        $poetry_collection->update(['status' => !$poetry_collection->status]);
        return response()->json(new PoetryCollectionResource($poetry_collection), 200);
    }

    // public function export(Request $request, $type)
    // {
    //     $q = PoetryCollection::query()->latest();

    //     if($request->q){
    //         $q->where(function($query) use ($request) {
    //             if (is_numeric($request->q))
    //                 $query->where('id', $request->q);
        
    //             $query->orWhere('title', 'LIKE', '%'.$request->q.'%')
    //                     ->orWhere('description', 'LIKE', '%'.$request->q.'%');
    //         });
    //     }

    //     if($request->status === '0'){
    //         $q->where('status', false);
    //     }else if ($request->status === '1') {
    //         $q->where('status', true);
    //     }

    //     if($request->poet_id)
    //         $q->where('poet_id', $request->poet_id);

    //     $poetry_collections = $q->get();

    //     if ($type == 'xlsx') {
    //         return Excel::download(new PoetryCollectionExport($poetry_collections), 'poetry_collections.xlsx', \Maatwebsite\Excel\Excel::XLSX);
    //     } else if ($type == 'csv') {
    //         return Excel::download(new PoetryCollectionExport($poetry_collections), 'poetry_collections.csv', \Maatwebsite\Excel\Excel::CSV);
    //     } else
    //         return Excel::download(new PoetryCollectionExport($poetry_collections), 'poetry_collections.pdf', \Maatwebsite\Excel\Excel::DOMPDF);
    // }
}