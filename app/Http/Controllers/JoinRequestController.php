<?php

namespace App\Http\Controllers;

use App\Http\Resources\JoinRequestResource;
use App\Mail\VerifiyEmail;
use App\Models\JoinRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class JoinRequestController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['verify_request']);
    }

    /**
     * @OA\Post(
     * path="/verify-request",
     * description="Send verify email and create account if you are a guest.",
     * tags={"User - Join as boet"},
     *   @OA\RequestBody(
     *       required=true,
     *       @OA\MediaType(
     *           mediaType="multipart/form-data",
     *           @OA\Schema(
     *              @OA\Property(property="name", type="string"),
     *              @OA\Property(property="email", type="string"),
     *              @OA\Property(property="password", type="string"),
     *              @OA\Property(property="password_confirmation", type="string"),
     *              @OA\Property(property="phone_country_id", type="integer"),
     *              @OA\Property(property="phone", type="string"),
     *              @OA\Property(property="country_id", type="integer"),
     *              @OA\Property(property="bio", type="string"),
     *              @OA\Property(property="image", type="file"),
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
    public function verify_request(Request $request)
    {
        $user = auth('api')->user();
        $new_user = $token = $type = $email_to_send = null;
        
        $request->validate([
            'name'              => ['required', 'string'],
            'email'             => ['required', 'string', 'email', Rule::unique('users', 'email')->ignore($user?->id)],
            'password'          => ['string', 'min:6', 'confirmed'],
            'phone_country_id'  => ['required', 'integer', 'exists:countries,id'],
            'phone'             => ['required', 'size:8', Rule::unique('users', 'phone')->ignore($user?->id)],
            'country_id'        => ['required', 'integer', 'exists:countries,id'],
            'bio'               => ['required', 'string'],
            'image'             => ['required'],
        ]);

        if($user && $user->email_verified_at)
            return response()->json(null, 200);

        // $verificationToken = Str::random(4);
        $verificationToken = 1234;

        if($user){
            $type = 'send';
            $email_to_send = $user->email;

            $image = null;
            if($request->image){
                if($request->image == $user->image){
                    $image = $user->image;
                }else{
                    if(!is_file($request->image))
                        throw ValidationException::withMessages(['image' => __('error_messages.Image should be a file')]);
                    $image = upload_file($request->image, 'users', 'user');
                }
            }

            $user->update([
                'name'              => $request->name,
                'phone_country_id'  => $request->phone_country_id,
                'phone'             => $request->phone,
                'country_id'        => $request->country_id,
                'bio'               => $request->bio,
                'image'             => $image,
                'email_verification_token' => $verificationToken
            ]);
            
        } else {
            $type = 'create_and_send';
            $email_to_send = $request->email;

            $image = upload_file($request->image, 'users', 'user');

            $new_user = User::create([
                'name'              => $request->name,
                'email'             => $request->email,
                'phone_country_id'  => $request->phone_country_id,
                'phone'             => $request->phone,
                'country_id'        => $request->country_id,
                'bio'               => $request->bio,
                'password'          => Hash::make($request->password),
                'has_account'       => true,
                'image'             => $image,
                'email_verification_token' => $verificationToken
            ]);
            $new_user->assignRole('user');
        }

        if($new_user)
            $token = $new_user->createToken('Sanctum', [])->plainTextToken;

        // try {
        //     Mail::to($email_to_send)->send(new VerifiyEmail($verificationToken));
        // } catch (\Throwable $th) {
        //     //
        // }

        return response()->json([
            'token'  => $token,
            'type'   => $type,
        ], 200);
    }

    /**
     * @OA\Post(
     * path="/verify-account",
     * description="Confirm the code.",
     * tags={"User - Join as boet"},
     * security={{"bearer_token":{}}},
     *   @OA\RequestBody(
     *       required=true,
     *       @OA\MediaType(
     *           mediaType="multipart/form-data",
     *           @OA\Schema(
     *              @OA\Property(property="code", type="string"),
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
    public function verify_account(Request $request){
        $request->validate([
            'code'   => 'required',
        ]);

        $user = Auth::user();

        if($user->email_verified_at)
            throw new BadRequestHttpException(__('error_messages.Sorry, This email is already verified!'));

        if(!$user->email_verification_token)
            throw new BadRequestHttpException(__('error_messages.Sorry, Send email to receive the code first!'));

        if($request->code != $user->email_verification_token)
            throw new BadRequestHttpException(__('error_messages.Sorry, Code is not correct!'));

        $user->is_verified = true;
        $user->email_verified_at = now();
        $user->email_verification_token = null;
        $user->save();

        return response()->json(null, 200);
    }

    /**
     * @OA\Post(
     * path="/join-requests",
     * description="Create the join request.",
     * tags={"User - Join as boet"},
     * security={{"bearer_token":{}}},
     * @OA\Response(
     *    response=200,
     *    description="successful operation",
     *     ),
     * )
     * )
    */
    public function join_request()
    {
        $user = to_user(Auth::user());
        $date = Carbon::now()->format('Y-m-d');
        
        JoinRequest::create([
            'user_id'        => $user->id,
            'date'           => $date,
        ]);

        return response()->json(null, 200);
    }
}
