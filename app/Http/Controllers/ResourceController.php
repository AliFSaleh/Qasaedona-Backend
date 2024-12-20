<?php

namespace App\Http\Controllers;

use App\Http\Resources\CountryResource;
use App\Http\Resources\LessonResource;
use App\Http\Resources\MediaConstantResource;
use App\Http\Resources\OccasionResource;
use App\Http\Resources\PageResource;
use App\Http\Resources\PoemAttributeResource;
use App\Http\Resources\PoetryCollectionResource;
use App\Http\Resources\RawadedResource;
use App\Http\Resources\SliderResource;
use App\Models\Category;
use App\Models\Country;
use App\Models\Language;
use App\Models\Lesson;
use App\Models\MediaConstant;
use App\Models\Occasion;
use App\Models\Page;
use App\Models\PoemType;
use App\Models\PoetryCollection;
use App\Models\Rawaded;
use App\Models\Slider;
use Illuminate\Http\Request;
use Mosab\Translation\Models\Translation;

class ResourceController extends Controller
{
    /**
     * @OA\Get(
     * path="/countries",
     * description="Get countries",
     * operationId="get_countries",
     * tags={"User - Resources"},
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
    public function get_countries(Request $request)
    {
        $request->validate([
            'with_paginate'      => ['integer', 'in:0,1'],
            'per_page'           => ['integer', 'min:1'],
            'q'                  => ['string']
        ]);

        $q = Country::query()->latest();

        if($request->q)
        {
            $countries_ids = Translation::where('translatable_type', Country::class)
                                        ->where('attribute', 'name')
                                        ->where('value', 'LIKE', '%'.$request->q.'%')
                                        ->groupBy('translatable_id')
                                        ->pluck('translatable_id');
            $q->whereIn('id', $countries_ids);
        }

        if($request->with_paginate === '0')
            $countries = $q->get();
        else
            $countries = $q->paginate($request->per_page ?? 10);

        return CountryResource::collection($countries);
    }

    /**
     * @OA\Get(
     * path="/occasions",
     * description="Get occasions",
     * operationId="get_occasions",
     * tags={"User - Resources"},
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
    public function get_occasions(Request $request)
    {
        $request->validate([
            'with_paginate'      => ['integer', 'in:0,1'],
            'per_page'           => ['integer', 'min:1'],
            'featured'           => ['integer', 'in:1,0'],
            'q'                  => ['string']
        ]);

        $q = Occasion::query()->latest();

        if($request->q){
            $q->where(function($query) use ($request) {
                if (is_numeric($request->q))
                    $query->where('id', $request->q);
        
                $query->orWhere('title', 'LIKE', '%'.$request->q.'%');
            });
        }

        $q->where('status', true);

        if($request->with_paginate === '0')
            $occasions = $q->get();
        else
            $occasions = $q->paginate($request->per_page ?? 10);

        return OccasionResource::collection($occasions);
    }

    /**
     * @OA\Get(
     * path="/rawadeds",
     * description="Get rawadeds",
     * operationId="get_rawadeds",
     * tags={"User - Resources"},
     * @OA\Parameter(
     *     in="query",
     *     name="with_paginate",
     *     required=false,
     *     @OA\Schema(type="integer",enum={0, 1})
     *   ),
     * @OA\Parameter(
     *     in="query",
     *     name="featured",
     *     required=false,
     *     @OA\Schema(type="integer",enum={0, 1})
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
    public function get_rawadeds(Request $request)
    {
        $request->validate([
            'with_paginate'      => ['integer', 'in:0,1'],
            'per_page'           => ['integer', 'min:1'],
            'featured'           => ['integer', 'in:1,0'],
            'q'                  => ['string']
        ]);

        $q = Rawaded::query()->latest();
        $q->where('status', true);

        if($request->q)
            $q->where('name', 'LIKE', '%'.$request->q.'%');

        if($request->featured === '0'){
            $q->where('featured', false);
        }else if ($request->featured === '1') {
            $q->where('featured', true);
        }

        if($request->with_paginate === '0')
            $rawadeds = $q->get();
        else
            $rawadeds = $q->paginate($request->per_page ?? 10);

        return RawadedResource::collection($rawadeds);
    }
    
    /**
     * @OA\Get(
     * path="/poem_types",
     * description="Get poem_types",
     * operationId="get_poem_types",
     * tags={"User - Resources"},
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
    public function get_poem_types(Request $request)
    {
        $request->validate([
            'with_paginate'      => ['integer', 'in:0,1'],
            'per_page'           => ['integer', 'min:1'],
            'q'                  => ['string']
        ]);

        $q = PoemType::query()->latest();

        if($request->q)
            $q->where('name', 'LIKE', '%'.$request->q.'%');

        if($request->with_paginate === '0')
            $poem_types = $q->get();
        else
            $poem_types = $q->paginate($request->per_page ?? 10);

        return PoemAttributeResource::collection($poem_types);
    }
    
    /**
     * @OA\Get(
     * path="/categories",
     * description="Get categories",
     * operationId="get_categories",
     * tags={"User - Resources"},
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
    public function get_categories(Request $request)
    {
        $request->validate([
            'with_paginate'      => ['integer', 'in:0,1'],
            'per_page'           => ['integer', 'min:1'],
            'q'                  => ['string']
        ]);

        $q = Category::query()->latest();

        if($request->q)
            $q->where('name', 'LIKE', '%'.$request->q.'%');

        if($request->with_paginate === '0')
            $categories = $q->get();
        else
            $categories = $q->paginate($request->per_page ?? 10);

        return PoemAttributeResource::collection($categories);
    }
    
    /**
     * @OA\Get(
     * path="/languages",
     * description="Get languages",
     * operationId="get_languages",
     * tags={"User - Resources"},
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
    public function get_languages(Request $request)
    {
        $request->validate([
            'with_paginate'      => ['integer', 'in:0,1'],
            'per_page'           => ['integer', 'min:1'],
            'q'                  => ['string']
        ]);

        $q = Language::query()->latest();

        if($request->q)
            $q->where('name', 'LIKE', '%'.$request->q.'%');

        if($request->with_paginate === '0')
            $languages = $q->get();
        else
            $languages = $q->paginate($request->per_page ?? 10);

        return PoemAttributeResource::collection($languages);
    }
    
    /**
     * @OA\Get(
     * path="/lessons",
     * description="Get lessons",
     * operationId="get_lessons",
     * tags={"User - Resources"},
     * @OA\Parameter(
     *    in="query",
     *    name="poet_id",
     *    required=false,
     *    @OA\Schema(type="integer"),
     * ),
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
    public function get_lessons(Request $request)
    {
        $request->validate([
            'poet_id'           => ['integer', 'exists:users,id'],
            'with_paginate'      => ['integer', 'in:0,1'],
            'per_page'           => ['integer', 'min:1'],
            'q'                  => ['string']
        ]);

        $q = Lesson::query()->latest();
        $q->where('status', true);

        if($request->poet_id)
            $q->where('poet_id', $request->poet_id);

        if($request->q)
            $q->where('title', 'LIKE', '%'.$request->q.'%');

        if($request->with_paginate === '0')
            $lessons = $q->get();
        else
            $lessons = $q->paginate($request->per_page ?? 10);

        return LessonResource::collection($lessons);
    }
    
    /**
     * @OA\Get(
     * path="/poetry_collections",
     * description="Get poetry_collections",
     * operationId="get_poetry_collections",
     * tags={"User - Resources"},
     * @OA\Parameter(
     *    in="query",
     *    name="poet_id",
     *    required=false,
     *    @OA\Schema(type="integer"),
     * ),
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
    public function get_poetry_collections(Request $request)
    {
        $request->validate([
            'poet_id'           => ['integer', 'exists:users,id'],
            'with_paginate'      => ['integer', 'in:0,1'],
            'per_page'           => ['integer', 'min:1'],
            'q'                  => ['string']
        ]);

        $q = PoetryCollection::query()->latest();
        $q->where('status', true);

        if($request->poet_id)
            $q->where('poet_id', $request->poet_id);

        if($request->q)
            $q->where('title', 'LIKE', '%'.$request->q.'%');

        if($request->with_paginate === '0')
            $poetry_collections = $q->get();
        else
            $poetry_collections = $q->paginate($request->per_page ?? 10);

        return PoetryCollectionResource::collection($poetry_collections);
    }
    
    /**
     * @OA\Get(
     * path="/pages",
     * description="Get pages",
     * operationId="get_pages",
     * tags={"User - Resources"},
     * @OA\Parameter(
     *    in="query",
     *    name="key",
     *    required=false,
     *    @OA\Schema(type="string", enum={"about_us","privacy_policy","terms_and_condition","refund_policy","rewards_rules"}),
     * ),
     *   @OA\Response(
     *     response=200,
     *     description="Success",
     *  ),
     *  )
    */
    public function get_pages(Request $request)
    {
        $request->validate([
            'key'   => ['string', 'in:about_us,privacy_policy,terms_and_condition,submit_poem_description,join_us_as_poet_description,default_rejection_reason']
        ]);

        $q = Page::query();

        if($request->key)
            $q->where('key', $request->key);
        $pages = $q->get();

        return PageResource::collection($pages);
    }

    /**
     * @OA\Get(
     * path="/sliders",
     * description="Get sliders",
     * operationId="get_sliders",
     * tags={"User - Resources"},
     *   @OA\Response(
     *     response=200,
     *     description="Success",
     *  ),
     *  )
    */
    public function get_sliders()
    {
        $q = Slider::query()->latest();
        $sliders = $q->get();

        return SliderResource::collection($sliders);
    }

    /**
     * @OA\Get(
     * path="/media_constants",
     * description="Get media_constants",
     * operationId="get_media_constants",
     * tags={"User - Resources"},
     *   @OA\Response(
     *     response=200,
     *     description="Success",
     *  ),
     *  )
    */
    public function get_media_constants()
    {
        $q = MediaConstant::latest();
        $media_constants = $q->get();

        return MediaConstantResource::collection($media_constants);
    }
}
