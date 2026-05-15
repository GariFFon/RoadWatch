{{--
    Shared Profile Upload Overlay
    @include('partials.profile-upload-ui')
    - Click pencil button → upload new photo/banner
    - Click photo/banner itself → lightbox preview
--}}

<style>
/* ── Upload buttons ─────────────────────────────── */
.pu-btn {
    position:absolute; display:flex; align-items:center; justify-content:center;
    border:none; cursor:pointer; font-family:inherit;
    transition:all .2s;
}

/* Pencil on avatar — small badge bottom-right */
.pu-pencil-avatar {
    width:28px; height:28px; border-radius:50%;
    background:#4f46e5; color:#fff;
    bottom:2px; right:2px;
    box-shadow:0 2px 8px rgba(79,70,229,.5);
    font-size:.8rem; z-index:10;
}
.pu-pencil-avatar:hover { background:#4338ca; transform:scale(1.1); }

/* Pencil on banner — pill bottom-right */
.pu-pencil-banner {
    bottom:.625rem; right:.75rem; border-radius:9999px;
    background:rgba(0,0,0,.55); color:#fff;
    padding:.35rem .85rem; gap:.4rem; flex-direction:row;
    font-size:.72rem; font-weight:700; letter-spacing:.02em;
    backdrop-filter:blur(6px);
    box-shadow:0 2px 10px rgba(0,0,0,.3);
}
.pu-pencil-banner:hover { background:rgba(0,0,0,.8); }

/* Spinner */
.pu-spinner {
    display:none; width:18px; height:18px;
    border:2.5px solid rgba(255,255,255,.35);
    border-top-color:#fff; border-radius:50%;
    animation:pu-spin .7s linear infinite;
}
@keyframes pu-spin { to { transform:rotate(360deg); } }

/* Toast */
.pu-toast {
    position:fixed; bottom:1.5rem; right:1.5rem; z-index:99999;
    color:#fff; padding:.75rem 1.25rem; border-radius:.875rem;
    font-size:.8125rem; font-weight:600;
    box-shadow:0 8px 32px rgba(0,0,0,.25);
    animation:pu-slidein .3s ease;
}
@keyframes pu-slidein { from{opacity:0;transform:translateY(1rem)} to{opacity:1;transform:translateY(0)} }

/* ── Lightbox ───────────────────────────────────── */
#pu-lightbox {
    display:none; position:fixed; inset:0; z-index:99998;
    background:rgba(0,0,0,.75); backdrop-filter:blur(8px);
    align-items:center; justify-content:center;
    cursor:zoom-out;
}
#pu-lightbox.open { display:flex; animation:pu-lbin .2s ease; }
@keyframes pu-lbin { from{opacity:0;transform:scale(.95)} to{opacity:1;transform:scale(1)} }
#pu-lightbox img {
    max-width:min(640px, 90vw); max-height:80vh;
    border-radius:1rem; object-fit:contain;
    box-shadow:0 24px 64px rgba(0,0,0,.5);
    cursor:default;
}
#pu-lightbox-close {
    position:fixed; top:1.25rem; right:1.5rem;
    background:rgba(255,255,255,.15); color:#fff;
    border:none; border-radius:50%; width:40px; height:40px;
    font-size:1.25rem; cursor:pointer; display:flex;
    align-items:center; justify-content:center;
    backdrop-filter:blur(4px);
    transition:background .15s;
}
#pu-lightbox-close:hover { background:rgba(255,255,255,.3); }
</style>

<script>
// ── Lightbox ────────────────────────────────────────────────────────────────
function puOpenLightbox(src) {
    if (!src || src === 'null' || src === '') return;
    const lb = document.getElementById('pu-lightbox');
    const img = document.getElementById('pu-lightbox-img');
    img.src = src;
    lb.classList.add('open');
    document.body.style.overflow = 'hidden';
}
function puCloseLightbox() {
    document.getElementById('pu-lightbox').classList.remove('open');
    document.body.style.overflow = '';
}

