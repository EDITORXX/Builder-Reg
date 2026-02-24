<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\BuilderFirm;
use App\Models\Customer;
use App\Models\Form;
use App\Models\Lead;
use App\Models\VisitCheckIn;
use App\Models\VisitSchedule;
use App\Services\LockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class VisitCheckinController extends Controller
{
    public function __construct(
        private LockService $lockService
    ) {}

    public function show(string $token): View|RedirectResponse
    {
        $schedule = VisitSchedule::where('token', $token)->with(['builderFirm', 'project', 'channelPartner'])->first();

        if (! $schedule) {
            return $this->invalidTokenView();
        }

        if (! $schedule->builderFirm->scheduled_visit_enabled) {
            return $this->invalidTokenView('This check-in is not available.');
        }

        if ($schedule->status !== VisitSchedule::STATUS_SCHEDULED) {
            return $this->invalidTokenView('This visit link has already been used or is no longer valid.');
        }

        if ($schedule->isTokenExpired()) {
            return $this->invalidTokenView('This visit link has expired. Please contact your channel partner.');
        }

        $builder = $schedule->builderFirm;
        $form = Form::getActiveForBuilder($builder, Form::TYPE_CUSTOMER_REGISTRATION);
        $fields = $form?->formFields ?? collect();

        return view('visit.checkin', [
            'schedule' => $schedule,
            'builder' => $builder,
            'fields' => $fields,
        ]);
    }

    public function submit(Request $request, string $token): RedirectResponse
    {
        $schedule = VisitSchedule::where('token', $token)->with(['builderFirm', 'project', 'channelPartner'])->first();

        if (! $schedule || $schedule->status !== VisitSchedule::STATUS_SCHEDULED
            || ! $schedule->builderFirm->scheduled_visit_enabled
            || $schedule->isTokenExpired()) {
            return redirect()->route('visit.checkin', ['token' => $token])
                ->with('error', 'Invalid or expired link. Please try again or contact your channel partner.');
        }

        $request->validate([
            'visit_photo' => 'required|file|max:5120|mimes:jpeg,jpg,png,gif',
        ]);

        $normalizedMobile = Customer::normalizeMobile($schedule->customer_mobile);
        $lockCheck = $this->lockService->checkLockAndDuplicate(
            (int) $schedule->project_id,
            $normalizedMobile,
            (int) $schedule->channel_partner_id
        );

        if (! $lockCheck['allowed']) {
            return redirect()->back()->with('error', $lockCheck['message']);
        }

        $customer = Customer::firstOrCreate(
            ['mobile' => $normalizedMobile],
            [
                'name' => $schedule->customer_name,
                'email' => $schedule->customer_email,
                'city' => null,
            ]
        );
        if (! $customer->wasRecentlyCreated) {
            $customer->update([
                'name' => $schedule->customer_name,
                'email' => $schedule->customer_email ?? $customer->email,
            ]);
        }

        $path = $request->file('visit_photo')->store(
            'visit_photos/' . now()->format('Ym'),
            'public'
        );

        $now = now();
        $lead = Lead::firstOrCreate(
            [
                'project_id' => $schedule->project_id,
                'customer_id' => $customer->id,
            ],
            [
                'channel_partner_id' => $schedule->channel_partner_id,
                'created_by' => null,
                'status' => Lead::STATUS_PENDING_VERIFICATION,
                'source' => Lead::SOURCE_CHANNEL_PARTNER,
                'visit_photo_path' => $path,
                'visit_status' => Lead::VISITED,
                'verification_status' => Lead::PENDING_VERIFICATION,
                'sales_status' => Lead::SALES_NEW,
            ]
        );

        if (! $lead->wasRecentlyCreated) {
            $lead->update([
                'channel_partner_id' => $schedule->channel_partner_id,
                'visit_photo_path' => $path,
                'visit_status' => Lead::VISITED,
                'verification_status' => Lead::PENDING_VERIFICATION,
            ]);
        }

        VisitCheckIn::create([
            'lead_id' => $lead->id,
            'visit_schedule_id' => $schedule->id,
            'project_id' => $schedule->project_id,
            'channel_partner_id' => $schedule->channel_partner_id,
            'customer_mobile' => $normalizedMobile,
            'submitted_at' => $now,
            'visit_photo_path' => $path,
            'verification_status' => VisitCheckIn::VERIFICATION_PENDING,
            'visit_type' => VisitCheckIn::TYPE_SCHEDULED_CHECKIN,
        ]);

        $schedule->update([
            'status' => VisitSchedule::STATUS_CHECKED_IN,
            'lead_id' => $lead->id,
            'checked_in_at' => $now,
        ]);

        return redirect()->route('visit.checkin.thanks');
    }

    private function invalidTokenView(?string $message = null): View
    {
        return view('visit.checkin-invalid', [
            'message' => $message ?? 'Invalid or expired link. Please contact your channel partner for a valid check-in link.',
        ]);
    }
}
