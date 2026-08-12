<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReviewResource;
use App\Models\Review;
use Illuminate\Http\Request;

class AdminReviewController extends Controller
{
    public function index(Request $request){
        $query = Review::with([
            'user',
            'court',
        ]);
        //search by name,court name or review text
        if($request->filled('search')){
            $search = $request->search;

            $query->where(function ($q) use($search){
                $q->where('review','like',"%{$search}%")
                    ->orwhereHas('user',function ($user) use($search){
                        $user->where('name','like',"%{$search}%");
                    })
                    ->orWhereHas('court',function ($court) use($search){
                        $court->where('name','like',"%{$search}%");
                    });
            });
        }

        //filter by rating
        if($request->filled('rating')){
            $query->where('rating',$request->rating);
        }

        //sorting
        switch ($request->sort){

            case 'oldest':
                $query->oldest();
                break;
            case 'latest':
            default:
                $query->latest();
        }   

        $review = $query->paginate(10);

        return response()->json([
            'success'=>true,
            'message'=>'Reviews fetched successfuly',
            'data'=>ReviewResource::collection($review),
            'pagination'=>[
                'current_page'=>$review->currentPage(),
                'last_page'=> $review->lastPage(),
                'per_page'=> $review->perPage(),
                'total'=> $review->total(),
            ]
        ],200);
    
    }

    public function show(Review $review){
        $review->load([
            'user',
            'court',
        ]);

        return response()->json([
            'success'=> true,
            'message'=> 'Review fetched successfuly',
            'data'=> new ReviewResource($review)
        ],200);

    }

    public function destroy(Review $review){
        $review->delete();

        return response()->json([
            'success'=>true,
            'message'=> 'Review Deleted successuly',
        ],200);
    }
}
