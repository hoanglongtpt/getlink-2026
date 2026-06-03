<?php

namespace App\Http\Controllers;

use App\Models\DownloadHistory;
use App\Models\Resource;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\User;
use App\Services\Web2mService;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    protected Web2mService $web2mService;

    public function __construct(Web2mService $web2mService)
    {
        $this->web2mService = $web2mService;
    }
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
        $googleDriveEmail = Setting::getValue('google_drive_oauth_account');
        $packages = $this->web2mService->getPackageInfo();

        return view('admin.settings', compact('downloadFee', 'googleDriveEmail', 'packages'));
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'download_fee' => 'required|integer|min:0',
            'packages' => 'required|array|min:1',
            'packages.*.name' => 'required|string|max:80',
            'packages.*.amount_vnd' => 'required|integer|min:1000',
            'packages.*.xu_main' => 'required|integer|min:0',
            'packages.*.xu_bonus' => 'nullable|integer|min:0',
            'packages.*.description' => 'nullable|string|max:255',
            'packages.*.is_popular' => 'nullable|in:0,1',
        ]);

        $packageData = [];
        foreach ($request->input('packages', []) as $package) {
            $packageData[] = [
                'name' => trim($package['name']),
                'amount_vnd' => (int) $package['amount_vnd'],
                'xu_main' => (int) $package['xu_main'],
                'xu_bonus' => (int) ($package['xu_bonus'] ?? 0),
                'description' => trim($package['description'] ?? ''),
                'is_popular' => isset($package['is_popular']) && $package['is_popular'] === '1',
            ];
        }

        // Ensure only one package is marked popular
        $hasPopular = false;
        foreach ($packageData as &$package) {
            if ($package['is_popular'] && ! $hasPopular) {
                $hasPopular = true;
                continue;
            }
            $package['is_popular'] = false;
        }
        unset($package);

        Setting::updateOrCreate(
            ['key' => 'download_fee'],
            ['value' => (string) $request->input('download_fee'), 'group' => 'billing', 'description' => 'Cost in xu per download']
        );

        Setting::updateOrCreate(
            ['key' => 'payment_packages'],
            ['value' => json_encode($packageData, JSON_UNESCAPED_UNICODE), 'group' => 'payment', 'description' => 'Web2M top-up package definitions']
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
