<?php

namespace App\Http\Controllers\Api\QuestionType;

use App\Http\Controllers\Controller;
use App\Http\Resources\QuestionTypeResource;
use App\Models\QuestionType\QuestionType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class QuestionTypeController extends Controller
{
    public function index(): JsonResponse
    {
        $questionTypes = QuestionType::query()
            ->orderBy('id')
            ->get();

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'message' => __('messages.common.list', ['entity' => __('messages.entities.question_type')]),
            'data' => QuestionTypeResource::collection($questionTypes),
        ]);
    }
}
