<?php

namespace App\Http\Controllers\Core;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
class ChatController extends Controller
{
    public function index()
    {
        return Inertia::render('utilities/chat', [

        ]);
    }

}
