<?php

namespace App\Services\General\Notification;

use App\Http\Resources\NotificationResource;
use App\Models\User;
use App\Constants\Constants;
use App\Constants\Notifications;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\NotificationRecourse;

class NotificationService
{
    protected ?User $user;

    public function __construct()
    {
        // @phpstan-ignore-next-line
        $this->user = auth('sanctum')->user();
    }



    public function getAllNotifications($hasRead = null, $countOnly = null, $read = '0')
    {
        $notifications = $this->user->notifications();

        if ($hasRead !== null) {
            $notifications->where('has_read', $hasRead);
        }

        if ($countOnly) {
            return $notifications->count();
        }
        $notifications = $notifications->orderByDesc('id')->paginate(config('app.pagination_limit'));
        if ($read !== '0') {
            $notifications->each(function ($notification) {
                $notification->update(['has_read' => 1]);
            });
        }
        return NotificationResource::collection($notifications);
    }
    public function getNotificationTypeStatistics($hasRead = null)
    {
        $stats = $this->user->notifications();
        if ($hasRead !== null) {
            $stats->where('has_read', $hasRead);
        }
        return $stats->select('type', DB::raw('count(*) as count'))
            ->groupBy('type')
            ->pluck('count', 'type');
    }


    public function readAllNotifications()
    {
        return $this->user->notifications()->update(['has_read' => 1]);
    }



    public function pushAdminsNotifications($notification, $object)
    {
        switch ($notification['STATE']) {
            case Notifications::NEW_TRANSACTION['STATE']:
                $description = __('notifications.new_transaction_description', []);
                $title = __('notifications.new_transaction_title', []);
                break;
            default:
                return;
        }

        $admins = User::role(Constants::ADMIN_ROLE)->get();
        $admins->map(function ($admin) use ($title, $description, $object, $notification) {
            $userFcmTokens = $admin->fcmTokens()->pluck('fcm_token');
            foreach ($userFcmTokens as $fcmToken) {
                pushFirebaseNotification($fcmToken, $title, $description, [
                    'transaction_id' => strval($object->id),
                    'type' => $notification['TYPE'],
                    'state' => $notification['STATE'],
                    'icon' => 'notification_icon',
                    'sound' => 'notification_sound'
                ]);
            }
            $this->pushNotification(
                $title,
                $description,
                $notification['TYPE'],
                $notification['STATE'],
                $admin,
                class_basename($object),
                $object->id,
            );
        });
    }

    public function pushNotification($title, $description, $type, $state, $user, $modelType, $modelId, $checkDuplicated = false)
    {
        $data = [
            'title' => $title,
            'description' => $description,
            'type' => $type,
            'state' => $state,
            'model_id' => $modelId,
            'model_type' => $modelType,
        ];

        if ($checkDuplicated) {
            $filteredData = array_diff_key($data, array_flip(['title', 'description']));
            $user->notifications()->firstOrCreate($filteredData, $data);
        } else
            $user->notifications()->create($data);
    }
}
