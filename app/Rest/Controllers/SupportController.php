<?php

namespace App\Rest\Controllers;

use App\Rest\Controller as RestController;
use App\Models\Support;
use App\Rest\Resources\SupportResource;
use Illuminate\Http\Request;
class SupportController extends RestController
{
    /**
     * The resource the controller corresponds to.
     *
     * @var class-string<\Lomkit\Rest\Http\Resource>
     */
    public static $resource = \App\Rest\Resources\SupportResource::class;
}