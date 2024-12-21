<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('permission:notification.write')->only(['send']);
    }

    /**
     * @OA\Post(
     * path="/admin/send_notification",
     * description="send_notification.",
     * tags={"Admin - Notifications"},
     * security={{"bearer_token": {} }},
     *   @OA\RequestBody(
     *       required=true,
     *       @OA\MediaType(
     *           mediaType="multipart/form-data",
     *           @OA\Schema(
     *              required={"title", "body"},
     *              @OA\Property(property="title", type="string"),
     *              @OA\Property(property="body", type="string"),
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
    public function send(Request $request)
    {
        $request->validate([
            'title' => ['required', 'string'],
            'body'  => ['required', 'string'],
        ]);

        $notification = Notification::create_notification(
            'from_admin',
            null,
            null,
            [
                'title' => $request->title,
                'body' => $request->body
            ]
        );

        $notification->send_to_all_users();
        $notification->send(null, true);

        return response()->json(new NotificationResource($notification), 200);
    }
}
