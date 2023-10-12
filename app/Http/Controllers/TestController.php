<?php

namespace App\Http\Controllers;

use App\Action\Authorization;
use App\Action\AuthorizationResource;
use App\Action\Callback;
use Illuminate\Http\Request;

class TestController extends Controller
{
    public function index()
    {
        return (new Authorization())->execute();

    }

    public function callback()
    {
        return (new Callback())->execute();
    }

    public function authResource()
    {
        return (new AuthorizationResource())->execute();
    }
}
