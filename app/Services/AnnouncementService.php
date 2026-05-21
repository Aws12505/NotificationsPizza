<?php

namespace App\Services;

use App\Models\Announcement;
use App\Models\AnnouncementUserState;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use App\Services\NotificationService;
use App\Jobs\SendBulkNotificationJob;

class AnnouncementService
{

    public function __construct(
        private readonly NotificationService $notificationService
    ) {
    }
    public function paginateForAdmin(int $perPage = 15): LengthAwarePaginator
    {
        return Announcement::query()
            ->orderByDesc('is_pinned')
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function create(array $data): Announcement
    {
        return DB::transaction(function () use ($data) {
            $announcement = Announcement::query()->create([
                'type' => $data['type'] ?? 'general',
                'title' => $data['title'],
                'body' => $data['body'],
                'version' => $data['version'] ?? null,
                'is_active' => $data['is_active'] ?? true,
                'is_pinned' => $data['is_pinned'] ?? false,
                'starts_at' => $data['starts_at'] ?? null,
                'ends_at' => $data['ends_at'] ?? null,
            ]);

            dispatch(new SendBulkNotificationJob(
                channels: ['web'],
                payload: [
                    'type' => 'announcement.created',
                    'title' => $announcement->title,
                    'body' => $announcement->body,
                    'action_url' => "/announcements/{$announcement->id}",
                    'announcement_id' => $announcement->id,
                ]
            ));

            return $announcement;
        });
    }

    public function update(Announcement $announcement, array $data): Announcement
    {
        return DB::transaction(function () use ($announcement, $data) {
            $announcement->update([
                'type' => $data['type'] ?? $announcement->type,
                'title' => $data['title'] ?? $announcement->title,
                'body' => $data['body'] ?? $announcement->body,
                'version' => array_key_exists('version', $data) ? $data['version'] : $announcement->version,
                'is_active' => $data['is_active'] ?? $announcement->is_active,
                'is_pinned' => $data['is_pinned'] ?? $announcement->is_pinned,
                'starts_at' => array_key_exists('starts_at', $data) ? $data['starts_at'] : $announcement->starts_at,
                'ends_at' => array_key_exists('ends_at', $data) ? $data['ends_at'] : $announcement->ends_at,
            ]);

            $announcement->refresh();

            dispatch(new SendBulkNotificationJob(
                channels: ['web'],
                payload: [
                    'type' => 'announcement.updated',
                    'title' => $announcement->title,
                    'body' => $announcement->body,
                    'action_url' => "/announcements/{$announcement->id}",
                    'announcement_id' => $announcement->id,
                ]
            ));

            return $announcement;
        });
    }


    public function delete(Announcement $announcement): void
    {
        $announcement->delete();
    }

    public function getVisibleForUser(int $userId): Collection
    {
        $now = now();

        return Announcement::query()
            ->where('is_active', true)
            ->where(function ($q) use ($now) {
                $q->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', $now);
            })
            ->orderByDesc('is_pinned')
            ->orderByDesc('created_at')
            ->get();
    }

    public function getUnseenForUser(int $userId): Collection
    {
        $now = now();

        return Announcement::query()
            ->where('is_active', true)
            ->where(function ($q) use ($now) {
                $q->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', $now);
            })
            ->whereDoesntHave('userStates', function ($q) use ($userId) {
                $q->where('user_id', $userId)
                    ->whereNotNull('seen_at');
            })
            ->orderByDesc('is_pinned')
            ->orderBy('created_at', 'asc') // fix here
            ->get();
    }

    public function markSeen(int $userId, int $announcementId): void
    {
        AnnouncementUserState::query()->updateOrCreate(
            [
                'announcement_id' => $announcementId,
                'user_id' => $userId,
            ],
            [
                'seen_at' => now(),
            ]
        );
    }

    public function markManySeen(int $userId, array $announcementIds): void
    {
        foreach ($announcementIds as $announcementId) {
            $id = is_numeric($announcementId) ? (int) $announcementId : 0;

            if ($id > 0) {
                $this->markSeen($userId, $id);
            }
        }
    }
}