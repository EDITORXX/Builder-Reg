<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\BuilderFirm;
use App\Models\CpApplication;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\Project;
use App\Models\VisitSchedule;
use App\Services\LockService;
use App\Services\QrCodeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class CpVisitScheduleController extends Controller
{
    public function __construct(
        private QrCodeService $qrCodeService,
        private LockService $lockService
    ) {}

    private function ensureCp(): void
    {
        if (! session('api_token') || ! session('user')) {
            abort(Response::HTTP_FOUND, route('login'));
        }
        $user = session('user');
        if (! $user->isChannelPartner() || ! $user->channelPartner) {
            abort(403, 'Channel partner access only.');
        }
    }

    private function getBuildersWithFeature(): \Illuminate\Support\Collection
    {
        $cp = session('user')->channelPartner;
        return CpApplication::where('channel_partner_id', $cp->id)
            ->where('status', CpApplication::STATUS_APPROVED)
            ->with('builderFirm')
            ->get()
            ->pluck('builderFirm')
            ->filter(fn ($b) => $b && $b->scheduled_visit_enabled);
    }

    public function index(Request $request): View|RedirectResponse
    {
        $this->ensureCp();
        $user = session('user');
        $cp = $user->channelPartner;
        $buildersWithFeature = $this->getBuildersWithFeature();

        if ($buildersWithFeature->isEmpty()) {
            return view('cp.scheduled_visits.index', [
                'schedules' => collect()->paginate(20),
                'buildersWithFeature' => $buildersWithFeature,
                'limitInfo' => [],
            ]);
        }

        $builderIds = $buildersWithFeature->pluck('id')->toArray();
        $schedules = VisitSchedule::with(['project', 'builderFirm'])
            ->where('channel_partner_id', $cp->id)
            ->whereIn('builder_firm_id', $builderIds)
            ->orderByDesc('scheduled_at')
            ->paginate(20);

        $limitInfo = [];
        foreach ($buildersWithFeature as $builder) {
            $count = VisitSchedule::where('builder_firm_id', $builder->id)
                ->whereIn('status', [VisitSchedule::STATUS_SCHEDULED, VisitSchedule::STATUS_CHECKED_IN])
                ->count();
            $limit = $builder->scheduled_visit_limit;
            $limitInfo[$builder->id] = [
                'used' => $count,
                'limit' => $limit,
                'unlimited' => $limit === null || $limit === 0,
            ];
        }

        return view('cp.scheduled_visits.index', [
            'schedules' => $schedules,
            'buildersWithFeature' => $buildersWithFeature,
            'limitInfo' => $limitInfo,
        ]);
    }

    public function create(): View|RedirectResponse
    {
        $this->ensureCp();
        $buildersWithFeature = $this->getBuildersWithFeature();
        if ($buildersWithFeature->isEmpty()) {
            return redirect()->route('cp.scheduled-visits.index')
                ->with('error', 'Scheduled visit feature is not enabled for any of your builders.');
        }

        $builderId = request()->query('builder_id');
        $projects = collect();
        $selectedBuilder = null;
        if ($builderId && $buildersWithFeature->contains('id', (int) $builderId)) {
            $selectedBuilder = $buildersWithFeature->firstWhere('id', (int) $builderId);
            $projects = Project::where('builder_firm_id', $selectedBuilder->id)->where('status', 'active')->orderBy('name')->get();
        } elseif ($buildersWithFeature->count() === 1) {
            $selectedBuilder = $buildersWithFeature->first();
            $projects = Project::where('builder_firm_id', $selectedBuilder->id)->where('status', 'active')->orderBy('name')->get();
        }

        return view('cp.scheduled_visits.create', [
            'buildersWithFeature' => $buildersWithFeature,
            'projects' => $projects,
            'selectedBuilder' => $selectedBuilder,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureCp();
        $user = session('user');
        $cp = $user->channelPartner;
        $buildersWithFeature = $this->getBuildersWithFeature();
        if ($buildersWithFeature->isEmpty()) {
            return redirect()->route('cp.scheduled-visits.index')->with('error', 'Feature not available.');
        }

        $request->validate([
            'builder_firm_id' => 'required|exists:builder_firms,id',
            'project_id' => 'required|exists:projects,id',
            'customer_name' => 'required|string|max:255',
            'customer_mobile' => 'required|string|max:20',
            'customer_email' => 'nullable|email|max:255',
            'scheduled_at' => 'required|date',
        ]);

        $builder = BuilderFirm::find($request->input('builder_firm_id'));
        if (! $builder || ! $builder->scheduled_visit_enabled || (int) $builder->id !== (int) $buildersWithFeature->firstWhere('id', $builder->id)?->id) {
            return redirect()->back()->with('error', 'Invalid builder.');
        }

        $project = Project::find($request->input('project_id'));
        if (! $project || (int) $project->builder_firm_id !== (int) $builder->id) {
            return redirect()->back()->with('error', 'Invalid project.');
        }

        $limit = $builder->scheduled_visit_limit;
        if ($limit !== null && $limit > 0) {
            $count = VisitSchedule::where('builder_firm_id', $builder->id)
                ->whereIn('status', [VisitSchedule::STATUS_SCHEDULED, VisitSchedule::STATUS_CHECKED_IN])
                ->count();
            if ($count >= $limit) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Scheduled visit limit reached. Contact your builder admin.');
            }
        }

        $schedule = VisitSchedule::create([
            'builder_firm_id' => $builder->id,
            'project_id' => $project->id,
            'channel_partner_id' => $cp->id,
            'customer_name' => $request->input('customer_name'),
            'customer_mobile' => $request->input('customer_mobile'),
            'customer_email' => $request->input('customer_email'),
            'scheduled_at' => $request->input('scheduled_at'),
            'token' => Str::random(64),
            'status' => VisitSchedule::STATUS_SCHEDULED,
        ]);

        return redirect()->route('cp.scheduled-visits.show', $schedule)->with('success', 'Visit scheduled. Share the QR or link with the customer.');
    }

    public function show(Request $request, VisitSchedule $visitSchedule): View|Response|RedirectResponse
    {
        $this->ensureCp();
        Gate::forUser(session('user'))->authorize('view', $visitSchedule);

        $visitSchedule->load(['project', 'builderFirm', 'channelPartner']);
        $checkInUrl = route('visit.checkin', ['token' => $visitSchedule->token]);
        $qrPng = $this->qrCodeService->generatePng($checkInUrl, 280);

        if ($request->boolean('download')) {
            return response($qrPng, 200, [
                'Content-Type' => 'image/png',
                'Content-Disposition' => 'attachment; filename="visit-checkin-' . $visitSchedule->id . '.png"',
            ]);
        }

        return view('cp.scheduled_visits.show', [
            'schedule' => $visitSchedule,
            'checkInUrl' => $checkInUrl,
            'qrPngBase64' => base64_encode($qrPng),
        ]);
    }

    private function getApprovedBuilders(): \Illuminate\Support\Collection
    {
        $cp = session('user')->channelPartner;
        return CpApplication::where('channel_partner_id', $cp->id)
            ->where('status', CpApplication::STATUS_APPROVED)
            ->with('builderFirm')
            ->get()
            ->pluck('builderFirm')
            ->filter(fn ($b) => $b);
    }

    public function directVisitForm(): View|RedirectResponse
    {
        $this->ensureCp();
        $builders = $this->getApprovedBuilders();
        if ($builders->isEmpty()) {
            return redirect()->route('cp.dashboard')->with('error', 'No approved builders.');
        }
        $builderId = request()->query('builder_id');
        $projects = collect();
        $selectedBuilder = null;
        if ($builderId && $builders->contains('id', (int) $builderId)) {
            $selectedBuilder = $builders->firstWhere('id', (int) $builderId);
            $projects = Project::where('builder_firm_id', $selectedBuilder->id)->where('status', 'active')->orderBy('name')->get();
        } elseif ($builders->count() === 1) {
            $selectedBuilder = $builders->first();
            $projects = Project::where('builder_firm_id', $selectedBuilder->id)->where('status', 'active')->orderBy('name')->get();
        }
        return view('cp.direct_visit', [
            'builders' => $builders,
            'projects' => $projects,
            'selectedBuilder' => $selectedBuilder,
        ]);
    }

    public function directVisitSubmit(Request $request): RedirectResponse
    {
        $this->ensureCp();
        $cp = session('user')->channelPartner;
        $builders = $this->getApprovedBuilders();
        $request->validate([
            'builder_firm_id' => 'required|exists:builder_firms,id',
            'project_id' => 'required|exists:projects,id',
            'customer_name' => 'required|string|max:255',
            'customer_mobile' => 'required|string|max:20',
            'visit_photo' => 'required|file|max:5120|mimes:jpeg,jpg,png,gif',
        ]);
        $builder = BuilderFirm::find($request->input('builder_firm_id'));
        if (! $builder || ! $builders->contains('id', $builder->id)) {
            return redirect()->back()->with('error', 'Invalid builder.');
        }
        $project = Project::find($request->input('project_id'));
        if (! $project || (int) $project->builder_firm_id !== (int) $builder->id) {
            return redirect()->back()->with('error', 'Invalid project.');
        }
        $normalizedMobile = Customer::normalizeMobile($request->input('customer_mobile'));
        $lockCheck = $this->lockService->checkLockAndDuplicate((int) $project->id, $normalizedMobile, (int) $cp->id);
        if (! $lockCheck['allowed']) {
            return redirect()->back()->withInput()->with('error', $lockCheck['message']);
        }
        $customer = Customer::firstOrCreate(
            ['mobile' => $normalizedMobile],
            ['name' => $request->input('customer_name'), 'email' => null, 'city' => null]
        );
        if (! $customer->wasRecentlyCreated) {
            $customer->update(['name' => $request->input('customer_name')]);
        }
        $path = $request->file('visit_photo')->store('visit_photos/' . now()->format('Ym'), 'public');
        Lead::create([
            'project_id' => $project->id,
            'customer_id' => $customer->id,
            'channel_partner_id' => $cp->id,
            'created_by' => null,
            'status' => Lead::STATUS_PENDING_VERIFICATION,
            'source' => Lead::SOURCE_CHANNEL_PARTNER,
            'visit_photo_path' => $path,
        ]);
        return redirect()->route('cp.direct-visit')->with('success', 'Visit registered. Pending manager verification.');
    }
}
