<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function index()
    {
        return view('message.index');
    }

    public function send(Request $request)
    {
        $validateRequest = $request->validate([
            'content' => 'required|string',
            'sender' => 'required|integer',
            'conversation_id' => 'required|integer',
        ]);

        $message = Message::create([
            'content' => $validateRequest['content'],
            'sender_id' => $validateRequest['sender'],
            'conversation_id' => $validateRequest['conversation_id'],
        ]);

        return response()->json([
            'status' => 'success',
            'message' => $message,
        ]);
    }
}
