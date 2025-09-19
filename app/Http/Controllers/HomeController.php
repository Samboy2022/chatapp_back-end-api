<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Message;
use App\Models\Chat;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        // Get some basic stats for the landing page
        $stats = [
            'total_users' => User::count(),
            'total_messages' => Message::count(),
            'total_chats' => Chat::count(),
            'users_today' => User::whereDate('created_at', today())->count()
        ];

        return view('welcome', compact('stats'));
    }

    public function about()
    {
        return view('about');
    }

    public function features()
    {
        return view('features');
    }

    public function contact()
    {
        return view('contact');
    }

    public function privacy()
    {
        return view('privacy');
    }

    public function terms()
    {
        return view('terms');
    }
}
