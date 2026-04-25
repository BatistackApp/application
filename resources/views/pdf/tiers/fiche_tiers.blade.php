@extends('pdf.layout')

@section('content')
    <!-- En-tête -->
    <div class="flex justify-between items-start mb-8 border-b-2 border-[#002157] pb-4">
        <div>
            <h1 class="text-blue-batistack text-2xl font-bold uppercase m-0">{{ $title }}</h1>
            <p class="text-slate-500 mt-1">Référence : <span class="font-bold text-slate-900">{{ $tiers->code }}</span></p>
        </div>
        <div class="text-right">
            <h2 class="text-xl font-bold text-slate-900 m-0">{{ $tiers->name }}</h2>
            <p class="text-slate-500 italic mt-1">
                {{ $tiers->typology?->getLabel() ?? $tiers->typology?->name }} - {{ $tiers->category?->getLabel() ?? $tiers->category?->name }}
            </p>
        </div>
    </div>

    <!-- Informations Générales -->
    <div class="mb-8">
        <h3 class="bg-blue-batistack text-white px-3 py-1 text-sm font-bold uppercase mb-4">Informations Générales</h3>
        <div class="grid grid-cols-2 gap-8">
            <table class="w-full">
                <tr>
                    <td class="font-bold w-1/3 border-none py-1">Civilité :</td>
                    <td class="border-none py-1">{{ $tiers->civility?->value ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="font-bold border-none py-1">SIREN :</td>
                    <td class="border-none py-1">{{ $tiers->siren ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="font-bold border-none py-1">Code NAF :</td>
                    <td class="border-none py-1">{{ $tiers->naf ?? 'N/A' }}</td>
                </tr>
            </table>
            <table class="w-full">
                <tr>
                    <td class="font-bold w-1/3 border-none py-1">N° TVA :</td>
                    <td class="border-none py-1">{{ $tiers->num_tva ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="font-bold border-none py-1">Site Web :</td>
                    <td class="border-none py-1 text-blue-600">{{ $tiers->website ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="font-bold border-none py-1">RGPD :</td>
                    <td class="border-none py-1">
                        @if($tiers->dgpd_concilient)
                            <span class="text-green-600 font-semibold text-[10px]">CONFORME</span>
                        @else
                            <span class="text-amber-600 text-[10px]">NON SPÉCIFIÉ</span>
                        @endif
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Adresses -->
    <div class="mb-8">
        <h3 class="bg-blue-batistack text-white px-3 py-1 text-sm font-bold uppercase mb-4">Adresses</h3>
        @if($addresses->isNotEmpty())
            <table class="w-full border-collapse">
                <thead>
                <tr>
                    <th class="bg-blue-batistack text-white text-[10px] uppercase p-2 text-left">Type / Nom</th>
                    <th class="bg-blue-batistack text-white text-[10px] uppercase p-2 text-left">Adresse</th>
                    <th class="bg-blue-batistack text-white text-[10px] uppercase p-2 text-left">Ville / CP</th>
                    <th class="bg-blue-batistack text-white text-[10px] uppercase p-2 text-left">Contact</th>
                </tr>
                </thead>
                <tbody>
                @foreach($addresses as $address)
                    <tr>
                        <td class="p-2 border-b border-slate-200">
                            <div class="font-bold text-blue-batistack">{{ $address->address_type }}</div>
                            <div class="text-[10px] text-slate-500">{{ $address->address_name }}</div>
                        </td>
                        <td class="p-2 border-b border-slate-200">{{ $address->address }}</td>
                        <td class="p-2 border-b border-slate-200">
                            <div>{{ $address->postal_code }} {{ $address->city }}</div>
                            <div class="text-[10px] uppercase text-slate-500">{{ $address->country }}</div>
                        </td>
                        <td class="p-2 border-b border-slate-200">
                            @if($address->phone) <div class="text-[11px]">Tél: {{ $address->phone }}</div> @endif
                            @if($address->email) <div class="text-[11px] text-blue-600">{{ $address->email }}</div> @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @else
            <p class="text-slate-400 italic text-center py-4 bg-slate-50 rounded">Aucune adresse enregistrée.</p>
        @endif
    </div>

    <!-- Contacts -->
    <div class="mb-8">
        <h3 class="bg-blue-batistack text-white px-3 py-1 text-sm font-bold uppercase mb-4">Contacts</h3>
        @if($contacts->isNotEmpty())
            <table class="w-full border-collapse">
                <thead>
                <tr>
                    <th class="bg-blue-batistack text-white text-[10px] uppercase p-2 text-left">Nom & Prénom</th>
                    <th class="bg-blue-batistack text-white text-[10px] uppercase p-2 text-left">Fonction</th>
                    <th class="bg-blue-batistack text-white text-[10px] uppercase p-2 text-left">Coordonnées</th>
                </tr>
                </thead>
                <tbody>
                @foreach($contacts as $contact)
                    <tr>
                        <td class="p-2 border-b border-slate-200 font-bold uppercase">{{ $contact->last_name }} {{ $contact->first_name }}</td>
                        <td class="p-2 border-b border-slate-200 italic">{{ $contact->fonction }}</td>
                        <td class="p-2 border-b border-slate-200">
                            @if($contact->tel_fix) <div class="text-[11px]">Fixe: {{ $contact->tel_fix }}</div> @endif
                            @if($contact->tel_portable) <div class="text-[11px]">Port: {{ $contact->tel_portable }}</div> @endif
                            @if($contact->email) <div class="text-[11px] font-semibold text-blue-700">{{ $contact->email }}</div> @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @else
            <p class="text-slate-400 italic text-center py-4 bg-slate-50 rounded">Aucun contact enregistré.</p>
        @endif
    </div>

    <!-- Paramètres de Gestion -->
    @if($gestion)
        <div class="mb-8">
            <h3 class="bg-blue-batistack text-white px-3 py-1 text-sm font-bold uppercase mb-4">Gestion & Paramètres</h3>
            <div class="grid grid-cols-2 gap-8">
                <table class="w-full">
                    <tr>
                        <td class="font-bold w-1/2 border-none py-1">En-cours autorisé :</td>
                        <td class="border-none py-1 font-bold text-blue-batistack">
                            {{ $gestion->outstanding ? number_format($gestion->outstanding, 2, ',', ' ').' €' : '0,00 €' }}
                        </td>
                    </tr>
                    <tr>
                        <td class="font-bold border-none py-1">Gestion des relances :</td>
                        <td class="border-none py-1">
                            @if($gestion->followup)
                                <span class="bg-green-100 text-green-800 px-2 py-0.5 rounded text-[10px] font-bold">ACTIVER</span>
                            @else
                                <span class="bg-slate-100 text-slate-500 px-2 py-0.5 rounded text-[10px] font-bold">DÉSACTIVER</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    @endif

    <div class="mt-12 text-center text-slate-400 text-[10px] italic border-t border-slate-100 pt-4">
        Document généré le {{ now()->format('d/m/Y H:i') }} par BatiStack
    </div>
@endsection
