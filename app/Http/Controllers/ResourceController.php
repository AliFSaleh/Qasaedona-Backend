<?php

namespace App\Http\Controllers;

use App\Http\Resources\CountryResource;
use App\Http\Resources\OccasionResource;
use App\Http\Resources\PoemTypeResource;
use App\Http\Resources\RawadedResource;
use App\Models\Country;
use App\Models\Occasion;
use App\Models\PoemType;
use App\Models\Rawaded;
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

        return PoemTypeResource::collection($poem_types);
    }
}
