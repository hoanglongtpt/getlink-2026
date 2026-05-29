<?php

namespace App\Http\Controllers;

use App\Models\DownloadHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user();

        $histories = DownloadHistory::where('user_id', $user->id)
            ->latest()
            ->take(10)
            ->get();

        return view('profile.show', compact('user', 'histories'));
    }
}
