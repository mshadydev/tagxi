<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Request\Request;
use Illuminate\Console\Command;
use App\Jobs\Notifications\SendPushNotification;
use App\Models\Admin\Driver;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class NotifyDriversForScheduledBidRides extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notify_drivers:for_scheduled_bid_rides';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notify Drivers for scheduled bid rides';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $current_date = Carbon::now()->format('Y-m-d H:i:s');

        $sub_minutes = Carbon::now()->subMinute()->format('Y-m-d H:i:s');
        $requests = Request::where('is_later', 1)
                    ->where('is_bid_ride', 1)
                    ->whereBetween('trip_start_time', [$sub_minutes, $current_date])
                    ->where('is_driver_arrived', 0)->where('is_completed', 0)->where('is_cancelled', 0)->where('is_driver_started', 1)->where('driver_id', '!=', null)->get();

        if ($requests->count() == 0) {
            return $this->info('no-schedule-rides-found');
        }

        foreach ($requests as $request) {
            if($request->userDetail) {
                $user = User::find($request->user_id);
                $notifable_user = $user;
                $title = trans('push_notifications.trip_accepted_title');
                $body = trans('push_notifications.trip_accepted_body');
                dispatch(new SendPushNotification($notifable_user, $title, $body, ['type' => 1]));
            }
            if($request->driverDetail) {
                $driver = Driver::where('id', $request->driver_id)->first();
                $notifable_driver = $driver->user;
                $title = trans('push_notifications.scheduled_request_title', [], $notifable_driver->lang);
                $body = trans('push_notifications.scheduled_request_body', [], $notifable_driver->lang);
                dispatch(new SendPushNotification($notifable_driver, $title, $body, ['type' => 1]));
            }
        }
        $this->info('success');
    }
}
