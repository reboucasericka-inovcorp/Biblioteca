<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-900 leading-tight">
            Logs
        </h2>
    </x-slot>

    <div class="space-y-6">
        <div class="card shadow bg-base-100">
            <div class="card-body p-6">
                <div class="overflow-x-auto">
                    <table class="table w-full">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Hora</th>
                                <th>Utilizador</th>
                                <th>Módulo</th>
                                <th>ID Objeto</th>
                                <th>Alteração</th>
                                <th>IP</th>
                                <th>Browser</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($logs as $log)
                                <tr>
                                    <td>{{ optional($log->log_date)->format('d/m/Y') ?? '-' }}</td>
                                    <td>{{ optional($log->log_time)->format('H:i:s') ?? '-' }}</td>
                                    <td>{{ $log->user?->name ?? 'Sistema' }}</td>
                                    <td>{{ $log->module }}</td>
                                    <td>{{ $log->object_id ?? '-' }}</td>
                                    <td class="max-w-md truncate" title="{{ $log->change }}">{{ $log->change }}</td>
                                    <td>{{ $log->ip ?? '-' }}</td>
                                    <td>{{ $log->browser ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-gray-500 py-4">
                                        Sem logs disponíveis.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $logs->links() }}
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
