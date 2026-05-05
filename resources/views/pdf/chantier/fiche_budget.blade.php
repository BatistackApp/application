@extends('pdf.layout')

@section('content')
    <div class="flex justify-between items-start mb-6 border-b-2 border-[#002157] pb-4">
        <div>
            <h1 class="text-blue-batistack text-2xl font-bold uppercase m-0">{{ $title }}</h1>
            <p class="text-slate-500 mt-1">Client : <strong>{{ $chantier->client->name }}</strong></p>
            <p class="text-slate-500">
                {{ $chantier->adresse }} — {{ $chantier->code_postal }} {{ $chantier->ville }}
            </p>
        </div>
        <div class="text-right">
            <p class="text-slate-700 text-lg font-bold">
                Total : {{ number_format($totalBudget, 2, ',', ' ') }} €
            </p>
            <p class="text-slate-400 text-xs italic mt-1">Généré le {{ now()->format('d/m/Y H:i') }}</p>
        </div>
    </div>

    @foreach($budgetParType as $type => $lignes)
        @php $typeEnum = \App\Enums\Chantier\ChantierBudgetType::from($type); @endphp
        <div class="mb-5">
            <h3 class="text-xs font-bold uppercase bg-slate-100 px-3 py-1.5 mb-2 text-slate-700">
                {{ $typeEnum->getLabel() }}
                — {{ number_format($lignes->sum('cout_total'), 2, ',', ' ') }} €
            </h3>
            <table class="w-full border-collapse text-xs">
                <thead>
                <tr>
                    <th class="bg-blue-batistack text-white p-2 text-left">Désignation</th>
                    <th class="bg-blue-batistack text-white p-2 text-right">Qté</th>
                    <th class="bg-blue-batistack text-white p-2 text-left">Unité</th>
                    <th class="bg-blue-batistack text-white p-2 text-right">Prix U. HT</th>
                    <th class="bg-blue-batistack text-white p-2 text-right">Total HT</th>
                </tr>
                </thead>
                <tbody>
                @foreach($lignes as $ligne)
                    <tr class="{{ $loop->even ? 'bg-slate-50' : 'bg-white' }}">
                        <td class="p-2 border-b border-slate-200">
                            {{ $ligne->designation }}
                            @if($ligne->article)
                                <span class="text-[9px] text-slate-400 ml-1">({{ $ligne->article->sku }})</span>
                            @endif
                        </td>
                        <td class="p-2 border-b border-slate-200 text-right font-mono">
                            {{ number_format($ligne->quantite, 3, ',', ' ') }}
                        </td>
                        <td class="p-2 border-b border-slate-200">{{ $ligne->unite ?? '—' }}</td>
                        <td class="p-2 border-b border-slate-200 text-right font-mono">
                            {{ number_format($ligne->cout_unitaire, 2, ',', ' ') }} €
                        </td>
                        <td class="p-2 border-b border-slate-200 text-right font-mono font-bold">
                            {{ number_format($ligne->cout_total, 2, ',', ' ') }} €
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endforeach

    <div class="mt-4 text-right text-slate-400 text-[9px] italic border-t border-slate-100 pt-3">
        Document confidentiel — BatiStack · {{ $chantier->reference }}
    </div>
@endsection
