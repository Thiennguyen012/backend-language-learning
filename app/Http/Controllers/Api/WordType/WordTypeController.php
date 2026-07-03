<?php

namespace App\Http\Controllers\Api\WordType;

use App\Http\Controllers\Controller;
use App\Http\Resources\WordTypeResource;
use App\Models\WordType\WordType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class WordTypeController extends Controller
{
    public function index(): JsonResponse
    {
        $wordTypes = WordType::query()
            ->orderBy('id')
            ->get();

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'message' => __('messages.common.list', ['entity' => __('messages.entities.word_type')]),
            'data' => WordTypeResource::collection($wordTypes),
        ]);
    }
}
