@extends('layouts.citizen')

@section('title', 'Report a Road Issue')

@section('content')

{{-- ══════════════════════════════════════════════════════
     STYLES
══════════════════════════════════════════════════════ --}}
<style>
@keyframes fadeInUp{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:translateY(0)}}
@keyframes shimmer{0%{background-position:-200% 0}100%{background-position:200% 0}}
@keyframes livePulse{0%,100%{opacity:1;transform:scale(1)}50%{opacity:.5;transform:scale(.85)}}

/* ── Page wrapper ── */
.cf-wrap{max-width:740px;margin:0 auto;}

/* ── Page header ── */
.cf-header{
  display:flex;align-items:flex-start;justify-content:space-between;
  gap:1rem;flex-wrap:wrap;margin-bottom:2rem;
  animation:fadeInUp .4s ease;
}
.cf-title{font-size:1.625rem;font-weight:800;color:#0f0e1a;letter-spacing:-.03em;line-height:1.2;}
.cf-subtitle{font-size:.875rem;color:#6b7280;margin-top:.375rem;line-height:1.6;}
.cf-draft-notice{
  display:none;
  font-size:.78rem;color:#6366f1;margin-top:.375rem;font-weight:600;
  display:none;align-items:center;gap:.3rem;
}

.cf-reset-btn{
  flex-shrink:0;display:inline-flex;align-items:center;gap:.4rem;
  background:#fef2f2;border:1.5px solid #fca5a5;border-radius:.875rem;
  padding:.5rem 1rem;font-size:.8125rem;font-weight:600;
  color:#dc2626;cursor:pointer;transition:all .2s;white-space:nowrap;
}
.cf-reset-btn:hover{background:#fee2e2;transform:translateY(-1px);}

/* ── Step card ── */
.cf-card{
  background:#fff;
  border:1px solid rgba(228,232,246,.9);
  border-radius:1.375rem;
  padding:1.75rem;
  margin-bottom:1.375rem;
  box-shadow:0 2px 12px rgba(15,14,26,.05),0 1px 3px rgba(15,14,26,.04);
  animation:fadeInUp .4s ease both;
  transition:box-shadow .25s;
}
.cf-card:hover{box-shadow:0 6px 24px rgba(15,14,26,.08);}

/* ── Step header ── */
.cf-step-header{display:flex;align-items:center;gap:.875rem;margin-bottom:1.5rem;}
.cf-step-num{
  display:flex;align-items:center;justify-content:center;
  width:36px;height:36px;border-radius:50%;
  background:linear-gradient(135deg,#6366f1,#8b5cf6);
  color:#fff;font-weight:700;font-size:.875rem;flex-shrink:0;
  box-shadow:0 3px 10px rgba(99,102,241,.3);
}
.cf-step-title{font-size:1.0625rem;font-weight:700;color:#0f0e1a;margin:0;letter-spacing:-.01em;}
.cf-step-desc{font-size:.8125rem;color:#6b7280;margin:.125rem 0 0;}

/* ── Label / input ── */
.cf-label{display:block;font-size:.875rem;font-weight:600;color:#374151;margin-bottom:.4375rem;}
.cf-required{color:#ef4444;margin-left:.125rem;}
.cf-hint{font-size:.75rem;color:#9ca3af;margin-top:.25rem;}
.cf-input{
  display:block;width:100%;
  border:1.5px solid #e5e7eb;border-radius:.875rem;
  padding:.625rem .9375rem;font-size:.875rem;color:#0f0e1a;
  background:#fafbff;outline:none;
  transition:all .2s;box-sizing:border-box;font-family:inherit;
  line-height:1.5;
}
.cf-input:focus{border-color:#6366f1;box-shadow:0 0 0 3.5px rgba(99,102,241,.12);background:#fff;}
.cf-input::placeholder{color:#9ca3af;}
.cf-error{color:#dc2626;font-size:.8125rem;margin-top:.3rem;margin-bottom:0;display:none;font-weight:500;}

/* ── Category grid ── */
.cf-cat-grid{
  display:grid;grid-template-columns:repeat(4,1fr);gap:.75rem;margin-bottom:1.5rem;
}
.cf-cat-item{cursor:pointer;}
.cf-cat-card{
  border:2px solid #e5e7eb;border-radius:1rem;padding:1rem .5rem;
  text-align:center;background:#fafbff;
  transition:all .2s cubic-bezier(.4,0,.2,1);cursor:pointer;
}
.cf-cat-card:hover{border-color:#c4b5fd;background:#f5f3ff;transform:translateY(-2px);box-shadow:0 4px 14px rgba(99,102,241,.1);}
.cf-cat-card.selected{
  border-color:#6366f1;background:#eef2ff;
  box-shadow:0 0 0 3px rgba(99,102,241,.15);
  transform:translateY(-2px);
}
.cf-cat-icon{font-size:1.875rem;line-height:1;margin-bottom:.4375rem;}
.cf-cat-name{font-size:.73rem;font-weight:600;color:#374151;line-height:1.3;}
.cf-cat-card.selected .cf-cat-name{color:#4f46e5;}

/* ── Severity grid ── */
.cf-sev-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:.625rem;}
.cf-sev-card{
  border:2px solid #e5e7eb;border-radius:.875rem;padding:.875rem .375rem;
  text-align:center;background:#fafbff;
  transition:all .2s cubic-bezier(.4,0,.2,1);cursor:pointer;
}
.cf-sev-card:hover{border-color:#c4b5fd;transform:translateY(-1px);}
.cf-sev-card.selected{box-shadow:0 0 0 3px rgba(99,102,241,.15);transform:translateY(-1px);}
.cf-sev-icon{font-size:1.375rem;}
.cf-sev-label{font-size:.73rem;font-weight:600;margin-top:.25rem;}

/* ── GPS button ── */
.cf-gps-btn{
  display:inline-flex;align-items:center;gap:.5rem;
  background:#eef2ff;border:1.5px solid #c4b5fd;border-radius:.875rem;
  padding:.5625rem 1.125rem;font-size:.875rem;font-weight:600;
  color:#4338ca;cursor:pointer;margin-bottom:1rem;transition:all .2s;
}
.cf-gps-btn:hover{background:#e0e7ff;transform:translateY(-1px);box-shadow:0 4px 12px rgba(99,102,241,.2);}

/* ── Map container ── */
.cf-map-wrap{
  height:300px;border-radius:1rem;
  border:1.5px solid #e5e7eb;overflow:hidden;
  margin-bottom:1rem;position:relative;z-index:1;
  box-shadow:0 2px 8px rgba(15,14,26,.06);
  transition:border-color .2s;
}
.cf-map-wrap:hover{border-color:#c4b5fd;}

/* ── Upload zones ── */
.cf-upload-zone{
  display:flex;align-items:center;justify-content:center;
  width:100%;padding:2.25rem 1rem;
  border:2px dashed #d1d5db;border-radius:1rem;
  cursor:pointer;background:#fafbff;
  transition:all .2s;box-sizing:border-box;
}
.cf-upload-zone:hover{border-color:#8b5cf6;background:#f5f3ff;}
.cf-upload-zone.has-files{border-color:#8b5cf6;border-style:solid;}
.cf-upload-icon{font-size:2.25rem;margin-bottom:.5rem;display:block;}
.cf-upload-text{font-size:.875rem;color:#6b7280;margin:0;}
.cf-upload-hint{font-size:.75rem;color:#9ca3af;margin:.25rem 0 0;}

/* ── Image preview grid ── */
.cf-img-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:.625rem;margin-top:.875rem;}
.cf-img-thumb{
  position:relative;aspect-ratio:1;border-radius:.75rem;overflow:hidden;
  border:1px solid #e5e7eb;
}
.cf-img-thumb img{width:100%;height:100%;object-fit:cover;display:block;}
.cf-img-name{font-size:.65rem;color:#9ca3af;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin:.25rem 0 0;}

/* ── Anonymous toggle ── */
.cf-anon-wrap{
  display:flex;align-items:flex-start;gap:.875rem;
  cursor:pointer;padding:1rem;border-radius:1rem;
  border:1.5px solid #e5e7eb;background:#fafbff;
  transition:all .2s;margin-bottom:1.75rem;
}
.cf-anon-wrap:hover{border-color:#c4b5fd;background:#f5f3ff;}
.cf-anon-check{
  width:20px;height:20px;margin-top:2px;
  accent-color:#6366f1;cursor:pointer;flex-shrink:0;
}

/* ── Submit section ── */
.cf-submit-row{
  display:flex;align-items:center;justify-content:space-between;
  padding-top:1.25rem;border-top:1px solid #f3f4f6;gap:1rem;
}
.cf-cancel-link{
  font-size:.875rem;color:#6b7280;text-decoration:none;
  display:flex;align-items:center;gap:.375rem;transition:color .15s;
}
.cf-cancel-link:hover{color:#4f46e5;}
.cf-submit-btn{
  display:inline-flex;align-items:center;gap:.5rem;
  background:linear-gradient(135deg,#6366f1,#8b5cf6);
  color:#fff;border:none;border-radius:1rem;
  padding:.75rem 1.875rem;font-size:.9375rem;font-weight:700;
  cursor:pointer;box-shadow:0 4px 16px rgba(99,102,241,.35);
  transition:all .2s;font-family:inherit;
}
.cf-submit-btn:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(99,102,241,.4);}
.cf-submit-btn:disabled{background:linear-gradient(135deg,#a5b4fc,#c4b5fd);cursor:not-allowed;transform:none;box-shadow:none;}

/* ── Error banner ── */
.cf-error-banner{
  display:none;background:#fef2f2;border:1px solid #fca5a5;
  border-radius:1rem;padding:1.125rem;margin-bottom:1.5rem;
}

/* ── Coord display ── */
.cf-coords{font-size:.78rem;color:#9ca3af;margin-top:.5rem;}
.cf-coords.pinned{color:#6366f1;font-weight:600;}

/* ── Stagger ── */
.cf-card:nth-child(1){animation-delay:.04s}
.cf-card:nth-child(2){animation-delay:.08s}
.cf-card:nth-child(3){animation-delay:.12s}
.cf-card:nth-child(4){animation-delay:.16s}
.cf-card:nth-child(5){animation-delay:.2s}

@media(max-width:640px){
  .cf-cat-grid{grid-template-columns:repeat(3,1fr);}
  .cf-sev-grid{grid-template-columns:repeat(2,1fr);}
  .cf-img-grid{grid-template-columns:repeat(3,1fr);}
  .cf-title{font-size:1.375rem;}
  .cf-card{padding:1.375rem;}
}
</style>

<div class="cf-wrap">

    {{-- Page header --}}
    <div class="cf-header">
        <div>
            <div style="display:inline-flex;align-items:center;gap:.35rem;font-size:.625rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#6366f1;background:#eef2ff;padding:.25rem .625rem;border-radius:9999px;border:1px solid #c4b5fd;margin-bottom:.625rem;">
                <span style="width:5px;height:5px;border-radius:50%;background:#6366f1;animation:livePulse 1.5s infinite;"></span>
                Report a Road Issue
            </div>
            <h1 class="cf-title">🚨 Tell us what's wrong</h1>
            <p class="cf-subtitle">Fill in the details below. Our team reviews reports within 24 hours.</p>
            <p id="draft-notice" class="cf-draft-notice" style="display:none;">
                ✏️ Draft restored — your previous progress has been loaded.
            </p>
        </div>
        <button type="button" onclick="resetDraft()" class="cf-reset-btn">
            <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            Reset Form
        </button>
    </div>

    {{-- API error banner --}}
    <div id="api-error-banner" class="cf-error-banner">
        <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.5rem;">
            <span style="font-size:1.125rem;">⚠️</span>
            <p style="font-weight:700;color:#dc2626;margin:0;font-size:.875rem;">Please fix the following errors:</p>
        </div>
        <ul id="api-error-list" style="margin:0;padding-left:1.375rem;color:#dc2626;font-size:.8125rem;line-height:1.7;"></ul>
    </div>

    <form id="complaint-form">

        {{-- ── STEP 1: Category & Severity ── --}}
        <div class="cf-card">
            <div class="cf-step-header">
                <span class="cf-step-num">1</span>
                <div>
                    <h2 class="cf-step-title">What type of issue is it?</h2>
                    <p class="cf-step-desc">Select a category and severity level</p>
                </div>
            </div>

            <div id="categories-grid" class="cf-cat-grid">
                @foreach([1,2,3,4,5,6,7,8] as $i)
                <div style="background:linear-gradient(90deg,#f0f3fb 25%,#e4eaf6 50%,#f0f3fb 75%);background-size:200% 100%;animation:shimmer 1.5s infinite;border-radius:1rem;height:90px;animation-delay:{{ ($i-1)*.05 }}s;"></div>
                @endforeach
            </div>
            <input type="hidden" id="category_id" name="category_id">
            <p id="category-error" class="cf-error">⚠ Please select a category</p>

            <div style="height:1px;background:#f3f4f6;margin:1.25rem 0;"></div>

            <label class="cf-label">Severity Level</label>
            <div id="severity-grid" class="cf-sev-grid">
                @foreach([1,2,3,4] as $i)
                <div style="background:linear-gradient(90deg,#f0f3fb 25%,#e4eaf6 50%,#f0f3fb 75%);background-size:200% 100%;animation:shimmer 1.5s infinite;border-radius:.875rem;height:72px;animation-delay:{{ ($i-1)*.05 }}s;"></div>
                @endforeach
            </div>
            <input type="hidden" id="severity_value" name="severity" value="medium">
            <p id="severity-error" class="cf-error">⚠ Please select a severity level</p>
        </div>

        {{-- ── STEP 2: Describe ── --}}
        <div class="cf-card">
            <div class="cf-step-header">
                <span class="cf-step-num">2</span>
                <div>
                    <h2 class="cf-step-title">Describe the issue</h2>
                    <p class="cf-step-desc">Give us enough detail for engineers to assess</p>
                </div>
            </div>
            <div style="margin-bottom:1.125rem;">
                <label class="cf-label" for="title">Title <span class="cf-required">*</span></label>
                <input type="text" name="title" id="title"
                       placeholder="e.g. Large pothole on MG Road near bus stop"
                       class="cf-input" required maxlength="255">
                <p id="error-title" class="cf-error"></p>
            </div>
            <div>
                <label class="cf-label" for="description">Description <span class="cf-required">*</span></label>
                <textarea name="description" id="description" rows="4"
                          placeholder="Describe the issue in detail — size, depth, hazard level, how long it's been there..."
                          class="cf-input" style="resize:vertical;" required minlength="20" maxlength="2000"></textarea>
                <p class="cf-hint">Minimum 20 characters</p>
                <p id="error-description" class="cf-error"></p>
            </div>
        </div>

        {{-- ── STEP 3: Location ── --}}
        <div class="cf-card">
            <div class="cf-step-header">
                <span class="cf-step-num">3</span>
                <div>
                    <h2 class="cf-step-title">Pin the exact location</h2>
                    <p class="cf-step-desc">Click "Use My Location" or tap on the map to drop a pin</p>
                </div>
            </div>
            <button type="button" id="gps-btn" class="cf-gps-btn">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Use My Location
            </button>
            <div id="map" class="cf-map-wrap"></div>
            <input type="hidden" id="latitude"  name="latitude">
            <input type="hidden" id="longitude" name="longitude">
            <div style="margin-top:.875rem;">
                <label class="cf-label" for="location">Address / Landmark <span class="cf-required">*</span></label>
                <input type="text" name="location" id="location"
                       placeholder="e.g. Near City Mall, MG Road, Bangalore"
                       class="cf-input" required>
            </div>
            <p id="coords-display" class="cf-coords">📍 No location selected yet — use the map above</p>
            <p id="error-latitude" class="cf-error">⚠ Please pin your location on the map</p>
        </div>

        {{-- ── STEP 4: Media ── --}}
        <div class="cf-card">
            <div class="cf-step-header">
                <span class="cf-step-num">4</span>
                <div>
                    <h2 class="cf-step-title">Attach photos / videos</h2>
                    <p class="cf-step-desc">At least one photo is required — videos help engineers assess severity faster</p>
                </div>
            </div>

            <div style="margin-bottom:1.375rem;">
                <label class="cf-label">
                    Photos <span class="cf-required">*</span>
                    <span style="color:#9ca3af;font-weight:400;font-size:.8125rem;"> (1–5 · JPG, PNG, WEBP · max 10MB each)</span>
                </label>
                <label id="photo-drop-zone" class="cf-upload-zone" for="images">
                    <div style="text-align:center;">
                        <span class="cf-upload-icon">📷</span>
                        <p class="cf-upload-text">Click to upload or drag &amp; drop</p>
                        <p class="cf-upload-hint">JPG, PNG, WEBP up to 10MB</p>
                    </div>
                    <input id="images" name="images[]" type="file" style="display:none;"
                           multiple accept=".jpg,.jpeg,.png,.webp,.heic" onchange="previewImages(this)">
                </label>
                <div id="image-previews" class="cf-img-grid"></div>
                <p id="error-images" class="cf-error">⚠ Please upload at least one photo of the site</p>
            </div>

            <div>
                <label class="cf-label">
                    Videos
                    <span style="color:#9ca3af;font-weight:400;font-size:.8125rem;"> (optional · up to 2 · MP4, MOV · max 50MB each)</span>
                </label>
                <label id="video-drop-zone" class="cf-upload-zone" for="videos" style="padding:1.5rem 1rem;">
                    <div style="text-align:center;">
                        <span class="cf-upload-icon" style="font-size:1.75rem;">🎥</span>
                        <p class="cf-upload-text">Click to upload video</p>
                    </div>
                    <input id="videos" name="videos[]" type="file" style="display:none;"
                           multiple accept=".mp4,.mov,.webm" onchange="previewVideos(this)">
                </label>
                <div id="video-names"></div>
            </div>
        </div>

        {{-- ── STEP 5: Options & Submit ── --}}
        <div class="cf-card">
            <div class="cf-step-header">
                <span class="cf-step-num">5</span>
                <div>
                    <h2 class="cf-step-title">Review &amp; Submit</h2>
                    <p class="cf-step-desc">Choose privacy option and submit your report</p>
                </div>
            </div>
            <label class="cf-anon-wrap">
                <input type="checkbox" name="is_anonymous" id="is_anonymous" value="1" class="cf-anon-check">
                <div>
                    <p style="font-size:.875rem;font-weight:600;color:#0f0e1a;margin:0;">Submit anonymously</p>
                    <p style="font-size:.8125rem;color:#6b7280;margin:.125rem 0 0;">Your name won't be shown publicly on this complaint.</p>
                </div>
            </label>
            <div class="cf-submit-row">
                <a href="{{ route('citizen.complaints.index') }}" class="cf-cancel-link">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                    Cancel
                </a>
                <button type="submit" id="submit-btn" class="cf-submit-btn">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    Submit Complaint
                </button>
            </div>
        </div>

    </form>
</div>

{{-- ══════════════════════════════════════════════════════
     SCRIPTS
══════════════════════════════════════════════════════ --}}
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── 1. Load categories ──────────────────────────────────────────────────
    axios.get('/api/v1/categories').then(res => {
        const grid = document.getElementById('categories-grid');
        grid.innerHTML = res.data.data.map(cat => `
            <label class="cf-cat-item" style="cursor:pointer;">
                <input type="radio" name="category_id" value="${cat.id}"
                       style="display:none;" class="cat-radio"
                       onchange="document.getElementById('category_id').value=this.value">
                <div class="cf-cat-card" onclick="selectCategory(this)">
                    <div class="cf-cat-icon">${cat.icon ?? '📌'}</div>
                    <div class="cf-cat-name">${cat.name}</div>
                </div>
            </label>`).join('');
    }).catch(() => {
        document.getElementById('categories-grid').innerHTML =
            '<div style="color:#dc2626;font-size:.875rem;grid-column:1/-1;">Failed to load categories.</div>';
    });

    // ── 2. Load severity options ────────────────────────────────────────────
    const SEV_STYLE = {
        low:       { bg:'#ecfdf5', color:'#059669', border:'#86efac' },
        medium:    { bg:'#fffbeb', color:'#d97706', border:'#fcd34d' },
        high:      { bg:'#fff7ed', color:'#c2410c', border:'#fdba74' },
        emergency: { bg:'#fef2f2', color:'#dc2626', border:'#fca5a5' },
    };
    fetch('/api/v1/complaint-options')
        .then(r => r.json())
        .then(data => {
            const sevGrid    = document.getElementById('severity-grid');
            const defaultSev = 'medium';
            sevGrid.innerHTML = data.severities.map(sev => {
                const st      = SEV_STYLE[sev.value] ?? { bg:'#f3f4f6', color:'#374151', border:'#e5e7eb' };
                const isDef   = sev.value === defaultSev;
                return `<label style="cursor:pointer;">
                    <input type="radio" name="severity" value="${sev.value}" style="display:none;"
                           class="sev-radio" ${isDef ? 'checked' : ''}>
                    <div class="cf-sev-card ${isDef ? 'selected' : ''}"
                         data-bg="${st.bg}" data-color="${st.color}" data-border="${st.border}"
                         onclick="selectSeverity(this)"
                         style="${isDef ? `background:${st.bg};border-color:${st.border};color:${st.color};` : ''}">
                        <div class="cf-sev-icon">${sev.icon}</div>
                        <div class="cf-sev-label" style="${isDef?`color:${st.color};`:''}">${sev.label}</div>
                    </div>
                </label>`;
            }).join('');
            document.getElementById('severity_value').value = defaultSev;
        })
        .catch(() => {
            document.getElementById('severity-grid').innerHTML =
                '<div style="color:#dc2626;font-size:.875rem;grid-column:1/-1;">Failed to load severity options.</div>';
        });

    // ── 3. Leaflet map ──────────────────────────────────────────────────────
    const map = L.map('map').setView([20.5937, 78.9629], 5);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://openstreetmap.org">OpenStreetMap</a>'
    }).addTo(map);
    delete L.Icon.Default.prototype._getIconUrl;
    L.Icon.Default.mergeOptions({
        iconRetinaUrl:'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-icon-2x.png',
        iconUrl:      'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-icon.png',
        shadowUrl:    'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
    });

    let marker = null;
    function setPin(lat, lng) {
        if (marker) map.removeLayer(marker);
        marker = L.marker([lat, lng], { draggable: true }).addTo(map);
        document.getElementById('latitude').value  = lat.toFixed(7);
        document.getElementById('longitude').value = lng.toFixed(7);
        const cd = document.getElementById('coords-display');
        cd.innerHTML = `📍 <strong style="color:#6366f1">${lat.toFixed(5)}, ${lng.toFixed(5)}</strong> — drag the pin to fine-tune`;
        cd.classList.add('pinned');
        document.getElementById('error-latitude').style.display = 'none';
        marker.on('dragend', e => { const p = e.target.getLatLng(); setPin(p.lat, p.lng); });
    }
    map.on('click', e => setPin(e.latlng.lat, e.latlng.lng));

    // ── 4. GPS button ───────────────────────────────────────────────────────
    document.getElementById('gps-btn').addEventListener('click', function () {
        this.innerHTML = `<svg width="15" height="15" class="spin-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg> Getting location…`;
        this.disabled = true;
        const btn = this;
        navigator.geolocation.getCurrentPosition(
            pos => {
                map.setView([pos.coords.latitude, pos.coords.longitude], 16);
                setPin(pos.coords.latitude, pos.coords.longitude);
                btn.innerHTML = `✅ Location captured`;
                setTimeout(() => { btn.innerHTML = `<svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg> Use My Location`; btn.disabled = false; }, 3000);
            },
            () => {
                btn.innerHTML = `<svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg> Use My Location`;
                btn.disabled = false;
                alert('Could not get location. Tap the map instead.');
            },
            { timeout: 10000 }
        );
    });

    // ── 5. Category / severity selection ────────────────────────────────────
    window.selectCategory = function (el) {
        document.querySelectorAll('.cf-cat-card').forEach(c => c.classList.remove('selected'));
        el.classList.add('selected');
        const radio = el.closest('label').querySelector('.cat-radio');
        radio.checked = true;
        document.getElementById('category_id').value = radio.value;
        document.getElementById('category-error').style.display = 'none';
    };
    window.selectSeverity = function (el) {
        document.querySelectorAll('.cf-sev-card').forEach(c => {
            c.classList.remove('selected');
            c.style.background = '#fafbff'; c.style.borderColor = '#e5e7eb'; c.style.color = '#374151';
        });
        el.classList.add('selected');
        el.style.background   = el.dataset.bg;
        el.style.borderColor  = el.dataset.border;
        el.style.color        = el.dataset.color;
        const radio = el.closest('label').querySelector('.sev-radio');
        radio.checked = true;
        document.getElementById('severity_value').value = radio.value;
        // update sev-label colour
        const lbl = el.querySelector('.cf-sev-label');
        if(lbl) lbl.style.color = el.dataset.color;
    };

    // ── 6. Image / video previews ────────────────────────────────────────────
    window.previewImages = function (input) {
        const c = document.getElementById('image-previews'); c.innerHTML = '';
        Array.from(input.files).slice(0, 5).forEach(file => {
            const r = new FileReader();
            r.onload = e => {
                c.innerHTML += `<div class="cf-img-thumb"><img src="${e.target.result}" alt="${file.name}"><p class="cf-img-name">${file.name}</p></div>`;
            };
            r.readAsDataURL(file);
        });
        if (input.files.length > 0) {
            document.getElementById('photo-drop-zone').classList.add('has-files');
            document.getElementById('error-images').style.display = 'none';
        }
    };
    window.previewVideos = function (input) {
        const c = document.getElementById('video-names'); c.innerHTML = '';
        Array.from(input.files).slice(0, 2).forEach(file => {
            c.innerHTML += `<div style="display:flex;align-items:center;gap:.5rem;font-size:.8125rem;color:#374151;margin:.375rem 0;padding:.5rem .75rem;background:#f5f3ff;border-radius:.625rem;border:1px solid #ddd6fe;">
                <span>🎥</span><strong>${file.name}</strong>
                <span style="color:#9ca3af;margin-left:auto;">${(file.size/1048576).toFixed(1)} MB</span>
            </div>`;
        });
        if (input.files.length > 0) document.getElementById('video-drop-zone').classList.add('has-files');
    };

    // ── 7. Form submit ───────────────────────────────────────────────────────
    document.getElementById('complaint-form').addEventListener('submit', async function (e) {
        e.preventDefault();
        if (!document.getElementById('category_id').value) {
            document.getElementById('category-error').style.display = 'block';
            document.getElementById('categories-grid').scrollIntoView({ behavior:'smooth' });
            return;
        }
        if (!document.getElementById('latitude').value) {
            document.getElementById('error-latitude').style.display = 'block';
            document.getElementById('map').scrollIntoView({ behavior:'smooth' });
            return;
        }
        if (document.getElementById('images').files.length === 0) {
            const photoErr = document.getElementById('error-images');
            photoErr.style.display = 'block';
            document.getElementById('photo-drop-zone').style.borderColor = '#ef4444';
            photoErr.scrollIntoView({ behavior:'smooth', block:'center' });
            return;
        }
        const btn = document.getElementById('submit-btn');
        btn.disabled = true;
        btn.innerHTML = `<svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="animation:spin .7s linear infinite"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg> Submitting…`;

        const formData = new FormData();
        formData.append('title',       document.getElementById('title').value);
        formData.append('description', document.getElementById('description').value);
        formData.append('category_id', document.getElementById('category_id').value);
        formData.append('severity',    document.querySelector('.sev-radio:checked')?.value ?? 'medium');
        formData.append('latitude',    document.getElementById('latitude').value);
        formData.append('longitude',   document.getElementById('longitude').value);
        formData.append('location',    document.getElementById('location').value);
        formData.append('is_anonymous',document.getElementById('is_anonymous').checked ? '1' : '0');
        [...document.getElementById('images').files].forEach(f => formData.append('images[]', f));
        [...document.getElementById('videos').files].forEach(f => formData.append('videos[]', f));

        try {
            await axios.post('/api/v1/citizen/complaints', formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            });
            localStorage.removeItem('rw_complaint_draft');
            window.location.href = '/citizen/complaints?filed=1';
        } catch (err) {
            btn.disabled = false;
            btn.innerHTML = `<svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg> Submit Complaint`;
            if (err.response?.status === 422) {
                const errors = err.response.data.errors ?? {};
                const list   = document.getElementById('api-error-list');
                list.innerHTML = Object.values(errors).flat().map(e => `<li>${e}</li>`).join('');
                document.getElementById('api-error-banner').style.display = 'block';
                document.getElementById('api-error-banner').scrollIntoView({ behavior:'smooth' });
                Object.keys(errors).forEach(field => {
                    const el = document.getElementById(`error-${field}`);
                    if (el) { el.textContent = errors[field][0]; el.style.display = 'block'; }
                });
            } else {
                alert('Something went wrong. Please try again.');
            }
        }
    });

    // ── 8. Draft persistence ─────────────────────────────────────────────────
    const DRAFT_KEY   = 'rw_complaint_draft';
    const TEXT_FIELDS = ['title','description','location'];

    function saveDraft() {
        localStorage.setItem(DRAFT_KEY, JSON.stringify({
            title:       document.getElementById('title').value,
            description: document.getElementById('description').value,
            location:    document.getElementById('location').value,
            latitude:    document.getElementById('latitude').value,
            longitude:   document.getElementById('longitude').value,
            category_id: document.getElementById('category_id').value,
            severity:    document.getElementById('severity_value').value,
            is_anonymous:document.getElementById('is_anonymous').checked,
        }));
    }
    TEXT_FIELDS.forEach(id => {
        const el = document.getElementById(id);
        if (el) el.addEventListener('input', saveDraft);
    });
    document.getElementById('is_anonymous').addEventListener('change', saveDraft);

    function restoreDraft() {
        const raw = localStorage.getItem(DRAFT_KEY);
        if (!raw) return;
        try {
            const d = JSON.parse(raw);
            if (d.title)       document.getElementById('title').value       = d.title;
            if (d.description) document.getElementById('description').value = d.description;
            if (d.location)    document.getElementById('location').value    = d.location;
            if (d.is_anonymous) document.getElementById('is_anonymous').checked = true;
            if (d.latitude && d.longitude) {
                const lat = parseFloat(d.latitude), lng = parseFloat(d.longitude);
                setPin(lat, lng); map.setView([lat, lng], 15);
            }
            if (d.category_id) {
                document.getElementById('category_id').value = d.category_id;
                const radio = document.querySelector(`.cat-radio[value="${d.category_id}"]`);
                if (radio) { radio.checked = true; const card = radio.closest('label')?.querySelector('.cf-cat-card'); if (card) selectCategory(card); }
            }
            if (d.severity) {
                const sevRadio = document.querySelector(`.sev-radio[value="${d.severity}"]`);
                if (sevRadio) { sevRadio.checked = true; const card = sevRadio.closest('label')?.querySelector('.cf-sev-card'); if (card) selectSeverity(card); }
            }
            if (d.title || d.description || d.location || d.category_id) {
                document.getElementById('draft-notice').style.display = 'flex';
            }
        } catch(_) { localStorage.removeItem(DRAFT_KEY); }
    }

    Promise.allSettled([
        axios.get('/api/v1/categories'),
        fetch('/api/v1/complaint-options').then(r => r.json())
    ]).then(() => setTimeout(restoreDraft, 150));

    window.resetDraft = function() {
        if (!confirm('Reset the form? All entered data will be cleared.')) return;
        localStorage.removeItem(DRAFT_KEY);
        document.getElementById('complaint-form').reset();
        document.getElementById('category_id').value   = '';
        document.getElementById('severity_value').value = 'medium';
        document.getElementById('latitude').value  = '';
        document.getElementById('longitude').value = '';
        const cd = document.getElementById('coords-display');
        cd.textContent = '📍 No location selected yet — use the map above';
        cd.classList.remove('pinned');
        if (marker) { map.removeLayer(marker); marker = null; }
        document.querySelectorAll('.cf-cat-card').forEach(c => c.classList.remove('selected'));
        document.querySelectorAll('.cf-sev-card').forEach(c => {
            c.classList.remove('selected');
            c.style.background = '#fafbff'; c.style.borderColor = '#e5e7eb'; c.style.color = '#374151';
        });
        document.getElementById('image-previews').innerHTML = '';
        document.getElementById('video-names').innerHTML    = '';
        document.getElementById('draft-notice').style.display = 'none';
        const medCard = document.querySelector('.sev-radio[value="medium"]');
        if (medCard) { medCard.checked = true; const c = medCard.closest('label')?.querySelector('.cf-sev-card'); if(c) selectSeverity(c); }
    };

    map.on('click', () => setTimeout(saveDraft, 50));
    document.getElementById('gps-btn').addEventListener('click', () => setTimeout(saveDraft, 2000));
});
</script>

<style>
@keyframes spin{to{transform:rotate(360deg)}}
.spin-icon{animation:spin .7s linear infinite;}
</style>

@endsection
