<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateTournamentRequest;
use App\Http\Resources\TournamentResource;
use App\Models\Tournament;
use Illuminate\Http\Request;

class AdminTournamentController extends Controller
{
    public function index(Request $request){
        $query = Tournament::with([
            'owner',
            'court',
        ]);

        //Search
        if($request->filled('search')){
            $search = $request->search;

            $query->where(function ($q) use ($search){
                $q->where('title','like',"%{$search}%")
                    ->orWhereHas('court',function ($court) use ($search){
                        $court->where('name','like',"%{$search}%");
                    });
            });
        }

        //Filter status
        if($request->filled('status')){
            $query->where('status',$request->status);
        }

        //Sorting
        switch ($request->sort){
            case 'oldest':
                $query->oldest();
                break;
            case 'latest':
            default:
                $query->latest();
                break;
        }

        $tournament = $query->paginate(10);

        return response()->json([
            'success'=>true,
            'message'=>'Tournament fetched successfuly',
            'data'=> TournamentResource::collection($tournament),
            'pagination'=>[
                'current_page'=> $tournament->currentPage(),
                'last_page'=> $tournament->lastPage(),
                'per_page'=> $tournament->perPage(),
                'total'=> $tournament->total(),
            ]
        ],200);

    }
    public function show(Tournament $tournament){
        $tournament->load([
            'owner',
            'court',
            'participants.user'
        ]);
        return response()->json([
            'success'=>true,
            'message'=>'Tournament fetched successfuly',
            'data'=> new TournamentResource($tournament),
        ],200);

    }
    public function update(UpdateTournamentRequest $request, Tournament $tournament){
        $tournament->update($request->validated());
        return response()->json([
            'success'=> true,
            'message'=> 'Tournament updated successfuly',
            'data'=>new TournamentResource(
                $tournament->fresh()->load([
                    'owner',
                    'court',
                ])
            ),
        ],200);
    }
    public function destroy(Tournament $tournament){
        if($tournament->status == 'cancelled'){
            return response()->json([
                'success'=>false,
                'message'=>'Tournament is already cancelled'
            ],400);

        }

        $tournament->update([
            'status'=> 'cancelled'
        ]);

        return response()->json([
            'success'=>true,
            'message'=>'Tournament cancelled successfuly',
            'data'=> new TournamentResource($tournament)
        ],200);
    }


}
