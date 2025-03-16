<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Tanda Terima Magang</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            line-height: 1.4;
        }
        h1 {
            font-size: 16px;
            text-align: center;
            margin-bottom: 20px;
            font-weight: bold;
        }
        .subtitle {
            margin-bottom: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        table, th, td {
            border: 1px solid #333;
        }
        th {
            background-color: #f2f2f2;
            padding: 8px;
            text-align: center;
            font-weight: bold;
        }
        td {
            padding: 8px;
            vertical-align: middle;
        }
        .footer {
            margin-top: 40px;
        }
        .signature {
            float: right;
            width: 230px;
            text-align: left;
        }
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
        .center {
            text-align: center;
        }
    </style>
</head>
<body>
    <h1>TANDA TERIMA</h1>
    
    <p class="subtitle">Telah diterima berkas dari peserta magang sebagai berikut:</p>
    
    <table>
        <thead>
            <tr>
                <th rowspan="2" width="5%">No.</th>
                <th rowspan="2" width="20%">Nama</th>
                <th rowspan="2" width="20%">Nama Institusi</th>
                <th rowspan="2" width="15%">Ruang Penempatan</th>
                <th rowspan="2" width="15%">Tgl Masuk - Tgl Keluar</th>
                <th colspan="2" width="25%">Diterima oleh</th>
            </tr>
            <tr>
                <th width="12.5%">Nama</th>
                <th width="12.5%">Tandatangan</th>
            </tr>
        </thead>
        <tbody>
            @if(isset($interns) && count($interns) > 0)
                @foreach($interns as $index => $intern)
                    <tr>
                        <td class="center">{{ $index + 1 }}</td>
                        <td>{{ $intern['nama'] }}</td>
                        <td>{{ $intern['nama_institusi'] }}</td>
                        <td>{{ $intern['nama_bidang'] }}</td>
                        <td>{{ $intern['tanggal_masuk'] }} - {{ $intern['tanggal_keluar'] }}</td>
                        <td>{{ $intern['nama_mentor'] }}</td>
                        <td></td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="7" class="center">Tidak ada data</td>
                </tr>
            @endif
        </tbody>
    </table>
    
    <div class="footer clearfix">
        <p>Demikian tanda terima ini dibuat untuk dipergunakan sebagaimana mestinya.</p>
        
        <div class="signature">
            <p>Padang, {{ $currentDate }}</p>
            <p>Kasubag Umpeg</p>
            <br><br><br>
            <p>Benny Wahyudi, ST, Msi</p>
            <p>NIP. 197810232010011009</p>
        </div>
    </div>
</body>
</html>