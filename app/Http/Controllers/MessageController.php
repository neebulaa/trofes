<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Inertia\Inertia;
use App\Models\Message;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function index(){
        return Inertia::render("ContactUs");
    }

    public function store(Request $request){
        $validated = $request->validate([
            'name' => 'required|string|max:255|min:3',
            'email' => 'required|email|max:255|email:rfc,dns',
            'message' => 'required|string|min:10',
        ]);

        Message::create($validated);

        ActivityLog::create([
            'user_id' => null,
            'action' => 'message.submitted',
            'meta' => json_encode([
                'name' => $validated['name'],
            ]),
        ]);

        return redirect()->back()->with('flash', [
            'type' => 'success',
            'message' => 'Your Message has been sent, Thank You!',
        ]);
    }
}
