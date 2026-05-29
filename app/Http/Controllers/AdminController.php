<?php

namespace App\Http\Controllers;

use App\Models\DownloadHistory;
use App\Models\Resource;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard()
    {
        $totalUsers = User::count();
        $totalTransactions = Transaction::count();
        $totalDownloads = DownloadHistory::count();
        $totalResources = Resource::count();

        return view('admin.dashboard', compact('totalUsers', 'totalTransactions', 'totalDownloads', 'totalResources'));
    }

    public function users()
    {
        $users = User::orderBy('created_at', 'desc')->paginate(15);

        return view('admin.users', compact('users'));
    }

    public function transactions()
    {
        $transactions = Transaction::with('user')->latest()->paginate(15);

        return view('admin.transactions', compact('transactions'));
    }

    public function resources()
    {
        $resources = Resource::latest()->paginate(15);

        return view('admin.resources', compact('resources'));
    }

    public function settings()
    {
        $downloadFee = Setting::getValue('download_fee', 10);

        return view('admin.settings', compact('downloadFee'));
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'download_fee' => 'required|integer|min:0',
        ]);

        Setting::updateOrCreate(
            ['key' => 'download_fee'],
            ['value' => (string) $request->input('download_fee'), 'group' => 'billing', 'description' => 'Cost in xu per download']
        );

        return back()->with('success', 'Settings updated successfully.');
    }

    public function uploadGoogleServiceAccount(Request $request)
    {
        $request->validate([
            'google_service_account' => 'required|file|mimes:json',
        ]);

        $request->file('google_service_account')->storeAs('', 'google-service-account.json');

        return back()->with('success', 'Google Service Account key uploaded.');
    }

    public function toggleUserStatus(Request $request, User $user)
    {
        if ($request->user()->id === $user->id) {
            return back()->with('error', 'You cannot change your own status.');
        }

        if ($user->blocked_at) {
            $user->blocked_at = null;
            $message = 'User unblocked successfully.';
        } else {
            $user->blocked_at = now();
            $message = 'User blocked successfully.';
        }

        $user->save();

        return back()->with('success', $message);
    }
}
