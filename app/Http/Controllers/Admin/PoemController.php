<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\PoemResource;
use App\Models\Audio;
use App\Models\Poem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class PoemController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('permission:poems.read|poems.write|poems.delete')->only('index', 'show', 'export');
        $this->middleware('permission:poems.write')->only('store', 'update');
        $this->middleware('permission:poems.delete')->only('destroy');
    }

    /**
     * @OA\Get(
     * path="/admin/poems",
     * description="Get all poems",
     * operationId="get_all_poems",
     * tags={"Admin - Poems"},
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
     *    in="query",
     *    name="poem_type_id",
     *    required=false,
     *    @OA\Schema(type="integer"),
     * ),
     * @OA\Parameter(
     *    in="query",
     *    name="language_id",
     *    required=false,
     *    @OA\Schema(type="integer"),
     * ),
     * @OA\Parameter(
     *    in="query",
     *    name="category_id",
     *    required=false,
     *    @OA\Schema(type="integer"),
     * ),
     * @OA\Parameter(
     *    in="query",
     *    name="occasion_id",
     *    required=false,
     *    @OA\Schema(type="integer"),
     * ),
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
            'poem_type_id'      => ['integer', 'exists:poem_types,id'],
            'language_id'       => ['integer', 'exists:languages,id'],
            'category_id'       => ['integer', 'exists:categories,id'],
            'occasion_id'       => ['integer', 'exists:occasions,id'],
            'poet_id'           => ['integer', 'exists:poets,id'],
            'per_page'          => ['integer', 'min:1'],
            'status'            => ['integer', 'in:1,0'],
            'type'              => ['string', 'in:madeh,rethaa'],
            'q'                 => ['string']
        ]);

        $q = Poem::query()->latest();

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

        if($request->type)
            $q->where('type', $request->type);
        
        if($request->poem_type_id)
            $q->where('poem_type_id', $request->poem_type_id);
        if($request->language_id)
            $q->where('language_id', $request->language_id);
        if($request->category_id)
            $q->where('category_id', $request->category_id);
        if($request->occasion_id)
            $q->where('occasion_id', $request->occasion_id);
        if($request->poet_id)
            $q->where('poet_id', $request->poet_id);

        if($request->with_paginate === '0')
            $poems = $q->get();
        else
            $poems = $q->paginate($request->per_page ?? 10);

        return PoemResource::collection($poems);
    }

    /**
     * @OA\Post(
     * path="/admin/poems",
     * description="Add new poem.",
     * tags={"Admin - Poems"},
     * security={{"bearer_token": {} }},
     *   @OA\RequestBody(
     *       required=true,
     *       @OA\MediaType(
     *           mediaType="multipart/form-data",
     *           @OA\Schema(
     *              required={"title","poet_id","type","poem_type_id","category_id","language_id","occasion_id","body","audios_type","audios[0][title]","audios[0][audio]"},
     *              @OA\Property(property="title", type="string"),
     *              @OA\Property(property="poet_id", type="integer"),
     *              @OA\Property(property="type", type="string", enum={"madeh","rethaa"}),
     *              @OA\Property(property="poem_type_id", type="integer"),
     *              @OA\Property(property="category_id", type="integer"),
     *              @OA\Property(property="language_id", type="integer"),
     *              @OA\Property(property="occasion_id", type="integer"),
     *              @OA\Property(property="body", type="string"),
     *              @OA\Property(property="audios_type", type="string", enum={"files","youtube"}),
     *              @OA\Property(property="audios[0][title]", type="string"),
     *              @OA\Property(property="audios[0][audio]", type="file"),
     *              @OA\Property(property="audios[1][title]", type="string"),
     *              @OA\Property(property="audios[1][audio]", type="file"),
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
            'title'                 => ['required', 'string'],
            'poet_id'               => ['required', 'integer', 'exists:poets,id'],
            'type'                  => ['required', 'in:madeh,rethaa'],
            'poem_type_id'          => ['required', 'integer', 'exists:poem_types,id'],
            'category_id'           => ['required', 'integer', 'exists:categories,id'],
            'language_id'           => ['required', 'integer', 'exists:languages,id'],
            'occasion_id'           => ['required', 'integer', 'exists:occasions,id'],
            'body'                  => ['required', 'string'],
            'audios_type'           => ['required', 'string', 'in:files,youtube'],
            'audios'                => ['required', 'array'],
            'audios.*.title'        => ['required', 'string'],
            'audios.*.audio'        => ['required'],
        ]);

        if($request->audios_type == 'files' && count($request->audios_type) > 5)
            throw new BadRequestHttpException(__('error_messages.Sorry, Max 5 files'));

        try {
            DB::beginTransaction();
        
            $poem = Poem::create([
                'created_by'        => 'admin',
                'user_id'           => null,
                'title'             => $request->title,
                'poet_id'           => $request->poet_id,
                'type'              => $request->type,
                'poem_type_id'      => $request->poem_type_id,
                'category_id'       => $request->category_id,
                'language_id'       => $request->language_id,
                'occasion_id'       => $request->occasion_id,
                'body'              => $request->body,
                'audios_count'      => count($request->audios),
                'publish_status'    => 'approved',
                'status'            => 1,
            ]);

            foreach($request->audios as $audio){
                $link = null;

                if($request->audios_type == 'files'){
                    if(!is_file($audio['audio']))
                        throw ValidationException::withMessages(['audio' => __('Audio should be a file')]);
                    $link = upload_file($audio['audio'], 'audios', 'audio');
                }else{
                    $link = $audio['audio'];
                }

                Audio::create([
                    'poem_id'       => $poem->id,
                    'audio_type'    => $request->audios_type,
                    'link'          => $link
                ]);
            }
            
            DB::commit();

            return response()->json(new PoemResource($poem), 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json($e->getMessage(), 400);
        } catch (\Error $e) {
            DB::rollBack();
            return response()->json($e->getMessage(), 400);
        }
    }

    /**
     * @OA\Get(
     * path="/admin/poems/{id}",
     * description="Get poem information.",
     *     @OA\Parameter(
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(type="string"),
     *      ),
     * operationId="show_poem",
     * tags={"Admin - Poems"},
     * security={{"bearer_token": {} }},
     * @OA\Response(
     *    response=200,
     *    description="successful operation"
     * ),
     * )
     *)
    */
    public function show(Poem $poem)
    {
        $poem->load(['user', 'poet', 'poem_type','category','language','occasion','audios']);
        return response()->json(new PoemResource($poem), 200);
    }

    /**
     * @OA\Post(
     * path="/admin/poems/{id}",
     * description="Edit poem.",
     *   @OA\Parameter(
     *     in="path",
     *     name="id",
     *     required=true,
     *     @OA\Schema(type="string"),
     *   ),
     *  tags={"Admin - Poems"},
     *  security={{"bearer_token": {} }},
     *   @OA\RequestBody(
     *       required=true,
     *       @OA\MediaType(
     *           mediaType="multipart/form-data",
     *           @OA\Schema(
     *              @OA\Property(property="title", type="string"),
     *              @OA\Property(property="poet_id", type="integer"),
     *              @OA\Property(property="type", type="string", enum={"madeh","rethaa"}),
     *              @OA\Property(property="poem_type_id", type="integer"),
     *              @OA\Property(property="category_id", type="integer"),
     *              @OA\Property(property="language_id", type="integer"),
     *              @OA\Property(property="occasion_id", type="integer"),
     *              @OA\Property(property="body", type="string"),
     *              @OA\Property(property="audios_type", type="string", enum={"files","youtube"}),
     *              @OA\Property(property="deleted_audios_ids[0]", type="integer"),
     *              @OA\Property(property="audios[0][id]", type="integer"),
     *              @OA\Property(property="audios[0][title]", type="string"),
     *              @OA\Property(property="audios[0][audio]", type="file"),
     *              @OA\Property(property="audios[1][title]", type="string"),
     *              @OA\Property(property="audios[1][audio]", type="file"),
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
    public function update(Request $request, Poem $poem)
    {
        $request->validate([
            'title'                 => ['required', 'string'],
            'poet_id'               => ['required', 'integer', 'exists:poets,id'],
            'type'                  => ['required', 'in:madeh,rethaa'],
            'poem_type_id'          => ['required', 'integer', 'exists:poem_types,id'],
            'category_id'           => ['required', 'integer', 'exists:categories,id'],
            'language_id'           => ['required', 'integer', 'exists:languages,id'],
            'occasion_id'           => ['required', 'integer', 'exists:occasions,id'],
            'body'                  => ['required', 'string'],
            'audios_type'           => ['required', 'string', 'in:files,youtube'],
            'deleted_audios_ids'    => ['nullable', 'array'],
            'deleted_audios_ids.*'  => ['integer', 'exists:audios,id'],
            'audios'                => ['required', 'array'],
            'audios.*.id'           => ['nullable', 'integer', 'exists:audios,id'],
            'audios.*.title'        => ['required', 'string'],
            'audios.*.audio'        => ['required'],
        ]);

        //TODO: max files count to uplaod is 5

        try {
            DB::beginTransaction();

            $poem->update([
                'title'             => $request->title,
                'poet_id'           => $request->poet_id,
                'type'              => $request->type,
                'poem_type_id'      => $request->poem_type_id,
                'category_id'       => $request->category_id,
                'language_id'       => $request->language_id,
                'occasion_id'       => $request->occasion_id,
                'body'              => $request->body,
            ]);

            foreach($request->audios as $audio){
                if(isset($audio['id'])){
                    $audio = Audio::find($audio['id']);

                    if($request->audios_type == 'files'){
                        $link = $audio->link;
                        if($audio['audio'] == $audio->link){
                            $link = $audio->link;
                        }else{
                            if(!is_file($audio['audio']))
                                throw ValidationException::withMessages(['audio' => __('Audio should be a file')]);
                            $link = upload_file($audio['audio'], 'audios', 'audio');
                        }
                    }else{
                        $link = $audio['audio'];
                    }
                } else {
                    $audio = new Audio();

                    if($request->audios_type == 'files'){
                        if(!is_file($audio['audio']))
                            throw ValidationException::withMessages(['audio' => __('Audio should be a file')]);
                        $link = upload_file($audio['audio'], 'audios', 'audio');
                    }else{
                        $link = $audio['audio'];
                    }
                }

                $audio->title = $audio['title'];
                $audio->audio_type = $request->audios_type;
                $audio->link = $link;

                $audio->save();
            }

            if($request->deleted_audios_ids)
                foreach($request->deleted_audios_ids as $audio_id)
                    Audio::where('id', $audio_id)->delete();

            $poem->audios_count = Audio::where('poem_id', $poem->id)->count();
            $poem->save();

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json($e->getMessage(), 400);
        } catch (\Error $e) {
            DB::rollBack();
            return response()->json($e->getMessage(), 400);
        }

        return response()->json(new PoemResource($poem), 200);
    }

    /**
     * @OA\Delete(
     * path="/admin/poems/{id}",
     * description="Delete entered poem.",
     *     @OA\Parameter(
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         @OA\Examples(example="int", value="1", summary="An int value."),
     *      ),
     * operationId="delete_poem",
     * tags={"Admin - Poems"},
     * security={{"bearer_token":{}}},
     * @OA\Response(
     *    response=200,
     *    description="successful operation"
     * ),
     * )
     *)
    */
    public function destroy(Poem $poem)
    {
        $poem->delete();
        return response()->json(null, 204);
    }

    /**
     * @OA\Post(
     * path="/admin/poems/{id}/activate",
     * description="activate the poem.",
     *   @OA\Parameter(
     *     in="path",
     *     name="id",
     *     required=true,
     *     @OA\Schema(type="string"),
     *   ),
     * tags={"Admin - Poems"},
     * security={{"bearer_token": {} }},
     * @OA\Response(
     *    response=200,
     *    description="successful operation",
     *     ),
     * )
     * )
    */
    public function poems_status_toggle(Poem $poem)
    {
        $poem->update(['status' => !$poem->status]);
        return response()->json(new PoemResource($poem), 200);
    }

    // public function export(Request $request, $type)
    // {
    //     $q = Poem::query()->latest();

    //     if($request->q){
    //         $q->where(function($query) use ($request) {
    //             if (is_numeric($request->q))
    //                 $query->where('id', $request->q);
        
    //             $query->orWhere('title', 'LIKE', '%'.$request->q.'%');
    //         });
    //     }

    //     if($request->status === '0'){
    //         $q->where('status', false);
    //     }else if ($request->status === '1') {
    //         $q->where('status', true);
    //     }

    //     if($request->type)
    //         $q->where('type', $request->type);
        
    //     if($request->poem_type_id)
    //         $q->where('poem_type_id', $request->poem_type_id);
    //     if($request->language_id)
    //         $q->where('language_id', $request->language_id);
    //     if($request->category_id)
    //         $q->where('category_id', $request->category_id);
    //     if($request->occasion_id)
    //         $q->where('occasion_id', $request->occasion_id);
    //     if($request->poet_id)
    //         $q->where('poet_id', $request->poet_id);

    //     $poems = $q->get();

    //     if ($type == 'xlsx') {
    //         return Excel::download(new PoemExport($poems), 'poems.xlsx', \Maatwebsite\Excel\Excel::XLSX);
    //     } else if ($type == 'csv') {
    //         return Excel::download(new PoemExport($poems), 'poems.csv', \Maatwebsite\Excel\Excel::CSV);
    //     } else
    //         return Excel::download(new PoemExport($poems), 'poems.pdf', \Maatwebsite\Excel\Excel::DOMPDF);
    // }
}
