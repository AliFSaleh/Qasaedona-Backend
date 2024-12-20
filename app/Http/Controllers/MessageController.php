<?php

namespace App\Http\Controllers;

use App\Http\Resources\MessageResource;
use App\Models\Message;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    /**
     * @OA\Post(
     * path="/messages",
     * description="Add new message.",
     * tags={"User - Contact Us"},
     *   @OA\RequestBody(
     *       required=true,
     *       @OA\MediaType(
     *           mediaType="multipart/form-data",
     *           @OA\Schema(
     *              required={"full_name","email","phone","message"},
     *              @OA\Property(property="full_name", type="string"),
     *              @OA\Property(property="email", type="string"),
     *              @OA\Property(property="phone", type="string"),
     *              @OA\Property(property="message", type="string"),
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
            'full_name'       => ['required', 'string'],
            'email'           => ['required', 'email'],
            'phone'           => ['required', 'size:8'],
            'message'         => ['required', 'string'],
        ]);

        $user = to_user(Auth::user());
        $date = Carbon::now()->format('Y-m-d');
        
        $message = Message::create([
            'user_id'        => $user?->id ?? null,
            'full_name'      => $request->full_name,
            'email'          => $request->email,
            'phone'          => $request->phone,
            'message'        => $request->message,
            'date'           => $date,
        ]);

        return response()->json(new MessageResource($message), 200);
    }
}
