@extends('pdf.layout')

@section('content')
    <div class="flex justify-between items-start mb-6 border-b-2 border-[#002157] pb-4">
        <div>
            <h1 class="text-blue-batistack text-2xl font-bold uppercase m-0">{{ $title }}</h1>
            <p class="text-slate-500 mt-1">Dépôt : <strong>{{ $session->warehouse->name }}</strong></p>
            <p class="text-slate-500">
                Période : <strong>{{ $session->opened_at->format('d/m/Y') }}</strong>
                → <strong>{{ $session->validated_at?->format('d/m/Y') ?? $session->closed_at?->format('d/m/Y') ?? '—' }}</strong>
            </p>
        </div>
        <div class="text-right">
        <span class="px-2 py-1 rounded text-xs font-bold
            {{ $session->status->value === 'validated' ? 'bg-green-100 text-green-700' : 'bg-orange-100 text-orange-700' }}">
            {{ $session->status->getLabel() }}
        </span>
            <p class="text-slate-400 text-xs italic mt-2">Généré le {{ now()->format('d/m/Y H:i') }}</p>
        </div>
    </div>

    {{-- Résumé --}}
    <div class="grid grid-cols-4 gap-4 mb-6 text-center text-xs">
        <div class="border border-slate-200 rounded p-3 bg-slate-50">
            <p class="text-slate-500">Articles comptés</p>
            <p class="text-xl font-bold text-slate-800 mt-1">{{ $lines->count() }}</p>
        </div>
        <div class="border border-green-200 rounded p-3 bg-green-50">
            <p class="text-green-600">Sans écart</p>
            <p class="text-xl font-bold text-green-700 mt-1">{{ $linesOk->count() }}</p>
        </div>
        <div class="border border-red-200 rounded p-3 bg-red-50">
            <p class="text-red-600">Avec écart</p>
            <p class="text-xl font-bold text-red-700 mt-1">{{ $linesWithDiff->count() }}</p>
        </div>
        <div class="border border-blue-200 rounded p-3 bg-blue-50">
            <p class="text-blue-600">Validé par</p>
            <p class="text-sm font-bold text-blue-700 mt-1">{{ $session->validator?->name ?? '—' }}</p>
        </div>
    </div>

    {{-- Lignes avec écarts --}}
    @if($linesWithDiff->count() > 0)
        <h3 class="text-sm font-bold text-red-700 uppercase mb-2">Écarts détectés ({{ $linesWithDiff->count() }})</h3>
        <table class="w-full border-collapse text-xs mb-6">
            <thead>
            <tr>
                <th class="bg-red-700 text-white p-2 text-left">Article</th>
                <th class="bg-red-700 text-white p-2 text-right">Théorique</th>
                <th class="bg-red-700 text-white p-2 text-right">Compté</th>
                <th class="bg-red-700 text-white p-2 text-right">Écart</th>
                <th class="bg-red-700 text-white p-2 text-center">Ajustement</th>
            </tr>
            </thead>
            <tbody>
            @foreach($linesWithDiff->sortBy('article.name') as $line)
                @php $diff = $line->counted_quantity - $line->theoretical_quantity; @endphp
                <tr class="{{ $loop->even ? 'bg-red-50' : 'bg-white' }}">
                    <td class="p-2 border-b border-slate-200">
                        <div class="font-semibold">{{ $line->article->name }}</div>
                        <div class="text-[9px] text-slate-400">{{ $line->article->sku }}</div>
                    </td>
                    <td class="p-2 border-b border-slate-200 text-right font-mono">
                        {{ number_format($line->theoretical_quantity, 3, ',', ' ') }}
                    </td>
                    <td class="p-2 border-b border-slate-200 text-right font-mono font-bold">
                        {{ number_format($line->counted_quantity, 3, ',', ' ') }}
                    </td>
                    <td class="p-2 border-b border-slate-200 text-right font-mono font-bold {{ $diff > 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ $diff > 0 ? '+' : '' }}{{ number_format($diff, 3, ',', ' ') }}
                    </td>
                    <td class="p-2 border-b border-slate-200 text-center">
                <span class="px-1.5 py-0.5 rounded text-[9px] font-bold {{ $diff > 0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                    {{ $diff > 0 ? 'Gain' : 'Perte' }}
                </span>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif

    {{-- Lignes sans écart --}}
    @if($linesOk->count() > 0)
        <h3 class="text-sm font-bold text-green-700 uppercase mb-2">Articles conformes ({{ $linesOk->count() }})</h3>
        <table class="w-full border-collapse text-xs">
            <thead>
            <tr>
                <th class="bg-green-700 text-white p-2 text-left">Article</th>
                <th class="bg-green-700 text-white p-2 text-right">Quantité</th>
            </tr>
            </thead>
            <tbody>
            @foreach($linesOk->sortBy('article.name') as $line)
                <tr class="{{ $loop->even ? 'bg-green-50' : 'bg-white' }}">
                    <td class="p-2 border-b border-slate-200">
                        <span class="font-semibold">{{ $line->article->name }}</span>
                        <span class="text-[9px] text-slate-400 ml-2">{{ $line->article->sku }}</span>
                    </td>
                    <td class="p-2 border-b border-slate-200 text-right font-mono">
                        {{ number_format($line->theoretical_quantity, 3, ',', ' ') }}
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif

    <div class="mt-6 text-right text-slate-400 text-[9px] italic border-t border-slate-100 pt-3">
        Document confidentiel — BatiStack · {{ $session->reference }}
    </div>
@endsection
