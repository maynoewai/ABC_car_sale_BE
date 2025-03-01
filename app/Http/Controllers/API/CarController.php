<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Car;
use App\Models\CarImage;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Cloudinary\Configuration\Configuration;
use Cloudinary\Api\Upload\UploadApi;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Auth;


class CarController extends Controller
{
    public function index(Request $request)
    {
        return Car::with(['images', 'user', 'bids'])
            ->filter($request->all())
            ->paginate(10);
    }

    public function store(Request $request)
    {
        // Authentication check
        if (!$request->user()) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }
    
      // Validate the request with all fields from the migration
    $validated = $request->validate([
        'title'             => 'required|string|max:255',
        'make'              => 'required|string|max:255',
        'model'             => 'required|string|max:255',
        'year'              => 'required|integer|min:1900',
        'price'             => 'required|numeric|min:0',
        'description'       => 'nullable|string',
        'mileage'           => 'nullable|numeric',
        'mileage_unit'      => 'nullable|string|max:50',
        'fuel_type'         => 'nullable|string|max:50',
        'transmission'      => 'nullable|string|max:50',
        'owner_number'      => 'nullable|string|max:50',
        'color'             => 'nullable|string|max:50',
        'body_type'         => 'nullable|string|max:50',
        'location'         => 'nullable|string|max:50',
        'registration_year' => 'nullable|integer|min:1900',
        'insurance_validity'=> 'nullable|date',
        'engine_cc'         => 'nullable|string|max:50',
        'variant'           => 'nullable|string|max:255',
        'power_windows'     => 'nullable|boolean',
        'abs'               => 'nullable|boolean',
        'airbags'           => 'nullable|boolean',
        'sunroof'           => 'nullable|boolean',
        'navigation'        => 'nullable|boolean',
        'rear_camera'       => 'nullable|boolean',
        'leather_seats'     => 'nullable|boolean',
        'images'            => 'required|array|max:5',
        'images.*'          => 'image|mimes:jpeg,png,jpg|max:2048',
    ]);

    Log::warning('Request data: ' . json_encode($request->all()));

        // Configure Cloudinary (add to config/services.php for production)
        Configuration::instance([
            'cloud' => [
                'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
                'api_key' => env('CLOUDINARY_API_KEY'),
                'api_secret' => env('CLOUDINARY_API_SECRET'),
            ],
            'http' => [
        'http_client' => new Client(['verify' => false])
    ]
        ]);
    
        try {
            // Create car record
            $car = $request->user()->cars()->create($validated);
    
            // Upload images
            $uploadedImages = [];
            foreach ($request->file('images') as $image) {
                $uploadResult = (new UploadApi())->upload(
                    $image->getRealPath(),
                    [
                        'folder' => 'car_listings',
                        'transformation' => [
                            'width' => 800,
                            'height' => 600,
                            'crop' => 'limit',
                            'quality' => 'auto'
                        ]
                    ]
                );
    
                $uploadedImages[] = [
                    'url' => $uploadResult['secure_url'],
                    'public_id' => $uploadResult['public_id']
                ];
            }
    
            // Attach images to car
            $car->images()->createMany($uploadedImages);
    
            return response()->json([
                'message' => 'Car listing created successfully',
                'data' => $car->load('images')
            ], 201);
    
        } catch (\Exception $e) {
            Log::error('Car listing creation failed: ' . $e->getMessage());
            
            // Delete partially created car record if it exists
            if (isset($car) && $car->exists) {
                $car->delete();
            }
    
            return response()->json([
                'message' => 'Car listing creation failed',
                'error' => env('APP_ENV') === 'local' ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
    public function show(Car $car)
    {
        return $car->load(['images', 'user', 'bids']);
    }

    public function update(Request $request, Car $car)
    {
        // Check if the authenticated user is the owner of the car.
        if ($request->user()->id !== $car->user_id) {
            return response()->json(['message' => 'Only Car Owner can edit listing'], 403);
        }
    
        $validated = $request->validate([
            'title'       => 'string|max:255',
            'make'        => 'string|max:255',
            'model'       => 'string|max:255',
            'year'        => 'integer|min:1900',
            'price'       => 'numeric|min:0',
            'description' => 'string',
        ]);
    
        $car->update($validated);
        return response()->json($car);
    }
    

    public function destroy($id)
    {
        $car = Car::find($id);
    
        if (!$car) {
            return response()->json(['message' => 'Car not found'], 404);
        }
    
        // $this->authorize('delete', $car); 
    
        foreach ($car->images as $image) {
            Cloudinary::destroy($image->public_id);
        }
    
        $car->delete($id);
        return response()->json(['message' => 'Car Deleted Succesfully'], 404);
    }

    public function carListings()
{
    // Retrieve the authenticated user's cars with only id and title.
    $carsForSale = Auth::user()->cars()->select('id', 'title' ,"price","model","year" , "created_at","make")->get();
    return response()->json($carsForSale);
}
}