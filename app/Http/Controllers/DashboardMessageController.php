<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Message;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class DashboardMessageController extends Controller
{
    public function index(Request $request){
        $search = $request->query('search');
        $perPage = $request->query('per_page', 9);

        $query = Message::query()->latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('message', 'like', "%{$search}%");
            });
        }

        $messages = $query->paginate($perPage)->appends($request->only(['search', 'per_page']));

        return Inertia::render('Dashboard/Messages/Index', [
            'messages' => $messages
        ]);
    }

    public function destroy(Request $request, Message $message){
        ActivityLog::create([
            'user_id' => null,
            'action' => 'message.deleted',
            'meta' => json_encode([
                'name' => $message->name,
            ]),
        ]);
        $message->delete();

        return redirect()->back()->with('flash', [
            'type' => 'success',
            'message' => 'Message deleted successfully.',
        ]);
    }
}
