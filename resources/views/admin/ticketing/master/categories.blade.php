@extends('layouts.master')

@section('title', 'Master Kategori')
@section('content')
@php
    $viewTitle = 'Master Kategori';
    $description = 'Kelola kategori aduan pada form Hotline.';
    $storeRoute = route('ticketing.master.categories.store');
    $itemUnit = 'category';
    $destroyPrefix = 'ticketing.master.categories.destroy';
    $updatePrefix = 'ticketing.master.categories.update';
@endphp
@include('admin.ticketing.master._generic-master')
@endsection