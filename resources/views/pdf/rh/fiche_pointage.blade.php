@extends('pdf.layout')

@section('content')
    <div class="flex justify-between items-start mb-6 border-b-2 border-[#002157] pb-4">
        <div>
            <h1 class="text-blue-batistack text-xl font-bold uppercase m-0">{{ $title }}</h1>
            <p class="text-slate-500 mt-1">
                Matricule : <strong>{{ $session->employee->matricule }}</strong>
                &nbsp;·&nbsp; Contrat : <strong>{{ $session->employee->type_contrat->getLabel() }}</strong>
                &nbsp;·&nbsp; Taux : <strong>{{ number_format($session->employee->taux_horaire, 2, ',', ' ') }} €/h</strong>
            </p>
        </div>
        <div class="text-right">
        <span class="px-2 py-1 rounded text-xs font-bold
            @if($session->status->value === 'imputed') bg-blue-100 text-blue-800
            @elseif($session->status->value === 'validated') bg-green-100 text-green-700
            @else bg-slate-100 text-slate-600 @endif">
            {{ $session->status->getLabel() }}
        </span>
            @if($session->validator)
                <p class="text-slate-400 text-xs mt-1">Validé par {{ $session->validator->name }}</p>
            @endif
            <p class="text-slate-400 text-xs italic mt-1">Généré le {{ now()->format('d/m/Y H:i') }}</p>
        </div>
    </div>

    {{-- Tableau des lignes par jour --}}
    @foreach($linesByDate as $dateStr => $lines)
        @php $date = \Carbon\Carbon::parse($dateStr); @endphp
        <div class="mb-4">
            <div class="bg-slate-100 px-3 py-1 text-xs font-bold uppercase text-slate-700 mb-1 rounded">
                {{ $date->translatedFormat('l d/m/Y') }}
            </div>
            <table class="w-full border-collapse text-xs">
                <thead>
                <tr>
                    <th class="bg-blue-batistack text-white p-1.5 text-left">Période</th>
                    <th class="bg-blue-batistack text-white p-1.5 text-left">Chantier</th>
                    <th class="bg-blue-batistack text-white p-1.5 text-left">Type</th>
                    <th class="bg-blue-batistack text-white p-1.5 text-right">Heures</th>
                    <th class="bg-blue-batistack text-white p-1.5 text-right">Trajet</th>
                    <th class="bg-blue-batistack text-white p-1.5 text-center">Panier</th>
                    <th class="bg-blue-batistack text-white p-1.5 text-center">GD</th>
                    <th class="bg-blue-batistack text-white p-1.5 text-left">Note</th>
                </tr>
                </thead>
                <tbody>
                @foreach($lines->sortBy('periode') as $line)
                    <tr class="{{ $loop->even ? 'bg-slate-50' : 'bg-white' }}">
                        <td class="p-1.5 border-b border-slate-200">{{ $line->periode->getLabel() }}</td>
                        <td class="p-1.5 border-b border-slate-200">
                            {{ $line->chantier?->nom ?? '—' }}
                            @if($line->chantier)
                                <span class="text-[9px] text-slate-400">({{ $line->chantier->reference }})</span>
                            @endif
                        </td>
                        <td class="p-1.5 border-b border-slate-200">{{ $line->type_heure->getLabel() }}</td>
                        <td class="p-1.5 border-b border-slate-200 text-right font-mono">{{ $line->heures }}h</td>
                        <td class="p-1.5 border-b border-slate-200 text-right font-mono">
                            {{ (float)$line->heures_trajet > 0 ? $line->heures_trajet.'h' : '—' }}
                        </td>
                        <td class="p-1.5 border-b border-slate-200 text-center">
                            {{ $line->panier_repas ? '✓' : '—' }}
                        </td>
                        <td class="p-1.5 border-b border-slate-200 text-center">
                            {{ $line->grand_deplacement ? '✓' : '—' }}
                        </td>
                        <td class="p-1.5 border-b border-slate-200 text-[9px] text-slate-500">
                            {{ $line->note ?? '—' }}
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endforeach

    {{-- Synthèse --}}
    <div class="mt-4 grid grid-cols-4 gap-3 text-center text-xs">
        <div class="border border-slate-200 rounded p-2 bg-slate-50">
            <p class="text-slate-500 uppercase font-bold mb-1">Total heures</p>
            <p class="text-lg font-bold text-slate-800">{{ $couts['total_heures'] }}h</p>
        </div>
        <div class="border border-blue-200 rounded p-2 bg-blue-50">
            <p class="text-blue-600 uppercase font-bold mb-1">Coût MO</p>
            <p class="text-lg font-bold text-blue-800">{{ number_format($couts['main_oeuvre'], 2, ',', ' ') }} €</p>
        </div>
        <div class="border border-slate-200 rounded p-2 bg-slate-50">
            <p class="text-slate-500 uppercase font-bold mb-1">Indemnités</p>
            <p class="text-lg font-bold text-slate-800">
                {{ number_format($couts['grand_deplacement'] + $couts['panier_repas'], 2, ',', ' ') }} €
            </p>
        </div>
        <div class="border border-green-200 rounded p-2 bg-green-50">
            <p class="text-green-600 uppercase font-bold mb-1">Total</p>
            <p class="text-lg font-bold text-green-800">{{ number_format($couts['total'], 2, ',', ' ') }} €</p>
        </div>
    </div>

    {{-- Signatures --}}
    <div class="mt-6 grid grid-cols-2 gap-8 text-xs">
        <div class="border border-slate-300 rounded p-3">
            <p class="font-bold text-slate-700 mb-3">Salarié : {{ $session->employee->user->name }}</p>
            <div class="border-b border-slate-400 h-8 mt-4"></div>
            <p class="text-slate-400 mt-1">Signature</p>
        </div>
        <div class="border border-slate-300 rounded p-3">
            <p class="font-bold text-slate-700 mb-3">
                Validé par : {{ $session->validator?->name ?? '—' }}
            </p>
            <div class="border-b border-slate-400 h-8 mt-4"></div>
            <p class="text-slate-400 mt-1">Signature</p>
        </div>
    </div>

    <div class="mt-4 text-right text-slate-400 text-[9px] italic border-t border-slate-100 pt-3">
        Document confidentiel — BatiStack · {{ $session->employee->matricule }}
    </div>
@endsection
