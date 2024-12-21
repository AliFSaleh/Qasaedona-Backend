<?php

namespace App\Http\Controllers;

use App\Http\Resources\NotificationResource;
use App\Models\FCMToken;
use App\Models\Notification;
use App\Models\NotificationUser;
use Illuminate\Http\Request;
use Mosab\Translation\Middleware\RequestLanguage;

class NotificationController extends Controller
{
    /**
     * @OA\Post(
     * path="/fcm_token",
     * description="create fcm token.",
     *  tags={"User - Notifications"},
     *   @OA\RequestBody(
     *       required=true,
     *       @OA\MediaType(
     *           mediaType="multipart/form-data",
     *           @OA\Schema(
     *              required={"device_token","fcm_token","language"},
     *              @OA\Property(property="device_token", type="string"),
     *              @OA\Property(property="fcm_token", type="string"),
     *              @OA\Property(property="language", type="string", enum={"en","ar"}),
     *          )
     *       )
     *   ),
     * @OA\Response(
     *         response="200",
     *         description="successful operation",
     *     ),
     * )
     * )
    */
    public function register_token(Request $request)
    {
        $request->validate([
            'device_token'  => ['required', 'string'],
            'fcm_token'     => ['required', 'string'],
            'language'      => ['required', 'in:'.implode(',',RequestLanguage::$all_languages)],
        ]);

        $user = auth('api')->user();

        FCMToken::updateOrCreate(
            ['user_id' => $user?->id, 'device_token' => $request->device_token],
            ['fcm_token' => $request->fcm_token, 'language' => $request->language]
        );
    }
    
    /**
     * @OA\Get(
     * path="/notifications",
     * description="Get my notifications.",
     * operationId="show_my_notifications",
     * tags={"User - Notifications"},
     * @OA\Parameter(
     *     in="query",
     *     name="device_token",
     *     required=false,
     *     @OA\Schema(type="string")
     *   ),
     * security={{"bearer_token":{}}},
     * @OA\Response(
     *    response=200,
     *    description="successful operation"
     * ),
     * )
     *)
    */
    public function get_notifications(Request $request)
    {
        $request->validate([
            'per_page'     => ['integer', 'min:1'],
            'device_token' => ['string']

        ]);

        $user = auth('api')->user();

        if($user){
            $unread_notifications_count = $user->notifications()->where('read', false)->count();

            return (NotificationResource::collection($user->notifications()->orderBy('created_at', 'DESC')->paginate($request->per_page??10)))
                ->additional(['meta' => [
                    'unread_notifications_count' => $unread_notifications_count,
                    ]]);
        } else {
            $device = FCMToken::where('device_token', $request->device_token)->first();
            $unread_notifications_count = $device->notifications()->where('read', false)->count();

            return (NotificationResource::collection($device->notifications()->orderBy('created_at', 'DESC')->paginate($request->per_page??10)))
                ->additional(['meta' => [
                    'unread_notifications_count' => $unread_notifications_count,
                    ]]);
        }
    }

    /**
     * @OA\Post(
     * path="/notifications/read",
     * description="read a notifications.",
     *  tags={"User - Notifications"},
     *   @OA\RequestBody(
     *       required=true,
     *       @OA\MediaType(
     *           mediaType="multipart/form-data",
     *           @OA\Schema(
     *              required={"notifications_ids[0]"},
     *              @OA\Property(property="notifications_ids[0]", type="integer"),
     *              @OA\Property(property="notifications_ids[1]", type="integer"),
     *              @OA\Property(property="device_token", type="string"),
     *          )
     *       )
     *   ),
     * @OA\Response(
     *         response="200",
     *         description="successful operation",
     *     ),
     * )
     * )
    */
    public function notifications_read(Request $request)
    {
        $request->validate([
            'notifications_ids'    => ['required', 'array'],
            'notifications_ids.*'  => ['integer'],
            'device_token'         => ['string'],
        ]);

        $user = auth('api')->user();
        
        if($user){
            NotificationUser::where('user_id',$user->id)
                        ->whereIn('notification_id', $request->notifications_ids)
                        ->update(['read' => 1]);
        } else {
            $device = FCMToken::where('device_token', $request->device_token)->first();
            NotificationUser::where('fcm_token_id',$device?->id)
                        ->whereIn('notification_id', $request->notifications_ids)
                        ->update(['read' => 1]);
        }
    }

    /**
     * @OA\Post(
     * path="/notifications/read/all",
     * description="read all notifications.",
     *  tags={"User - Notifications"},
     *   @OA\RequestBody(
     *       required=true,
     *       @OA\MediaType(
     *           mediaType="multipart/form-data",
     *           @OA\Schema(
     *              @OA\Property(property="device_token", type="string"),
     *          )
     *       )
     *   ),
     * @OA\Response(
     *         response="200",
     *         description="successful operation",
     *     ),
     * )
     * )
    */
    public function notifications_read_all(Request $request)
    {
        $request->validate([
            'device_token'         => ['string'],
        ]);

        $user = auth('api')->user();
        
        if($user){
            NotificationUser::where('user_id',$user->id)
                        ->update(['read' => 1]);
        } else {
            $device = FCMToken::where('device_token', $request->device_token)->first();
            NotificationUser::where('fcm_token_id',$device?->id)
                        ->update(['read' => 1]);
        }
    }

    public function test_notifications(){
        $notification = Notification::create_notification(
            'testing',
            null,
            null,
            []
        );
        $notification->send_to_admins();
        // $notification->send_to_all_users();
        // $notification->send(null, true);

        return response()->json(null, 200);
    }
}
