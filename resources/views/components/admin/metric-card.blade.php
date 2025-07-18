<div class="bg-white rounded-lg shadow-sm p-6">
    <div class="flex items-center">
        <div class="p-3 rounded-full {{ $bgColor ?? 'bg-blue-100' }} mr-4">
            {!! $icon !!}
        </div>
        <div>
            <div class="text-gray-500 text-sm">{{ $label }}</div>
            <div class="text-2xl font-semibold">{{ $value }}</div>
        </div>
    </div>
    @if(isset($change))
        <div class="mt-2 text-sm {{ $change >= 0 ? 'text-green-600' : 'text-red-600' }}">
            {{ $change >= 0 ? '+' : '' }}{{ $change }} new this period
        </div>
    @endif
</div>