<?php

namespace App\Jobs;

use App\Models\UserTestAttempt\UserTestAttempt;
use App\Services\UserTestAttempt\UserTestAttemptService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AutoSubmitUserTestAttempt implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $attemptId;

    public function __construct(int $attemptId)
    {
        $this->attemptId = $attemptId;
    }

    public function handle(UserTestAttemptService $service): void
    {
        $attempt = UserTestAttempt::query()->find($this->attemptId);

        if (!$attempt || (int) $attempt->status !== UserTestAttempt::STATUS_IN_PROGRESS) {
            return;
        }

        if (!$attempt->expired_at || $attempt->expired_at->isFuture()) {
            return;
        }

        $service->finalizeAttempt($attempt);
    }
}
