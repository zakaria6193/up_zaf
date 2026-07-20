<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Format;
use Intervention\Image\ImageManager;

class BusinessController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $businesses = Business::query()
            ->when($request->input('search'), function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString()
            ->through(fn ($business) => [
                'nanoid' => $business->nanoid,
                'name' => $business->name,
                'address' => $business->address,
                'color' => $business->color,
                'logo' => $business->logoUrl(),
                'public_url' => $business->publicUrl(),
                'qr_code' => $business->qrCodeUrl(),
                'is_active' => $business->is_active,
                'created_at' => $business->created_at->format('M d, Y'),
                'total_views' => $business->total_views,
                'views_this_week' => $business->views_this_week,
                'growth_percentage' => (float) $business->growth_percentage,
            ]);

        return Inertia::render('Admin/Businesses/Index', [
            'businesses' => $businesses,
            'filters' => $request->only(['search']),
            'businessUsers' => BusinessUser::orderBy('name')->get(['id', 'name', 'email']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Businesses/Create', [
            'businessUsers' => BusinessUser::orderBy('name')->get(['id', 'name', 'email']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'business_user_id' => ['nullable', 'exists:business_users,id'],
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'numeric', 'between:-180,180'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string'],
            'seo_keywords' => ['nullable', 'string', 'max:255'],
        ]);

        if ($request->hasFile('logo')) {
            $validated['logo'] = $this->processLogo($request->file('logo'));
        }

        $business = Business::create($validated);

        return redirect()->route('admin.businesses.show', $business)
            ->with('success', 'Business created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Business $business): Response
    {
        $business->load(['links', 'menuCategories.items']);

        return Inertia::render('Admin/Businesses/Show', [
            'business' => [
                'nanoid' => $business->nanoid,
                'name' => $business->name,
                'address' => $business->address,
                'lat' => $business->lat,
                'lng' => $business->lng,
                'color' => $business->color,
                'logo' => $business->logoUrl(),
                'public_url' => $business->publicUrl(),
                'qr_code' => $business->qrCodeUrl(),
                'is_active' => $business->is_active,
                'seo_title' => $business->seo_title,
                'seo_description' => $business->seo_description,
                'seo_keywords' => $business->seo_keywords,
                'created_at' => $business->created_at->format('M d, Y'),
                'links' => $business->links,
                'menu_categories' => $business->menuCategories,
            ],
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Business $business): Response
    {
        $business->load(['menuCategories.items']);

        return Inertia::render('Admin/Businesses/Edit', [
            'business' => [
                'nanoid' => $business->nanoid,
                'name' => $business->name,
                'address' => $business->address,
                'lat' => $business->lat,
                'lng' => $business->lng,
                'color' => $business->color,
                'logo' => $business->logoUrl(),
                'seo_title' => $business->seo_title,
                'seo_description' => $business->seo_description,
                'seo_keywords' => $business->seo_keywords,
            ],
            'categories' => $business->menuCategories->map(fn ($category) => [
                'id' => $category->id,
                'name' => $category->name,
                'order' => $category->order,
                'items' => $category->items->map(fn ($item) => [
                    'id' => $item->id,
                    'name' => $item->name,
                    'description' => $item->description,
                    'price' => $item->price,
                    'image' => $item->imageUrl(),
                    'order' => $item->order,
                    'is_active' => $item->is_active,
                ])->values(),
            ])->values(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Business $business): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'numeric', 'between:-180,180'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string'],
            'seo_keywords' => ['nullable', 'string', 'max:255'],
        ]);

        if ($request->hasFile('logo')) {
            // Delete old logo
            if ($business->logo) {
                Storage::disk('public')->delete($business->logo);
            }
            $validated['logo'] = $this->processLogo($request->file('logo'));
        }

        $business->update($validated);

        return redirect()->route('admin.businesses.show', $business)
            ->with('success', 'Business updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Business $business): RedirectResponse
    {
        // Delete associated files
        if ($business->logo) {
            Storage::disk('public')->delete($business->logo);
        }
        if ($business->qr_code) {
            Storage::disk('public')->delete($business->qr_code);
        }

        $business->delete();

        return redirect()->route('admin.businesses.index')
            ->with('success', 'Business deleted successfully!');
    }

    /**
     * Toggle the is_active status of the business.
     */
    public function toggleActive(Business $business): RedirectResponse
    {
        $business->update(['is_active' => ! $business->is_active]);

        return back()->with(
            'success',
            $business->is_active ? 'Business activated successfully!' : 'Business deactivated successfully!'
        );
    }

    /**
     * Process and optimize logo image for web/mobile.
     */
    private function processLogo($file): string
    {
        $manager = ImageManager::usingDriver(Driver::class);

        // Read the uploaded image
        $image = $manager->decodePath($file->getRealPath());

        // Resize to max 400x400 while maintaining aspect ratio
        $image->scale(width: 400, height: 400);

        // Encode with quality optimization (85% quality for good balance)
        $encoded = $image->encodeUsingFormat(Format::JPEG, quality: 85);

        // Generate unique filename
        $filename = 'logos/'.uniqid().'_'.time().'.jpg';

        // Save to storage
        Storage::disk('public')->put($filename, (string) $encoded);

        return $filename;
    }
}
