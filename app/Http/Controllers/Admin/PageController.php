<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\PageResource;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('permission:pages.read')->only(['index']);
        $this->middleware('permission:pages.write')->only(['update']);
    }

    /**
     * @OA\Get(
     * path="/admin/pages",
     * description="Get all pages",
     * operationId="get_all_pages",
     * tags={"Admin - Pages"},
     *  security={{"bearer_token": {} }},
     * @OA\Parameter(
     *    in="query",
     *    name="key",
     *    required=false,
     *    @OA\Schema(type="string", enum={"about_us","privacy_policy","terms_and_condition","submit_poem_description","join_us_as_poet_description","default_rejection_reason"}),
     * ),
     *   @OA\Response(
     *     response=200,
     *     description="Success",
     *  ),
     *  )
    */
    public function index(Request $request)
    {
        $request->validate([
            'key'   => ['string', 'in:about_us,privacy_policy,terms_and_condition,submit_poem_description,join_us_as_poet_description,default_rejection_reason']
        ]);

        $q = Page::with('media');

        if($request->key)
            $q->where('key', $request->key);
        $pages = $q->get();

        return PageResource::collection($pages);
    }

    /**
     * @OA\Post(
     * path="/admin/pages/{id}",
     * description="Edit page.",
     *   @OA\Parameter(
     *     in="path",
     *     name="id",
     *     required=true,
     *     @OA\Schema(type="string"),
     *   ),
     *  tags={"Admin - Pages"},
     *  security={{"bearer_token": {} }},
     *   @OA\RequestBody(
     *       required=true,
     *       @OA\MediaType(
     *           mediaType="multipart/form-data",
     *           @OA\Schema(
     *              @OA\Property(property="content", type="string"),
     *              @OA\Property(property="image", type="file"),
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
    public function update(Request $request, Page $page)
    {
        $request->validate([
            'content'     => ['required', 'string'],
            'image'       => [],
        ]);

        $image = null;
        if($request->image){
            if($request->image == $page->image){
                $image = $page->image;
            }else{
                if(!is_file($request->image))
                    throw ValidationException::withMessages(['image' => __('error_messages.Image should be a file')]);
                $image = upload_file($request->image, 'pages', 'page');
            }
        }

        $page->update([
            'content'    => $request->content,
            'image'      => $image,
        ]);

        return response()->json(new PageResource($page), 200);
    }
}
