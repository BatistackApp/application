@extends('pdf.layout')

@section('content')
    <div class="document-header">
        <div class="header-grid">
            <div class="client-info">
                <div class="section-title">Client</div>
                <div class="info-block">
                    <strong>{{ $document->client->name }}</strong><br>
                    @if($document->client->addresses->isNotEmpty())
                        @php $adresse = $document->client->addresses->first(); @endphp
                        {{ $adresse->street }}<br>
                        {{ $adresse->postal_code }} {{ $adresse->city }}
                    @endif
                </div>
            </div>

            <div class="document-info">
                <div class="section-title">Informations</div>
                <div class="info-block">
                    <div class="info-row">
                        <span class="label">Date :</span>
                        <span class="value">{{ $document->date_document->format('d/m/Y') }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Validité :</span>
                        <span class="value">{{ $document->date_validite?->format('d/m/Y') ?? 'Non définie' }}</span>
                    </div>
                    @if($document->chantier)
                        <div class="info-row">
                            <span class="label">Chantier :</span>
                            <span class="value">{{ $document->chantier->reference }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($document->notes)
        <div class="document-notes">
            <strong>Objet :</strong> {{ $document->notes }}
        </div>
    @endif

    <table class="document-lines">
        <thead>
        <tr>
            <th class="col-designation">Désignation</th>
            <th class="col-number">Qté</th>
            <th class="col-number">P.U. HT</th>
            <th class="col-number">TVA</th>
            <th class="col-number">Remise</th>
            <th class="col-number">Total HT</th>
        </tr>
        </thead>
        <tbody>
        @foreach($document->lines as $line)
            <tr>
                <td>
                    {{ $line->designation }}
                    @if($line->article)
                        <br><span class="text-muted">Réf: {{ $line->article->sku }}</span>
                    @endif
                </td>
                <td class="text-right">{{ number_format($line->quantite, 2, ',', ' ') }} {{ $line->unite }}</td>
                <td class="text-right">{{ number_format($line->prix_unitaire_ht, 2, ',', ' ') }} €</td>
                <td class="text-right">{{ number_format($line->taux_tva, 1, ',', ' ') }} %</td>
                <td class="text-right">
                    @if($line->remise_pct > 0)
                        {{ number_format($line->remise_pct, 1, ',', ' ') }} %
                    @elseif($line->remise_montant > 0)
                        {{ number_format($line->remise_montant, 2, ',', ' ') }} €
                    @else
                        —
                    @endif
                </td>
                <td class="text-right font-bold">{{ number_format($line->total_ht, 2, ',', ' ') }} €</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div class="document-totals">
        <table>
            <tr>
                <td>Total HT</td>
                <td class="text-right">{{ number_format($totaux['total_ht'], 2, ',', ' ') }} €</td>
            </tr>
            @foreach($totaux['par_taux_tva'] as $tva)
                <tr>
                    <td>TVA {{ number_format($tva['taux'], 1, ',', ' ') }} %</td>
                    <td class="text-right">{{ number_format($tva['montant_tva'], 2, ',', ' ') }} €</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td>TOTAL TTC</td>
                <td class="text-right">{{ number_format($totaux['total_ttc'], 2, ',', ' ') }} €</td>
            </tr>
        </table>
    </div>

    @if($conditions)
        <div class="document-conditions">
            <div class="section-title">Conditions</div>
            <p>{{ $conditions }}</p>
        </div>
    @endif

    <div class="signature-block">
        <div class="signature-column">
            <strong>Signature de l'entreprise</strong>
            <div class="signature-box">
                {{ $entreprise['nom'] }}<br>
                Date : ___/___/______
            </div>
        </div>
        <div class="signature-column">
            <strong>Signature du client</strong><br>
            <span class="text-muted">(Précédé de "Lu et approuvé")</span>
            <div class="signature-box">
                {{ $document->client->name }}<br>
                Date : ___/___/______
            </div>
        </div>
    </div>

    @if(!empty($mentions_legales))
        <div class="document-footer">
            @foreach($mentions_legales as $mention)
                <p>• {{ $mention }}</p>
            @endforeach
        </div>
    @endif
@endsection

@section('styles')
    <style>
        .document-header {
            margin-bottom: 2rem;
        }

        .header-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }

        .section-title {
            font-weight: 700;
            font-size: 0.875rem;
            color: #e67e22;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .info-block {
            background: #f8f9fa;
            padding: 1rem;
            border-left: 3px solid #e67e22;
            font-size: 0.875rem;
            line-height: 1.6;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.25rem;
        }

        .info-row .label {
            font-weight: 600;
        }

        .document-notes {
            margin-bottom: 1.5rem;
            padding: 1rem;
            background: #fff3cd;
            border-left: 3px solid #ffc107;
            font-size: 0.875rem;
        }

        .document-lines {
            width: 100%;
            border-collapse: collapse;
            margin: 1.5rem 0;
            font-size: 0.875rem;
        }

        .document-lines thead {
            background: #e67e22;
            color: white;
        }

        .document-lines th {
            padding: 0.75rem 0.5rem;
            text-align: left;
            font-weight: 600;
            font-size: 0.8125rem;
        }

        .document-lines th.col-number {
            text-align: right;
            width: 10%;
        }

        .document-lines th.col-designation {
            width: 40%;
        }

        .document-lines tbody tr {
            border-bottom: 1px solid #dee2e6;
        }

        .document-lines tbody tr:nth-child(even) {
            background: #f8f9fa;
        }

        .document-lines td {
            padding: 0.625rem 0.5rem;
        }

        .text-right {
            text-align: right;
        }

        .text-muted {
            color: #6c757d;
            font-size: 0.75rem;
        }

        .font-bold {
            font-weight: 600;
        }

        .document-totals {
            width: 50%;
            margin-left: auto;
            margin-top: 1.5rem;
        }

        .document-totals table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.875rem;
        }

        .document-totals td {
            padding: 0.5rem 0.75rem;
            border-bottom: 1px solid #dee2e6;
        }

        .document-totals td:first-child {
            font-weight: 600;
        }

        .document-totals td:last-child {
            text-align: right;
            width: 40%;
        }

        .document-totals .total-row {
            background: #e67e22;
            color: white;
            font-weight: 700;
            font-size: 1rem;
        }

        .document-conditions {
            margin-top: 2rem;
            padding: 1rem;
            background: #f8f9fa;
            border-left: 3px solid #e67e22;
            font-size: 0.875rem;
        }

        .signature-block {
            margin-top: 3rem;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }

        .signature-column {
            text-align: center;
            font-size: 0.875rem;
        }

        .signature-box {
            margin-top: 1rem;
            border: 2px dashed #999;
            padding: 3rem 1rem;
            min-height: 100px;
        }

        .document-footer {
            margin-top: 2rem;
            padding-top: 1rem;
            border-top: 1px solid #dee2e6;
            font-size: 0.75rem;
            color: #6c757d;
            line-height: 1.6;
        }

        .document-footer p {
            margin-bottom: 0.25rem;
        }
    </style>
@endsection
