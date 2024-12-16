<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\PoemAttributeResource;
use App\Models\Language;
use Illuminate\Http\Request;

class LanguageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('permission:languages.read|languages.write|languages.delete')->only('index', 'show');
        $this->middleware('permission:languages.write')->only('store', 'update');
        $this->middleware('permission:languages.delete')->only('destroy');
    }

    /**
     * @OA\Get(
     * path="/admin/languages",
     * description="Get all languages",
     * operationId="get_all_languages",
     * tags={"Admin - Languages"},
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

        $q = Language::query()->latest();

        if($request->q)
        {
            $q->where(function($query) use ($request) {
                if (is_numeric($request->q))
                    $query->where('id', $request->q);
        
                $query->orWhere('name', 'LIKE', '%'.$request->q.'%');
            });
        }

        if($request->with_paginate === '0')
            $languages = $q->get();
        else
            $languages = $q->paginate($request->per_page ?? 10);

        return PoemAttributeResource::collection($languages);
    }

    /**
     * @OA\Post(
     * path="/admin/languages",
     * description="Add new language.",
     * tags={"Admin - Languages"},
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

        $language = Language::create([
            'name'          => $request->name,
        ]);

        return response()->json(new PoemAttributeResource($language), 200);
    }

    /**
     * @OA\Get(
     * path="/admin/languages/{id}",
     * description="Get language information.",
     *     @OA\Parameter(
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(type="string"),
     *      ),
     * operationId="show_languages",
     * tags={"Admin - Languages"},
     * security={{"bearer_token": {} }},
     * @OA\Response(
     *    response=200,
     *    description="successful operation"
     * ),
     * )
     *)
    */
    public function show(Language $language)
    {
        return response()->json(new PoemAttributeResource($language), 200);
    }

    /**
     * @OA\Post(
     * path="/admin/languages/{id}",
     * description="Edit language.",
     *   @OA\Parameter(
     *     in="path",
     *     name="id",
     *     required=true,
     *     @OA\Schema(type="string"),
     *   ),
     *  tags={"Admin - Languages"},
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
    public function update(Request $request, Language $language)
    {
        $request->validate([
            'name'         => ['required', 'string',]
        ]);

        $language->update([
            'name'         => $request->name,
        ]);

        return response()->json(new PoemAttributeResource($language), 200);
    }

    /**
     * @OA\Delete(
     * path="/admin/languages/{id}",
     * description="Delete entered language.",
     *     @OA\Parameter(
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         @OA\Examples(example="int", value="1", summary="An int value."),
     *      ),
     * operationId="delete_language",
     * tags={"Admin - Languages"},
     * security={{"bearer_token":{}}},
     * @OA\Response(
     *    response=200,
     *    description="successful operation"
     * ),
     * )
     *)
    */
    public function destroy(Language $language)
    {
        //TODO: Deleting a radod will delete all related poems

        $language->delete();
        return response()->json(null, 204);
    }
}
