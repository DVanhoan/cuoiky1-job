<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;

class savedJobController extends Controller
{
    public function index()
    {
        $posts = auth()->user()->posts()->with('company')->get();
        return view('account.saved-job', compact('posts'));
    }

    public function store($id)
    {
<<<<<<< HEAD
        $user = User::find(auth()->user()->id);
        $hasPost = $user->posts()->where('id', $id)->get();
        if (count($hasPost)) {
            Alert::toast('You already have saved this job!', 'success');
            return redirect()->back();
=======
        $user = auth()->user();

        $hasPost = $user->posts()->where('id', $id)->exists();

        if ($hasPost) {
            Alert::toast('You have already saved this job!', 'info');
>>>>>>> d2eb0ac4100e695c81459489a196af1aa897593d
        } else {
            $user->posts()->attach($id);
            Alert::toast('Job successfully saved!', 'success');
        }

        return redirect()->route('savedJob.index');
    }

    public function destroy($id)
    {
        $user = auth()->user();

        $user->posts()->detach($id);
        Alert::toast('Deleted saved job!', 'success');

        return redirect()->route('savedJob.index');
    }
}
