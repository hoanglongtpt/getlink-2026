<?php

namespace App\Http\Controllers;

use App\Models\Resource;
use App\Models\Transaction;
use App\Models\DownloadHistory;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard()
    {
        $totalUsers = \App\Models\User::count();
        $totalTransactions = Transaction::count();
        $totalDownloads = DownloadHistory::count();
        $totalResources = Resource::count();

        return view('admin.dashboard', compact('totalUsers', 'totalTransactions', 'totalDownloads', 'totalResources'));
    }
}
