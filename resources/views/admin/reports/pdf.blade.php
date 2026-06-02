<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'Report' }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12px; color: #333; margin: 0; padding: 20px; }
        .header { text-align: center; margin-bottom: 30px; }
        h1 { font-size: 24px; color: #16a34a; margin-bottom: 5px; }
        .date { font-size: 11px; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 11px; }
        th, td { border: 1px solid #e2e8f0; padding: 8px 10px; text-align: left; }
        th { background-color: #f8fafc; font-weight: bold; color: #1e293b; border-bottom: 2px solid #cbd5e1; }
        tr:nth-child(even) { background-color: #f8fafc; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ config('app.name', 'Farmers Network') }} - {{ $title ?? 'Report' }}</h1>
        <div class="date">Generated on: {{ now()->format('F j, Y, g:i a') }}</div>
    </div>
    
    <table>
        <thead>
            <tr>
                @foreach($headers as $header)
                    <th>{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($data as $row)
                <tr>
                    @foreach($row as $cell)
                        <td>{{ $cell }}</td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
