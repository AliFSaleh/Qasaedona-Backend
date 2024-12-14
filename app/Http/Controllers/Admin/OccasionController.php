<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\OccasionResource;
use App\Models\Occasion;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class OccasionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('permission:occasions.read|occasions.write|occasions.delete')->only('index', 'show', 'export');
        $this->middleware('permission:occasions.write')->only('store', 'update');
        $this->middleware('permission:occasions.delete')->only('destroy');
    }

    /**
     * @OA\Get(
     * path="/admin/occasions",
     * description="Get all occasions",
     * operationId="get_all_categories",
     * tags={"Admin - Occasions"},
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
     *     @OA\Schema(type="integer",enum={0, 1})
     *   ),
     * @OA\Parameter(
     *     in="query",
     *     name="type",
     *     required=false,
     *     @OA\Schema(type="string",enum={"madeh", "rethaa"})
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
            'status'             => ['integer', 'in:1,0'],
            'type'               => ['string', 'in:madeh,rethaa'],
            'start_date'         => ['date_format:Y-m-d'],
            'end_date'           => ['date_format:Y-m-d'],
            'q'                  => ['string']
        ]);

        $q = Occasion::query()->latest();

        if($request->q){
            $q->where(function($query) use ($request) {
                if (is_numeric($request->q))
                    $query->where('id', $request->q);
        
                $query->orWhereIn('title', 'LIKE', '%'.$request->q.'%');
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

        if($request->type)
            $q->where('type', $request->type);

        if($request->with_paginate === '0')
            $occasions = $q->get();
        else
            $occasions = $q->paginate($request->per_page ?? 10);

        return OccasionResource::collection($occasions);
    }

    /**
     * @OA\Post(
     * path="/admin/occasions",
     * description="Add new occasion.",
     * tags={"Admin - Occasions"},
     * security={{"bearer_token": {} }},
     *   @OA\RequestBody(
     *       required=true,
     *       @OA\MediaType(
     *           mediaType="multipart/form-data",
     *           @OA\Schema(
     *              required={"title","image","date","type"},
     *              @OA\Property(property="title", type="string"),
     *              @OA\Property(property="date", type="date"),
     *              @OA\Property(property="type", type="string", enum={"madeh","rethaa"}),
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
    public function store(Request $request)
    {
        $request->validate([
            'title'         => ['required', 'string'],
            'date'          => ['required', 'date_format:Y-m-d'],
            'type'          => ['required', 'in:madeh,rethaa'],
            'image'         => ['required', 'image'],
        ]);

        $image = upload_file($request->image, 'occasions', 'occasion');

        $occasion = Occasion::create([
            'title'         => $request->title,
            'date'          => $request->date,
            'type'          => $request->type,
            'status'        => 1,
            'image'         => $image,
        ]);

        return response()->json(new OccasionResource($occasion), 200);
    }

    /**
     * @OA\Get(
     * path="/admin/occasions/{id}",
     * description="Get occasion information.",
     *     @OA\Parameter(
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(type="string"),
     *      ),
     * operationId="show_occasion",
     * tags={"Admin - Occasions"},
     * security={{"bearer_token": {} }},
     * @OA\Response(
     *    response=200,
     *    description="successful operation"
     * ),
     * )
     *)
    */
    public function show(Occasion $occasion)
    {
        return response()->json(new OccasionResource($occasion), 200);
    }

    /**
     * @OA\Post(
     * path="/admin/occasions/{id}",
     * description="Edit occasion.",
     *   @OA\Parameter(
     *     in="path",
     *     name="id",
     *     required=true,
     *     @OA\Schema(type="string"),
     *   ),
     *  tags={"Admin - Occasions"},
     *  security={{"bearer_token": {} }},
     *   @OA\RequestBody(
     *       required=true,
     *       @OA\MediaType(
     *           mediaType="multipart/form-data",
     *           @OA\Schema(
     *              @OA\Property(property="title", type="string"),
     *              @OA\Property(property="date", type="date"),
     *              @OA\Property(property="type", type="string", enum={"madeh","rethaa"}),
     *              @OA\Property(property="image", type="file"),
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
    public function update(Request $request, Occasion $occasion)
    {
        $request->validate([
            'title'         => ['required', 'string'],
            'date'          => ['required', 'date_format:Y-m-d'],
            'type'          => ['required', 'in:madeh,rethaa'],
            'image'         => ['required'],
        ]);

        $image = $occasion->image;
        if($request->image == $occasion->image){
            $image = $occasion->image;
        }else{
            if(!is_file($request->image))
                throw ValidationException::withMessages(['image' => __('Image should be a file')]);
            $image = upload_file($request->image, 'occasions', 'occasion');
        }

        $occasion->update([
            'title'        => $request->title,
            'date'         => $request->date,
            'type'         => $request->type,
            'image'        => $image,
        ]);

        return response()->json(new OccasionResource($occasion), 200);
    }

    /**
     * @OA\Delete(
     * path="/admin/occasions/{id}",
     * description="Delete entered occasion.",
     *     @OA\Parameter(
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         @OA\Examples(example="int", value="1", summary="An int value."),
     *      ),
     * operationId="delete_occasion",
     * tags={"Admin - Occasions"},
     * security={{"bearer_token":{}}},
     * @OA\Response(
     *    response=200,
     *    description="successful operation"
     * ),
     * )
     *)
    */
    public function destroy(Occasion $occasion)
    {
        $occasion->delete();
        return response()->json(null, 204);
    }

    /**
     * @OA\Post(
     * path="/admin/occasions/{id}/activate",
     * description="activate the occasion.",
     *   @OA\Parameter(
     *     in="path",
     *     name="id",
     *     required=true,
     *     @OA\Schema(type="string"),
     *   ),
     * tags={"Admin - Occasions"},
     * security={{"bearer_token": {} }},
     * @OA\Response(
     *    response=200,
     *    description="successful operation",
     *     ),
     * )
     * )
    */
    public function occasion_status_toggle(Occasion $occasion)
    {
        $occasion->update(['status' => !$occasion->status]);

        // deactive related poems
        $status = false;
        if($occasion->status)
            $status = true;

        // Poem::where('occasion_id', $occasion->id)->update([
        //     'status' => $status
        // ]);        

        return response()->json(new OccasionResource($occasion), 200);
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
