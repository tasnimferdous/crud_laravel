<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Intervention\Image\Facades\Image;

class UserController extends Controller
{
    public function storeAvatar(Request $request){
        $request->validate([
            'avatar' => 'required|image|max:3000',
        ]);

        $user = auth()->user();
        $fileName = $user->id . '_' . uniqid() . '.jpg';
        $imgData = Image::make($request->file('avatar'))->fit(120)->encode('jpg');
        Storage::put('public/avatars/'.$fileName, $imgData);

        $oldAvatar = $user->avatar;
        $user->avatar = $fileName;
        $user->save();

        if($oldAvatar != "/fallback-avatar.jpg"){
            Storage::delete(str_replace("/storage/", "public/", $oldAvatar));
        }
        return redirect('/profile/'.auth()->user()->username)->with('success','Successfully uploaded!');
    }

    public function showAvatarForm(){
        return view('avatar-form');
    }

    public function profile(User $user){
        $context = [
            'username' => $user->username,
            'avatar' => $user->avatar,
            'posts' => $user->posts()->latest()->get(),
            'postCount' => $user->posts()->count(),
        ];
        return view('profile-posts', $context);
    }

    //================================================
    public function logout(){
        auth()->logout();
        return redirect('/')->with('success', 'You are now logged out!!');
    }

    public function showCorrectHomePage(){
        if(auth()->check()){
            return view('homepage-feed');
        }
        return view('homepage');
    }

    public function login(Request $request){
        $incomingFields = $request->validate([
            'loginusername' => 'required',
            'loginpassword' => 'required'
        ]);
        $data = [
            'username'=>$incomingFields['loginusername'],
            'password'=>$incomingFields['loginpassword']
        ];
        if(auth()->attempt($data)){
            $request->session()->regenerate();
            return redirect('/')->with('success', 'You are successfully logged in!');
        }else{
            return redirect('/')->with('invalid', 'Sorry! Invalid credentials!');
        }
    }

    public function register(Request $request){
        $incomingFields = $request->validate([
            'username' => ['required','min:3', 'max:20', Rule::unique('users', 'username')],
            'email' => ['required', 'email', Rule::unique('users', 'email')],
            'password' => ['required', 'min:6', 'confirmed']
        ]);
        $newUser = User::create($incomingFields);
        auth()->login($newUser);
        return redirect('/')->with('success', 'Successfully created the account!');
    }
}
