<x-app-layout>

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h2 class="text-3xl font-bold text-gray-900">{{ __('Activity Logs') }}</h2>
            <p class="text-gray-600 mt-2">{{ __('Track all system activities and changes') }}</p>
        </div>

        <!-- Filtros -->
        <div class="bg-white shadow-md rounded-lg p-6 mb-6">
            <form method="GET" action="{{ route('logs.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Filtro por Módulo -->
                <div>
                    <label for="module" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Module') }}</label>
                    <select name="module" id="module" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">{{ __('All Modules') }}</option>
                        @foreach($modules as $mod)
                            <option value="{{ $mod }}" @selected(request('module') === $mod)>{{ $mod }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Filtro por Utilizador -->
                <div>
                    <label for="user_id" class="block text-sm font-medium text-gray-700 mb-2">{{ __('User') }}</label>
                    <select name="user_id" id="user_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">{{ __('All Users') }}</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" @selected(request('user_id') == $user->id)>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Filtro por Data Inicial -->
                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Start Date') }}</label>
                    <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <!-- Filtro por Data Final -->
                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">{{ __('End Date') }}</label>
                    <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <!-- Botões -->
                <div class="flex items-end gap-2 md:col-span-4">
                    <button type="submit" class="flex-1 md:flex-none bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg transition">
                        {{ __('Filter') }}
                    </button>
                    @if(request()->hasAny(['module', 'user_id', 'start_date', 'end_date']))
                        <a href="{{ route('logs.index') }}" class="flex-1 md:flex-none bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-lg text-center transition">
                            {{ __('Clear') }}
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Tabela de Logs -->
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">{{ __('Date') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">{{ __('Time') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">{{ __('User') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">{{ __('Module') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">{{ __('Object ID') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">{{ __('Change') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">{{ __('IP') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">{{ __('Browser') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($logs as $log)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $log->log_date->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $log->log_time?->format('H:i:s') ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    @if($log->user)
                                        {{ $log->user->name }}
                                    @else
                                        <span class="text-gray-500">{{ __('System') }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                        {{ $log->module }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    @if($log->object_id)
                                        {{ $log->object_id }}
                                    @else
                                        <span class="text-gray-500">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 max-w-md truncate">
                                    {{ $log->change }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 font-mono text-xs">
                                    {{ $log->ip ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $log->browser ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <a href="{{ route('logs.show', $log) }}" class="text-indigo-600 hover:text-indigo-900 font-medium">
                                        {{ __('View') }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-6 py-4 text-center text-gray-500">
                                    {{ __('No logs found for the selected filters.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginação -->
            @if($logs->hasPages())
                <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
</x-app-layout>
