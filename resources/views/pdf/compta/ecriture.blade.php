@extends('pdf.layout')

@section('content')
    <div class="document-header">
        <div class="info-grid">
            <div class="info-block">
                <div class="label">Journal</div>
                <div class="value">{{ $ecriture->journal->code }} - {{ $ecriture->journal->libelle }}</div>
            </div>
            <div class="info-block">
                <div class="label">Date</div>
                <div class="value">{{ $ecriture->date_ecriture->format('d/m/Y') }}</div>
            </div>
            <div class="info-block">
                <div class="label">Exercice</div>
                <div class="value">{{ $ecriture->exercice->libelle }}</div>
            </div>
            <div class="info-block">
                <div class="label">Statut</div>
                <div class="value status-{{ $ecriture->status->value }}">{{ $ecriture->status->getLabel() }}</div>
            </div>
        </div>
    </div>

    <div class="section-title">{{ $ecriture->libelle }}</div>

    <table class="document-table">
        <thead>
        <tr>
            <th style="width: 15%;">Compte</th>
            <th style="width: 40%;">Libellé</th>
            <th style="width: 15%;">Chantier</th>
            <th style="width: 15%; text-align: right;">Débit</th>
            <th style="width: 15%; text-align: right;">Crédit</th>
        </tr>
        </thead>
        <tbody>
        @foreach($ecriture->lignes as $ligne)
            <tr>
                <td class="font-mono">{{ $ligne->compte->numero }}</td>
                <td>
                    {{ $ligne->compte->libelle }}<br>
                    <span class="text-muted">{{ $ligne->libelle }}</span>
                </td>
                <td class="text-muted">
                    @if($ligne->chantier)
                        {{ $ligne->chantier->reference }}
                    @else
                        —
                    @endif
                </td>
                <td class="text-right font-bold">
                    @if($ligne->isDebit())
                        {{ number_format($ligne->montant, 2, ',', ' ') }} €
                    @endif
                </td>
                <td class="text-right font-bold">
                    @if($ligne->isCredit())
                        {{ number_format($ligne->montant, 2, ',', ' ') }} €
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
        <tfoot>
        <tr class="total-row">
            <td colspan="3" class="text-right font-bold">TOTAL</td>
            <td class="text-right font-bold">{{ number_format($ecriture->total_debit, 2, ',', ' ') }} €</td>
            <td class="text-right font-bold">{{ number_format($ecriture->total_credit, 2, ',', ' ') }} €</td>
        </tr>
        <tr>
            <td colspan="5" class="text-center">
                @if($ecriture->isEquilibree())
                    <span class="badge-success">✓ Écriture équilibrée</span>
                @else
                    <span class="badge-danger">⚠ Écriture déséquilibrée</span>
                @endif
            </td>
        </tr>
        </tfoot>
    </table>

    @if($ecriture->validated_at)
        <div class="validation-info">
            <strong>Validée le :</strong> {{ $ecriture->validated_at->format('d/m/Y à H:i') }}
            @if($ecriture->validator)
                par {{ $ecriture->validator->name }}
            @endif
        </div>
    @endif
@endsection

@section('styles')
    <style>
        .info-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .info-block {
            padding: 0.75rem;
            background: #f8f9fa;
            border-left: 3px solid #3b82f6;
        }

        .info-block .label {
            font-size: 0.75rem;
            color: #6c757d;
            margin-bottom: 0.25rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .info-block .value {
            font-weight: 600;
            font-size: 0.875rem;
        }

        .status-valide {
            color: #10b981;
        }

        .status-brouillon {
            color: #6c757d;
        }

        .status-extourne {
            color: #ef4444;
        }

        .section-title {
            font-size: 1rem;
            font-weight: 700;
            margin: 1.5rem 0 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #3b82f6;
        }

        .document-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.875rem;
        }

        .document-table thead {
            background: #3b82f6;
            color: white;
        }

        .document-table th {
            padding: 0.75rem 0.5rem;
            text-align: left;
            font-weight: 600;
        }

        .document-table tbody tr {
            border-bottom: 1px solid #dee2e6;
        }

        .document-table tbody tr:nth-child(even) {
            background: #f8f9fa;
        }

        .document-table td {
            padding: 0.625rem 0.5rem;
        }

        .document-table tfoot {
            border-top: 2px solid #3b82f6;
        }

        .document-table tfoot td {
            padding: 0.75rem 0.5rem;
        }

        .total-row {
            background: #e0f2fe;
            font-weight: 700;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .font-mono {
            font-family: 'Courier New', monospace;
            font-weight: 600;
        }

        .font-bold {
            font-weight: 600;
        }

        .text-muted {
            color: #6c757d;
            font-size: 0.75rem;
        }

        .badge-success {
            display: inline-block;
            padding: 0.5rem 1rem;
            background: #d1fae5;
            color: #065f46;
            border-radius: 0.25rem;
            font-weight: 600;
        }

        .badge-danger {
            display: inline-block;
            padding: 0.5rem 1rem;
            background: #fee2e2;
            color: #991b1b;
            border-radius: 0.25rem;
            font-weight: 600;
        }

        .validation-info {
            margin-top: 2rem;
            padding: 1rem;
            background: #f0fdf4;
            border-left: 3px solid #10b981;
            font-size: 0.875rem;
        }
    </style>
@endsection
