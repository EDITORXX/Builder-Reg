@extends('layouts.app')

@section('title', 'Plans')
@section('heading', 'Plans')
@section('subtitle', 'Subscription plans and limits. Tenants are assigned a plan when created.')

@section('content')
    <div class="card">
        <div class="card-body">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 1px solid var(--border); text-align: left;">
                        <th style="padding: 0.75rem 0; font-weight: 600;">Plan</th>
                        <th style="padding: 0.75rem 0; font-weight: 600;">Users</th>
                        <th style="padding: 0.75rem 0; font-weight: 600;">Projects</th>
                        <th style="padding: 0.75rem 0; font-weight: 600;">Brokers (CPs)</th>
                        <th style="padding: 0.75rem 0; font-weight: 600;">Leads</th>
                        <th style="padding: 0.75rem 0; font-weight: 600;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($plans as $plan)
                        <tr style="border-bottom: 1px solid var(--border);">
                            <td style="padding: 0.75rem 0;">{{ $plan->name }}</td>
                            <td style="padding: 0.75rem 0;">{{ $plan->max_users }}</td>
                            <td style="padding: 0.75rem 0;">{{ $plan->max_projects }}</td>
                            <td style="padding: 0.75rem 0;">{{ $plan->max_channel_partners }}</td>
                            <td style="padding: 0.75rem 0;">{{ $plan->max_leads }}</td>
                            <td style="padding: 0.75rem 0;">{{ $plan->is_active ? 'Active' : 'Inactive' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
