<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Report - {{ $configuration['name'] ?? 'Dynamic Report' }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            color: #333;
        }
        .meta-info {
            margin-bottom: 20px;
            font-size: 10px;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $configuration['name'] ?? 'Dynamic Report' }}</h1>
        @if(isset($configuration['description']))
            <p>{{ $configuration['description'] }}</p>
        @endif
    </div>

    <div class="meta-info">
        <strong>Generated:</strong> {{ $generated_at }}<br>
        <strong>Total Records:</strong> {{ count($data) }}<br>
        @if(isset($configuration['base_model']))
            <strong>Data Source:</strong> {{ ucfirst(str_replace('_', ' ', $configuration['base_model'])) }}
        @endif
    </div>

    @if(count($data) > 0)
        <table>
            <thead>
                <tr>
                    @foreach(array_keys($data[0]) as $column)
                        <th>{{ ucfirst(str_replace('_', ' ', $column)) }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($data as $row)
                    <tr>
                        @foreach($row as $value)
                            <td>{{ $value }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div style="text-align: center; padding: 50px; color: #666;">
            <h3>No data found</h3>
            <p>The report configuration did not return any results.</p>
        </div>
    @endif

    <div class="footer">
        <p>This report was generated automatically by the Educational ERP System</p>
    </div>
</body>
</html>