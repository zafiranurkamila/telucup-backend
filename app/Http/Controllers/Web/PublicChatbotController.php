<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

class PublicChatbotController extends Controller
{
    public function index()
    {
        return view('public.chatbot');
    }
}
