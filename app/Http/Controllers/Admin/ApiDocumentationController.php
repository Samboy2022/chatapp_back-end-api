<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class ApiDocumentationController extends Controller
{
    public function index()
    {
        return view('admin.api-documentation.index');
    }

    public function endpoints()
    {
        return view('admin.api-documentation.endpoints');
    }

    public function authentication()
    {
        return view('admin.api-documentation.authentication');
    }

    public function examples()
    {
        return view('admin.api-documentation.examples');
    }

    public function configuration()
    {
        return view('admin.api-documentation.configuration');
    }

    public function testing()
    {
        return view('admin.api-documentation.testing');
    }
} 