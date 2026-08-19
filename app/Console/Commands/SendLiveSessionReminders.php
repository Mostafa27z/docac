<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\LiveSession;
use App\Models\CourseEnrollment;
use App\Models\DeviceToken;
use App\Services\FirebaseNotificationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SendLiveSessionReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'live-sessions:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send FCM push notifications to enrolled students 24 hours and 1 hour before scheduled live sessions start';

    protected FirebaseNotificationService $fcmService;

    public function __construct(FirebaseNotificationService $fcmService)
    {
        parent::__construct();
        $this->fcmService = $fcmService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Checking for upcoming live sessions...");

        $now = Carbon::now();

        // 1. One Day (24 Hours) Alert
        $dayStartLimit = $now->copy()->addMinutes(24 * 60 - 10);
        $dayEndLimit = $now->copy()->addMinutes(24 * 60 + 10);

        $oneDaySessions = LiveSession::where('status', 'scheduled')
            ->where('notified_one_day', false)
            ->whereBetween('start_at', [$dayStartLimit, $dayEndLimit])
            ->get();

        foreach ($oneDaySessions as $session) {
            $studentIds = CourseEnrollment::where('course_id', $session->course_id)
                ->where('status', 'active')
                ->pluck('student_id');

            $tokens = DeviceToken::whereIn('user_id', $studentIds)->pluck('token')->toArray();

            if (!empty($tokens)) {
                $timeString = $session->start_at->format('H:i');
                $title = "تذكير بمحاضرة الغد 🩺";
                $body = "البث المباشر المجدول بعنوان \"{$session->title}\" سيبدأ غداً في تمام الساعة {$timeString}.";
                
                $sent = $this->fcmService->sendNotificationToTokens($tokens, $title, $body, [
                    'session_id' => $session->id,
                    'course_id' => $session->course_id,
                    'type' => 'live_session_reminder_24h'
                ]);

                if ($sent) {
                    $this->info("Sent 24h reminder notification for session: {$session->title}");
                }
            }

            $session->update(['notified_one_day' => true]);
        }

        // 2. One Hour (60 Minutes) Alert
        $hourStartLimit = $now->copy()->addMinutes(50);
        $hourEndLimit = $now->copy()->addMinutes(70);

        $oneHourSessions = LiveSession::where('status', 'scheduled')
            ->where('notified_one_hour', false)
            ->whereBetween('start_at', [$hourStartLimit, $hourEndLimit])
            ->get();

        foreach ($oneHourSessions as $session) {
            $studentIds = CourseEnrollment::where('course_id', $session->course_id)
                ->where('status', 'active')
                ->pluck('student_id');

            $tokens = DeviceToken::whereIn('user_id', $studentIds)->pluck('token')->toArray();

            if (!empty($tokens)) {
                $timeString = $session->start_at->format('H:i');
                $title = "البث المباشر سيبدأ قريباً 🔴";
                $body = "البث المباشر المجدول بعنوان \"{$session->title}\" سيبدأ بعد ساعة في تمام الساعة {$timeString}.";

                $sent = $this->fcmService->sendNotificationToTokens($tokens, $title, $body, [
                    'session_id' => $session->id,
                    'course_id' => $session->course_id,
                    'type' => 'live_session_reminder_1h'
                ]);

                if ($sent) {
                    $this->info("Sent 1h reminder notification for session: {$session->title}");
                }
            }

            $session->update(['notified_one_hour' => true]);
        }

        $this->info("Live session reminder checks completed.");
    }
}
