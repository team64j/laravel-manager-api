<?php

namespace Team64j\LaravelManagerApi\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class PreviewController extends Controller
{
    public function index(Request $request, int $id)
    {
        return redirect(URL::getRouteById($id)['url'] ?? '/');
    }
}
