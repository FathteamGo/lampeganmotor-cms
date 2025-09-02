@extends('layouts.app')

@php
use Illuminate\Support\Facades\Storage;
@endphp

@section('title', 'Jual Motor - Lampegan Motor')

@section('content')
<div class="bg-gray-50 text-gray-900 dark:text-white pb-20">
    @includeWhen(View::exists('partials.hero-slider'), 'partials.hero-slider')

    <div class="container mx-auto max-w-lg px-4 sm:px-6 lg:px-8">
        @if(session('success'))
            <div class="mb-6 p-4 rounded-lg bg-green-100 text-green-800">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 p-4 rounded-lg bg-red-100 text-red-800">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6 text-center">Form Jual Motor</h2>

            <form action="{{ route('landing.sell.submit') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                @csrf

                {{-- Nama --}}
                <div>
                    <label for="name" class="block text-sm font-medium">Nama</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                           class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-red-500 focus:ring-red-500 sm:text-sm">
                </div>

                {{-- No WhatsApp --}}
                <div>
                    <label for="phone" class="block text-sm font-medium">No WhatsApp</label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone') }}" required
                           placeholder="08xxxxxxxxxx"
                           class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-red-500 focus:ring-red-500 sm:text-sm">
                </div>

                {{-- Merk (dropdown) --}}
                <div>
                    <label for="brand_id" class="block text-sm font-medium">Merk</label>
                    <select name="brand_id" id="brand_id" required
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-red-500 focus:ring-red-500 sm:text-sm">
                        <option value="">-- Pilih Merk --</option>
                        @foreach($brands as $b)
                            <option value="{{ $b->id }}" {{ old('brand_id') == $b->id ? 'selected' : '' }}>
                                {{ $b->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Model (dependent) --}}
                <div>
                    <label for="vehicle_model_id" class="block text-sm font-medium">Model</label>
                    <select name="vehicle_model_id" id="vehicle_model_id" required
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-red-500 focus:ring-red-500 sm:text-sm"
                        {{ old('brand_id') ? '' : 'disabled' }}>
                        <option value="">-- Pilih Model --</option>
                        {{-- opsi diisi via JS --}}
                    </select>
                </div>

                {{-- Tahun --}}
                <div>
                    <label for="year_id" class="block text-sm font-medium">Tahun</label>
                    <select name="year_id" id="year_id" required
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-red-500 focus:ring-red-500 sm:text-sm">
                        <option value="">-- Pilih Tahun --</option>
                        @foreach($years as $y)
                            <option value="{{ $y->id }}" {{ old('year_id') == $y->id ? 'selected' : '' }}>
                                {{ $y->year }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Plat Nomor --}}
                <div>
                    <label for="license_plate" class="block text-sm font-medium">Plat Nomor</label>
                    <input type="text" name="license_plate" id="license_plate" value="{{ old('license_plate') }}" required
                           placeholder="contoh: D 1234 ABC"
                           class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-red-500 focus:ring-red-500 sm:text-sm">
                </div>

                {{-- Odometer --}}
                <div>
                    <label for="odometer" class="block text-sm font-medium">Odometer (KM)</label>
                    <input type="number" name="odometer" id="odometer" value="{{ old('odometer') }}" min="0"
                           class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-red-500 focus:ring-red-500 sm:text-sm">
                </div>

                {{-- Catatan --}}
                <div>
                    <label for="notes" class="block text-sm font-medium">Catatan</label>
                    <textarea name="notes" id="notes" rows="3"
                              placeholder="Kondisi, lokasi, tambahan lain"
                              class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-red-500 focus:ring-red-500 sm:text-sm">{{ old('notes') }}</textarea>
                </div>

                {{-- Upload Foto (dinamis + preview) --}}
                <div>
                    <label class="block text-sm font-medium mb-1">Upload Foto (maks. 5)</label>

                    <div id="photos-wrapper" class="space-y-2">
                        <input type="file" name="photos[]" accept=".jpg,.jpeg,.png,.webp" data-idx="1"
                               class="photo-input block w-full text-sm text-gray-700 dark:text-gray-300
                                      file:mr-4 file:py-2 file:px-4
                                      file:rounded-md file:border-0
                                      file:text-sm file:font-semibold
                                      file:bg-red-600 file:text-white
                                      hover:file:bg-red-700">
                        <small class="block text-gray-500 dark:text-gray-400">Maksimal 5 foto, masing-masing ≤ 4MB.</small>
                    </div>

                    <div id="preview-grid" class="mt-3 grid grid-cols-3 gap-3"></div>
                </div>

                <div class="pt-2">
                    <button type="submit"
                            class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-4 rounded-lg shadow-md transition">
                        Kirim Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    // ========= Dependent dropdown: BRAND -> MODELS =========
    const brandSelect  = document.getElementById('brand_id');
    const modelSelect  = document.getElementById('vehicle_model_id');
    const oldBrandId   = "{{ old('brand_id') }}";
    const oldModelId   = "{{ old('vehicle_model_id') }}";

    const populateModels = async (brandId, selectedId = null) => {
        modelSelect.innerHTML = '<option value="">-- Pilih Model --</option>';
        modelSelect.disabled = true;
        if (!brandId) return;

        try {
            const url = `{{ route('ajax.models.byBrand', ':id') }}`.replace(':id', brandId);
            const res = await fetch(url);
            const data = await res.json();

            data.forEach(m => {
                const opt = document.createElement('option');
                opt.value = m.id;
                opt.textContent = m.name;
                if (String(selectedId) === String(m.id)) opt.selected = true;
                modelSelect.appendChild(opt);
            });

            modelSelect.disabled = false;
        } catch (e) {
            console.error(e);
            alert('Gagal memuat model. Coba lagi.');
        }
    };

    brandSelect?.addEventListener('change', (e) => {
        populateModels(e.target.value);
    });

    // Auto-populate saat reload (validasi gagal)
    if (oldBrandId) {
        populateModels(oldBrandId, oldModelId);
    }

    // ========= Preview foto (yang sudah kamu punya) =========
    const MAX_FILES = 5;
    const MAX_SIZE = 4 * 1024 * 1024; // 4MB
    const wrapper = document.getElementById('photos-wrapper');
    const previewGrid = document.getElementById('preview-grid');

    const makeThumb = (idx, src, name, size) => {
        const item = document.createElement('div');
        item.className = 'relative rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700';

        const img = document.createElement('img');
        img.src = src;
        img.alt = name;
        img.className = 'w-full h-28 object-cover';

        const cap = document.createElement('div');
        cap.className = 'px-2 py-1 text-xs truncate dark:bg-gray-800';
        cap.textContent = `${name} (${(size/1024).toFixed(0)} KB)`;

        const badge = document.createElement('span');
        badge.className = 'absolute top-1 left-1 bg-black/70 text-white text-[10px] rounded px-1';
        badge.textContent = idx;

        item.appendChild(img);
        item.appendChild(cap);
        item.appendChild(badge);
        return item;
    };

    const countInputs = () => wrapper.querySelectorAll('.photo-input').length;

    const addInput = () => {
        const current = countInputs();
        if (current >= MAX_FILES) return;

        const newInput = document.createElement('input');
        newInput.type = 'file';
        newInput.name = 'photos[]';
        newInput.accept = '.jpg,.jpeg,.png,.webp';
        newInput.dataset.idx = String(current + 1);
        newInput.className = `photo-input block w-full text-sm text-gray-700 dark:text-gray-300
                              file:mr-4 file:py-2 file:px-4
                              file:rounded-md file:border-0
                              file:text-sm file:font-semibold
                              file:bg-red-600 file:text-white
                              hover:file:bg-red-700`;

        newInput.addEventListener('change', onChangeInput);
        wrapper.appendChild(newInput);
    };

    const onChangeInput = (e) => {
        const input = e.target;
        const file = input.files?.[0];

        if (!file) { refreshPreviews(); return; }

        if (file.size > MAX_SIZE) {
            alert('Ukuran tiap foto maksimal 4MB.');
            input.value = '';
            refreshPreviews();
            return;
        }
        const okTypes = ['image/jpeg','image/png','image/webp'];
        if (!okTypes.includes(file.type)) {
            alert('Format harus JPG/PNG/WebP.');
            input.value = '';
            refreshPreviews();
            return;
        }

        if (countInputs() < MAX_FILES) {
            const all = wrapper.querySelectorAll('.photo-input');
            const filled = Array.from(all).filter(i => i.files && i.files.length > 0).length;
            if (filled === countInputs()) addInput();
        }
        refreshPreviews();
    };

    const refreshPreviews = () => {
        previewGrid.innerHTML = '';
        const inputs = wrapper.querySelectorAll('.photo-input');
        let idx = 0;
        inputs.forEach((inp, i) => {
            const f = inp.files?.[0];
            inp.dataset.idx = String(i + 1);
            if (f) {
                idx++;
                const url = URL.createObjectURL(f);
                previewGrid.appendChild(makeThumb(idx, url, f.name, f.size));
            }
        });
    };

    wrapper.querySelector('.photo-input').addEventListener('change', onChangeInput);
});
</script>
@endpush
