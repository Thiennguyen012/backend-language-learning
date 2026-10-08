<?php

namespace App\Http\Controllers\Api\SpeakingAssessment;

use App\Http\Controllers\Controller;
use App\Http\Requests\SpeakingAssessment\StoreSpeakingAssessmentRequest;
use App\Http\Resources\SpeakingAssessmentResource;
use App\Jobs\ScoreSpeakingAssessment;
use App\Models\SpeakingAssessment\SpeakingAssessment;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SpeakingAssessmentController extends Controller
{
    public function store(StoreSpeakingAssessmentRequest $request): JsonResponse
    {
        $provider = config('services.speaking_assessment.provider');
        $configured = $provider === 'openpronounce'
            ? (bool) config('services.openpronounce.url')
            : (bool) config('services.speechace.api_key');

        if (! $configured) {
            return response()->json([
                'status_code' => Response::HTTP_SERVICE_UNAVAILABLE,
                'message' => 'Speech assessment is not configured.',
            ], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        $audio = $request->file('audio');
        $idempotencyKey = $request->validated('idempotency_key');
        $audioHash = hash_file('sha256', $audio->getRealPath());
        $requestHash = hash('sha256', implode('|', [
            trim($request->validated('reference_text')),
            $request->validated('dialect'),
            $audioHash,
        ]));

        $existing = SpeakingAssessment::query()
            ->where('user_id', $request->user()->id)
            ->where('idempotency_key', $idempotencyKey)
            ->first();

        if ($existing) {
            return $this->respondToDuplicate($existing, $requestHash);
        }

        $uuid = (string) Str::uuid();
        $disk = config('services.speechace.storage_disk');
        $extension = strtolower($audio->getClientOriginalExtension());
        $path = $audio->storeAs(
            'speaking-assessments/'.$request->user()->id,
            $uuid.'.'.$extension,
            $disk
        );

        if (! $path) {
            return response()->json([
                'status_code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'The audio could not be stored.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        try {
            $assessment = DB::transaction(function () use ($request, $uuid, $path, $idempotencyKey, $requestHash) {
                return SpeakingAssessment::query()->create([
                    'uuid' => $uuid,
                    'user_id' => $request->user()->id,
                    'idempotency_key' => $idempotencyKey,
                    'request_hash' => $requestHash,
                    'reference_text' => trim($request->validated('reference_text')),
                    'dialect' => $request->validated('dialect'),
                    'status' => SpeakingAssessment::STATUS_QUEUED,
                    'audio_path' => $path,
                ]);
            });
        } catch (QueryException $exception) {
            Storage::disk($disk)->delete($path);

            $existing = SpeakingAssessment::query()
                ->where('user_id', $request->user()->id)
                ->where('idempotency_key', $idempotencyKey)
                ->first();

            if ($existing) {
                return $this->respondToDuplicate($existing, $requestHash);
            }

            throw $exception;
        } catch (\Throwable $exception) {
            Storage::disk($disk)->delete($path);

            throw $exception;
        }

        ScoreSpeakingAssessment::dispatch($assessment->id)->afterCommit();

        return response()->json([
            'status_code' => Response::HTTP_ACCEPTED,
            'message' => 'Speaking assessment queued.',
            'data' => new SpeakingAssessmentResource($assessment),
        ], Response::HTTP_ACCEPTED);
    }

    private function respondToDuplicate(SpeakingAssessment $assessment, string $requestHash): JsonResponse
    {
        if (! hash_equals($assessment->request_hash, $requestHash)) {
            return response()->json([
                'status_code' => Response::HTTP_CONFLICT,
                'message' => 'Idempotency key was already used with a different request.',
            ], Response::HTTP_CONFLICT);
        }

        return response()->json([
            'status_code' => Response::HTTP_ACCEPTED,
            'message' => 'Speaking assessment already exists.',
            'data' => new SpeakingAssessmentResource($assessment),
        ], Response::HTTP_ACCEPTED);
    }

    public function show(string $uuid): JsonResponse
    {
        $assessment = SpeakingAssessment::query()
            ->where('uuid', $uuid)
            ->where('user_id', request()->user()->id)
            ->first();

        if (! $assessment) {
            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'message' => 'Speaking assessment not found.',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'message' => 'Speaking assessment retrieved.',
            'data' => new SpeakingAssessmentResource($assessment),
        ], Response::HTTP_OK);
    }

    public function audio(string $uuid): StreamedResponse|JsonResponse
    {
        $assessment = SpeakingAssessment::query()
            ->where('uuid', $uuid)
            ->where('user_id', request()->user()->id)
            ->first();

        $disk = config('services.speechace.storage_disk');

        if (! $assessment || ! Storage::disk($disk)->exists($assessment->audio_path)) {
            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'message' => 'Speaking assessment audio not found.',
            ], Response::HTTP_NOT_FOUND);
        }

        return Storage::disk($disk)->download(
            $assessment->audio_path,
            basename($assessment->audio_path)
        );
    }

    public function destroy(string $uuid): JsonResponse
    {
        $assessment = SpeakingAssessment::query()
            ->where('uuid', $uuid)
            ->where('user_id', request()->user()->id)
            ->first();

        if (! $assessment) {
            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'message' => 'Speaking assessment not found.',
            ], Response::HTTP_NOT_FOUND);
        }

        Storage::disk(config('services.speechace.storage_disk'))->delete($assessment->audio_path);
        $assessment->delete();

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'message' => 'Speaking assessment deleted.',
        ]);
    }
}
