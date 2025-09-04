@extends('layouts.app')

@php
use Illuminate\Support\Facades\Storage;

$fieldClass = "mt-1 block w-full rounded-md
               bg-white text-black placeholder-black/60
               border border-black/30
               focus:border-yellow-500 focus:ring-yellow-500
               sm:text-sm
               dark:bg-white dark:text-black dark:placeholder-black/60 dark:border-black/30";
@endphp

{{-- input --}}
@section('title', 'Jual Motor - Lampegan Motor')

@section('content')
<div class="bg-white text-black dark:text-white pb-24 pt-6">
    <div class="mx-auto max-w-sm px-4">

        {{-- Judul --}}
        <h2 class="text-2xl font-extrabold text-black text-center mb-6 border-b-4 border-yellow-400 inline-block pb-1">
            Form Jual Motor
        </h2>

        {{-- Flash message --}}
        @if(session('success'))
            <div class="mb-6 p-4 rounded-lg bg-green-100 text-green-800">
                {{ session('success') }}
            </div>
        @endif

        {{-- Error list --}}
        @if($errors->any())
            <div class="mb-6 p-4 rounded-lg bg-red-100 text-red-800">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- FORM --}}
        <form action="{{ route('landing.sell.submit') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
            @csrf

            {{-- Nama --}}
            <div>
                <label for="name" class="block text-sm font-semibold text-black mb-1">Nama</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required class="{{ $fieldClass }}">
            </div>

            {{-- No WhatsApp --}}
            <div>
                <label for="phone" class="block text-sm font-semibold text-black mb-1">No WhatsApp</label>
                <input type="text" name="phone" id="phone" value="{{ old('phone') }}" required placeholder="08xxxxxxxxxx" class="{{ $fieldClass }}">
            </div>

            {{-- Merk --}}
            <div>
                <label for="brand_id" class="block text-sm font-semibold text-black mb-1">Merk</label>
                <select name="brand_id" id="brand_id" required class="{{ $fieldClass }}">
                    <option value="">-- Pilih Merk --</option>
                    @foreach($brands as $b)
                        <option value="{{ $b->id }}" {{ old('brand_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Model --}}
            <div>
                <label for="vehicle_model_id" class="block text-sm font-semibold text-black mb-1">Model</label>
                <select name="vehicle_model_id" id="vehicle_model_id" required class="{{ $fieldClass }}" {{ old('brand_id') ? '' : 'disabled' }}>
                    <option value="">-- Pilih Model --</option>
                </select>
            </div>

            {{-- Tahun --}}
            <div>
                <label for="year_id" class="block text-sm font-semibold text-black mb-1">Tahun</label>
                <select name="year_id" id="year_id" required class="{{ $fieldClass }}">
                    <option value="">-- Pilih Tahun --</option>
                    @foreach($years as $y)
                        <option value="{{ $y->id }}" {{ old('year_id') == $y->id ? 'selected' : '' }}>{{ $y->year }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Plat Nomor --}}
            <div>
                <label for="license_plate" class="block text-sm font-semibold text-black mb-1">Plat Nomor</label>
                <input type="text" name="license_plate" id="license_plate" value="{{ old('license_plate') }}" required placeholder="contoh: D 1234 ABC" class="{{ $fieldClass }}">
            </div>

            {{-- Odometer --}}
            <div>
                <label for="odometer" class="block text-sm font-semibold text-black mb-1">Odometer (KM)</label>
                <input type="number" name="odometer" id="odometer" value="{{ old('odometer') }}" min="0" class="{{ $fieldClass }}">
            </div>

            {{-- Catatan --}}
            <div>
                <label for="notes" class="block text-sm font-semibold text-black mb-1">Catatan</label>
                <textarea name="notes" id="notes" rows="3" placeholder="Kondisi, lokasi, tambahan lain" class="{{ $fieldClass }}">{{ old('notes') }}</textarea>
            </div>

            {{-- Upload Foto --}}
            <div>
                <label class="block text-sm font-semibold text-black mb-1">Upload Foto (maks. 5)</label>

                <div id="photos-wrapper" class="space-y-2">
                    <input type="file" name="photos[]" accept=".jpg,.jpeg,.png,.webp" data-idx="1"
                           class="photo-input block w-full text-sm text-black bg-white border border-black/30 rounded-md
                                  file:mr-4 file:py-2 file:px-4
                                  file:rounded-md file:border-0
                                  file:text-sm file:font-semibold
                                  file:bg-red-600 file:text-white hover:file:bg-red-700">
                    <small class="block text-black/70">Maksimal 5 foto, masing-masing ≤ 4MB.</small>
                </div>

                <div id="preview-grid" class="mt-3 grid grid-cols-3 gap-3"></div>
            </div>

            {{-- Submit --}}
            <div class="pt-2">
                <button type="submit"
                        class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-4 rounded-lg shadow-md shadow-yellow-400/50 transition">
                    Kirim Data
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
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

    brandSelect?.addEventListener('change', (e) => populateModels(e.target.value));
    if (oldBrandId) populateModels(oldBrandId, oldModelId);

    const MAX_FILES = 5, MAX_SIZE = 4 * 1024 * 1024;
    const wrapper = document.getElementById('photos-wrapper');
    const previewGrid = document.getElementById('preview-grid');

    const makeThumb = (idx, src, name, size) => {
        const item = document.createElement('div');
        item.className = 'relative rounded-lg overflow-hidden border border-black/20';

        const img = document.createElement('img');
        img.src = src; img.alt = name; img.className = 'w-full h-28 object-cover';

        const cap = document.createElement('div');
        cap.className = 'px-2 py-1 text-xs truncate bg-yellow-300 text-black font-semibold';
        cap.textContent = `${name} (${(size/1024).toFixed(0)} KB)`;

        const badge = document.createElement('span');
        badge.className = 'absolute top-1 left-1 bg-yellow-500 text-black text-[10px] rounded px-1 font-bold';
        badge.textContent = idx;

        item.appendChild(img); item.appendChild(cap); item.appendChild(badge);
        return item;
    };

    const countInputs = () => wrapper.querySelectorAll('.photo-input').length;

    const addInput = () => {
        const current = countInputs();
        if (current >= MAX_FILES) return;
        const el = document.createElement('input');
        el.type='file'; el.name='photos[]'; el.accept='.jpg,.jpeg,.png,.webp'; el.dataset.idx=String(current+1);
        el.className = `photo-input block w-full text-sm text-black bg-white border border-black/30 rounded-md
                        file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0
                        file:text-sm file:font-semibold file:bg-red-600 file:text-white hover:file:bg-red-700`;
        el.addEventListener('change', onChangeInput);
        wrapper.appendChild(el);
    };

    const onChangeInput = (e) => {
        const input = e.target;
        const file = input.files?.[0];
        if (!file) { refreshPreviews(); return; }
        if (file.size > MAX_SIZE) { alert('Ukuran tiap foto maksimal 4MB.'); input.value=''; refreshPreviews(); return; }
        if (!['image/jpeg','image/png','image/webp'].includes(file.type)) { alert('Format harus JPG/PNG/WebP.'); input.value=''; refreshPreviews(); return; }

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
            if (f) { idx++; const url = URL.createObjectURL(f); previewGrid.appendChild(makeThumb(idx, url, f.name, f.size)); }
        });
    };

    wrapper.querySelector('.photo-input').addEventListener('change', onChangeInput);
});
</script>
@endpush
