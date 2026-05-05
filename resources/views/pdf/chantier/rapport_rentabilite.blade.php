@extends('pdf.layout')

@section('content')
    {{-- En-tête --}}
    <div class="flex justify-between items-start mb-6 border-b-2 border-[#002157] pb-4">
        <div>
            <h1 class="text-blue-batistack text-2xl font-bold uppercase m-0">{{ $title }}</h1>
            <p class="text-slate-500 mt-1">
                Client : <strong>{{ $chantier->client->name }}</strong>
                &nbsp;·&nbsp; Responsable : <strong>{{ $chantier->responsable->name ?? '—' }}</strong>
            </p>
            <p class="text-slate-500">
                {{ $chantier->adresse }} — {{ $chantier->code_postal }} {{ $chantier->ville }}
            </p>
        </div>
        <div class="text-right">
        <span class="px-2 py-1 rounded text-xs font-bold bg-blue-100 text-blue-800">
            {{ $chantier->status->getLabel() }}
        </span>
            <p class="text-slate-400 text-xs italic mt-2">Généré le {{ now()->format('d/m/Y H:i') }}</p>
        </div>
    </div>

    {{-- KPI globaux --}}
    <div class="grid grid-cols-4 gap-3 mb-6 text-center text-xs">
        <div class="border border-slate-200 rounded p-3 bg-slate-50">
            <p class="text-slate-500 uppercase font-bold mb-1">Budget total</p>
            <p class="text-xl font-bold text-blue-900">{{ number_format($kpis['budget_total'], 2, ',', ' ') }} €</p>
        </div>
        <div class="border border-slate-200 rounded p-3 {{ $kpis['en_depassement'] ? 'bg-red-50 border-red-200' : 'bg-green-50 border-green-200' }}">
            <p class="text-slate-500 uppercase font-bold mb-1">Coût réel</p>
            <p class="text-xl font-bold {{ $kpis['en_depassement'] ? 'text-red-700' : 'text-green-700' }}">
                {{ number_format($kpis['cout_reel'], 2, ',', ' ') }} €
            </p>
        </div>
        <div class="border border-slate-200 rounded p-3 {{ $kpis['reste_a_depenser'] < 0 ? 'bg-red-50' : 'bg-slate-50' }}">
            <p class="text-slate-500 uppercase font-bold mb-1">Reste à dépenser</p>
            <p class="text-xl font-bold {{ $kpis['reste_a_depenser'] < 0 ? 'text-red-700' : 'text-slate-800' }}">
                {{ number_format($kpis['reste_a_depenser'], 2, ',', ' ') }} €
            </p>
        </div>
        <div class="border border-slate-200 rounded p-3 bg-slate-50">
            <p class="text-slate-500 uppercase font-bold mb-1">Avancement</p>
            <p class="text-xl font-bold text-slate-800">{{ $kpis['avancement_global'] }} %</p>
            <p class="text-[9px] text-slate-400">Conso : {{ $kpis['taux_consommation'] }} %</p>
        </div>
    </div>

    {{-- Écart par type --}}
    <h3 class="text-sm font-bold text-[#002157] uppercase mb-2">Analyse par type de coût</h3>
    <table class="w-full border-collapse text-xs mb-6">
        <thead>
        <tr>
            <th class="bg-blue-batistack text-white p-2 text-left">Type</th>
            <th class="bg-blue-batistack text-white p-2 text-right">Budget</th>
            <th class="bg-blue-batistack text-white p-2 text-right">Réel</th>
            <th class="bg-blue-batistack text-white p-2 text-right">Écart</th>
            <th class="bg-blue-batistack text-white p-2 text-right">Consommation</th>
        </tr>
        </thead>
        <tbody>
        @foreach($kpis['ecart_par_type'] as $type => $data)
            <tr class="{{ $loop->even ? 'bg-slate-50' : 'bg-white' }}">
                <td class="p-2 border-b border-slate-200 font-semibold">{{ $data['label'] }}</td>
                <td class="p-2 border-b border-slate-200 text-right font-mono">
                    {{ number_format($data['budget'], 2, ',', ' ') }} €
                </td>
                <td class="p-2 border-b border-slate-200 text-right font-mono">
                    {{ number_format($data['reel'], 2, ',', ' ') }} €
                </td>
                <td class="p-2 border-b border-slate-200 text-right font-mono font-bold
                {{ $data['en_depassement'] ? 'text-red-600' : 'text-green-600' }}">
                    {{ $data['en_depassement'] ? '' : '+' }}{{ number_format($data['ecart'], 2, ',', ' ') }} €
                </td>
                <td class="p-2 border-b border-slate-200 text-right">
                    <div class="flex items-center justify-end gap-2">
                        <div class="w-24 bg-slate-200 rounded-full h-1.5">
                            <div class="h-1.5 rounded-full {{ $data['taux_conso'] > 100 ? 'bg-red-500' : 'bg-blue-500' }}"
                                 style="width: {{ min($data['taux_conso'], 100) }}%"></div>
                        </div>
                        <span class="{{ $data['taux_conso'] > 100 ? 'text-red-600 font-bold' : '' }}">
                        {{ $data['taux_conso'] }} %
                    </span>
                    </div>
                </td>
            </tr>
        @endforeach
        </tbody>
        <tfoot>
        <tr class="bg-[#002157] text-white font-bold">
            <td class="p-2">TOTAL</td>
            <td class="p-2 text-right font-mono">{{ number_format($kpis['budget_total'], 2, ',', ' ') }} €</td>
            <td class="p-2 text-right font-mono">{{ number_format($kpis['cout_reel'], 2, ',', ' ') }} €</td>
            <td class="p-2 text-right font-mono">
                {{ number_format($kpis['reste_a_depenser'], 2, ',', ' ') }} €
            </td>
            <td class="p-2 text-right">{{ $kpis['taux_consommation'] }} %</td>
        </tr>
        </tfoot>
    </table>

    {{-- Tâches --}}
    @if($chantier->tasks->count() > 0)
        <h3 class="text-sm font-bold text-[#002157] uppercase mb-2">Avancement des tâches</h3>
        <table class="w-full border-collapse text-xs">
            <thead>
            <tr>
                <th class="bg-blue-batistack text-white p-2 text-left">Tâche</th>
                <th class="bg-blue-batistack text-white p-2 text-center">Statut</th>
                <th class="bg-blue-batistack text-white p-2 text-center">Période</th>
                <th class="bg-blue-batistack text-white p-2 text-right">Avancement</th>
                <th class="bg-blue-batistack text-white p-2 text-left">Responsable</th>
            </tr>
            </thead>
            <tbody>
            @foreach($chantier->tasks->sortBy('ordre') as $task)
                <tr class="{{ $loop->even ? 'bg-slate-50' : 'bg-white' }}">
                    <td class="p-2 border-b border-slate-200">
                        @if($task->parent_task_id)
                            <span class="text-slate-400 mr-1">↳</span>
                        @endif
                        {{ $task->designation }}
                    </td>
                    <td class="p-2 border-b border-slate-200 text-center">
                <span class="px-1.5 py-0.5 rounded text-[9px] font-bold
                    @if($task->status->value === 'done') bg-green-100 text-green-700
                    @elseif($task->status->value === 'in_progress') bg-blue-100 text-blue-700
                    @elseif($task->status->value === 'blocked') bg-red-100 text-red-700
                    @else bg-slate-100 text-slate-600 @endif">
                    {{ $task->status->getLabel() }}
                </span>
                    </td>
                    <td class="p-2 border-b border-slate-200 text-center text-[10px]">
                        {{ $task->date_debut->format('d/m/Y') }} → {{ $task->date_fin->format('d/m/Y') }}
                    </td>
                    <td class="p-2 border-b border-slate-200">
                        <div class="flex items-center justify-end gap-2">
                            <div class="w-20 bg-slate-200 rounded-full h-1.5">
                                <div class="h-1.5 rounded-full bg-blue-500"
                                     style="width: {{ $task->avancement_pct }}%"></div>
                            </div>
                            <span class="font-mono">{{ $task->avancement_pct }} %</span>
                        </div>
                    </td>
                    <td class="p-2 border-b border-slate-200 text-[10px]">
                        {{ $task->assignee->name ?? '—' }}
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif

    <div class="mt-6 text-right text-slate-400 text-[9px] italic border-t border-slate-100 pt-3">
        Document confidentiel — BatiStack · {{ $chantier->reference }}
    </div>
@endsection
