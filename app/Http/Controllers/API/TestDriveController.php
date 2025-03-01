<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\TestDrive;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class TestDriveController extends Controller
{
    // Your existing admin authorization method
    protected function authorizeAdmin()
    {
        $user = auth()->user();
        
        if (!$user || !$user->hasRole('admin')) {
            abort(403, 'Unauthorized action.');
        }
    }

    public function index(Request $request)
    {
        // Regular user request: include car model details with each test drive.
        return $request->user()->testDrives()
            ->with('car')
            ->paginate(10);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'car_id' => 'required|exists:cars,id',
            'scheduled_time' => 'required|date|after:now'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Check if the user already booked a test drive for this car.
        $user = $request->user();
        $existingTestDrive = $user->testDrives()->where('car_id', $request->car_id)->first();
        if ($existingTestDrive) {
            return response()->json([
                'error' => 'You have already booked a test drive for this car.'
            ], 422);
        }

        $testDrive = $user->testDrives()->create([
            'car_id' => $request->car_id,
            'scheduled_time' => $request->scheduled_time,
            'status' => 'pending'
        ]);

        // Return the test drive with the associated car details.
        return response()->json($testDrive->load('car'), 201);
    }

    public function update(Request $request, TestDrive $testDrive)
    {
        $this->authorizeAdmin();

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:approved,rejected',
            'admin_notes' => 'nullable|string',
            'scheduled_time' => 'sometimes|date|after:now'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $testDrive->update($validator->validated());
        return response()->json($testDrive->load(['user', 'car']));
    }

    public function destroy(Request $request, TestDrive $testDrive)
    {
        // Allow admins to delete any
        if ($request->is('api/admin/*')) {
            $this->authorizeAdmin();
            $testDrive->delete();
            return response()->noContent();
        }

        // Allow users to delete their own pending requests
        if ($testDrive->user_id === auth()->id() && $testDrive->status === 'pending') {
            $testDrive->delete();
            return response()->noContent();
        }

        abort(403, 'Unauthorized action.');
    }

    public function userTestDrives()
    {
        $testDrives = Auth::user()->testDrives()->with('car:id,title')->get();
        return response()->json($testDrives);
    }
}
