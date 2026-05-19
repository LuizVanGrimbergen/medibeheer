<?php

namespace App\Console\Commands\Privacy;

use App\Services\Audit\ActivityLogName;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;

class PurgeExpiredPrivacyDataCommand extends Command
{
    protected $signature = 'privacy:purge-expired-data';

    protected $description = 'Remove expired sessions and activity log entries according to privacy retention settings.';

    public function handle(): int
    {
        $now = Carbon::now();

        $securityCutoff = $now->copy()->subDays((int) config('privacy.retention.security_activity_log_days'));
        $dataCutoff = $now->copy()->subDays((int) config('privacy.retention.data_activity_log_days'));
        $sessionCutoff = $now->copy()->subDays((int) config('privacy.retention.session_days'));

        $securityDeleted = Activity::query()
            ->where('log_name', ActivityLogName::SECURITY)
            ->where('created_at', '<', $securityCutoff)
            ->delete();

        $dataDeleted = Activity::query()
            ->where('log_name', ActivityLogName::DATA)
            ->where('created_at', '<', $dataCutoff)
            ->delete();

        $sessionsDeleted = DB::table('sessions')
            ->where('last_activity', '<', $sessionCutoff->getTimestamp())
            ->delete();

        $this->components->info(sprintf(
            'Purged %d security log entries, %d data log entries, and %d sessions.',
            $securityDeleted,
            $dataDeleted,
            $sessionsDeleted,
        ));

        return self::SUCCESS;
    }
}
