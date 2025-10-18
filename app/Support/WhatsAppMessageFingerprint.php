<?php

namespace App\Support;

use Illuminate\Support\Str;

class WhatsAppMessageFingerprint
{
    /**
     * Builds a stable identifier for an incoming webhook payload.
     *
     * The API Brasil webhook is not consistent about where it places the message
     * id, so we try a list of known keys before falling back to a deterministic hash.
     */
    public static function forPayload(array $payload, ?string $from = null, ?string $body = null): string
    {
        $candidates = [
            data_get($payload, 'id'),
            data_get($payload, 'message.id'),
            data_get($payload, 'message.key.id'),
            data_get($payload, 'message.key._serialized'),
            data_get($payload, 'message.key.id._serialized'),
            data_get($payload, 'message.id._serialized'),
            data_get($payload, 'data.id'),
            data_get($payload, 'data.data.id'),
            data_get($payload, 'data.data.id.id'),
            data_get($payload, 'data.data.id._serialized'),
            data_get($payload, 'data.message.id'),
            data_get($payload, 'data.message.key.id'),
            data_get($payload, 'data.message.key._serialized'),
            data_get($payload, 'data.response.id'),
            data_get($payload, 'response.id'),
        ];

        foreach ($candidates as $candidate) {
            if ($candidate !== null && $candidate !== '') {
                return (string) $candidate;
            }
        }

        $timestamp = data_get($payload, 'data.data.t')
            ?? data_get($payload, 'data.timestamp')
            ?? data_get($payload, 'data.datetime')
            ?? data_get($payload, 'timestamp')
            ?? data_get($payload, 'message.timestamp');

        $fingerprintSource = implode('|', [
            $from ?: 'unknown',
            Str::upper(trim((string) $body)),
            $timestamp ?: 'no-timestamp',
            data_get($payload, 'data.session') ?: 'no-session',
        ]);

        return hash('sha256', $fingerprintSource);
    }
}
