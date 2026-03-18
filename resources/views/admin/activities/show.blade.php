@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">{{ $activity->title }} - Management</h1>
        <a href="{{ route('admin.activities.export', $activity) }}" class="bg-green-500 text-white px-4 py-2 rounded">Export CSV</a>
    </div>

    <div class="bg-white shadow-md rounded my-6 overflow-x-auto">
        <table class="min-w-full w-full table-auto">
            <thead>
                <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
                    <th class="py-3 px-6 text-left">Child</th>
                    <th class="py-3 px-6 text-left">Parent</th>
                    <th class="py-3 px-6 text-center">Status</th>
                    <th class="py-3 px-6 text-center">Paid</th>
                    <th class="py-3 px-6 text-center">Attended</th>
                    <th class="py-3 px-6 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="text-gray-600 text-sm font-light">
                @foreach($registrations as $reg)
                <tr class="border-b border-gray-200 hover:bg-gray-100">
                    <td class="py-3 px-6 text-left whitespace-nowrap">
                        <span class="font-medium">{{ $reg->child->first_name }} {{ $reg->child->last_name }}</span>
                    </td>
                    <td class="py-3 px-6 text-left">
                        {{ $reg->child->parent->name }}<br>
                        <span class="text-xs text-gray-500">{{ $reg->child->parent->email }}</span>
                    </td>
                    <td class="py-3 px-6 text-center">
                        <span class="bg-{{ $reg->status === 'confirmed' ? 'green' : ($reg->status === 'waiting' ? 'yellow' : 'red') }}-200 text-{{ $reg->status === 'confirmed' ? 'green' : ($reg->status === 'waiting' ? 'yellow' : 'red') }}-600 py-1 px-3 rounded-full text-xs">
                            {{ ucfirst($reg->status) }}
                        </span>
                        @if($reg->position) <span class="text-xs">#{{ $reg->position }}</span> @endif
                    </td>
                    <td class="py-3 px-6 text-center">
                        {{ number_format($reg->amount_paid, 2) }} / {{ number_format($reg->fee_amount, 2) }}
                    </td>
                    <td class="py-3 px-6 text-center">
                        @if($reg->attended)
                            <span class="text-green-500">Yes</span>
                        @else
                            <span class="text-red-500">No</span>
                        @endif
                    </td>
                    <td class="py-3 px-6 text-center">
                        <div class="flex item-center justify-center space-x-2">
                            @if($reg->status === 'waiting' || $reg->status === 'pending_approval')
                                <form action="{{ route('admin.registrations.promote', $reg) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="text-blue-500 hover:underline">Promote</button>
                                </form>
                            @endif

                            @if($reg->status === 'confirmed' && !$reg->attended)
                                <form action="{{ route('admin.registrations.absent', $reg) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="text-red-500 hover:underline">Mark Absent</button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
