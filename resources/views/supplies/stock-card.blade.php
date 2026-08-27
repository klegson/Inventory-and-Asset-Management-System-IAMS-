<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Stock Card - {{ $supply->article }}</title>
    <style>
        :root { color: #111; font-family: Arial, Helvetica, sans-serif; }
        body { margin: 0; padding: 24px; background: #eef1f4; }
        .page { max-width: 1180px; margin: 0 auto; padding: 28px; background: #fff; }
        .toolbar { display: flex; justify-content: flex-end; gap: 8px; margin-bottom: 16px; }
        button, .back { border: 1px solid #475569; background: #fff; color: #172033; padding: 8px 14px; text-decoration: none; cursor: pointer; }
        .title { text-align: center; border: 2px solid #111; border-bottom: 0; padding: 14px 8px 10px; }
        .title h1 { margin: 0; font-size: 25px; letter-spacing: .08em; }
        .title p { margin: 5px 0 0; font-size: 13px; font-weight: 800; }
        .meta { display: grid; grid-template-columns: 1fr 1fr 1fr; border: 2px solid #111; border-bottom: 0; }
        .meta div { min-height: 42px; padding: 7px 10px; border-right: 1px solid #111; }
        .meta div:last-child { border-right: 0; }
        .meta strong { display: block; font-size: 11px; text-transform: uppercase; }
        .meta span { display: block; margin-top: 5px; font-size: 15px; }
        table { width: 100%; border-collapse: collapse; border: 2px solid #111; font-size: 12px; }
        th, td { border: 1px solid #111; padding: 8px 7px; vertical-align: middle; }
        th { background: #f1f3f5; text-align: center; font-weight: 700; }
        td.center { text-align: center; }
        td.balance { font-weight: 700; text-align: center; }
        .subhead th { padding: 5px; font-size: 11px; }
        .summary { display: flex; justify-content: space-between; margin-top: 14px; font-size: 12px; }
        .muted { color: #64748b; }
        @media print {
            body { padding: 0; background: #fff; }
            .page { max-width: none; padding: 0; }
            .toolbar { display: none; }
            .title { padding-top: 8px; }
            th { background: #f1f3f5 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
        @media (max-width: 720px) {
            body { padding: 8px; }
            .page { padding: 10px; overflow-x: auto; }
            .meta { grid-template-columns: 1fr; }
            .meta div { border-right: 0; border-bottom: 1px solid #111; }
            .meta div:last-child { border-bottom: 0; }
            table { min-width: 760px; }
        }
    </style>
</head>
<body>
<main class="page">
    <div class="toolbar">
        <a class="back" href="{{ url('/supplies') }}">Back to Supplies</a>
        <button type="button" onclick="window.print()">Print Stock Card</button>
    </div>

    <header class="title">
        <h1>STOCK CARD</h1>
        <p>Department of Education, Region V <br>Agency</p>
    </header>

    <section class="meta">
        <div><strong>Item</strong><span>{{ $supply->article }}</span></div>
        <div><strong>Description</strong><span>{{ $supply->description }}</span></div>
        <div><strong>Stock No.</strong><span>{{ $supply->barcode_id ?: 'N/A' }}</span></div>
        <div><strong>Unit of Measure</strong><span>{{ $supply->unit_measure }}</span></div>
        <div><strong>Re-order Point</strong><span>{{ $supply->low_stock_threshold }}</span></div>
    </section>

    <table>
        <thead>
            <tr>
                <th rowspan="2">Date</th>
                <th rowspan="2">Reference</th>
                <th colspan="1">Receipt</th>
                <th colspan="2">Issuance</th>
                <th rowspan="2">Balance<br>Qty.</th>
                <th rowspan="2">No. of Days<br>to Consume</th>
                <th rowspan="2">Unit Value</th>
            </tr>
            <tr class="subhead">
                <th>Qty.</th>
                <th>Qty.</th>
                <th>Office</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    <td class="center">{{ $row['date'] ? \Carbon\Carbon::parse($row['date'])->format('m/d/Y') : '-' }}</td>
                    <td>{{ $row['reference'] ?: '-' }}</td>
                    <td class="center">{{ $row['receipt_quantity'] ?? '' }}</td>
                    <td class="center">{{ $row['issue_quantity'] ?? '' }}</td>
                    <td>{{ $row['office'] ?: '-' }}</td>
                    <td class="balance">{{ $row['balance'] }}</td>
                    <td class="center">{{ $row['days_to_consume'] ?? '' }}</td>
                    <td class="center">{{ $row['unit_price'] !== null ? number_format((float) $row['unit_price'], 2) : number_format((float) $supply->unit_value, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="8" class="center muted">No stock movements recorded.</td></tr>
            @endforelse
            @for($i = $rows->count(); $i < 30; $i++)
                <tr><td>&nbsp;</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
            @endfor
        </tbody>
    </table>

    <div class="summary">
        <span>Current balance: <strong>{{ $supply->quantity }} {{ $supply->unit_measure }}</strong></span>
        <span>Current weighted-average unit value: <strong>{{ number_format((float) $supply->unit_value, 2) }}</strong></span>
        <span class="muted">Generated {{ now()->format('m/d/Y h:i A') }}</span>
    </div>
</main>
</body>
</html>