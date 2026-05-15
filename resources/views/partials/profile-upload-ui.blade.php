{{--
    Shared Profile Upload Overlay
    Usage: @include('partials.profile-upload-ui', ['colors' => ['#4f46e5','#7c3aed']])
    Required IDs in host page:
      - profile-banner-area   : the cover strip div
      - profile-avatar-wrap   : the avatar container
      - p-avatar-img OR ep-avatar-img OR ap-avatar-img: img/div inside avatar wrap
--}}

<style>
.pu-btn {
    position:absolute; display:flex; align-items:center; justify-content:center;
    background:rgba(0,0,0,.5); color:#fff; border:none; cursor:pointer;
    font-size:.72rem; font-weight:700; letter-spacing:.02em;
    transition:background .2s; font-family:inherit;
    backdrop-filter:blur(4px);
}
.pu-btn:hover { background:rgba(0,0,0,.75); }

.pu-overlay-avatar {
    inset:0; border-radius:50%; gap:.25rem; flex-direction:column;
    opacity:0; transition:opacity .2s;
}
.pu-overlay-avatar:hover {
    opacity:1;
}
.pu-spinner {
    display:none; width:22px; height:22px; border:3px solid rgba(255,255,255,.3);
    border-top-color:#fff; border-radius:50%; animation:pu-spin .7s linear infinite;
}
@keyframes pu-spin { to { transform:rotate(360deg); } }
.pu-toast {
    position:fixed; bottom:1.5rem; right:1.5rem; z-index:9999;
    background:#111827; color:#fff; padding:.75rem 1.25rem;
    border-radius:.75rem; font-size:.8125rem; font-weight:600;
    box-shadow:0 8px 24px rgba(0,0,0,.25); transform:translateY(0);
    animation:pu-slidein .3s ease;
}
@keyframes pu-slidein { from{opacity:0;transform:translateY(1rem)} to{opacity:1;transform:translateY(0)} }
</style>

<script>
// ── Profile Upload Helpers ────────────────────────────────────────────────────
function puShowToast(msg, ok = true) {
    const t = document.createElement('div');
    t.className = 'pu-toast';
    t.style.background = ok ? '#15803d' : '#dc2626';
    t.textContent = msg;
    document.body.appendChild(t);
    setTimeout(() => t.remove(), 3500);
}

function puUpload(endpoint, fileInput, onSuccess) {
    const file = fileInput.files[0];
    if (!file) return;
    const fd = new FormData();
    fd.append(endpoint === '/api/v1/profile/photo' ? 'photo' : 'banner', file);

    // Show spinner on the relevant button
    const spinner = document.getElementById(
        endpoint.includes('photo') ? 'pu-avatar-spinner' : 'pu-banner-spinner'
    );
    if (spinner) spinner.style.display = 'block';

    axios.post(endpoint, fd, {headers:{'Content-Type':'multipart/form-data'}})
        .then(r => {
            puShowToast('✅ Updated successfully!');
            onSuccess(r.data);
        })
        .catch(e => {
            const msg = e.response?.data?.message || 'Upload failed.';
            puShowToast('❌ ' + msg, false);
        })
        .finally(() => { if (spinner) spinner.style.display = 'none'; });
}

function puTriggerBanner() { document.getElementById('pu-banner-input').click(); }
function puTriggerPhoto()  { document.getElementById('pu-photo-input').click(); }

document.addEventListener('DOMContentLoaded', function () {
    const bannerInput = document.getElementById('pu-banner-input');
    const photoInput  = document.getElementById('pu-photo-input');
    if (!bannerInput || !photoInput) return;

    bannerInput.addEventListener('change', function () {
        puUpload('/api/v1/profile/banner', this, function(data) {
            const el = document.getElementById('profile-banner-img');
            if (el) {
                el.src = data.profile_banner_url;
            } else {
                // Replace gradient with actual image
                const area = document.getElementById('profile-banner-area');
                if (area) {
                    area.style.backgroundImage = `url('${data.profile_banner_url}')`;
                    area.style.backgroundSize  = 'cover';
                    area.style.backgroundPosition = 'center';
                }
            }
        });
    });

    photoInput.addEventListener('change', function () {
        puUpload('/api/v1/profile/photo', this, function(data) {
            const wrap = document.getElementById('profile-avatar-wrap') ||
                         document.getElementById('ep-avatar-wrap') ||
                         document.getElementById('ap-avatar-wrap');
            if (!wrap) return;
            // Replace contents with real image, keeping overlay button
            const existing = wrap.querySelector('img, div[style*="border-radius:50%"]');
            if (existing) {
                // Swap to image
                const overlay = wrap.querySelector('.pu-overlay-avatar');
                wrap.innerHTML = `
                    <div style="position:relative;display:inline-block;">
                        <img src="${data.profile_photo_url}" alt="avatar"
                             style="width:90px;height:90px;border-radius:50%;object-fit:cover;
                                    border:4px solid #fff;box-shadow:0 4px 16px rgba(0,0,0,.12);display:block;">
                        <button class="pu-btn pu-overlay-avatar" onclick="puTriggerPhoto()" style="width:90px;height:90px;top:0;left:0;">
                            <div id="pu-avatar-spinner" class="pu-spinner"></div>
                            <span>📷</span>
                        </button>
                    </div>`;
            }
        });
    });
});
</script>

{{-- Hidden file inputs --}}
<input type="file" id="pu-banner-input" accept="image/jpeg,image/png,image/webp" style="display:none;">
<input type="file" id="pu-photo-input"  accept="image/jpeg,image/png,image/webp,image/gif" style="display:none;">
