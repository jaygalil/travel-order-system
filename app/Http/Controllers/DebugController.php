<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DebugController extends Controller
{
    public function debugForm(Request $request)
    {
        return response()->json([
            'method' => $request->method(),
            'url' => $request->url(),
            'headers' => $request->headers->all(),
            'input' => $request->all(),
            'files' => $request->files->all(),
            'user' => auth()->check() ? auth()->user()->only(['id', 'name', 'email']) : null,
        ]);
    }
}
