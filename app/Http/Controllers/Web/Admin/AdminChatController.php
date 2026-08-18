<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\Request;

class AdminChatController extends Controller
{
    public function index(Request $request)
    {
        $query = Conversation::with(['course', 'student', 'instructor']);

        // Search or filter by student name, instructor name, or course title
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('student', function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%");
                })->orWhereHas('instructor', function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%");
                })->orWhereHas('course', function ($sub) use ($search) {
                    $sub->where('title', 'like', "%{$search}%");
                });
            });
        }

        $conversations = $query->orderBy('last_message_at', 'desc')->get();

        $selectedConversation = null;
        $messages = collect();

        if ($request->has('conversation_id')) {
            $selectedConversation = Conversation::where('id', $request->conversation_id)
                ->with(['student', 'instructor', 'course'])
                ->first();

            if ($selectedConversation) {
                $messages = $selectedConversation->messages()->with('sender')->get();
            }
        }

        return view('admin.chats.index', compact('conversations', 'selectedConversation', 'messages'));
    }
}
