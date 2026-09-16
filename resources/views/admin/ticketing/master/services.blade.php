@extends('layouts.master')

@section('title', 'Master Layanan')
@section('content')
@php
    $viewTitle = 'Master Layanan';
    $description = 'Kelola layanan yang tersedia pada form Hotline.';
    $storeRoute = route('ticketing.master.services.store');
    $itemUnit = 'service';
    $destroyPrefix = 'ticketing.master.services.destroy';
    $updatePrefix = 'ticketing.master.services.update';
@endphp
@include('admin.ticketing.master._generic-master')
@endsection