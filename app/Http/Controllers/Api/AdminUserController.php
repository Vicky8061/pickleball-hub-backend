<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use App\Models\User;

class AdminUserController extends Controller
{
    public function index(Request $request){

        $query = User::where('role','user');

        //search by name/email

        if($request->filled('search')){
            $search = $request->search;

            $query->where(function ($q) use($search){
                $q->where('name','like','%' . $search . '%')
                  ->orWhere('email','like','%'. $search .'%');
            });
        }

        //sort
        switch($request->sort){
            case 'oldest':
                $query->oldest();
                break;
            case 'latest':
                $query->latest();
                break;
            default:
                $query->latest();
                break;

        }

        $users = $query->paginate(10);

        return response()->json([
            'success'=> true,
            'message'=> 'Users fetched successfuly',
            'data'=> UserResource::collection($users),
            'pagination'=>[
                'current_page'=> $users->currentPage(),
                'last_page'=> $users->lastPage(),
                'per_page'=> $users->perPage(),
                'total'=> $users->total(),
            ]
        ],200);
    }
    public function show(User $user){
        //Sirt normal user 
        if($user->role != 'user'){
            return response()->json([
                'success'=>false,
                'message'=> 'User not found',
            ],404);
        }

        return response()->json([
            'success'=>true,
            'message'=> 'User fetched successfuly.',
            'data'=> new UserResource($user)
        ],200);
    }   
    public function update(UpdateUserRequest $request, User $user){
        if($user->role !== 'user'){
            return response()->json([
                'success'=>false,
                'message'=>'User not found',
            ],404);
        }
        $user->update($request->validated());
        
        return response()->json([
            'success'=>true,
            'message'=>'User updated successfuly',
            'data'=> new UserResource($user)
        ],200);
    }
    public function destroy(User $user){
        if($user->role !== 'user'){
            return response()->json([
                'success'=>false,
                'message'=>'User not found',
            ],404);
        }

        //Already blocked user
        if($user->status == 'blocked'){
            return response()->json([
                'success'=>false,
                'message'=>'User is already blocked',
            ],400);
        }
        $user->update([
            'status'=> 'blocked'
        ]);
        return response()->json([
            'success'=>true,
            'message'=>'User blocked Successfuly',
            'data'=> new UserResource($user)
        ],200);
    }
}
