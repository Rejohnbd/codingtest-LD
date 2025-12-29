<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>
    @include('partials.edit-user-modal')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    {{ __("Welcome Home") }}
                    <h2 class="mb-4">User List</h2>
                    @include('datatables.user-list-datatable')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
