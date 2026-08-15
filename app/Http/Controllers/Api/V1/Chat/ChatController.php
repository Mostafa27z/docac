<?php

namespace App\Http\Controllers\Api\V1\Chat;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function getConversations(Request $request)
    {
        $user = $request->user();

        $conversations = Conversation::where('student_id', $user->id)
            ->orWhere('instructor_id', $user->id)
            ->with(['course', 'student', 'instructor'])
            ->orderBy('last_message_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Conversations retrieved successfully.',
            'data' => $conversations
        ]);
    }

    public function getMessages(Request $request, Conversation $conversation)
    {
        $user = $request->user();

        // Security check: Verify user is part of the conversation
        if ($conversation->student_id !== $user->id && $conversation->instructor_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to this conversation.'
            ], 403);
        }

        $messages = $conversation->messages()->with('sender')->get();

        return response()->json([
            'success' => true,
            'message' => 'Messages retrieved successfully.',
            'data' => $messages
        ]);
    }

    public function sendMessage(Request $request, Conversation $conversation)
    {
        $user = $request->user();

        // Security check
        if ($conversation->student_id !== $user->id && $conversation->instructor_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access.'
            ], 403);
        }

        $validated = $request->validate([
            'message_text' => 'required_without:attachment_path|string',
            'type' => 'required|in:text,image,file',
            'attachment_path' => 'nullable|string', // Uploaded separately or dynamically
        ]);

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'message_text' => $validated['message_text'] ?? null,
            'type' => $validated['type'],
            'attachment_path' => $validated['attachment_path'] ?? null,
        ]);

        $conversation->update([
            'last_message_at' => now()
        ]);

        // Realtime broadcasting event can be triggered here e.g.:
        // broadcast(new MessageSent($message))->toOthers();

        return response()->json([
            'success' => true,
            'message' => 'Message sent successfully.',
            'data' => $message->load('sender')
        ], 201);
    }

    public function getCourseChat(Request $request, \App\Models\Course $course)
    {
        $user = $request->user();

        // Check if student is enrolled
        $isEnrolled = $course->enrollments()->where('student_id', $user->id)->exists();
        if (!$isEnrolled) {
            return response()->json([
                'success' => false,
                'message' => 'You are not enrolled in this course.'
            ], 403);
        }

        // Find or create conversation between student and instructor for this course
        $conversation = Conversation::firstOrCreate([
            'course_id' => $course->id,
            'student_id' => $user->id,
            'instructor_id' => $course->instructor_id,
        ]);

        $messages = $conversation->messages()->with('sender')->paginate(20);

        return response()->json([
            'success' => true,
            'message' => 'Messages retrieved successfully.',
            'data' => $messages->items(),
            'meta' => [
                'current_page' => $messages->currentPage(),
                'last_page' => $messages->lastPage(),
                'per_page' => $messages->perPage(),
                'total' => $messages->total(),
            ]
        ]);
    }

    public function sendCourseChatMessage(Request $request, \App\Models\Course $course)
    {
        $user = $request->user();

        // Check if student is enrolled
        $isEnrolled = $course->enrollments()->where('student_id', $user->id)->exists();
        if (!$isEnrolled) {
            return response()->json([
                'success' => false,
                'message' => 'You are not enrolled in this course.'
            ], 403);
        }

        $validated = $request->validate([
            'message_text' => 'required|string',
            'attachment' => 'nullable|file', // Accepting actual file uploads or URLs depending on storage setup
        ]);

        $conversation = Conversation::firstOrCreate([
            'course_id' => $course->id,
            'student_id' => $user->id,
            'instructor_id' => $course->instructor_id,
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('chats/attachments', 'public');
        }

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'message_text' => $validated['message_text'],
            'type' => $attachmentPath ? 'file' : 'text',
            'attachment_path' => $attachmentPath,
        ]);

        $conversation->update([
            'last_message_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Message sent successfully.',
            'data' => $message->load('sender')
        ], 201);
    }
}