// ── Toast ────────────────────────────────────────────────────────────────────
function puShowToast(msg, ok = true) {
    const t = document.createElement('div');
    t.className = 'pu-toast';
    t.style.background = ok ? '#15803d' : '#dc2626';
    t.textContent = msg;
    document.body.appendChild(t);
    setTimeout(() => t.remove(), 3500);
}

// ── Upload ───────────────────────────────────────────────────────────────────
function puUpload(endpoint, fileInput, onSuccess) {
    const file = fileInput.files[0];
    if (!file) return;
    const fd = new FormData();
    fd.append(endpoint.includes('photo') ? 'photo' : 'banner', file);

    const spinnerId = endpoint.includes('photo') ? 'pu-avatar-spinner' : 'pu-banner-spinner';
    const spinner = document.getElementById(spinnerId);
    if (spinner) spinner.style.display = 'block';

    axios.post(endpoint, fd, { headers: { 'Content-Type': 'multipart/form-data' } })
        .then(r => {
            puShowToast('✅ Updated successfully!');
            onSuccess(r.data);
        })
        .catch(e => {
            puShowToast('❌ ' + (e.response?.data?.message || 'Upload failed.'), false);
        })
        .finally(() => { if (spinner) spinner.style.display = 'none'; });
}

function puTriggerBanner() { document.getElementById('pu-banner-input').click(); }
function puTriggerPhoto()  { document.getElementById('pu-photo-input').click(); }

// ── Wire up inputs ────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    // Close lightbox on backdrop click
    document.getElementById('pu-lightbox').addEventListener('click', function (e) {
        if (e.target === this) puCloseLightbox();
    });

    const bannerInput = document.getElementById('pu-banner-input');
    const photoInput  = document.getElementById('pu-photo-input');
    if (!bannerInput || !photoInput) return;

    bannerInput.addEventListener('change', function () {
        puUpload('/api/v1/profile/banner', this, function (data) {
            const area = document.getElementById('profile-banner-area');
            if (area) {
                area.style.backgroundImage    = `url('${data.profile_banner_url}')`;
                area.style.backgroundSize     = 'cover';
                area.style.backgroundPosition = 'center';
                area.dataset.src = data.profile_banner_url;
            }
        });
    });

    photoInput.addEventListener('change', function () {
        puUpload('/api/v1/profile/photo', this, function (data) {
            // Update all avatar img elements
            const img = document.querySelector('#avatar-wrap img, #ep-avatar-wrap img, #ap-avatar-wrap img');
            if (img) {
                img.src = data.profile_photo_url;
                img.dataset.src = data.profile_photo_url;
            }
            // Also swap initials div if present
            const div = document.querySelector('#avatar-wrap > div > div[style*="border-radius:50%"]:not(button), #ep-avatar-wrap > div > div[style*="border-radius:50%"]:not(button), #ap-avatar-wrap > div > div[style*="border-radius:50%"]:not(button)');
            if (div && !img) {
                div.outerHTML = `<img src="${data.profile_photo_url}" data-src="${data.profile_photo_url}" alt="avatar"
                    style="width:98px;height:98px;border-radius:50%;object-fit:cover;
                           border:4px solid #fff;box-sizing:border-box;
                           box-shadow:0 4px 16px rgba(0,0,0,.12);display:block;cursor:zoom-in;"
                    onclick="puOpenLightbox(this.src)">`;
            }
        });
    });
});
</script>

{{-- Lightbox modal --}}
<div id="pu-lightbox">
    <button id="pu-lightbox-close" onclick="puCloseLightbox()" title="Close">✕</button>
    <img id="pu-lightbox-img" src="" alt="Preview">
</div>

{{-- Hidden file inputs --}}
<input type="file" id="pu-banner-input" accept="image/jpeg,image/png,image/webp" style="display:none;">
<input type="file" id="pu-photo-input"  accept="image/jpeg,image/png,image/webp,image/gif" style="display:none;">
