@extends('layouts.app')

@section('content')
    <livewire:agent-grid />
    <livewire:dialog-panel />
    <livewire:run-history-panel />
    <livewire:agent-edit-modal />
    <livewire:log-stream-panel />
@endsection
