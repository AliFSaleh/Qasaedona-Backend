<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\LessonResource;
use App\Models\Lesson;
use Illuminate\Http\Request;

class LessonController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('permission:lessons.read|lessons.write|lessons.delete')->only('index', 'show', 'export');
        $this->middleware('permission:lessons.write')->only('store', 'update', 'lessons_status_toggle');
        $this->middleware('permission:lessons.delete')->only('destroy');
    }

    /**
     * @OA\Get(
     * path="/admin/lessons",
     * description="Get all lessons",
     * operationId="get_all_lessons",
     * tags={"Admin - Lessons"},
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
     *    in="query",
     *    name="poet_id",
     *    required=false,
     *    @OA\Schema(type="integer"),
     * ),
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
            'with_paginate'     => ['integer', 'in:0,1'],
            'poet_id'           => ['integer', 'exists:users,id'],
            'per_page'          => ['integer', 'min:1'],
            'status'            => ['integer', 'in:1,0'],
            'q'                 => ['string']
        ]);

        $q = Lesson::query()->latest();

        if($request->q){
            $q->where(function($query) use ($request) {
                if (is_numeric($request->q))
                    $query->where('id', $request->q);
        
                $query->orWhere('title', 'LIKE', '%'.$request->q.'%');
            });
        }

        if($request->status === '0'){
            $q->where('status', false);
        }else if ($request->status === '1') {
            $q->where('status', true);
        }

        if($request->poet_id)
            $q->where('poet_id', $request->poet_id);

        if($request->with_paginate === '0')
            $lessons = $q->get();
        else
            $lessons = $q->paginate($request->per_page ?? 10);

        return LessonResource::collection($lessons);
    }

    /**
     * @OA\Post(
     * path="/admin/lessons",
     * description="Add new lessons.",
     * tags={"Admin - Lessons"},
     * security={{"bearer_token": {} }},
     *   @OA\RequestBody(
     *       required=true,
     *       @OA\MediaType(
     *           mediaType="multipart/form-data",
     *           @OA\Schema(
     *              required={"title","body"},
     *              @OA\Property(property="poet_id", type="integer"),
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
    public function store(Request $request)
    {
        $request->validate([
            'poet_id'               => ['integer', 'exists:users,id'],
            'title'                 => ['required', 'string'],
            'body'                  => ['required', 'string'],
        ]);

        $lesson = Lesson::create([
            'title'             => $request->title,
            'poet_id'           => $request->poet_id,
            'body'              => $request->body,
            'publish_status'    => 'approved',
            'status'            => 1,
        ]);

        return response()->json(new LessonResource($lesson), 200);
    }

    /**
     * @OA\Get(
     * path="/admin/lessons/{id}",
     * description="Get lesson information.",
     *     @OA\Parameter(
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(type="string"),
     *      ),
     * operationId="show_lesson",
     * tags={"Admin - Lessons"},
     * security={{"bearer_token": {} }},
     * @OA\Response(
     *    response=200,
     *    description="successful operation"
     * ),
     * )
     *)
    */
    public function show(Lesson $lesson)
    {
        $lesson->load(['poet']);
        return response()->json(new LessonResource($lesson), 200);
    }

    /**
     * @OA\Post(
     * path="/admin/lessons/{id}",
     * description="Edit lesson.",
     *   @OA\Parameter(
     *     in="path",
     *     name="id",
     *     required=true,
     *     @OA\Schema(type="string"),
     *   ),
     *  tags={"Admin - Lessons"},
     *  security={{"bearer_token": {} }},
     *   @OA\RequestBody(
     *       required=true,
     *       @OA\MediaType(
     *           mediaType="multipart/form-data",
     *           @OA\Schema(
     *              @OA\Property(property="title", type="string"),
     *              @OA\Property(property="poet_id", type="integer"),
     *              @OA\Property(property="body", type="string"),
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
    public function update(Request $request, Lesson $lesson)
    {
        $request->validate([
            'title'             => ['required', 'string'],
            'body'              => ['required', 'string'],
            'poet_id'           => ['integer', 'exists:users,id'],
        ]);

        $lesson->update([
            'title'             => $request->title,
            'poet_id'           => $request->poet_id,
            'body'              => $request->body,
        ]);

        return response()->json(new LessonResource($lesson), 200);
    }

    /**
     * @OA\Delete(
     * path="/admin/lessons/{id}",
     * description="Delete entered lesson.",
     *     @OA\Parameter(
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         @OA\Examples(example="int", value="1", summary="An int value."),
     *      ),
     * operationId="delete_lesson",
     * tags={"Admin - Lessons"},
     * security={{"bearer_token":{}}},
     * @OA\Response(
     *    response=200,
     *    description="successful operation"
     * ),
     * )
     *)
    */
    public function destroy(Lesson $lesson)
    {
        // delete all related 

        $lesson->delete();
        return response()->json(null, 204);
    }

    /**
     * @OA\Post(
     * path="/admin/lessons/{id}/activate",
     * description="activate the Lesson.",
     *   @OA\Parameter(
     *     in="path",
     *     name="id",
     *     required=true,
     *     @OA\Schema(type="string"),
     *   ),
     * tags={"Admin - Lessons"},
     * security={{"bearer_token": {} }},
     * @OA\Response(
     *    response=200,
     *    description="successful operation",
     *     ),
     * )
     * )
    */
    public function lessons_status_toggle(Lesson $lesson)
    {
        $lesson->update(['status' => !$lesson->status]);
        return response()->json(new LessonResource($lesson), 200);
    }

    // public function export(Request $request, $type)
    // {
    //     $q = Lesson::query()->latest();

    //     if($request->q){
    //         $q->where(function($query) use ($request) {
    //             if (is_numeric($request->q))
    //                 $query->where('id', $request->q);
        
    //             $query->orWhere('title', 'LIKE', '%'.$request->q.'%')
    //                     ->orWhere('body', 'LIKE', '%'.$request->q.'%');
    //         });
    //     }

    //     if($request->status === '0'){
    //         $q->where('status', false);
    //     }else if ($request->status === '1') {
    //         $q->where('status', true);
    //     }

    //     if($request->poet_id)
    //         $q->where('poet_id', $request->poet_id);

    //     $lessons = $q->get();

    //     if ($type == 'xlsx') {
    //         return Excel::download(new LessonsCollectionExport($lessons), 'lessons.xlsx', \Maatwebsite\Excel\Excel::XLSX);
    //     } else if ($type == 'csv') {
    //         return Excel::download(new LessonsCollectionExport($lessons), 'lessons.csv', \Maatwebsite\Excel\Excel::CSV);
    //     } else
    //         return Excel::download(new LessonsCollectionExport($lessons), 'lessons.pdf', \Maatwebsite\Excel\Excel::DOMPDF);
    // }
}
