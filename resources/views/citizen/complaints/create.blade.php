@extends('layouts.citizen')

@section('title', 'Report a Road Issue')

@section('content')
<div style="max-width: 720px; margin: 0 auto;">

    {{-- Header --}}
    <div style="margin-bottom: 2rem;">
        <h1 style="font-size: 1.5rem; font-weight: 700; color: #111827; margin: 0;">🚨 Report a Road Issue</h1>
        <p style="margin-top: 0.25rem; font-size: 0.875rem; color: #6b7280;">
            Fill in the details below. Your complaint will be reviewed by our team within 24 hours.
        </p>
    </div>

    {{-- API error banner (shown by JS) --}}
    <div id="api-error-banner" style="display:none; background:#fef2f2; border:1px solid #fca5a5;
         border-radius:0.75rem; padding:1rem; margin-bottom:1.5rem;">
        <p style="font-weight:600; color:#dc2626; margin:0 0 0.5rem;">Please fix the following errors:</p>
        <ul id="api-error-list" style="margin:0; padding-left:1.25rem; color:#dc2626; font-size:0.875rem;"></ul>
    </div>

    {{-- NOTE: no action/method on form — submission handled by axios --}}
    <form id="complaint-form">

        {{-- STEP 1 — Category --}}
        <div class="rw-card" style="margin-bottom: 1.5rem;">
            <div class="rw-step-header">
                <span class="rw-step-num">1</span>
                <h2 class="rw-step-title">What type of issue is it?</h2>
            </div>

            <div id="categories-grid" style="display:grid; grid-template-columns:repeat(4,1fr); gap:0.75rem; margin-bottom:1.5rem;">
                <div style="grid-column:1/-1; text-align:center; color:#9ca3af; padding:1rem;">Loading categories…</div>
            </div>
            <input type="hidden" id="category_id" name="category_id">
            <p id="category-error" style="display:none; color:#dc2626; font-size:0.813rem;">⚠ Please select a category</p>

            {{-- Severity --}}
            <label style="display:block; font-size:0.875rem; font-weight:600; color:#374151; margin-bottom:0.5rem;">Severity Level</label>
            <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:0.5rem;">
                @foreach(['low'=>['Low','🟢','#d1fae5','#065f46'],'medium'=>['Medium','🟡','#fef9c3','#713f12'],'high'=>['High','🟠','#ffedd5','#7c2d12'],'emergency'=>['Emergency','🔴','#fee2e2','#7f1d1d']] as $val=>[$label,$emoji,$bg,$color])
                <label style="cursor:pointer;">
                    <input type="radio" name="severity" value="{{ $val }}" style="display:none;" class="sev-radio"
                           {{ $val === 'medium' ? 'checked' : '' }}>
                    <div class="sev-card {{ $val === 'medium' ? 'sev-selected' : '' }}"
                         data-bg="{{ $bg }}" data-color="{{ $color }}"
                         onclick="selectSeverity(this)"
                         style="{{ $val === 'medium' ? "background:{$bg}; border-color:{$color}; color:{$color};" : '' }}">
                        <div style="font-size:1.25rem;">{{ $emoji }}</div>
                        <div style="font-size:0.75rem; font-weight:600; margin-top:2px;">{{ $label }}</div>
                    </div>
                </label>
                @endforeach
            </div>
        </div>

        {{-- STEP 2 — Describe --}}
        <div class="rw-card" style="margin-bottom: 1.5rem;">
            <div class="rw-step-header">
                <span class="rw-step-num">2</span>
                <h2 class="rw-step-title">Describe the issue</h2>
            </div>
            <div style="margin-bottom:1rem;">
                <label class="rw-label">Title <span style="color:#ef4444;">*</span></label>
                <input type="text" name="title" id="title" placeholder="e.g. Large pothole on MG Road near bus stop"
                       class="rw-input" required maxlength="255">
                <p id="error-title" style="display:none;" class="rw-error"></p>
            </div>
            <div>
                <label class="rw-label">Description <span style="color:#ef4444;">*</span></label>
                <textarea name="description" id="description" rows="4"
                          placeholder="Describe the issue in detail — size, depth, hazard level, how long it's been there..."
                          class="rw-input" style="resize:vertical;" required minlength="20" maxlength="2000"></textarea>
                <p style="font-size:0.75rem; color:#9ca3af; margin-top:0.25rem;">Minimum 20 characters</p>
                <p id="error-description" style="display:none;" class="rw-error"></p>
            </div>
        </div>

        {{-- STEP 3 — Location --}}
        <div class="rw-card" style="margin-bottom: 1.5rem;">
            <div class="rw-step-header">
                <span class="rw-step-num">3</span>
                <h2 class="rw-step-title">Pin the exact location</h2>
            </div>
            <p style="font-size:0.875rem; color:#6b7280; margin-bottom:1rem; margin-top:-0.5rem;">
                Click "Use My Location" or tap anywhere on the map to drop a pin.
            </p>
            <button type="button" id="gps-btn" class="rw-gps-btn">📍 Use My Location</button>
            <div id="map" style="height:280px; width:100%; border-radius:0.75rem; border:1px solid #e5e7eb;
                                  margin-bottom:1rem; z-index:1; position:relative;"></div>
            <input type="hidden" id="latitude"  name="latitude">
            <input type="hidden" id="longitude" name="longitude">
            <div>
                <label class="rw-label">Address / Landmark <span style="color:#ef4444;">*</span></label>
                <input type="text" name="location" id="location"
                       placeholder="e.g. Near City Mall, MG Road, Bangalore"
                       class="rw-input" required>
            </div>
            <p id="coords-display" style="font-size:0.75rem; color:#9ca3af; margin-top:0.5rem;">
                No location selected yet — use the map above
            </p>
            <p id="error-latitude" style="display:none;" class="rw-error">⚠ Please pin your location on the map</p>
        </div>

        {{-- STEP 4 — Media --}}
        <div class="rw-card" style="margin-bottom: 1.5rem;">
            <div class="rw-step-header">
                <span class="rw-step-num">4</span>
                <h2 class="rw-step-title">Attach photos / videos</h2>
            </div>
            <p style="font-size:0.875rem; color:#6b7280; margin-bottom:1rem; margin-top:-0.5rem;">
                Photos help engineers assess the severity faster.
            </p>
            <div style="margin-bottom:1.25rem;">
                <label class="rw-label">Photos
                    <span style="color:#9ca3af; font-weight:400;">(up to 5 · JPG, PNG, WEBP · max 10MB each)</span>
                </label>
                <label id="photo-drop-zone" class="rw-upload-zone">
                    <div style="text-align:center;">
                        <div style="font-size:2.5rem; margin-bottom:0.5rem;">📷</div>
                        <p style="font-size:0.875rem; color:#6b7280; margin:0;">Click to upload or drag &amp; drop</p>
                        <p style="font-size:0.75rem; color:#9ca3af; margin:0.25rem 0 0;">JPG, PNG, WEBP up to 10MB</p>
                    </div>
                    <input id="images" name="images[]" type="file" style="display:none;"
                           multiple accept=".jpg,.jpeg,.png,.webp,.heic" onchange="previewImages(this)">
                </label>
                <div id="image-previews" style="display:grid; grid-template-columns:repeat(4,1fr); gap:0.5rem; margin-top:0.75rem;"></div>
            </div>
            <div>
                <label class="rw-label">Videos
                    <span style="color:#9ca3af; font-weight:400;">(up to 2 · MP4, MOV · max 50MB each)</span>
                </label>
                <label id="video-drop-zone" class="rw-upload-zone" style="padding:1.25rem;">
                    <div style="text-align:center;">
                        <div style="font-size:2rem; margin-bottom:0.375rem;">🎥</div>
                        <p style="font-size:0.875rem; color:#6b7280; margin:0;">Click to upload video</p>
                    </div>
                    <input id="videos" name="videos[]" type="file" style="display:none;"
                           multiple accept=".mp4,.mov,.webm" onchange="previewVideos(this)">
                </label>
                <div id="video-names" style="margin-top:0.5rem;"></div>
            </div>
        </div>

        {{-- STEP 5 — Options & Submit --}}
        <div class="rw-card">
            <div class="rw-step-header">
                <span class="rw-step-num">5</span>
                <h2 class="rw-step-title">Options</h2>
            </div>
            <label style="display:flex; align-items:flex-start; gap:0.75rem; cursor:pointer; margin-bottom:2rem;">
                <input type="checkbox" name="is_anonymous" id="is_anonymous" value="1"
                       style="margin-top:3px; width:1rem; height:1rem; accent-color:#4f46e5; cursor:pointer;">
                <div>
                    <p style="font-size:0.875rem; font-weight:600; color:#111827; margin:0;">Submit anonymously</p>
                    <p style="font-size:0.813rem; color:#6b7280; margin:0.125rem 0 0;">Your name won't be shown publicly.</p>
                </div>
            </label>
            <div style="display:flex; align-items:center; justify-content:space-between;
                        padding-top:1.25rem; border-top:1px solid #f3f4f6;">
                <a href="{{ route('citizen.complaints.index') }}"
                   style="font-size:0.875rem; color:#6b7280; text-decoration:none;">← Cancel</a>
                <button type="submit" id="submit-btn" class="rw-submit-btn">
                    🚨 Submit Complaint
                </button>
            </div>
        </div>

    </form>
