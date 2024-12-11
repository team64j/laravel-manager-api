<?php

namespace Team64j\LaravelManagerApi\Http\Controllers;

use Illuminate\Http\Request;

class PreviewController extends Controller
{
    public function index(Request $request, int $id)
    {
        return redirect(url()->getRouteById($id)['url'] ?? '/');
    }
}
