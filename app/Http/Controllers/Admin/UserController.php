<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $search = $request->input('search');

        $users = BusinessUser::query()
            ->withCount('businesses')
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return Inertia::render('Admin/Users/Index', [
            'users' => $users->through(fn ($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'businesses_count' => $user->businesses_count,
                'created_at' => $user->created_at->format('d/m/Y'),
            ]),
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Users/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:business_users,email'],
            'phone' => ['nullable', 'string', 'max:20', 'unique:business_users,phone'],
            'password' => ['required', Password::defaults()],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        BusinessUser::create($validated);

        return redirect()->route('admin.users.index')
            ->with('success', 'Business user created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(BusinessUser $user): Response
    {
        $user->load(['businesses' => function ($query) {
            $query->orderBy('created_at', 'desc');
        }]);

        return Inertia::render('Admin/Users/Show', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'created_at' => $user->created_at->format('d F Y'),
            ],
            'businesses' => $user->businesses->map(fn ($business) => [
                'nanoid' => $business->nanoid,
                'name' => $business->name,
                'address' => $business->address,
                'is_active' => $business->is_active,
                'created_at' => $business->created_at->format('d/m/Y'),
            ]),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BusinessUser $user): Response
    {
        return Inertia::render('Admin/Users/Edit', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BusinessUser $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:business_users,email,'.$user->id],
            'phone' => ['nullable', 'string', 'max:20', 'unique:business_users,phone,'.$user->id],
            'password' => ['nullable', Password::defaults()],
        ]);

        if (! empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('admin.users.index')
            ->with('success', 'Business user updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BusinessUser $user): RedirectResponse
    {
        // Check if user has businesses
        if ($user->businesses()->count() > 0) {
            return back()->withErrors([
                'user' => 'Cannot delete user with associated businesses. Please reassign or delete businesses first.',
            ]);
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Business user deleted successfully!');
    }
}
