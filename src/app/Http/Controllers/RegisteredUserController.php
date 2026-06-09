<?php

namespace App\Http\Controllers;

use App\Actions\Fortify\CreateNewUser;
use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RegisteredUserController extends Controller
{
    public function store(Request $request, CreateNewUser $creator)
    {
        event(new Registered($user = $creator->create($request->all())));

        Auth::login($user);

        return redirect('/email/verify');
    }
}
