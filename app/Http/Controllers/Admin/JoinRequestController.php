<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\JoinRequestResource;
use App\Models\JoinRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class JoinRequestController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('permission:join_requests.read|join_requests.write')->only('index', 'export');
        $this->middleware('permission:join_requests.write')->only('approve', 'reject');
    }

    /**
     * @OA\Get(
     * path="/admin/join_requests",
     * description="Get all join_requests",
     * operationId="get_all_join_requests",
     * tags={"Admin - Join as Poet"},
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
     *     @OA\Schema(type="string",enum={"pending","approved","rejected"})
     *   ),
     * @OA\Parameter(
     *     in="query",
     *     name="start_date",
     *     required=false,
     *     @OA\Schema(type="date")
     *   ),
     * @OA\Parameter(
     *     in="query",
     *     name="end_date",
     *     required=false,
     *     @OA\Schema(type="date")
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
            'status'             => ['string', 'in:pending,approved,rejected'],
            'start_date'         => ['date_format:Y-m-d'],
            'end_date'           => ['date_format:Y-m-d'],
            'q'                  => ['string']
        ]);

        $q = JoinRequest::with('user')->latest();

        if($request->q){
            $users_ids = User::where('name', 'LIKE', '%' . $request->q . '%')
                ->orWhere('email', 'LIKE', '%' . $request->q . '%')
                ->orWhere('phone', 'LIKE', '%' . $request->q . '%')
                ->pluck('id')->toArray();

            $q->where(function ($query) use ($request, $users_ids) {
                if (is_numeric($request->q)) {
                    $query->where('id', $request->q);
                }
                $query->orWhereIn('user_id', $users_ids);
            });
        }

        if($request->status)
            $q->where('status', $request->status);

        if($request->start_date)
            $q->where('date','>=', $request->start_date);
        if($request->end_date)
            $q->where('date','<=', $request->end_date);

        if($request->with_paginate === '0')
            $join_requests = $q->get();
        else
            $join_requests = $q->paginate($request->per_page ?? 10);

        return JoinRequestResource::collection($join_requests);
    }

    /**
     * @OA\Post(
     * path="/admin/join_requests/{id}/approve",
     * description="approve the request.",
     *   @OA\Parameter(
     *     in="path",
     *     name="id",
     *     required=true,
     *     @OA\Schema(type="string"),
     *   ),
     * tags={"Admin - Join as Poet"},
     * security={{"bearer_token": {} }},
     * @OA\Response(
     *    response=200,
     *    description="successful operation",
     *     ),
     * )
     * )
    */
    public function approve(JoinRequest $join_request)
    {
        if($join_request->status != 'pending')
            throw new BadRequestHttpException(__('error_messages.Sorry, The request has already been processed.'));

        $user = $join_request->user;
        $user->syncRoles('poet');

        $join_request->update(['status' => 'approved']);
        return response()->json(new JoinRequestResource($join_request), 200);
    }

    /**
     * @OA\Post(
     * path="/admin/join_requests/{id}/reject",
     * description="reject the request.",
     *   @OA\Parameter(
     *     in="path",
     *     name="id",
     *     required=true,
     *     @OA\Schema(type="string"),
     *   ),
     * tags={"Admin - Join as Poet"},
     * security={{"bearer_token": {} }},
     * @OA\Response(
     *    response=200,
     *    description="successful operation",
     *     ),
     * )
     * )
    */
    public function reject(JoinRequest $join_request)
    {
        if($join_request->status != 'pending')
            throw new BadRequestHttpException(__('error_messages.Sorry, The request has already been processed.'));

        $join_request->update(['status' => 'rejected']);
        return response()->json(new JoinRequestResource($join_request), 200);
    }

    // public function export(Request $request, $type)
    // {
    //     $q = Occasion::query()->latest();

    //     if($request->q)
    //     {
    //         $categories_ids = Translation::where('translatable_type', Occasion::class)
    //                                     ->where('attribute', 'name')
    //                                     ->where('value', 'LIKE', '%'.$request->q.'%')
    //                                     ->groupBy('translatable_id')
    //                                     ->pluck('translatable_id');

    //         $q->where(function($query) use ($request, $categories_ids) {
    //             if (is_numeric($request->q))
    //                 $query->where('id', $request->q);
        
    //             $query->orWhereIn('id', $categories_ids);
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

    //     $occasions = $q->get();

    //     if ($type == 'xlsx') {
    //         return Excel::download(new OccasionExport($occasions), 'occasions.xlsx', \Maatwebsite\Excel\Excel::XLSX);
    //     } else if ($type == 'csv') {
    //         return Excel::download(new OccasionExport($occasions), 'occasions.csv', \Maatwebsite\Excel\Excel::CSV);
    //     } else
    //         return Excel::download(new OccasionExport($occasions), 'occasions.pdf', \Maatwebsite\Excel\Excel::DOMPDF);
    // }
}
