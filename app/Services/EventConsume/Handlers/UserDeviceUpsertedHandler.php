<?php

namespace App\Services\EventConsume\Handlers;

use App\Models\User;
use App\Models\UserDevice;
use App\Services\EventConsume\EventHandlerInterface;
use Illuminate\Support\Facades\DB;

class UserDeviceUpsertedHandler implements EventHandlerInterface
{
    public function handle(array $event): void
    {
        $userId = $this->asInt(data_get($event, 'data.user_id'));
        $device = data_get($event, 'data.device', []);

        if ($userId <= 0) {
            throw new \Exception('UserDeviceUpsertedHandler: missing/invalid data.user_id');
        }

        if (!is_array($device) || empty($device)) {
            throw new \Exception('UserDeviceUpsertedHandler: missing/invalid data.device');
        }

        $deviceId = data_get($device, 'device_id');
        $platform = data_get($device, 'platform');
        $model = data_get($device, 'model');

        if (!$deviceId && !$platform && !$model) {
            throw new \Exception('UserDeviceUpsertedHandler: device must include device_id or platform/model');
        }

        DB::transaction(function () use ($userId, $deviceId, $platform, $model, $device) {
            $exists = User::query()->whereKey($userId)->exists();

            if (!$exists) {
                throw new \Exception("UserDeviceUpsertedHandler: user {$userId} not synced yet");
            }

            $query = UserDevice::query()->where('user_id', $userId);

            if (!empty($deviceId)) {
                $query->where('device_id', $deviceId);
            } else {
                $query->where('platform', $platform)
                    ->where('model', $model);
            }

            $existing = $query->first();

            $payload = [
                'user_id' => $userId,
                'device_id' => $deviceId,
                'platform' => $platform,
                'model' => $model,
                'os_version' => data_get($device, 'os_version'),
                'app_version' => data_get($device, 'app_version'),
                'fcm_token' => data_get($device, 'fcm_token'),
                'last_seen_at' => now(),
            ];

            if ($existing) {
                $existing->update($payload);
                return;
            }

            UserDevice::query()->create($payload);
        });
    }

    private function asInt(mixed $value): int
    {
        if (is_int($value)) {
            return $value;
        }

        if (is_string($value) && ctype_digit($value)) {
            return (int) $value;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        return 0;
    }
}