@extends('pdf.layout')

@section('content')
    <div class="flex justify-between items-center mb-8 border-b-2 border-[#002157] pb-4">
        <div>
            <h1 class="text-blue-batistack text-2xl font-bold uppercase m-0">{{ $title }}</h1>
            <p class="text-slate-500 mt-1">Total : <span class="font-bold text-slate-900">{{ $tiers->count() }} tiers</span></p>
        </div>
        <div class="text-right">
            <p class="text-slate-400 text-xs italic">Généré le {{ now()->format('d/m/Y') }}</p>
        </div>
    </div>

    <table class="w-full border-collapse">
        <thead>
            <tr>
                <th class="bg-blue-batistack text-white text-[10px] uppercase p-3 text-left w-20">Code</th>
                <th class="bg-blue-batistack text-white text-[10px] uppercase p-3 text-left">Nom / Raison Sociale</th>
                <th class="bg-blue-batistack text-white text-[10px] uppercase p-3 text-left">Typologie</th>
                <th class="bg-blue-batistack text-white text-[10px] uppercase p-3 text-left">Catégorie</th>
                <th class="bg-blue-batistack text-white text-[10px] uppercase p-3 text-left">SIREN</th>
                <th class="bg-blue-batistack text-white text-[10px] uppercase p-3 text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tiers as $item)
                <tr class="even:bg-slate-50">
                    <td class="p-3 border-b border-slate-200 font-bold text-blue-batistack">{{ $item->code }}</td>
                    <td class="p-3 border-b border-slate-200">
                        <div class="font-semibold text-slate-900">{{ $item->name }}</div>
                        @if($item->website)
                            <div class="text-[10px] text-blue-500">{{ $item->website }}</div>
                        @endif
                    </td>
                    <td class="p-3 border-b border-slate-200 text-slate-600">
                        {{ $item->typology?->getLabel() ?? $item->typology?->name ?? 'N/A' }}
                    </td>
                    <td class="p-3 border-b border-slate-200 text-slate-600">
                        {{ $item->category?->getLabel() ?? $item->category?->name ?? 'N/A' }}
                    </td>
                    <td class="p-3 border-b border-slate-200 text-slate-600">
                        {{ $item->siren ?? '-' }}
                    </td>
                    <td class="p-3 border-b border-slate-200 text-center">
                        @if($item->status)
                            <span class="px-2 py-0.5 rounded-full text-[9px] font-bold uppercase
                                {{ $item->status->value === 'active' ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-600' }}">
                                {{ $item->status->getLabel() ?? $item->status->name }}
                            </span>
                        @else
                            -
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="p-8 text-center text-slate-400 italic">
                        Aucun tiers trouvé dans la base de données.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-8 text-right text-slate-400 text-[10px] italic pt-4 border-t border-slate-100">
        Document confidentiel - BatiStack v1.0
    </div>
@endsection
