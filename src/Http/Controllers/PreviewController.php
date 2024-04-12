<?php

namespace Team64j\LaravelManagerApi\Http\Controllers;

use Illuminate\Http\Request;
use Team64j\LaravelManagerApi\Support\Url;

class PreviewController extends Controller
{
    public function index(Request $request, int $id)
    {
        return redirect(Url::getRouteById($id)['url'] ?? '/');
    }
}
