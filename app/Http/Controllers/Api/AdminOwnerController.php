<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateOwnerRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;

class AdminOwnerController extends Controller
{
    public function index(Request $request){

        $query = User::where('role','owner');

        if($request->filled('search')){
            $search = $request->search;
            $query->where(function ($q) use($search){
                $q->where('name','like',"%{$search}%")
                    ->orWhere('email','like',"%{$search}%");
            });
        }

        switch ($request->sort){
            case 'oldest':
                $query->oldest();
                break;
            case 'latest':
            default:
                $query->latest();
                break;

        }

        $owners = $query->paginate(10);

        return response()->json([
            'success'=>true,
            'message'=> 'Owner fetched successfuly.',
            'data'=> UserResource::collection($owners),
            'pagination'=>[
                'current_page'=> $owners->currentPage(),
                'last_page'=> $owners->lastPage(),
                'per_page'=> $owners->perPage(),
                'total'=> $owners->total(),
            ],
        ],200);
    }
    public function show(User $owner){
        if($owner->role !== 'owner'){
            return response()->json([
                'success'=> false,
                'message'=>'Owner not found',
            ],404);
        }

        return response()->json([
            'success'=>true,
            'message'=>'Owner fetched successfuly',
            'data'=> new UserResource($owner),
        ],200);
    }

    public function update(UpdateOwnerRequest $request, User $owner){
        if($owner->role !== 'owner'){
            return response()->json([
                'success'=>false,
                'message'=>'Owner not found',
            ],404);
        }

        $owner->update($request->validated());

        return response()->json([
            'success'=>true,
            'message'=>'Owner updated successfuly',
            'data'=> new UserResource($owner),
        ]);
    }
    public function block(User $owner){
        if($owner->role!== 'owner'){
            return response()->json([
                'success'=>false,
                'message'=>'Owner not found',
            ],404);
        }

        if($owner->status == 'blocked'){
            return response()->json([
                'success'=>false,
                'message'=>'Owner is already blocked',
            ],400);
        }

        $owner->update([
            'status'=> 'blocked',
        ]);

        return response()->json([
            'success'=>true,
            'message'=>'Owner blocked successfuly',
            'data'=> new UserResource($owner),
        ]);

    }
}
