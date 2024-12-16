<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\PoemTypeResource;
use App\Models\PoemType;
use Illuminate\Http\Request;

class PoemTypeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('permission:poem_types.read|poem_types.write|poem_types.delete')->only('index', 'show', 'export');
        $this->middleware('permission:poem_types.write')->only('store', 'update');
        $this->middleware('permission:poem_types.delete')->only('destroy');
    }

    /**
     * @OA\Get(
     * path="/admin/poem_types",
     * description="Get all poem_types",
     * operationId="get_all_poem_types",
     * tags={"Admin - Poem Types"},
     *   security={{"bearer_token": {} }},
     * @OA\Parameter(
     *     in="query",
     *     name="with_paginate",
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
            'q'                  => ['string']
        ]);

        $q = PoemType::query()->latest();

        if($request->q)
        {
            $q->where(function($query) use ($request) {
                if (is_numeric($request->q))
                    $query->where('id', $request->q);
        
                $query->orWhere('name', 'LIKE', '%'.$request->q.'%');
            });
        }

        if($request->with_paginate === '0')
            $poems = $q->get();
        else
            $poems = $q->paginate($request->per_page ?? 10);

        return PoemTypeResource::collection($poems);
    }

    /**
     * @OA\Post(
     * path="/admin/poem_types",
     * description="Add new poem_types.",
     * tags={"Admin - Poem Types"},
     * security={{"bearer_token": {} }},
     *   @OA\RequestBody(
     *       required=true,
     *       @OA\MediaType(
     *           mediaType="multipart/form-data",
     *           @OA\Schema(
     *              required={"name"},
     *              @OA\Property(property="name", type="string"),
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
        ]);

        $poem_type = PoemType::create([
            'name'          => $request->name,
        ]);

        return response()->json(new PoemTypeResource($poem_type), 200);
    }

    /**
     * @OA\Get(
     * path="/admin/poem_types/{id}",
     * description="Get poem_type information.",
     *     @OA\Parameter(
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(type="string"),
     *      ),
     * operationId="show_poem_type",
     * tags={"Admin - Poem Types"},
     * security={{"bearer_token": {} }},
     * @OA\Response(
     *    response=200,
     *    description="successful operation"
     * ),
     * )
     *)
    */
    public function show(PoemType $poem_type)
    {
        return response()->json(new PoemTypeResource($poem_type), 200);
    }

    /**
     * @OA\Post(
     * path="/admin/poem_types/{id}",
     * description="Edit poem_type.",
     *   @OA\Parameter(
     *     in="path",
     *     name="id",
     *     required=true,
     *     @OA\Schema(type="string"),
     *   ),
     *  tags={"Admin - Poem Types"},
     *  security={{"bearer_token": {} }},
     *   @OA\RequestBody(
     *       required=true,
     *       @OA\MediaType(
     *           mediaType="multipart/form-data",
     *           @OA\Schema(
     *              @OA\Property(property="name", type="string"),
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
    public function update(Request $request, PoemType $poem_type)
    {
        $request->validate([
            'name'         => ['required', 'string',]
        ]);

        $poem_type->update([
            'name'         => $request->name,
        ]);

        return response()->json(new PoemTypeResource($poem_type), 200);
    }

    /**
     * @OA\Delete(
     * path="/admin/poem_types/{id}",
     * description="Delete entered poem_type.",
     *     @OA\Parameter(
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         @OA\Examples(example="int", value="1", summary="An int value."),
     *      ),
     * operationId="delete_poem_type",
     * tags={"Admin - Poem Types"},
     * security={{"bearer_token":{}}},
     * @OA\Response(
     *    response=200,
     *    description="successful operation"
     * ),
     * )
     *)
    */
    public function destroy(PoemType $poem_type)
    {
        //TODO: Deleting a radod will delete all related poems

        $poem_type->delete();
        return response()->json(null, 204);
    }
}
