<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\MessageResource;
use App\Models\Message;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('permission:messages.read')->only(['get_messages']);
        $this->middleware('permission:messages.write')->only(['message_read_toggle']);
    }

    
    /**
     * @OA\Get(
     * path="/admin/messages",
     * description="Get all messages",
     * operationId="get_all_messages",
     * tags={"Admin - Contact Us"},
     *   security={{"bearer_token": {} }},
     * @OA\Parameter(
     *     in="query",
     *     name="status",
     *     required=false,
     *     @OA\Schema(type="integer",enum={0, 1})
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
     *     in="query",
     *     name="q",
     *     required=false,
     *     @OA\Schema(type="string")
     *   ),
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
     *   @OA\Response(
     *     response=200,
     *     description="Success",
     *  )
     *  )
    */
    public function get_messages(Request $request)
    {
        $request->validate([
            'q'                  => ['string'],
            'status'             => ['integer', 'in:1,0'],
            'start_date'         => ['date_format:Y-m-d'],
            'end_date'           => ['date_format:Y-m-d'],
            'with_paginate'      => ['integer', 'in:0,1'],
            'per_page'           => ['integer', 'min:1'],
        ]);

        $q = Message::query()->latest();

        if ($request->q) {
            $q->where(function ($query) use ($request) {
                $query->where('full_name', 'like', '%' . $request->q . '%')
                        ->orWhere('email', 'like', '%' . $request->q . '%')
                        ->orWhere('phone', 'like', '%' . $request->q . '%')
                        ->orWhere('id', $request->q);
            });
        }

        if($request->status === '0'){
            $q->where('status', false);
        }else if ($request->status === '1') {
            $q->where('status', true);
        }

        if($request->start_date)
            $q->where('date','>=', $request->start_date);
        if($request->end_date)
            $q->where('date','<=', $request->end_date);

        if($request->with_paginate === '0')
            $messages = $q->get();
        else
            $messages = $q->paginate($request->per_page ?? 10);

        return MessageResource::collection($messages);
    }

    /**
     * @OA\Post(
     * path="/admin/messages/{id}/read",
     * description="read the message.",
     *   @OA\Parameter(
     *     in="path",
     *     name="id",
     *     required=true,
     *     @OA\Schema(type="string"),
     *   ),
     * tags={"Admin - Contact Us"},
     * security={{"bearer_token": {} }},
     * @OA\Response(
     *    response=200,
     *    description="successful operation",
     *     ),
     * )
     * )
    */
    public function message_read_toggle(Message $message)
    {
        $message->update(['status' => !$message->status]);
        return response()->json(new MessageResource($message), 200);
    }
}
