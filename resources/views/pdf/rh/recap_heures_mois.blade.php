@extends('pdf.layout')

@section('content')
    <div class="flex justify-between items-start mb-6 border-b-2 border-[#002157] pb-4">
        <div>
            <h1 class="text-blue-batistack text-xl font-bold uppercase m-0">{{ $title }}</h1>
            <p class="text-slate-500 mt-1">
                Matricule : <strong>{{ $employee->matricule }}</strong>
                &nbsp;·&nbsp; Taux horaire : <strong>{{ number_format($employee->taux_horaire, 2, ',', ' ') }} €/h</strong>
            </p>
        </div>
        <p class="text-slate-400 text-xs italic">Généré le {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <table class="w-full border-collapse text-xs mb-6">
        <thead>
        <tr>
            <th class="bg-blue-batistack text-white p-2 text-left">Chantier</th>
            <th class="bg-blue-batistack text-white p-2 text-right">Heures</th>
            <th class="bg-blue-batistack text-white p-2 text-right">Coût MO</th>
            <th class="bg-blue-batistack text-white p-2 text-right">Trajet</th>
            <th class="bg-blue-batistack text-white p-2 text-right">Total</th>
        </tr>
        </thead>
        <tbody>
        @forelse($parChantier as $data)
            <tr class="{{ $loop->even ? 'bg-slate-50' : 'bg-white' }}">
                <td class="p-2 border-b border-slate-200">
                    <div class="font-semibold">{{ $data['chantier']->nom }}</div>
                    <div class="text-[9px] text-slate-400">{{ $data['chantier']->reference }}</div>
                </td>
                <td class="p-2 border-b border-slate-200 text-right font-mono">
                    {{ number_format($data['heures'], 2, ',', ' ') }}h
                </td>
                <td class="p-2 border-b border-slate-200 text-right font-mono">
                    {{ number_format($data['main_oeuvre'], 2, ',', ' ') }} €
                </td>
                <td class="p-2 border-b border-slate-200 text-right font-mono">
                    {{ number_format($data['trajet'], 2, ',', ' ') }} €
                </td>
                <td class="p-2 border-b border-slate-200 text-right font-mono font-bold">
                    {{ number_format($data['total'], 2, ',', ' ') }} €
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="p-6 text-center text-slate-400 italic">
                    Aucune heure validée ce mois.
                </td>
            </tr>
        @endforelse
        </tbody>
        <tfoot>
        <tr class="bg-[#002157] text-white font-bold">
            <td class="p-2">TOTAL</td>
            <td class="p-2 text-right font-mono">{{ number_format($totalHeures, 2, ',', ' ') }}h</td>
            <td class="p-2" colspan="2"></td>
            <td class="p-2 text-right font-mono">{{ number_format($totalCout, 2, ',', ' ') }} €</td>
        </tr>
        </tfoot>
    </table>

    <div class="text-right text-slate-400 text-[9px] italic border-t border-slate-100 pt-3">
        Document confidentiel — BatiStack · {{ $employee->matricule }}
    </div>
@endsection
