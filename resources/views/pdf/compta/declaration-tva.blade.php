@extends('pdf.layout')

@section('content')
    <div class="document-header">
        <div class="info-grid">
            <div>
                <strong>Régime :</strong> {{ $declaration->regime->getLabel() }}
            </div>
            <div>
                <strong>Période :</strong> {{ $declaration->date_debut->format('d/m/Y') }} au {{ $declaration->date_fin->format('d/m/Y') }}
            </div>
        </div>
    </div>

    <div class="section-title">A - TVA COLLECTÉE</div>
    <table class="ca3-table">
        <tr>
            <td class="label-col">Base 20%</td>
            <td class="amount-col">{{ number_format($ca3['a1_base_20'], 2, ',', ' ') }} €</td>
            <td class="label-col">TVA 20%</td>
            <td class="amount-col amount-highlight">{{ number_format($ca3['a1_tva_20'], 2, ',', ' ') }} €</td>
        </tr>
        <tr>
            <td class="label-col">Base 10%</td>
            <td class="amount-col">{{ number_format($ca3['a2_base_10'], 2, ',', ' ') }} €</td>
            <td class="label-col">TVA 10%</td>
            <td class="amount-col amount-highlight">{{ number_format($ca3['a2_tva_10'], 2, ',', ' ') }} €</td>
        </tr>
        <tr>
            <td class="label-col">Base 5,5%</td>
            <td class="amount-col">{{ number_format($ca3['a3_base_55'], 2, ',', ' ') }} €</td>
            <td class="label-col">TVA 5,5%</td>
            <td class="amount-col amount-highlight">{{ number_format($ca3['a3_tva_55'], 2, ',', ' ') }} €</td>
        </tr>
        <tr class="total-row">
            <td colspan="3" class="text-right font-bold">TOTAL TVA COLLECTÉE</td>
            <td class="amount-col font-bold">{{ number_format($ca3['a_total'], 2, ',', ' ') }} €</td>
        </tr>
    </table>

    <div class="section-title">B - TVA DÉDUCTIBLE</div>
    <table class="ca3-table">
        <tr>
            <td class="label-col">Sur immobilisations</td>
            <td class="amount-col amount-highlight">{{ number_format($ca3['b1_immo'], 2, ',', ' ') }} €</td>
        </tr>
        <tr>
            <td class="label-col">Sur biens et services</td>
            <td class="amount-col amount-highlight">{{ number_format($ca3['b2_biens'], 2, ',', ' ') }} €</td>
        </tr>
        <tr class="total-row">
            <td class="text-right font-bold">TOTAL TVA DÉDUCTIBLE</td>
            <td class="amount-col font-bold">{{ number_format($ca3['b_total'], 2, ',', ' ') }} €</td>
        </tr>
    </table>

    <div class="section-title">C - TVA NETTE</div>
    <table class="ca3-table">
        <tr>
            <td class="label-col">TVA collectée - TVA déductible</td>
            <td class="amount-col amount-highlight">{{ number_format($ca3['c1_tva_nette'], 2, ',', ' ') }} €</td>
        </tr>
        <tr>
            <td class="label-col">Crédit période précédente</td>
            <td class="amount-col">{{ number_format($ca3['c2_credit_precedent'], 2, ',', ' ') }} €</td>
        </tr>
        <tr class="total-row final-row">
            <td class="text-right font-bold">TVA À PAYER</td>
            <td class="amount-col font-bold tva-due">{{ number_format($ca3['c3_tva_due'], 2, ',', ' ') }} €</td>
        </tr>
    </table>

    @if($declaration->validee)
        <div class="validation-block">
            ✓ Déclaration validée le {{ $ca3['validee_at'] }}
            @if($declaration->transmise)
                <br>✓ Transmise le {{ $ca3['transmise_at'] }}
            @endif
        </div>
    @endif
@endsection

@section('styles')
    <style>
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 2rem;
            font-size: 0.875rem;
        }

        .section-title {
            font-size: 1rem;
            font-weight: 700;
            margin: 1.5rem 0 0.75rem;
            padding: 0.5rem;
            background: #3b82f6;
            color: white;
        }

        .ca3-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.875rem;
            margin-bottom: 1.5rem;
        }

        .ca3-table tr {
            border-bottom: 1px solid #dee2e6;
        }

        .ca3-table td {
            padding: 0.625rem;
        }

        .label-col {
            width: 40%;
            font-weight: 500;
        }

        .amount-col {
            width: 30%;
            text-align: right;
        }

        .amount-highlight {
            background: #f0f9ff;
            font-weight: 600;
        }

        .total-row {
            background: #e0f2fe;
            border-top: 2px solid #3b82f6;
        }

        .final-row {
            background: #dbeafe;
            border-top: 3px solid #2563eb;
            font-size: 1rem;
        }

        .tva-due {
            color: #2563eb;
            font-size: 1.125rem;
        }

        .text-right {
            text-align: right;
        }

        .font-bold {
            font-weight: 700;
        }

        .validation-block {
            margin-top: 2rem;
            padding: 1rem;
            background: #d1fae5;
            border-left: 4px solid #10b981;
            color: #065f46;
            font-weight: 600;
            font-size: 0.875rem;
        }
    </style>
@endsection
