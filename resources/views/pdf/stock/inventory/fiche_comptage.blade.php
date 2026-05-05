@extends('pdf.layout')

@section('content')
    <div class="flex justify-between items-start mb-6 border-b-2 border-[#002157] pb-4">
        <div>
            <h1 class="text-blue-batistack text-2xl font-bold uppercase m-0">{{ $title }}</h1>
            <p class="text-slate-500 mt-1">Dépôt : <strong>{{ $session->warehouse->name }}</strong></p>
            <p class="text-slate-500">Ouvert le : <strong>{{ $session->opened_at->format('d/m/Y H:i') }}</strong> par <strong>{{ $session->creator->name }}</strong></p>
        </div>
        <div class="text-right">
            <p class="text-slate-400 text-xs italic">Imprimé le {{ now()->format('d/m/Y H:i') }}</p>
            <p class="text-slate-700 text-sm font-bold mt-1">{{ $session->lines->count() }} article(s) à compter</p>
            @if($session->notes)
                <p class="text-slate-500 text-xs mt-1 italic">{{ $session->notes }}</p>
            @endif
        </div>
    </div>

    <table class="w-full border-collapse text-xs">
        <thead>
        <tr>
            <th class="bg-blue-batistack text-white p-2 text-left w-20">SKU</th>
            <th class="bg-blue-batistack text-white p-2 text-left">Désignation</th>
            <th class="bg-blue-batistack text-white p-2 text-left">Catégorie</th>
            <th class="bg-blue-batistack text-white p-2 text-right w-24">Qté théorique</th>
            <th class="bg-blue-batistack text-white p-2 text-center w-32">Qté comptée</th>
            <th class="bg-blue-batistack text-white p-2 text-left w-32">Observation</th>
        </tr>
        </thead>
        <tbody>
        @foreach($session->lines->sortBy('article.name') as $line)
            <tr class="{{ $loop->even ? 'bg-slate-50' : 'bg-white' }}">
                <td class="p-2 border-b border-slate-200 font-mono text-[10px] text-slate-500">
                    {{ $line->article->sku }}
                </td>
                <td class="p-2 border-b border-slate-200 font-semibold">
                    {{ $line->article->name }}
                </td>
                <td class="p-2 border-b border-slate-200 text-slate-500">
                    {{ $line->article->articleCategory->name ?? '—' }}
                </td>
                <td class="p-2 border-b border-slate-200 text-right font-mono">
                    {{ number_format($line->theoretical_quantity, 3, ',', ' ') }}
                </td>
                <td class="p-2 border-b border-slate-200">
                    <div class="border-b-2 border-slate-400 h-5 mx-2"></div>
                </td>
                <td class="p-2 border-b border-slate-200">
                    <div class="border-b border-slate-300 h-5 mx-1"></div>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div class="mt-8 grid grid-cols-3 gap-8 text-xs">
        <div class="border border-slate-300 rounded p-3">
            <p class="font-bold text-slate-700 mb-2">Compteur</p>
            <div class="border-b border-slate-400 h-6 mt-4"></div>
            <p class="text-slate-400 mt-1">Nom & Signature</p>
        </div>
        <div class="border border-slate-300 rounded p-3">
            <p class="font-bold text-slate-700 mb-2">Vérificateur</p>
            <div class="border-b border-slate-400 h-6 mt-4"></div>
            <p class="text-slate-400 mt-1">Nom & Signature</p>
        </div>
        <div class="border border-slate-300 rounded p-3">
            <p class="font-bold text-slate-700 mb-2">Date de comptage</p>
            <div class="border-b border-slate-400 h-6 mt-4"></div>
            <p class="text-slate-400 mt-1">JJ / MM / AAAA</p>
        </div>
    </div>

    <div class="mt-4 text-right text-slate-400 text-[9px] italic border-t border-slate-100 pt-3">
        Document confidentiel — BatiStack · {{ $session->reference }}
    </div>
@endsection
