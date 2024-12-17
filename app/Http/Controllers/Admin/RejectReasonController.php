<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\RejectReasonResource;
use App\Models\RejectReason;
use Illuminate\Http\Request;

class RejectReasonController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('permission:reject_reasons.read|reject_reasons.write|reject_reasons.delete')->only('index', 'show');
        $this->middleware('permission:reject_reasons.write')->only('store', 'update');
        $this->middleware('permission:reject_reasons.delete')->only('destroy');
    }

    /**
     * @OA\Get(
     * path="/admin/reject_reasons",
     * description="Get all reject_reasons",
     * operationId="get_all_reject_reasons",
     * tags={"Admin - Reject Reasons"},
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

        $q = RejectReason::query()->latest();

        if($request->q)
        {
            $q->where(function($query) use ($request) {
                if (is_numeric($request->q))
                    $query->where('id', $request->q);
        
                $query->orWhere('name', 'LIKE', '%'.$request->q.'%');
            });
        }

        if($request->with_paginate === '0')
            $reject_reasons = $q->get();
        else
            $reject_reasons = $q->paginate($request->per_page ?? 10);

        return RejectReasonResource::collection($reject_reasons);
    }

    /**
     * @OA\Post(
     * path="/admin/reject_reasons",
     * description="Add new reject_reason.",
     * tags={"Admin - Reject Reasons"},
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

        $reject_reason = RejectReason::create([
            'name'          => $request->name,
        ]);

        return response()->json(new RejectReasonResource($reject_reason), 200);
    }

    /**
     * @OA\Get(
     * path="/admin/reject_reasons/{id}",
     * description="Get reject_reason information.",
     *     @OA\Parameter(
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(type="string"),
     *      ),
     * operationId="show_reject_reasons",
     * tags={"Admin - Reject Reasons"},
     * security={{"bearer_token": {} }},
     * @OA\Response(
     *    response=200,
     *    description="successful operation"
     * ),
     * )
     *)
    */
    public function show(RejectReason $reject_reason)
    {
        return response()->json(new RejectReasonResource($reject_reason), 200);
    }

    /**
     * @OA\Post(
     * path="/admin/reject_reasons/{id}",
     * description="Edit reject_reason.",
     *   @OA\Parameter(
     *     in="path",
     *     name="id",
     *     required=true,
     *     @OA\Schema(type="string"),
     *   ),
     *  tags={"Admin - Reject Reasons"},
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
    public function update(Request $request, RejectReason $reject_reason)
    {
        $request->validate([
            'name'         => ['required', 'string',]
        ]);

        $reject_reason->update([
            'name'         => $request->name,
        ]);

        return response()->json(new RejectReasonResource($reject_reason), 200);
    }

    /**
     * @OA\Delete(
     * path="/admin/reject_reasons/{id}",
     * description="Delete entered reject_reason.",
     *     @OA\Parameter(
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         @OA\Examples(example="int", value="1", summary="An int value."),
     *      ),
     * operationId="delete_reject_reasons",
     * tags={"Admin - Reject Reasons"},
     * security={{"bearer_token":{}}},
     * @OA\Response(
     *    response=200,
     *    description="successful operation"
     * ),
     * )
     *)
    */
    public function destroy(RejectReason $reject_reason)
    {
        //TODO: Deleting a radod will delete all related poems

        $reject_reason->delete();
        return response()->json(null, 204);
    }
}
