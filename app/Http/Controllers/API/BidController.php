<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Car;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BidController extends Controller
{
    public function store(Request $request, Car $car)
    {
        // Ensure the user is authenticated.
        if (!$request->user()) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }
    
        // Prevent the car owner from bidding on their own car.
        if ($car->user_id === $request->user()->id) {
            return response()->json(['message' => 'You cannot bid on your own car.'], 403);
        }
    
        // Retrieve the highest bid for this car.
        $highestBid = $car->bids()->orderBy('amount', 'desc')->first();
        // If there is a highest bid, set the minimum bid to highest bid + 1,
        // otherwise, the minimum acceptable bid is the car's price.
        $minBid = $highestBid ? $highestBid->amount + 1 : $car->price;
    
        // Validate the bid amount.
        $validated = $request->validate([
            'amount' => [
                'required',
                'numeric',
                'min:' . $minBid,
            ]
        ], [
            'amount.min' => "The bid amount must be at least {$minBid} to compete with the last winning bid."
        ]);
    
        // Create the bid.
        $bid = $car->bids()->create([
            'user_id' => $request->user()->id,
            'amount'  => $validated['amount']
        ]);
    
        return response()->json($bid, 201);
    }
    

    public function index(Car $car)
    {
        return $car->bids()->with('user')->orderByDesc('amount')->paginate(10);
    }

    public function userBids()
    {
        $bids = Auth::user()->bids()->with('car:id,title,price,model,year')->get();
        return response()->json($bids);
    }
}
