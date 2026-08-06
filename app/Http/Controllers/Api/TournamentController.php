<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tournament;
use App\Models\Court;
use App\Models\TournamentParticipant;
use App\Http\Resources\TournamentResource;
use App\Http\Resources\TournamentParticipantResource;
use App\Http\Requests\StoreTournamentRequest;
use App\Http\Requests\UpdateTournamentRequest;
use Carbon\Carbon;

class TournamentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tournaments = Tournament::with([
            'owner',
            'court.owner',
            'court.images',
            'participants.user',
        ])->latest()->get();

        return response()->json([
            'success'=> true,
            'message'=> 'Tournaments fetched successfuly',
            'data'=> TournamentResource::collection($tournaments)
        ],200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTournamentRequest $request)
    {
        $court = Court::findOrFail($request->court_id);

        if($court->owner_id != $request->user()->id){
            return response()->json([
                'success'=> false,
                'message'=> 'You are not authorized to create a tournament for this court',
            ],403);
        }

        $tournament = Tournament::create([
            'owner_id'=> $request->user()->id,
            'court_id'=> $request->court_id,
            'title'=> $request->title,
            'description'=> $request->description,
            'banner'=> $request->banner,
            'tournament_date'=> $request->tournament_date,
            'registration_last_date'=> $request->registration_last_date,
            'start_time'=> $request->start_time,
            'end_time'=> $request->end_time,
            'entry_fee'=> $request->entry_fee,
            'max_participants'=> $request->max_participants,
            'prize'=> $request->prize,
            'status'=> 'upcoming',
        ]);
        $tournament->load([
            'owner',
            'court.owner',
            'court.images',
        ]);
        return response()->json([
            'success'=> true,
            'message'=> 'Tournament created successfuly',
            'data'=> new TournamentResource($tournament)
        ],201);

    }

    /**
     * Display the specified resource.
     */
    public function show(Tournament $tournament)
    {
        $tournament->load([
            'owner',
            'court.owner',
            'court.images',
            'participants.user',
        ]);
        return response()->json([
            'success'=> true,
            'message'=> 'Tournament fetched successfuly',
            'data'=> new TournamentResource($tournament)
        ],200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTournamentRequest $request, Tournament $tournament)
    {

        if($tournament->owner_id != $request->user()->id){
            return response()->json([
                'success'=>false,
                'message'=> 'You are not authorized to update this tournament',
            ],403);
        }
        $tournament->update($request->validated());
        $tournament->load([
            'owner',
            'court.owner',
            'court.images',
            'participants.user',
        ]);
        return response()->json([
            'success'=>true,
            'message'=> 'Tournament updated successfuly',
            'data'=> new TournamentResource($tournament),
        ],200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tournament $tournament, Request $request)
    {
        if($tournament->owner_id != $request->user()->id){
            return response()->json([
                'success'=>false,
                'message'=> 'You are not authorized to delete this tournament',
            ],403);
        }
        $tournament->update(['status'=> 'cancelled']);
        return response()->json([
            'success'=>true,
            'message'=> 'Tournament cancelled successfuly',
        ],200);
    }

    public function myTournaments(Request $request)
    {
        $tournaments = Tournament::with([
            'owner',
            'court.owner',
            'court.images',
            'participants.user',
        ])->where('owner_id', $request->user()->id)->latest()->get();

        return response()->json([
            'success'=> true,
            'message'=> 'My tournaments fetched successfuly',
            'data'=> TournamentResource::collection($tournaments)
        ],200);
    }

    public function joinTournament(Tournament $tournament, Request $request)
    {
        //Already joined?
        $exist = TournamentParticipant::where('tournament_id', $tournament->id)
            ->where('user_id', $request->user()->id)
            ->first();
        if($exist){
            return response()->json([
                'success'=>false,
                'message'=> 'You have already joined this tournament',
            ],400);
        }

        //Tournament cancelled/completed?
        if(in_array($tournament->status, ['cancelled','completed'])){
            return response()->json([
                'success'=>false,
                'message'=> 'Tournament is not available for registration',
            ],400);
        }

        //Registration closed?
        if(Carbon::today()->greaterThan(Carbon::parse($tournament->registration_last_date))){
            return response()->json([
                'success'=>false,
                'message'=> 'Registration for this tournament is closed',
            ],400);
        }

        //tournament full?
        if($tournament->participants()->count() >= $tournament->max_participants){
            return response()->json([
                'success'=>false,
                'message'=>'Tournament is full.'
            ],400);
        }

        $participant = TournamentParticipant::create([
            'tournament_id'=> $tournament->id,
            'user_id'=> $request->user()->id,
            'payment_status'=> 'pending',
        ]);

        $participant->load([
            'user',
        ]);

        return response()->json([
            'success'=>true,
            'message'=> 'You have successfully joined the tournament',
            'data'=> new TournamentParticipantResource($participant)
        ],200);
    }

    public function leaveTournament(Tournament $tournament, Request $request){
        $participant = TournamentParticipant::where('tournament_id',$tournament->id)
            ->where('user_id',$request->user()->id)
            ->first();
        
        if(!$participant){
            return response()->json([
                'success'=> false,
                'message'=> 'You are not participant of this tournament',
            ],404);
        }
        $participant->delete();

        return response()->json([
            'success'=>true,
            'message'=>'Tournament left successfuly',
        ],200);
    }
    
    public function myJoinedTournaments(Request $request){
        $participants = TournamentParticipant::with([
            'tournament.owner',
            'tournament.court.owner',
            'tournament.court.images',
        ])
        ->where('user_id', $request->user()->id)
        ->latest()
        ->get();

        return response()->json([
            'success'=>true,
            'message'=> 'Joined Tournaments fetched successfuly',
            'data'=> TournamentParticipantResource::collection($participants),
        ],200);
    }
}
