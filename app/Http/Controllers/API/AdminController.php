<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Car;
use App\Models\Bid;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }
    
    protected function authorizeAdmin()
    {
        $user = auth()->user();

        \Log::debug('Authenticated User:', [
            'id'    => $user->id ?? null,
            'email' => $user->email ?? null,
            'role'  => $user->role ?? null,
        ]);

        if (!$user) {
            abort(403, 'Unauthorized: No user found');
        }

        if (!$user->hasRole('admin')) {
            abort(403, 'Unauthorized: User does not have admin role');
        }
    }
    
    /**
     * Get all registered users (excluding current admin).
     */
    public function users(Request $request)
    {
        $this->authorizeAdmin();
        $users = User::with('roles')
            ->where('id', '<>', auth()->id())
            ->paginate(10);

        return response()->json([
            'data' => $users->items(),
            'pagination' => [
                'current_page' => $users->currentPage(),
                'per_page'     => $users->perPage(),
                'total'        => $users->total(),
                'last_page'    => $users->lastPage(),
            ]
        ]);
    }

    /**
     * Delete a user.
     */
    public function deleteUser(User $user)
    {
        $this->authorizeAdmin();
        if ($user->id === auth()->id()) {
            return response()->json(['message' => 'You cannot delete yourself.'], 403);
        }
        $user->delete();
        return response()->json(['message' => 'User deleted successfully.']);
    }

    /**
     * Get all cars (admin view).
     */
    public function allCars(Request $request)
    {
        $this->authorizeAdmin();
        $cars = Car::with(['images', 'user'])
            ->filter($request->all())
            ->paginate(10);

        return response()->json([
            'data' => $cars->items(),
            'pagination' => [
                'current_page' => $cars->currentPage(),
                'per_page'     => $cars->perPage(),
                'total'        => $cars->total(),
                'last_page'    => $cars->lastPage(),
            ]
        ]);
    }

    /**
     * Delete a car (admin only).
     */
    public function deleteCar(Car $car)
    {
        $this->authorizeAdmin();
        foreach ($car->images as $image) {
            Cloudinary::destroy($image->public_id);
        }
        $car->delete();
        return response()->json(['message' => 'Car deleted successfully.']);
    }

    /**
     * Update a user's role.
     */
    public function updateUserRole(Request $request, User $user)
    {
        $this->authorizeAdmin();
        $validator = Validator::make($request->all(), [
            'role' => 'required|in:user,admin',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user->role = $request->role;
        $user->save();

        return response()->json(['message' => 'User role updated successfully.']);
    }

    /**
     * Show details of a specific car.
     */
    public function showCar(Car $car)
    {
        $this->authorizeAdmin();
        return response()->json([
            'data' => $car->load(['images', 'user']),
        ]);
    }

    /**
     * Get all bids (admin view).
     */
    public function allBids(Request $request)
    {
        $this->authorizeAdmin();
        $bids = Bid::with(['user', 'car'])->paginate(10);

        return response()->json([
            'data' => $bids->items(),
            'pagination' => [
                'current_page' => $bids->currentPage(),
                'per_page'     => $bids->perPage(),
                'total'        => $bids->total(),
                'last_page'    => $bids->lastPage(),
            ]
        ]);
    }

    /**
     * Update a bid's status.
     * If a bid is approved, update the corresponding car's status to 'sold'.
     */
    public function updateBid(Request $request, Bid $bid)
    {
        $this->authorizeAdmin();

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:approved,rejected,pending',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $bid->status = $request->status;
        $bid->save();

        // If the bid is approved, update the car's status to 'sold'
        if ($request->status === 'approved') {
            $car = $bid->car;
            $car->status = 'sold';
            $car->save();
        }

        return response()->json([
            'message' => 'Bid status updated successfully.',
            'data'    => $bid->load(['user', 'car']),
        ]);
    }

    /**
     * Delete a bid.
     */
    public function deleteBid(Bid $bid)
    {
        $this->authorizeAdmin();
        $bid->delete();
        return response()->json(['message' => 'Bid deleted successfully.']);
    }
}
