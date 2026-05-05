@extends('pdf.layout')

@section('content')
    <div class="flex justify-between items-center mb-6 border-b-2 border-[#002157] pb-4">
        <div>
            <h1 class="text-blue-batistack text-2xl font-bold uppercase m-0">{{ $title }}</h1>
            <p class="text-slate-500 mt-1">
                Période : <strong>{{ $date_from->format('d/m/Y') }}</strong> → <strong>{{ $date_to->format('d/m/Y') }}</strong>
            </p>
        </div>
        <p class="text-slate-400 text-xs italic">Généré le {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <table class="w-full border-collapse text-xs">
        <thead>
        <tr>
            <th class="bg-blue-batistack text-white p-2 text-left">Dépôt</th>
            <th class="bg-blue-batistack text-white p-2 text-right">Entrées</th>
            <th class="bg-blue-batistack text-white p-2 text-right">Sorties</th>
            <th class="bg-blue-batistack text-white p-2 text-right">Transferts</th>
            <th class="bg-blue-batistack text-white p-2 text-right">Nb mouvements</th>
        </tr>
        </thead>
        <tbody>
        @forelse($lignes as $ligne)
            <tr class="even:bg-slate-50">
                <td class="p-2 border-b border-slate-200 font-semibold">
                    {{ $ligne->warehouse->name ?? '—' }}
                </td>
                <td class="p-2 border-b border-slate-200 text-right font-mono text-green-700">
                    {{ number_format($ligne->total_entrees, 3, ',', ' ') }}
                </td>
                <td class="p-2 border-b border-slate-200 text-right font-mono text-red-700">
                    {{ number_format($ligne->total_sorties, 3, ',', ' ') }}
                </td>
                <td class="p-2 border-b border-slate-200 text-right font-mono text-blue-700">
                    {{ number_format($ligne->total_transferts, 3, ',', ' ') }}
                </td>
                <td class="p-2 border-b border-slate-200 text-right">{{ $ligne->nb_mouvements }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="p-8 text-center text-slate-400 italic">Aucune donnée.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
@endsection
