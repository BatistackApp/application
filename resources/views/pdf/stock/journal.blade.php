@extends('pdf.layout')

@section('content')
    <div class="flex justify-between items-center mb-6 border-b-2 border-[#002157] pb-4">
        <div>
            <h1 class="text-blue-batistack text-2xl font-bold uppercase m-0">{{ $title }}</h1>
            <p class="text-slate-500 mt-1">
                Période : <strong>{{ $date_from->format('d/m/Y') }}</strong> → <strong>{{ $date_to->format('d/m/Y') }}</strong>
                @if($warehouse) · Dépôt : <strong>{{ $warehouse->name }}</strong> @endif
            </p>
        </div>
        <div class="text-right">
            <p class="text-slate-400 text-xs italic">Généré le {{ now()->format('d/m/Y H:i') }}</p>
            <p class="text-slate-700 text-sm font-bold">{{ $mouvements->count() }} mouvement(s)</p>
            <p class="text-green-700 text-xs">Entrées : {{ number_format($total_entries, 3, ',', ' ') }}</p>
            <p class="text-red-700 text-xs">Sorties : {{ number_format($total_exits, 3, ',', ' ') }}</p>
        </div>
    </div>

    <table class="w-full border-collapse text-xs">
        <thead>
        <tr>
            <th class="bg-blue-batistack text-white p-2 text-left">Date</th>
            <th class="bg-blue-batistack text-white p-2 text-left">Type</th>
            <th class="bg-blue-batistack text-white p-2 text-left">Article</th>
            <th class="bg-blue-batistack text-white p-2 text-left">Dépôt source</th>
            <th class="bg-blue-batistack text-white p-2 text-left">Dépôt dest.</th>
            <th class="bg-blue-batistack text-white p-2 text-right">Quantité</th>
            <th class="bg-blue-batistack text-white p-2 text-right">Coût U. HT</th>
            <th class="bg-blue-batistack text-white p-2 text-left">Référence</th>
            <th class="bg-blue-batistack text-white p-2 text-left">Opérateur</th>
        </tr>
        </thead>
        <tbody>
        @forelse($mouvements as $m)
            <tr class="even:bg-slate-50">
                <td class="p-2 border-b border-slate-200 whitespace-nowrap">{{ $m->created_at->format('d/m/Y H:i') }}</td>
                <td class="p-2 border-b border-slate-200">
                <span class="px-1.5 py-0.5 rounded text-[9px] font-bold uppercase
                    @if($m->type->value === 'entry' || $m->type->value === 'return') bg-green-100 text-green-700
                    @elseif($m->type->value === 'exit') bg-red-100 text-red-700
                    @elseif($m->type->value === 'transfer') bg-blue-100 text-blue-700
                    @else bg-orange-100 text-orange-700 @endif">
                    {{ $m->type->getLabel() }}
                </span>
                </td>
                <td class="p-2 border-b border-slate-200">
                    <div class="font-semibold">{{ $m->article->name }}</div>
                    <div class="text-[9px] text-slate-400">{{ $m->article->sku }}</div>
                </td>
                <td class="p-2 border-b border-slate-200">{{ $m->warehouse->name ?? '—' }}</td>
                <td class="p-2 border-b border-slate-200">{{ $m->targetWarehouse->name ?? '—' }}</td>
                <td class="p-2 border-b border-slate-200 text-right font-mono">
                    {{ number_format($m->quantity, 3, ',', ' ') }}
                </td>
                <td class="p-2 border-b border-slate-200 text-right font-mono">
                    {{ $m->unit_cost_ht ? number_format($m->unit_cost_ht, 2, ',', ' ').' €' : '—' }}
                </td>
                <td class="p-2 border-b border-slate-200 text-[10px]">{{ $m->reference ?? '—' }}</td>
                <td class="p-2 border-b border-slate-200 text-[10px]">{{ $m->user->name ?? '—' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="9" class="p-8 text-center text-slate-400 italic">
                    Aucun mouvement sur cette période.
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>

    <div class="mt-6 text-right text-slate-400 text-[9px] italic border-t border-slate-100 pt-3">
        Document confidentiel — BatiStack
    </div>
@endsection
