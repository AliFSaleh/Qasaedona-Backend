<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\SliderResource;
use App\Models\Lesson;
use App\Models\Rawaded;
use App\Models\Slider;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SliderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('permission:sliders.read|sliders.write|sliders.delete')->only('index', 'show');
        $this->middleware('permission:sliders.write')->only('store', 'update');
        $this->middleware('permission:sliders.delete')->only('destroy');
    }

    /**
     * @OA\Get(
     * path="/admin/sliders",
     * description="Get all sliders",
     * operationId="get_all_sliders",
     * tags={"Admin - Sliders"},
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
        ]);

        $q = Slider::query()->latest();

        if($request->with_paginate === '0')
            $sliders = $q->get();
        else
            $sliders = $q->paginate($request->per_page ?? 10);

        return SliderResource::collection($sliders);
    }

    /**
     * @OA\Post(
     * path="/admin/sliders",
     * description="Add new slider.",
     * tags={"Admin - Sliders"},
     * security={{"bearer_token": {} }},
     *   @OA\RequestBody(
     *       required=true,
     *       @OA\MediaType(
     *           mediaType="multipart/form-data",
     *           @OA\Schema(
     *              required={"type","media_id"},
     *              @OA\Property(property="type", type="string", enum={"internal_linked","external_link","not_linked"}),
     *              @OA\Property(property="media_id", type="integer"),
     *              @OA\Property(property="linked_type", type="string", enum={"radod","poet","lesson","add_poem","contact_us"}),
     *              @OA\Property(property="linked_id", type="integer"),
     *              @OA\Property(property="external_link", type="string"),
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
            'type'              => ['required', 'string', 'in:internal_linked,external_link,not_linked'],
            'image'             => ['required', 'image'],
            'linked_type'       => ['required_if:type,==,internal_linked', 'string', 'in:radod,poet,lesson,add_poem,contact_us'],
            'linked_id'         => ['integer'],
            'external_link'     => ['required_if:type,==,external_link', 'url'],
        ]);

        $linked_type = null;
        $linked_id = null;
        $external_link = null;

        if($request->type == 'internal_linked'){
            $linked_id = $request->linked_id;
            
            if($request->linked_type == 'radod')
                $linked_type = Rawaded::class;
            if($request->linked_type == 'poet')
                $linked_type = User::class;
            if($request->linked_type == 'lesson')
                $linked_type = Lesson::class;
        } else if ($request->type == 'external_link') {
            $external_link = $request->external_link;
        }

        $image = upload_file($request->image, 'sliders', 'slider');

        $slider = Slider::create([
            'type'            => $request->type,
            'image'           => $image,
            'linked_type'     => $linked_type,
            'linked_id'       => $linked_id,
            'external_link'   => $external_link,
        ]);

        return response()->json(new SliderResource($slider), 200);
    }

    /**
     * @OA\Get(
     * path="/admin/sliders/{id}",
     * description="Get slider information.",
     *     @OA\Parameter(
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(type="string"),
     *      ),
     * operationId="show_slider",
     * tags={"Admin - Sliders"},
     * security={{"bearer_token": {} }},
     * @OA\Response(
     *    response=200,
     *    description="successful operation"
     * ),
     * )
     *)
    */
    public function show(Slider $slider)
    {
        return response()->json(new SliderResource($slider), 200);
    }

    /**
     * @OA\Post(
     * path="/admin/sliders/{id}",
     * description="Edit slider.",
     *   @OA\Parameter(
     *     in="path",
     *     name="id",
     *     required=true,
     *     @OA\Schema(type="string"),
     *   ),
     *  tags={"Admin - Sliders"},
     *  security={{"bearer_token": {} }},
     *   @OA\RequestBody(
     *       required=true,
     *       @OA\MediaType(
     *           mediaType="multipart/form-data",
     *           @OA\Schema(
     *              @OA\Property(property="type", type="string", enum={"internal_linked","external_link","not_linked"}),
     *              @OA\Property(property="media_id", type="integer"),
     *              @OA\Property(property="linked_type", type="string", enum={"radod","poet","lesson","add_poem","contact_us"}),
     *              @OA\Property(property="linked_id", type="integer"),
     *              @OA\Property(property="external_link", type="string"),
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
    public function update(Request $request, Slider $slider)
    {
        $request->validate([
            'type'              => ['required', 'string', 'in:internal_linked,external_link,not_linked'],
            'image'             => ['required'],
            'linked_type'       => ['required_if:type,==,internal_linked', 'string', 'in:radod,poet,lesson,add_poem,contact_us'],
            'linked_id'         => ['required_if:type,==,internal_linked', 'integer'],
            'external_link'     => ['required_if:type,==,external_link', 'url'],
        ]);

        $linked_type = null;
        $linked_id = null;
        $external_link = null;

        if($request->type == 'internal_linked'){
            $linked_id = $request->linked_id;
            
            if($request->linked_type == 'radod')
                $linked_type = Rawaded::class;
            if($request->linked_type == 'poet')
                $linked_type = User::class;
            if($request->linked_type == 'lesson')
                $linked_type = Lesson::class;
        } else if ($request->type == 'external_link') {
            $external_link = $request->external_link;
        }

        $image = null;
        if($request->image){
            if($request->image == $slider->image){
                $image = $slider->image;
            }else{
                if(!is_file($request->image))
                    throw ValidationException::withMessages(['image' => __('error_messages.Image should be a file')]);
                $image = upload_file($request->image, 'sliders', 'slider');
            }
        }

        $slider->update([
            'type'            => $request->type,
            'image'           => $image,
            'linked_type'     => $linked_type,
            'linked_id'       => $linked_id,
            'external_link'   => $external_link,
        ]);

        return response()->json(new SliderResource($slider), 200);
    }

    /**
     * @OA\Delete(
     * path="/admin/sliders/{id}",
     * description="Delete entered slider.",
     *     @OA\Parameter(
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         @OA\Examples(example="int", value="1", summary="An int value."),
     *      ),
     * operationId="delete_slider",
     * tags={"Admin - Sliders"},
     * security={{"bearer_token":{}}},
     * @OA\Response(
     *    response=200,
     *    description="successful operation"
     * ),
     * )
     *)
    */
    public function destroy(Slider $slider)
    {
        $slider->delete();
        return response()->json(null, 204);
    }
}
