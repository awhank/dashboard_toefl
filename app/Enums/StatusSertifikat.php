<?php

namespace App\Enums;

enum StatusSertifikat: string
{
    case BelumTes = 'belum_tes';
    case Berlaku = 'berlaku';
    case AkanExpired = 'akan_expired';
    case Expired = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::BelumTes => 'Belum pernah tes ITP',
            self::Berlaku => 'Berlaku',
            self::AkanExpired => 'Akan expired (≤ 3 bln)',
            self::Expired => 'Expired',
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::BelumTes => 'muted',
            self::Berlaku => 'ok',
            self::AkanExpired => 'warning',
            self::Expired => 'danger',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::BelumTes => 'var(--color-status-muted)',
            self::Berlaku => 'var(--color-status-ok)',
            self::AkanExpired => 'var(--color-status-warning)',
            self::Expired => 'var(--color-status-danger)',
        };
    }
}

