<?php

namespace App\Services;

use App\Models\Visit;
use App\Models\VisitOtp;
use Illuminate\Support\Facades\Hash;

class OtpService
{
    public function generateAndStore(Visit $visit): string
    {
        $otp = (string) random_int(100000, 999999);
        $visit->visitOtp?->delete();

        VisitOtp::create([
            'visit_id' => $visit->id,
            'otp_hash' => Hash::make($otp),
            'expires_at' => now()->addMinutes(VisitOtp::VALIDITY_MINUTES),
            'attempt_count' => 0,
        ]);

        return $otp;
    }

    public function verify(Visit $visit, string $inputOtp): bool
    {
        $otpRecord = $visit->visitOtp;
        if (! $otpRecord) {
            return false;
        }
        if ($otpRecord->isExpired()) {
            return false;
        }
        if ($otpRecord->isMaxAttemptsReached()) {
            return false;
        }

        $otpRecord->increment('attempt_count');

        if (! Hash::check($inputOtp, $otpRecord->otp_hash)) {
            return false;
        }

        $otpRecord->update(['verified_at' => now()]);
        return true;
    }

    public function isMaxAttemptsReached(Visit $visit): bool
    {
        $otpRecord = $visit->visitOtp;
        return $otpRecord && $otpRecord->isMaxAttemptsReached();
    }
}
