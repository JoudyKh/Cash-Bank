<?php

namespace App\Services\Admin\User;

use App\Constants\Constants;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;

class UserService
{
    public function index(Request $request)
    {
        $users = User::whereHas('roles', function($q){
            $q->where('name', Constants::USER_ROLE);
        });
        if ($request->has('search') && $request->search) {
            $searchTerm = $request->search;
            
            $users = $users->whereHas('wallet', function ($query) use ($searchTerm) {
                $query->where('number', $searchTerm);
            });
        }
        $users = $users->paginate(config('app.pagination_limit'));
        return UserResource::collection($users);
    }
    public function destroy(User $user)
    {
        $user->delete();
        return true;
    }
}
