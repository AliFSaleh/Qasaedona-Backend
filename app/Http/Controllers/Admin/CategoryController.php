<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\PoemAttributeResource;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('permission:categories.read|categories.write|categories.delete')->only('index', 'show');
        $this->middleware('permission:categories.write')->only('store', 'update');
        $this->middleware('permission:categories.delete')->only('destroy');
    }

    /**
     * @OA\Get(
     * path="/admin/categories",
     * description="Get all categories",
     * operationId="get_all_categories",
     * tags={"Admin - Categories"},
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

        $q = Category::query()->latest();

        if($request->q)
        {
            $q->where(function($query) use ($request) {
                if (is_numeric($request->q))
                    $query->where('id', $request->q);
        
                $query->orWhere('name', 'LIKE', '%'.$request->q.'%');
            });
        }

        if($request->with_paginate === '0')
            $categories = $q->get();
        else
            $categories = $q->paginate($request->per_page ?? 10);

        return PoemAttributeResource::collection($categories);
    }

    /**
     * @OA\Post(
     * path="/admin/categories",
     * description="Add new categories.",
     * tags={"Admin - Categories"},
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

        $category = Category::create([
            'name'          => $request->name,
        ]);

        return response()->json(new PoemAttributeResource($category), 200);
    }

    /**
     * @OA\Get(
     * path="/admin/categories/{id}",
     * description="Get category information.",
     *     @OA\Parameter(
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(type="string"),
     *      ),
     * operationId="show_categories",
     * tags={"Admin - Categories"},
     * security={{"bearer_token": {} }},
     * @OA\Response(
     *    response=200,
     *    description="successful operation"
     * ),
     * )
     *)
    */
    public function show(Category $category)
    {
        return response()->json(new PoemAttributeResource($category), 200);
    }

    /**
     * @OA\Post(
     * path="/admin/categories/{id}",
     * description="Edit categories.",
     *   @OA\Parameter(
     *     in="path",
     *     name="id",
     *     required=true,
     *     @OA\Schema(type="string"),
     *   ),
     *  tags={"Admin - Categories"},
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
    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name'         => ['required', 'string',]
        ]);

        $category->update([
            'name'         => $request->name,
        ]);

        return response()->json(new PoemAttributeResource($category), 200);
    }

    /**
     * @OA\Delete(
     * path="/admin/categories/{id}",
     * description="Delete entered category.",
     *     @OA\Parameter(
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         @OA\Examples(example="int", value="1", summary="An int value."),
     *      ),
     * operationId="delete_category",
     * tags={"Admin - Categories"},
     * security={{"bearer_token":{}}},
     * @OA\Response(
     *    response=200,
     *    description="successful operation"
     * ),
     * )
     *)
    */
    public function destroy(Category $category)
    {
        //TODO: Deleting a radod will delete all related poems

        $category->delete();
        return response()->json(null, 204);
    }
}
