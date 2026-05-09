@extends('pdf.layout')

@section('content')
    <div class="document-header">
        <div class="info-row">
            <strong>Période :</strong>
            {{ $dateDebut->format('d/m/Y') }} au {{ $dateFin->format('d/m/Y') }}
        </div>
    </div>

    <table class="balance-table">
        <thead>
        <tr>
            <th style="width: 10%;">N° Compte</th>
            <th style="width: 35%;">Libellé</th>
            <th style="width: 12%;">Débit</th>
            <th style="width: 12%;">Crédit</th>
            <th style="width: 12%;">Solde Débit</th>
            <th style="width: 12%;">Solde Crédit</th>
        </tr>
        </thead>
        <tbody>
        @foreach($balance as $ligne)
            <tr>
                <td class="font-mono">{{ $ligne['numero'] }}</td>
                <td>{{ $ligne['libelle'] }}</td>
                <td class="text-right">{{ number_format($ligne['total_debit'], 2, ',', ' ') }}</td>
                <td class="text-right">{{ number_format($ligne['total_credit'], 2, ',', ' ') }}</td>
                <td class="text-right font-bold">
                    @if($ligne['solde_debit'] > 0)
                        {{ number_format($ligne['solde_debit'], 2, ',', ' ') }}
                    @endif
                </td>
                <td class="text-right font-bold">
                    @if($ligne['solde_credit'] > 0)
                        {{ number_format($ligne['solde_credit'], 2, ',', ' ') }}
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
        <tfoot>
        <tr class="total-row">
            <td colspan="2" class="text-right font-bold">TOTAUX</td>
            <td class="text-right font-bold">{{ number_format($verification['total_debit'], 2, ',', ' ') }}</td>
            <td class="text-right font-bold">{{ number_format($verification['total_credit'], 2, ',', ' ') }}</td>
            <td class="text-right font-bold">{{ number_format($verification['total_solde_debit'], 2, ',', ' ') }}</td>
            <td class="text-right font-bold">{{ number_format($verification['total_solde_credit'], 2, ',', ' ') }}</td>
        </tr>
        </tfoot>
    </table>

    <div class="verification-block">
        @if($verification['equilibree'])
            <div class="badge-success">✓ Balance équilibrée</div>
        @else
            <div class="badge-danger">
                ⚠ Balance déséquilibrée<br>
                Écart mouvements : {{ number_format($verification['ecart_mouvements'], 2, ',', ' ') }} €<br>
                Écart soldes : {{ number_format($verification['ecart_soldes'], 2, ',', ' ') }} €
            </div>
        @endif
    </div>
@endsection

@section('styles')
    <style>
        .info-row {
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
        }

        .balance-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.75rem;
        }

        .balance-table thead {
            background: #3b82f6;
            color: white;
        }

        .balance-table th {
            padding: 0.5rem 0.25rem;
            text-align: left;
            font-weight: 600;
        }

        .balance-table tbody tr {
            border-bottom: 1px solid #dee2e6;
        }

        .balance-table tbody tr:nth-child(even) {
            background: #f8f9fa;
        }

        .balance-table td {
            padding: 0.4rem 0.25rem;
        }

        .balance-table tfoot {
            border-top: 2px solid #3b82f6;
            background: #e0f2fe;
        }

        .balance-table tfoot td {
            padding: 0.625rem 0.25rem;
        }

        .text-right {
            text-align: right;
        }

        .font-mono {
            font-family: 'Courier New', monospace;
        }

        .font-bold {
            font-weight: 600;
        }

        .verification-block {
            margin-top: 1.5rem;
            text-align: center;
        }

        .badge-success {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background: #d1fae5;
            color: #065f46;
            border-radius: 0.25rem;
            font-weight: 600;
            font-size: 0.875rem;
        }

        .badge-danger {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background: #fee2e2;
            color: #991b1b;
            border-radius: 0.25rem;
            font-weight: 600;
            font-size: 0.875rem;
        }
    </style>
@endsection
