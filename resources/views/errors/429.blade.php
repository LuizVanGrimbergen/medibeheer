@php
    $retryAfter = (int) ($exception->getHeaders()['Retry-After'] ?? 0);
    $message = $retryAfter > 0
        ? trans('errors.429.throttle', ['seconds' => $retryAfter])
        : trans('errors.429.message');
@endphp

@extends('errors::minimal')

@section('title', __('errors.429.title'))
@section('code', '429')
@section('heading', __('errors.429.title'))
@section('message', $message)

@if ($retryAfter > 0)
    @push('scripts')
        <script>
            (() => {
                const messageElement = document.getElementById('error-message');

                if (!messageElement) {
                    return;
                }

                let remainingSeconds = {{ $retryAfter }};
                const template = @json(trans('errors.429.throttle', ['seconds' => '__SECONDS__']));

                const renderMessage = () => {
                    messageElement.textContent = template.replace('__SECONDS__', String(remainingSeconds));
                };

                renderMessage();

                const intervalId = window.setInterval(() => {
                    if (remainingSeconds <= 1) {
                        remainingSeconds = 0;
                        messageElement.textContent = @json(trans('errors.429.message'));
                        window.clearInterval(intervalId);

                        return;
                    }

                    remainingSeconds -= 1;
                    renderMessage();
                }, 1000);
            })();
        </script>
    @endpush
@endif
