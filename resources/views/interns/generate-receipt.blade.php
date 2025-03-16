@extends('layouts.app')

@section('content')
<div class="p-4 md:p-6 lg:p-8 w-full">
    <!-- Header -->
    <div class="bg-gradient-to-r from-green-400 to-green-500 rounded-lg shadow-md p-4 mb-6">
        <div class="flex items-center">
            <a href="{{ route('interns.management') }}" class="text-white mr-4">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1 class="text-white text-xl md:text-2xl font-bold">Generate Tanda Terima</h1>
        </div>
    </div>
    
    <!-- Form -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <form action="{{ route('interns.download-receipt') }}" method="POST">
            @csrf
            
            @if (session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                    <p>{{ session('error') }}</p>
                </div>
            @endif
            
            <div class="mb-4">
                <p class="mb-2">Pilih peserta magang yang akan dicetak tanda terimanya:</p>
                
                <div class="overflow-x-auto mt-4">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <input type="checkbox" id="select-all" class="form-checkbox h-4 w-4 text-green-600">
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Nama
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Institusi
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Ruang Penempatan
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Tanggal Mulai
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Tanggal Selesai
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($interns as $intern)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="checkbox" name="intern_ids[]" value="{{ $intern->id_magang }}" class="intern-checkbox form-checkbox h-4 w-4 text-green-600">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ $intern->nama }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ $intern->nama_institusi }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ $intern->bidang->nama_bidang ?? 'UMUM' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ date('d/m/Y', strtotime($intern->tanggal_masuk)) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ date('d/m/Y', strtotime($intern->tanggal_keluar)) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($intern->status == 'aktif')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                Aktif
                                            </span>
                                        @elseif($intern->status == 'selesai')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                Selesai
                                            </span>
                                        @elseif($intern->status == 'menunggu')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                Belum Mulai
                                            </span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                {{ ucfirst($intern->status) }}
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-4 text-center">
                                        Tidak ada data peserta magang
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="flex justify-end mt-6">
                <button type="submit" id="generate-btn" class="px-5 py-2.5 bg-green-500 text-white font-medium rounded-lg hover:bg-green-600 focus:outline-none focus:ring-4 focus:ring-green-300 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                    Generate Tanda Terima
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectAllCheckbox = document.getElementById('select-all');
        const internCheckboxes = document.querySelectorAll('.intern-checkbox');
        const generateBtn = document.getElementById('generate-btn');
        
        // Enable/disable generate button based on selection
        function updateGenerateButton() {
            const hasSelection = Array.from(internCheckboxes).some(cb => cb.checked);
            generateBtn.disabled = !hasSelection;
        }
        
        // Select/deselect all checkboxes
        selectAllCheckbox.addEventListener('change', function() {
            internCheckboxes.forEach(cb => {
                cb.checked = this.checked;
            });
            updateGenerateButton();
        });
        
        // Update select all status when individual checkboxes change
        internCheckboxes.forEach(cb => {
            cb.addEventListener('change', function() {
                updateGenerateButton();
                
                // Update select all checkbox
                const allChecked = Array.from(internCheckboxes).every(cb => cb.checked);
                const someChecked = Array.from(internCheckboxes).some(cb => cb.checked);
                
                selectAllCheckbox.checked = allChecked;
                selectAllCheckbox.indeterminate = someChecked && !allChecked;
            });
        });
    });
</script>
@endsection