@extends('layouts.app')

@section('content')
    <livewire:dashboard-stats />
    <livewire:agent-grid />
    <livewire:dialog-panel />
    <livewire:run-history-panel />
    <livewire:agent-edit-modal />
    <livewire:log-stream-panel />
    <livewire:claude-md-editor />
@endsection
