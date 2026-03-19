@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <a href="{{ route('logs.index') }}" class="text-indigo-600 hover:text-indigo-900 font-medium">← {{ __('Back to Logs') }}</a>
            <h2 class="text-3xl font-bold text-gray-900 mt-4">{{ __('Log Details') }}</h2>
        </div>

        <!-- Detalhes do Log -->
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">{{ $log->change }}</h3>
            </div>

            <div class="divide-y divide-gray-200">
                <!-- Data e Hora -->
                <div class="px-6 py-4 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Date') }}</label>
                        <p class="text-gray-900 font-medium">{{ $log->log_date->format('d/m/Y') }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Time') }}</label>
                        <p class="text-gray-900 font-medium">{{ $log->log_time->format('H:i:s') }}</p>
                    </div>
                </div>

                <!-- Utilizador -->
                <div class="px-6 py-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('User') }}</label>
                    @if($log->user)
                        <p class="text-gray-900 font-medium">{{ $log->user->name }} ({{ $log->user->email }})</p>
                    @else
                        <p class="text-gray-500">{{ __('System') }}</p>
                    @endif
                </div>

                <!-- Módulo -->
                <div class="px-6 py-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Module') }}</label>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                        {{ $log->module }}
                    </span>
                </div>

                <!-- ID do Objeto -->
                <div class="px-6 py-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Object ID') }}</label>
                    @if($log->object_id)
                        <p class="text-gray-900 font-medium">{{ $log->object_id }}</p>
                    @else
                        <p class="text-gray-500">-</p>
                    @endif
                </div>

                <!-- Mudança -->
                <div class="px-6 py-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Change') }}</label>
                    <p class="text-gray-900 whitespace-pre-wrap break-words">{{ $log->change }}</p>
                </div>

                <!-- IP Address -->
                <div class="px-6 py-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('IP Address') }}</label>
                    @if($log->ip)
                        <p class="text-gray-900 font-mono font-medium">{{ $log->ip }}</p>
                    @else
                        <p class="text-gray-500">-</p>
                    @endif
                </div>

                <!-- Browser -->
                <div class="px-6 py-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Browser') }}</label>
                    @if($log->browser)
                        <p class="text-gray-900 font-medium">{{ $log->browser }}</p>
                    @else
                        <p class="text-gray-500">-</p>
                    @endif
                </div>

                <!-- Metadata -->
                <div class="px-6 py-4 bg-gray-50">
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Metadata') }}</label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs text-gray-500">{{ __('Created At') }}</p>
                            <p class="text-gray-900 font-mono text-sm">{{ $log->created_at->format('d/m/Y H:i:s') }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">{{ __('Updated At') }}</p>
                            <p class="text-gray-900 font-mono text-sm">{{ $log->updated_at->format('d/m/Y H:i:s') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-6">
            <a href="{{ route('logs.index') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition">
                {{ __('Back to Logs') }}
            </a>
        </div>
    </div>
</div>
@endsection
