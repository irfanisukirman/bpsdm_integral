@extends('layouts.master')
@section('content')
<h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Sistem /</span> Log Aktivitas Admin</h4>

<div class="card">
    <h5 class="card-header">Riwayat Aktivitas Terakhir</h5>
    <div class="table-responsive text-nowrap">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Waktu</th>
                    <th>Admin</th>
                    <th>Modul</th>
                    <th>Aktivitas</th>
                    <th>IP Address</th>
                </tr>
            </thead>
            <tbody>
                @foreach($logs as $log)
                <tr>
                    <td>{{ $log->created_at->format('d/m/Y H:i') }}</td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="badge badge-center rounded-pill bg-label-primary me-2">
                                {{ substr($log->user->name, 0, 1) }}
                            </div>
                            <span>{{ $log->user->name }}</span>
                        </div>
                    </td>
                    <td><span class="badge bg-label-secondary">{{ $log->module }}</span></td>
                    <td class="text-wrap" style="max-width: 300px;">{{ $log->activity }}</td>
                    <td><small class="text-muted">{{ $log->ip_address }}</small></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection