<?php

namespace App\Helpers;

class ActivityLogFormatter
{
    public static function toText(string $action, ?array $meta = null): string
    {
        $meta = $meta ?? [];

        return match ($action) {
            'user.registered' => 'New user joined: ' . ($meta['username'] ?? '-'),
            'user.login' => 'User logged in: ' . ($meta['username'] ?? '-'),
            'user.logout' => 'User logged out: ' . ($meta['username'] ?? '-'),
            'user.role_changed' => 'User role changed: ' . ($meta['username'] ?? '-') . ' to ' . (($meta['is_admin'] ?? false) ? 'Admin' : 'User'),
            'allergy.added' => 'Allergy added: ' . ($meta['allergy'] ?? '-'),
            'allergy.updated' => 'Allergy updated: ' . ($meta['allergy'] ?? '-'),
            'allergy.deleted' => 'Allergy deleted: ' . ($meta['allergy'] ?? '-'),
            'dietary.added' => 'Dietary preference added: ' . ($meta['dietary'] ?? '-'),
            'dietary.updated' => 'Dietary preference updated: ' . ($meta['dietary'] ?? '-'),
            'dietary.deleted' => 'Dietary preference deleted: ' . ($meta['dietary'] ?? '-'),
            'guide.added' => 'Guide published: ' . ($meta['guide'] ?? '-'),
            'guide.updated' => 'Guide updated: ' . ($meta['guide'] ?? '-'),
            'guide.deleted' => 'Guide deleted: ' . ($meta['guide'] ?? '-'),
            'message.submitted' => 'New message from: ' . ($meta['name'] ?? '-'),
            'message.deleted' => 'Message from ' . ($meta['name'] ?? '-') . ' deleted',
            default => 'Activity: ' . $action,
        };
    }
}