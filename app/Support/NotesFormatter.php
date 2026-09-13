<?php

namespace App\Support;

class NotesFormatter
{
    public static function toText(mixed $notesRaw, string $lineSeparator = ' · ', string $emptyText = 'No notes'): string
    {
        $lines = self::toLines($notesRaw);
        if ($lines === []) {
            return $emptyText;
        }

        return implode($lineSeparator, $lines);
    }

    public static function toLines(mixed $notesRaw): array
    {
        $raw = null;
        if (is_string($notesRaw) && $notesRaw !== '') {
            $decoded = json_decode($notesRaw, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $raw = $decoded;
            } else {
                return [trim($notesRaw)];
            }
        } elseif (is_array($notesRaw)) {
            $raw = $notesRaw;
        } else {
            return [];
        }

        if (!is_array($raw) || $raw === []) {
            return [];
        }

        $lines = [];

        if (!empty($raw['user_notes']) && is_string($raw['user_notes']) && trim($raw['user_notes']) !== '') {
            $lines[] = 'Note: ' . trim($raw['user_notes']);
        }

        $source = $raw['deduct_source'] ?? null;
        $partnerIds = $raw['deduct_partner_ids'] ?? null;
        $partnerNames = $raw['deduct_partner_names'] ?? null;
        $shareMap = $raw['per_partner_share_map'] ?? null;
        if (is_array($partnerIds) && $partnerIds !== [] && is_array($shareMap)) {
            $shares = [];
            foreach ($partnerIds as $idx => $pid) {
                $name = is_array($partnerNames) && isset($partnerNames[$idx]) && trim((string) $partnerNames[$idx]) !== ''
                    ? trim((string) $partnerNames[$idx])
                    : ('Partner #' . $pid);
                $amount = (float) ($shareMap[$pid] ?? 0);
                $shares[] = $name . ' Rs. ' . number_format($amount, 2);
            }
            if ($shares !== []) {
                $lines[] = 'Split: ' . implode(', ', $shares);
            }
        }

        if (in_array($source, ['profit', 'invested'], true)) {
            $lines[] = 'Source: ' . ($source === 'invested' ? 'Invested Capital' : 'Earned Profit');
        }

        if (!empty($raw['entry_type']) && is_string($raw['entry_type'])) {
            $lines[] = 'Type: ' . ucfirst($raw['entry_type']);
        }

        if (!empty($raw['employee_name'])) {
            $month = !empty($raw['salary_month']) ? ' (' . $raw['salary_month'] . ')' : '';
            $lines[] = 'Employee: ' . $raw['employee_name'] . $month;
        }

        if (!empty($raw['original_salary_id'])) {
            $lines[] = 'Salary ID: #' . $raw['original_salary_id'];
        } elseif (!empty($raw['salary_id'])) {
            $lines[] = 'Salary ID: #' . $raw['salary_id'];
        } elseif (!empty($raw['original_expense_id'])) {
            $lines[] = 'Expense ID: #' . $raw['original_expense_id'];
        } elseif (!empty($raw['share_partner_id'])) {
            $lines[] = 'Share Partner: #' . $raw['share_partner_id'];
        }

        if (!empty($raw['applied_at'])) {
            $lines[] = 'Applied: ' . $raw['applied_at'];
        }

        return $lines;
    }

    public static function toHtmlBr(mixed $notesRaw, string $emptyText = 'No extra notes'): string
    {
        $lines = self::toLines($notesRaw);
        if ($lines === []) {
            return e($emptyText);
        }

        return implode('<br>', array_map(static fn (string $line): string => e($line), $lines));
    }

    public static function extractUserNotes(mixed $notesRaw): string
    {
        $decoded = self::decode($notesRaw);
        if (is_array($decoded)) {
            return (string) ($decoded['user_notes'] ?? '');
        }
        if (is_string($decoded)) {
            return $decoded;
        }

        return '';
    }

    public static function decode(mixed $notesRaw): mixed
    {
        if (is_string($notesRaw) && $notesRaw !== '') {
            $decoded = json_decode($notesRaw, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }

            return $notesRaw;
        }

        return $notesRaw;
    }
}
