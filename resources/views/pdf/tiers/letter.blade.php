@extends('pdf.layout')

@section('content')
    <div class="relative w-[210mm] min-h-[297mm] mx-auto bg-white">

        <!-- EN-TÊTE SOCIÉTÉ -->
        <div class="absolute top-0 left-0 right-0 border-b-4 border-slate-800 pb-4 pt-2 px-12">
            <div class="flex justify-between items-start">
                <!-- Logo / Nom société -->
                <div>
                    <h1 class="text-3xl font-black uppercase tracking-tight text-slate-800 mb-1">
                        {{ $company->name }}
                    </h1>
                    <div class="text-xs text-slate-500 space-y-0.5 font-medium">
                        <p>{{ $company->address }}</p>
                        <p>{{ $company->code_postal }} {{ $company->ville }}</p>
                        <p class="mt-2">
                            <span class="font-semibold">SIRET :</span> {{ $company->siret }} &nbsp;|&nbsp;
                            <span class="font-semibold">APE :</span> {{ $company->ape }}
                        </p>
                    </div>
                </div>

                <!-- Contact -->
                <div class="text-right text-xs text-slate-600">
                    <p class="font-semibold text-slate-800 mb-2">CONTACT</p>
                    <p>📞 {{ $company->telephone ?? '02 51 XX XX XX' }}</p>
                    <p>📧 {{ $company->email ?? 'contact@c2me.fr' }}</p>
                    <p class="mt-2 text-[10px] italic">
                        Société au capital de {{ \Illuminate\Support\Number::currency($company->capital, 'EUR') }}
                    </p>
                </div>
            </div>
        </div>

        <!-- ADRESSE DESTINATAIRE (Zone fenêtre enveloppe) -->
        <div class="envelope-window">
            <div class="text-sm leading-relaxed">
                @if(isset($tiers->name))
                    <p class="font-bold text-base mb-1">{{ $tiers->name }}</p>
                @endif

                <p>{{ $address->address ?? 'Adresse ligne 1' }}</p>
                <p class="font-medium">
                    {{ $address->postal_code ?? '00000' }} {{ $address->city ?? 'VILLE' }}
                </p>
            </div>
        </div>

        <!-- RÉFÉRENCES ET DATE -->
        <div class="absolute top-[110mm] right-12 text-right text-xs text-slate-600">
            <div class="space-y-1">
                <p class="mt-2">
                    A {{ $company->ville }},<br>
                    le {{ now()->format('d/m/Y') }}
                </p>
            </div>
        </div>

        <!-- OBJET -->
        <div class="absolute top-[130mm] left-12 right-12">
            @if(isset($title))
                <div class="mb-6">
                    <p class="text-sm font-bold uppercase text-slate-700 mb-1">Objet :</p>
                    <p class="text-sm font-semibold">{{ $title }}</p>
                </div>
            @endif

            <!-- CORPS DE LA LETTRE -->
            <div class="text-sm leading-relaxed space-y-4 text-justify">
                {!! $content ?? '' !!}
            </div>

            <!-- FORMULE DE POLITESSE -->
            <div class="mt-6 text-sm">
                <p>Nous vous prions d'agréer, Madame, Monsieur, l'expression de nos salutations distinguées.</p>
            </div>

            <!-- SIGNATURE -->
            <div class="mt-12">
                <div class="flex justify-end">
                    <div class="text-sm text-right">
                        <p class="font-semibold mb-1">{{ $signatory_title ?? 'Le Gérant' }}</p>
                        <p class="font-bold text-base">{{ $signatory_name ?? 'Doizy Corentin' }}</p>

                        <!-- Espace signature manuscrite -->
                        <div class="h-20 border-b border-slate-300 mt-8 w-48 ml-auto"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- PIED DE PAGE -->
        <div class="absolute bottom-8 left-12 right-12 text-[9px] text-slate-400 border-t border-slate-200 pt-3 text-center">
            <p>
                {{ $company->name }} – {{ $company->adresse }} – {{ $company->code_postal }} {{ $company->ville }} – SIRET {{ $company->siret }} – APE {{ $company->ape }}
            </p>
            <p class="mt-1">
                {{ $company->telephone }} – {{ $company->email }}
            </p>
        </div>

    </div>
@endsection
