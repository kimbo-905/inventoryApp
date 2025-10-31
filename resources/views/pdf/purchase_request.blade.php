<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EMA GROUP GABON - Bon de Commande</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            background: #f5f5f5;
            padding: 20px;
        }
        
        .form-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border: 2px solid #333;
        }

        .form-value {
            flex: 1;
            border-bottom: 1px solid #333;
            padding: 2px 5px;
            text-align: right;
        }

        .two-column {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 20px;
        }
        .left-section,
        .right-section {
            width: 100%;
            border: 1px solid #333;
            padding: 15px;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        
        .items-table th {
            background: #f0f0f0;
            border: 1px solid #333;
            padding: 8px;
            font-weight: bold;
            text-align: center;
        }
        
        .items-table td {
            border: 1px solid #333;
            padding: 8px;
            height: 30px;
        }
        
        .total-section {
            text-align: right;
            margin-top: 10px;
        }
        
        .total-row {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 5px;
        }
        
        .total-label {
            font-weight: bold;
            margin-right: 20px;
            min-width: 100px;
            text-align: right;
        }
        
        .total-value {
            border: 1px solid #333;
            padding: 5px 10px;
            min-width: 120px;
            background: white;
        }
        
        .signatures {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-top: 40px;
            gap: 20px;
            border-top: 1px solid #333;
            padding-top: 20px;
        }

        .signature-box {
            flex: 1;
            text-align: center;
        }

        .signature-title {
            font-weight: bold;
            margin-bottom: 10px;
        }

        .signature-img {
            height: 120px;
            display: block;
            margin: 0 auto 10px auto;
            object-fit: contain;
        }

        .signature-line {
            border-bottom: 1px solid #333;
            height: 60px;
            margin: 0 auto;
            width: 60%;
        }

        .stamp-box {
            flex: 0 0 auto;
            text-align: right;
        }

        .stamp-img {
            height: 100px;
            object-fit: contain;
        }

         
        @media print {
            body {
                background: white;
                padding: 0;
            }
            .form-container {
                border: none;
                padding: 0;
            }
        }
    </style>

</head>
    <body>
        <div class="form-container">

            <table style="width:100%; border-bottom:2px solid #333; margin-bottom:20px;">
                <tr>
                  <td style="width:80px; text-align:left; vertical-align:top;">
                    <img src="{{ public_path('storage/images/ema.png') }}" style="width:70px; height:auto;" alt="Logo">
                  </td>
                  <td style="text-align:left; font-size:11px; vertical-align:top;">
                    <div style="font-weight:bold; font-size:16px; margin-bottom:5px;">EMA GROUP GABON</div>
                    <div>B.P 395 Port-Gentil, Derrière l'ancienne base armée française</div>
                    <div>Tel : (+241) 01 56 05 48 B.P : 1633</div>
                    <div>Libreville, Route COGYGA ; Tel : (+241) 01 76 47 17</div>
                    <div>Email : Emagroup@emagabon.com</div>
                  </td>
                  <!-- Order Info -->
                  <td style="width:200px; text-align:right; font-size:12px; vertical-align:top; border: 2px solid #333; padding: 10px;">
                    <div style="font-weight:bold;">BON DE COMMANDE N°LBV</div>
                    <div class="order-number" style="font-size:16px; font-weight:bold;">0068</div>
                    <div style="margin-top:10px; font-weight:bold;">DESTINATAIRE</div>
                    <div>____________________</div>
                  </td>
                  <tr>
                    <td colspan="3" style="border-bottom: px solid #ffffff; padding-top:10px;"></td>
                  </tr>
                </tr>
            </table>

            <div class="two-column">
                <div class="left-section">
                    <div class="form-row" style="display:flex; align-items:flex-start; gap:10px;">
                        <span class="form-label" style="font-weight:bold;">Date :</span>
                        <div style="display:flex; flex-direction:column; align-items:flex-start;">
                            <span style="margin-bottom:4px;">
                                @php
                                    $date = optional($purchase->approvals->first())->date_validation;
                                @endphp
                                {{ $date && strtotime($date) ? \Carbon\Carbon::parse($date)->format('d/m/Y') : '—' }}
                            </span>
                            <div style="border-top:1px solid #333; width:100%; margin-top:4px;"></div>
                        </div>
                    </div>
                    
                </div>
                <div class="right-section">
                    <!-- Right section content if needed -->
                </div>
            </div> 

            <div class="form-section" style="margin-bottom:20px;">
                <div class="form-row" style="display:flex;align-items:center;margin-bottom:8px;">
                  <span class="form-label" style="font-weight:bold;margin-right:10px;white-space:nowrap;">Mode d'expéditions :</span>
                  <span class="form-line" style="flex:1;border-bottom:1px solid #333;max-width:auto;height:0.8em;"></span>
                </div>
                <div class="form-row" style="display:flex;align-items:center;margin-bottom:8px;">
                  <span class="form-label" style="font-weight:bold;margin-right:10px;white-space:nowrap;">Conditions de paiement :</span>
                  <span class="form-line" style="flex:1;border-bottom:1px solid #333;max-width:auto;height:0.8em;"></span>
                </div>
                <div class="form-row" style="display:flex;align-items:center;margin-bottom:8px;">
                  <span class="form-label" style="font-weight:bold;margin-right:10px;white-space:nowrap;">Devis N° :</span>
                  <span class="form-line" style="flex:1;border-bottom:1px solid #333;max-width:auto;height:0.8em;"></span>
                </div>
                <div class="form-row" style="display:flex;align-items:center;margin-bottom:8px;">
                  <span class="form-label" style="font-weight:bold;margin-right:10px;white-space:nowrap;">Réf. Demande d'Achat :</span>
                  <span class="form-line" style="flex:1;border-bottom:1px solid #333;max-width:auto;height:0.8em;"></span>
                </div>
            </div>  

            <table class="items-table">
                <thead>
                    <tr>
                        <th style="width: 8%;">Réf</th>
                        <th style="width: 40%;">Désignation</th>
                        <th style="width: 15%;">Prix Unité</th>
                        <th style="width: 12%;">Quantité</th>
                        <th style="width: 15%;">Prix total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($purchase->items as $i => $item)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $item->article }}</td>
                        <td>
                            {{ number_format($item->prix_unitaire ?? 0, 0, ',', ' ') }} FCFA 
                        </td>
                        <td>{{ $item->quantité }}</td>
                        <td>
                            <strong>
                                {{ number_format(($item->prix_unitaire ?? 0) * $item->quantité, 0, ',', ' ') }} FCFA
                            </strong>
                        </td>
                    </tr>
                    
                        {{-- <tr><td></td><td></td><td></td><td></td><td></td></tr> --}}
                        
                    @endforeach
                </tbody>
            </table>

            <div class="total-section">
                    <div class="total-row">
                        <span class="total-label">TOTAL HT :</span>
                    </div>
                    <div class="total-row">
                        <span class="total-label">TVA :</span>
                    </div>
                    <div class="total-row">
                        <span class="total-label">TOTAL TTC :</span>
                        <strong>
                            {{ number_format($purchase->items->sum(function($item) {
                                return $item->quantité * $item->prix_unitaire;
                            }), 0, ',', ' ') }} FCFA
                        </strong>
                    </div>
            </div> 

            <div class="signatures">
                <!-- Left: Responsable Achats -->
                <div class="signature-box">
                    <div class="signature-title">Responsable Achats</div>
                    @if($admin && $admin->signature_url)
                        <img src="{{ public_path('storage/' . $admin->signature_url) }}"
                            class="signature-img" />
                    @endif
                    <div class="signature-line"></div>
                </div>
            
                <!-- Middle: Demandeur -->
                <div class="signature-box">
                    <div class="signature-title">Demandeur</div>
                    <div class="signature-line"></div>
                </div>
            </div>
                    
            <div class="footer-code" 
                style=" margin-top: 20px;
                font-size: 8px;
                text-align: center;
                color: #666;">
                RCCM : 801 PGO 2010002711UE • 271 178 CNSS • 001 017 0716 A • CNAMPS • 041500004092
            </div>
        </div>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const priceInputs = document.querySelectorAll('.items-table input[type="number"]');
                    const totalHT = document.querySelector('.total-value[placeholder="Total HT"]');
                    document.addEventListener('keydown', function(e) {
                        if (e.ctrlKey && e.key === 'p') {
                            window.print();
                            e.preventDefault();
                        }
                    });
                });
            </script>
    </body>
</html>