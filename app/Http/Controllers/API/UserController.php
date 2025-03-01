<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Display the authenticated user's data.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show()
    {
        // Get the currently authenticated user.
        $user = Auth::user();

        return response()->json(['data' => $user]);
    }

    /**
     * Update the authenticated user's data.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        // Get the currently authenticated user.
        $user = Auth::user();

        // Validate the incoming request data.
        $validator = Validator::make($request->all(), [
            'name'     => 'sometimes|required|string|max:255',
            'email'    => 'sometimes|required|email|max:255|unique:users,email,' . $user->id,
            // 'password' => 'sometimes|required|string|min:8|confirmed',
            // // Only include role if you want users to be able to update their role.
            // // Caution: Allowing users to update their own role might lead to security issues.
            // 'role'     => 'sometimes|required|in:user,admin',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Update each attribute if provided.
        if ($request->has('name')) {
            $user->name = $request->input('name');
        }

        if ($request->has('email')) {
            $user->email = $request->input('email');
        }

        if ($request->has('password')) {
            // Hash the new password before saving.
            $user->password = bcrypt($request->input('password'));
        }

        if ($request->has('role')) {
            // Only update role if you are sure this should be allowed.
            // Otherwise, remove this block to prevent users from changing their role.
            $user->role = $request->input('role');
        }

        $user->save();

        return response()->json([
            'message' => 'User updated successfully.',
            'data'    => $user,
        ]);
    }

    /**
     * Delete the authenticated user's account.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy()
    {
        // Get the currently authenticated user.
        $user = Auth::user();

        // Delete the user's account.
        $user->delete();

        return response()->json(['message' => 'User deleted successfully.']);
    }
}
