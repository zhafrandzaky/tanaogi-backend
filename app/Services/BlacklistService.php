<?php

namespace App\Services;

use App\Models\BlacklistIp;
use App\Models\BlacklistPhone;
use App\Models\WhitelistIp;
use App\Models\WhitelistPhone;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BlacklistService
{
    // ─── IP ──────────────────────────────────────────────────────────────────

    public function isIpBlacklisted(string $ip): bool
    {
        return BlacklistIp::active()
            ->where('ip_address', $ip)
            ->where(function ($query) {
                $query->whereNull('banned_until')
                    ->orWhere('banned_until', '>', now());
            })
            ->exists();
    }

    public function isIpWhitelisted(string $ip): bool
    {
        return WhitelistIp::active()->where('ip_address', $ip)->exists();
    }

    public function banIp(string $ip, string $reason, bool $isAuto = false, ?int $durationMinutes = null): BlacklistIp
    {
        return BlacklistIp::create([
            'ip_address'   => $ip,
            'reason'       => $reason,
            'is_auto'      => $isAuto,
            'banned_at'    => now(),
            'banned_until' => $durationMinutes ? now()->addMinutes($durationMinutes) : null,
            'is_active'    => true,
        ]);
    }

    public function unbanIp(string $id): bool
    {
        return (bool) BlacklistIp::where('id', $id)->update(['is_active' => false]);
    }

    public function whitelistIp(string $ip, string $note = ''): WhitelistIp
    {
        return WhitelistIp::create([
            'ip_address' => $ip,
            'note'       => $note,
            'is_active'  => true,
        ]);
    }

    public function removeIpFromWhitelist(string $id): bool
    {
        return (bool) WhitelistIp::where('id', $id)->delete();
    }

    public function getBlacklistedIps(): Collection
    {
        return BlacklistIp::active()->latest('banned_at')->get();
    }

    public function getWhitelistedIps(): Collection
    {
        return WhitelistIp::active()->get();
    }

    // ─── Phone ───────────────────────────────────────────────────────────────

    public function isPhoneBlacklisted(string $phone): bool
    {
        return BlacklistPhone::active()
            ->where('phone', $phone)
            ->where(function ($query) {
                $query->whereNull('banned_until')
                    ->orWhere('banned_until', '>', now());
            })
            ->exists();
    }

    public function isPhoneWhitelisted(string $phone): bool
    {
        return WhitelistPhone::active()->where('phone', $phone)->exists();
    }

    public function banPhone(string $phone, string $reason, bool $isAuto = false, ?int $durationMinutes = null): BlacklistPhone
    {
        return BlacklistPhone::create([
            'phone'        => $phone,
            'reason'       => $reason,
            'is_auto'      => $isAuto,
            'banned_at'    => now(),
            'banned_until' => $durationMinutes ? now()->addMinutes($durationMinutes) : null,
            'is_active'    => true,
        ]);
    }

    public function unbanPhone(string $id): bool
    {
        return (bool) BlacklistPhone::where('id', $id)->update(['is_active' => false]);
    }

    public function whitelistPhone(string $phone, string $note = ''): WhitelistPhone
    {
        return WhitelistPhone::create([
            'phone'     => $phone,
            'note'      => $note,
            'is_active' => true,
        ]);
    }

    public function removePhoneFromWhitelist(string $id): bool
    {
        return (bool) WhitelistPhone::where('id', $id)->delete();
    }

    public function getBlacklistedPhones(): Collection
    {
        return BlacklistPhone::active()->latest('banned_at')->get();
    }

    public function getWhitelistedPhones(): Collection
    {
        return WhitelistPhone::active()->get();
    }
}