</div>

{{-- ══ Styles ══════════════════════════════════════════════════════════════ --}}
<style>
.rw-card { background:#fff; border:1px solid #e5e7eb; border-radius:1rem; padding:1.5rem; box-shadow:0 1px 3px rgba(0,0,0,0.07); }
.rw-step-header { display:flex; align-items:center; gap:0.75rem; margin-bottom:1.25rem; }
.rw-step-num { display:flex; align-items:center; justify-content:center; width:2rem; height:2rem; border-radius:9999px; background:#4f46e5; color:#fff; font-weight:700; font-size:0.875rem; flex-shrink:0; }
.rw-step-title { font-size:1rem; font-weight:700; color:#111827; margin:0; }
.cat-card { border:2px solid #e5e7eb; border-radius:0.75rem; padding:0.875rem 0.5rem; text-align:center; background:#fff; transition:all 0.15s; cursor:pointer; }
.cat-card:hover { border-color:#a5b4fc; background:#eef2ff; }
.cat-selected { border-color:#4f46e5 !important; background:#eef2ff !important; box-shadow:0 0 0 3px rgba(79,70,229,0.15); }
.sev-card { border:2px solid #e5e7eb; border-radius:0.75rem; padding:0.75rem 0.25rem; text-align:center; background:#fff; transition:all 0.15s; cursor:pointer; }
.sev-card:hover { border-color:#a5b4fc; }
.sev-selected { box-shadow:0 0 0 3px rgba(79,70,229,0.15); }
.rw-label { display:block; font-size:0.875rem; font-weight:600; color:#374151; margin-bottom:0.375rem; }
.rw-input { display:block; width:100%; border:1.5px solid #d1d5db; border-radius:0.625rem; padding:0.625rem 0.875rem; font-size:0.875rem; color:#111827; background:#fff; outline:none; transition:border-color 0.15s; box-sizing:border-box; font-family:inherit; }
.rw-input:focus { border-color:#4f46e5; box-shadow:0 0 0 3px rgba(79,70,229,0.12); }
.rw-error { color:#dc2626; font-size:0.813rem; margin-top:0.25rem; margin-bottom:0; }
.rw-gps-btn { display:inline-flex; align-items:center; gap:0.5rem; background:#eef2ff; border:1.5px solid #a5b4fc; border-radius:0.625rem; padding:0.5rem 1rem; font-size:0.875rem; font-weight:600; color:#4338ca; cursor:pointer; margin-bottom:0.875rem; transition:background 0.15s; }
.rw-gps-btn:hover { background:#e0e7ff; }
.rw-upload-zone { display:flex; align-items:center; justify-content:center; width:100%; padding:2rem 1rem; border:2px dashed #d1d5db; border-radius:0.75rem; cursor:pointer; background:#f9fafb; transition:border-color 0.15s,background 0.15s; box-sizing:border-box; }
.rw-upload-zone:hover { border-color:#818cf8; background:#eef2ff; }
.rw-submit-btn { display:inline-flex; align-items:center; gap:0.5rem; background:#4f46e5; color:#fff; border:none; border-radius:0.75rem; padding:0.75rem 1.75rem; font-size:0.9375rem; font-weight:700; cursor:pointer; box-shadow:0 4px 12px rgba(79,70,229,0.3); transition:background 0.15s,transform 0.1s; font-family:inherit; }
.rw-submit-btn:hover { background:#4338ca; transform:translateY(-1px); }
.rw-submit-btn:disabled { background:#a5b4fc; cursor:not-allowed; transform:none; }
</style>

{{-- ══ Scripts ══════════════════════════════════════════════════════════════ --}}
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── 1. Load categories from API ─────────────────────────────────────────
    axios.get('/api/v1/categories').then(res => {
        const grid = document.getElementById('categories-grid');
        grid.innerHTML = res.data.data.map(cat => `
            <label style="cursor:pointer;">
                <input type="radio" name="category_id" value="${cat.id}"
                       style="display:none;" class="cat-radio"
                       onchange="document.getElementById('category_id').value=this.value">
                <div class="cat-card" onclick="selectCategory(this)">
                    <div style="font-size:1.75rem; line-height:1; margin-bottom:0.375rem;">${cat.icon ?? '📌'}</div>
                    <div style="font-size:0.75rem; font-weight:600; color:#374151;">${cat.name}</div>
                </div>
            </label>`).join('');
    }).catch(() => {
        document.getElementById('categories-grid').innerHTML =
            '<div style="color:#dc2626; font-size:0.875rem; grid-column:1/-1;">Failed to load categories.</div>';
    });

    // ── 2. Leaflet map ──────────────────────────────────────────────────────
    const map = L.map('map').setView([20.5937, 78.9629], 5);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://openstreetmap.org">OpenStreetMap</a>'
    }).addTo(map);

    delete L.Icon.Default.prototype._getIconUrl;
    L.Icon.Default.mergeOptions({
        iconRetinaUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-icon-2x.png',
        iconUrl:       'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-icon.png',
        shadowUrl:     'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
    });

    let marker = null;
    function setPin(lat, lng) {
        if (marker) map.removeLayer(marker);
        marker = L.marker([lat, lng], { draggable: true }).addTo(map);
        document.getElementById('latitude').value  = lat.toFixed(7);
        document.getElementById('longitude').value = lng.toFixed(7);
        document.getElementById('coords-display').innerHTML =
            `<span style="color:#4f46e5;font-weight:600;">📍 ${lat.toFixed(5)}, ${lng.toFixed(5)}</span> — drag the pin to fine-tune`;
        marker.on('dragend', e => { const p = e.target.getLatLng(); setPin(p.lat, p.lng); });
    }
    map.on('click', e => setPin(e.latlng.lat, e.latlng.lng));

    // ── 3. GPS button ───────────────────────────────────────────────────────
    document.getElementById('gps-btn').addEventListener('click', function () {
        this.textContent = '⏳ Getting your location...';
        this.disabled = true;
        const btn = this;
        navigator.geolocation.getCurrentPosition(
            pos => { map.setView([pos.coords.latitude, pos.coords.longitude], 16); setPin(pos.coords.latitude, pos.coords.longitude); btn.textContent = '✅ Location captured'; },
            ()  => { btn.textContent = '📍 Use My Location'; btn.disabled = false; alert('Could not get location. Tap the map instead.'); },
            { timeout: 10000 }
        );
    });

    // ── 4. Category / severity selection ────────────────────────────────────
    window.selectCategory = function (el) {
        document.querySelectorAll('.cat-card').forEach(c => c.classList.remove('cat-selected'));
        el.classList.add('cat-selected');
        const radio = el.closest('label').querySelector('.cat-radio');
        radio.checked = true;
        document.getElementById('category_id').value = radio.value;
        document.getElementById('category-error').style.display = 'none';
    };
    window.selectSeverity = function (el) {
        document.querySelectorAll('.sev-card').forEach(c => {
            c.classList.remove('sev-selected'); c.style.background='#fff'; c.style.borderColor='#e5e7eb'; c.style.color='#374151';
        });
        el.classList.add('sev-selected');
        el.style.background = el.dataset.bg; el.style.borderColor = el.dataset.color; el.style.color = el.dataset.color;
        el.closest('label').querySelector('.sev-radio').checked = true;
    };

    // ── 5. Image / video previews ────────────────────────────────────────────
    window.previewImages = function (input) {
        const c = document.getElementById('image-previews'); c.innerHTML = '';
        Array.from(input.files).slice(0, 5).forEach(file => {
            const r = new FileReader();
            r.onload = e => { c.innerHTML += `<div style="position:relative;"><img src="${e.target.result}" style="height:80px;width:100%;object-fit:cover;border-radius:0.5rem;border:1px solid #e5e7eb;"><p style="font-size:0.65rem;color:#9ca3af;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin:0.2rem 0 0;">${file.name}</p></div>`; };
            r.readAsDataURL(file);
        });
        if (input.files.length > 0) document.getElementById('photo-drop-zone').style.borderColor = '#818cf8';
    };
    window.previewVideos = function (input) {
        const c = document.getElementById('video-names'); c.innerHTML = '';
        Array.from(input.files).slice(0, 2).forEach(file => {
            c.innerHTML += `<p style="font-size:0.813rem;color:#374151;display:flex;align-items:center;gap:0.375rem;margin:0.25rem 0;">🎥 <strong>${file.name}</strong> <span style="color:#9ca3af;">(${(file.size/1048576).toFixed(1)} MB)</span></p>`;
        });
        if (input.files.length > 0) document.getElementById('video-drop-zone').style.borderColor = '#818cf8';
    };

    // ── 6. Form submit → POST /api/v1/citizen/complaints ────────────────────
    document.getElementById('complaint-form').addEventListener('submit', async function (e) {
        e.preventDefault();

        // Client-side guards
        if (!document.getElementById('category_id').value) {
            document.getElementById('category-error').style.display = 'block';
            document.getElementById('categories-grid').scrollIntoView({ behavior: 'smooth' });
            return;
        }
        if (!document.getElementById('latitude').value) {
            document.getElementById('error-latitude').style.display = 'block';
            document.getElementById('map').scrollIntoView({ behavior: 'smooth' });
            return;
        }

        const btn = document.getElementById('submit-btn');
        btn.disabled = true; btn.textContent = '⏳ Submitting...';

        // Build multipart FormData
        const formData = new FormData();
        formData.append('title',        document.getElementById('title').value);
        formData.append('description',  document.getElementById('description').value);
        formData.append('category_id',  document.getElementById('category_id').value);
        formData.append('severity',     document.querySelector('.sev-radio:checked')?.value ?? 'medium');
        formData.append('latitude',     document.getElementById('latitude').value);
        formData.append('longitude',    document.getElementById('longitude').value);
        formData.append('location',     document.getElementById('location').value);
        formData.append('is_anonymous', document.getElementById('is_anonymous').checked ? '1' : '0');

        const imageFiles = document.getElementById('images').files;
        const videoFiles = document.getElementById('videos').files;
        [...imageFiles].forEach(f => formData.append('images[]', f));
        [...videoFiles].forEach(f => formData.append('videos[]', f));

        try {
            const res = await axios.post('/api/v1/citizen/complaints', formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            });
            // Redirect to the new complaint's detail page
            window.location.href = `/citizen/complaints/${res.data.complaint.id}`;

        } catch (err) {
            btn.disabled = false; btn.textContent = '🚨 Submit Complaint';

            if (err.response?.status === 422) {
                // Show validation errors from API
                const errors = err.response.data.errors ?? {};
                const banner = document.getElementById('api-error-banner');
                const list   = document.getElementById('api-error-list');
                list.innerHTML = Object.values(errors).flat().map(e => `<li>${e}</li>`).join('');
                banner.style.display = 'block';
                banner.scrollIntoView({ behavior: 'smooth' });

                // Highlight individual fields
                Object.keys(errors).forEach(field => {
                    const el = document.getElementById(`error-${field}`);
                    if (el) { el.textContent = errors[field][0]; el.style.display = 'block'; }
                });
            } else {
                alert('Something went wrong. Please try again.');
            }
        }
    });
});
</script>
@endsection
