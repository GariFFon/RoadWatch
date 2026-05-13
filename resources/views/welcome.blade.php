<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>RoadWatch — Report Road Issues, Track Real Fixes</title>
<meta name="description" content="RoadWatch lets citizens report potholes, waterlogging and road hazards. Track resolution in real time.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth}
body{font-family:'Inter',sans-serif;background:#fff;color:#111827;-webkit-font-smoothing:antialiased}

/* ─── NAV ─────────────────────────────────────────────────────────────────── */
nav{position:fixed;top:0;left:0;right:0;z-index:100;background:rgba(255,255,255,0.92);
    backdrop-filter:blur(16px);border-bottom:1px solid #f1f1f1;padding:0 2rem;
    transition:box-shadow .3s}
nav.scrolled{box-shadow:0 2px 20px rgba(0,0,0,.07)}
.ni{max-width:1200px;margin:0 auto;height:64px;display:flex;align-items:center;justify-content:space-between}
.logo{display:flex;align-items:center;gap:.625rem;text-decoration:none}
.li{width:36px;height:36px;background:#4f46e5;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0}
.lt{font-size:1.0625rem;font-weight:800;color:#111827;letter-spacing:-.02em}
.nl{display:flex;align-items:center;gap:.5rem}
.nlk{font-size:.875rem;font-weight:500;color:#6b7280;text-decoration:none;padding:.375rem .75rem;border-radius:.5rem;transition:background .15s,color .15s}
.nlk:hover{background:#f3f4f6;color:#111827}
.bo{border:1.5px solid #e5e7eb;color:#374151;border-radius:.625rem;padding:.4rem .9rem;font-size:.875rem;font-weight:600;text-decoration:none;transition:all .15s}
.bo:hover{border-color:#4f46e5;color:#4f46e5}
.bp{background:#4f46e5;color:#fff;border-radius:.625rem;padding:.475rem 1rem;font-size:.875rem;font-weight:600;text-decoration:none;transition:background .15s,box-shadow .15s;box-shadow:0 1px 6px rgba(79,70,229,.3)}
.bp:hover{background:#4338ca;box-shadow:0 4px 14px rgba(79,70,229,.35)}

/* ─── HERO ────────────────────────────────────────────────────────────────── */
.hero{min-height:100vh;display:flex;align-items:center;padding:7rem 2rem 5rem;
      background:#fff;position:relative;overflow:hidden}
/* subtle dot grid pattern */
.hero::before{content:'';position:absolute;inset:0;
  background-image:radial-gradient(circle,#e5e7eb 1px,transparent 1px);
  background-size:28px 28px;opacity:.55;pointer-events:none}
/* soft color blobs */
.hero::after{content:'';position:absolute;width:700px;height:700px;
  background:radial-gradient(circle,rgba(79,70,229,.06) 0%,transparent 70%);
  top:-100px;right:-100px;pointer-events:none}
.hi{max-width:1200px;margin:0 auto;width:100%;display:grid;grid-template-columns:1fr 1fr;
    gap:4rem;align-items:center;position:relative;z-index:1}

/* Left */
.h-badge{display:inline-flex;align-items:center;gap:.5rem;background:#f0f0ff;
  border:1px solid #c7d2fe;border-radius:9999px;padding:.3rem .9rem;
  font-size:.78rem;font-weight:700;color:#4f46e5;margin-bottom:1.5rem;letter-spacing:.01em}
.h-badge span{width:7px;height:7px;background:#4f46e5;border-radius:50%;animation:blink 1.5s infinite}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.3}}
@keyframes catpulse{0%,100%{opacity:1}50%{opacity:.4}}
h1{font-size:3.625rem;font-weight:900;color:#111827;line-height:1.08;letter-spacing:-.03em;margin-bottom:1.375rem}
h1 em{font-style:normal;color:#4f46e5}
.hdesc{font-size:1.1rem;color:#6b7280;line-height:1.75;margin-bottom:2.25rem;max-width:460px}
.hctas{display:flex;gap:.875rem;flex-wrap:wrap}
.bhp{display:inline-flex;align-items:center;gap:.5rem;background:#4f46e5;color:#fff;
  text-decoration:none;border-radius:.875rem;padding:.875rem 1.875rem;font-size:.9375rem;
  font-weight:700;box-shadow:0 4px 18px rgba(79,70,229,.35);transition:all .2s}
.bhp:hover{background:#4338ca;transform:translateY(-2px);box-shadow:0 8px 28px rgba(79,70,229,.4)}
.bhs{display:inline-flex;align-items:center;gap:.5rem;background:#fff;color:#374151;
  text-decoration:none;border-radius:.875rem;padding:.875rem 1.625rem;font-size:.9375rem;
  font-weight:600;border:1.5px solid #e5e7eb;transition:all .2s}
.bhs:hover{border-color:#a5b4fc;color:#4f46e5;background:#f5f3ff}

/* Right — complaint cards */
.hv{display:flex;flex-direction:column;gap:.875rem}
.hc{background:#fff;border:1.5px solid #f1f1f1;border-radius:1.125rem;padding:1.25rem 1.375rem;
    box-shadow:0 4px 20px rgba(0,0,0,.05);transition:transform .25s,box-shadow .25s;cursor:default}
.hc:hover{transform:translateY(-4px);box-shadow:0 12px 32px rgba(0,0,0,.1)}
.hc-top{display:flex;align-items:center;justify-content:space-between;margin-bottom:.625rem}
.hc-cat{font-size:.8125rem;font-weight:700;color:#111827;display:flex;align-items:center;gap:.375rem}
.hc-badge{font-size:.7rem;font-weight:700;padding:.2rem .625rem;border-radius:9999px}
.hc-loc{font-size:.78rem;color:#9ca3af;display:flex;align-items:center;gap:.3rem;margin-bottom:.875rem}
.hc-bar-wrap{height:5px;background:#f3f4f6;border-radius:9999px;overflow:hidden}
.hc-bar{height:100%;border-radius:9999px;transition:width .8s ease}

/* ─── STATS ───────────────────────────────────────────────────────────────── */
.stats{padding:4rem 2rem;border-top:1px solid #f3f4f6;border-bottom:1px solid #f3f4f6}
.si{max-width:1200px;margin:0 auto;display:grid;grid-template-columns:repeat(4,1fr);gap:2rem;text-align:center}
.sn{font-size:2.625rem;font-weight:900;color:#4f46e5;line-height:1}
.sl{font-size:.8125rem;font-weight:600;color:#9ca3af;margin-top:.375rem;text-transform:uppercase;letter-spacing:.04em}

/* ─── SECTIONS ────────────────────────────────────────────────────────────── */
.sec{padding:5.5rem 2rem}
.sec-in{max-width:1200px;margin:0 auto}
.stag{display:inline-block;background:#eef2ff;color:#4f46e5;font-size:.73rem;font-weight:700;
  padding:.3rem .875rem;border-radius:9999px;margin-bottom:1rem;letter-spacing:.06em;text-transform:uppercase}
.stit{font-size:2.25rem;font-weight:800;color:#111827;line-height:1.2;letter-spacing:-.02em}
.ssub{font-size:1.0625rem;color:#6b7280;line-height:1.7;max-width:500px}

/* ─── HOW IT WORKS ────────────────────────────────────────────────────────── */
.steps{display:grid;grid-template-columns:repeat(3,1fr);gap:2rem;margin-top:3.5rem}
.step{padding:2rem;border:1.5px solid #f1f1f1;border-radius:1.25rem;background:#fafafa;
      transition:border-color .2s,box-shadow .2s,transform .2s}
.step:hover{border-color:#c7d2fe;box-shadow:0 8px 28px rgba(79,70,229,.08);transform:translateY(-4px)}
.step-num{width:52px;height:52px;border-radius:14px;background:#4f46e5;
  display:flex;align-items:center;justify-content:center;font-size:1.375rem;
  margin-bottom:1.25rem;box-shadow:0 4px 14px rgba(79,70,229,.3)}
.step h3{font-size:1.0625rem;font-weight:700;color:#111827;margin-bottom:.5rem}
.step p{font-size:.875rem;color:#6b7280;line-height:1.65}

/* ─── CATEGORIES ──────────────────────────────────────────────────────────── */
.cg{display:grid;grid-template-columns:repeat(4,1fr);gap:1.125rem;margin-top:3rem}
.ci{background:#fff;border:1.5px solid #f1f1f1;border-radius:1.125rem;padding:1.625rem 1.125rem;
    text-align:center;transition:all .2s;cursor:default}
.ci:hover{border-color:#a5b4fc;background:#f8f7ff;transform:translateY(-4px);
  box-shadow:0 10px 28px rgba(79,70,229,.1)}
.ci-em{font-size:2.25rem;margin-bottom:.75rem}
.ci-nm{font-size:.9rem;font-weight:700;color:#111827;margin-bottom:.25rem}
.ci-ds{font-size:.78rem;color:#9ca3af;line-height:1.5}

/* ─── CTA ─────────────────────────────────────────────────────────────────── */
.cta{background:linear-gradient(135deg,#f8f7ff 0%,#eef2ff 100%);
     border-top:1px solid #e0e7ff;border-bottom:1px solid #e0e7ff;
     padding:5.5rem 2rem;text-align:center}
.cta h2{font-size:2.5rem;font-weight:900;color:#111827;margin-bottom:.875rem;letter-spacing:-.03em}
.cta h2 em{font-style:normal;color:#4f46e5}
.cta p{font-size:1.0625rem;color:#6b7280;margin-bottom:2.25rem;max-width:460px;margin-left:auto;margin-right:auto;line-height:1.7}
.cta-btns{display:flex;justify-content:center;gap:1rem;flex-wrap:wrap}

/* ─── FOOTER ──────────────────────────────────────────────────────────────── */
footer{background:#111827;padding:2.5rem 2rem}
.fi{max-width:1200px;margin:0 auto;display:flex;align-items:center;
    justify-content:space-between;flex-wrap:wrap;gap:1.25rem}
.fl{display:flex;align-items:center;gap:.5rem;text-decoration:none}
.fli{width:30px;height:30px;background:#4f46e5;border-radius:7px;
  display:flex;align-items:center;justify-content:center;font-size:.9rem}
.flt{font-size:.9375rem;font-weight:700;color:#fff}
.flinks{display:flex;gap:1.5rem}
.flink{font-size:.8rem;color:#6b7280;text-decoration:none;transition:color .15s}
.flink:hover{color:#fff}
.fc{font-size:.78rem;color:#4b5563}

/* ─── RESPONSIVE ──────────────────────────────────────────────────────────── */
@media(max-width:960px){
  .hi{grid-template-columns:1fr;gap:3rem;text-align:center}
  .hv{max-width:480px;margin:0 auto}
  h1{font-size:2.625rem}
  .hdesc,.hctas{margin-left:auto;margin-right:auto}
  .hctas{justify-content:center}
  .si{grid-template-columns:repeat(2,1fr)}
  .steps,.cg{grid-template-columns:repeat(2,1fr)}
}
@media(max-width:600px){
  h1{font-size:2.125rem}
  .steps,.cg{grid-template-columns:1fr}
  .si{grid-template-columns:repeat(2,1fr)}
}
</style>
</head>
<body>

<!-- NAV -->
<nav id="navbar">
  <div class="ni">
    <a href="/" class="logo">
      <div class="li">🛣️</div>
      <span class="lt">RoadWatch</span>
    </a>
    <div class="nl">
      <a href="javascript:void(0)" onclick="scrollTo('how')" class="nlk">How it works</a>
      <a href="javascript:void(0)" onclick="scrollTo('categories')" class="nlk">Categories</a>
      {{-- Populated by JS from /api/v1/me --}}
      <div id="nav-user-area" style="display:flex;align-items:center;gap:.5rem;">
        {{-- skeleton --}}
        <div style="width:90px;height:34px;background:#f3f4f6;border-radius:.625rem;animation:catpulse 1.4s infinite;"></div>
      </div>
    </div>
  </div>
</nav>

<!-- HERO -->
<section class="hero">
  <div class="hi">
    <!-- Left -->
    <div>
      <div class="h-badge"><span></span>🚀 Now live in Beta &nbsp;·&nbsp; 500+ citizens joined</div>
      <h1>Report Roads.<br><em>Track Real Fixes.</em></h1>
      <p class="hdesc">Spotted a pothole? Flooded street? RoadWatch connects citizens directly with road authorities — with live status tracking every step of the way.</p>
      <div class="hctas" id="hero-ctas">
        {{-- Populated by JS from /api/v1/me --}}
        <div style="width:180px;height:50px;background:rgba(0,0,0,.06);border-radius:.875rem;animation:catpulse 1.4s infinite;"></div>
        <div style="width:160px;height:50px;background:rgba(0,0,0,.04);border-radius:.875rem;animation:catpulse 1.4s infinite .1s;"></div>
      </div>
    </div>
    <!-- Right: demo cards -->
    <div class="hv">
      <div class="hc">
        <div class="hc-top">
          <div class="hc-cat">🕳️ Pothole</div>
          <span class="hc-badge" style="background:#f0fdf4;color:#15803d;">✅ Resolved</span>
        </div>
        <div class="hc-loc">📍 Near City Mall, MG Road, Bangalore</div>
        <div style="font-size:.8125rem;font-weight:600;color:#374151;margin-bottom:.75rem;">Large pothole causing accidents near bus stop</div>
        <div class="hc-bar-wrap"><div class="hc-bar" style="width:100%;background:linear-gradient(90deg,#22c55e,#4ade80);"></div></div>
        <div style="display:flex;justify-content:space-between;margin-top:.5rem;">
          <span style="font-size:.72rem;color:#9ca3af;">Submitted 3 days ago</span>
          <span style="font-size:.72rem;font-weight:700;color:#15803d;">Fixed ✓</span>
        </div>
      </div>

      <div class="hc">
        <div class="hc-top">
          <div class="hc-cat">🌊 Waterlogging</div>
          <span class="hc-badge" style="background:#eff6ff;color:#1d4ed8;">🔧 In Progress</span>
        </div>
        <div class="hc-loc">📍 Signal No.4, Koramangala</div>
        <div style="font-size:.8125rem;font-weight:600;color:#374151;margin-bottom:.75rem;">Road flooded after rain, blocking traffic both ways</div>
        <div class="hc-bar-wrap"><div class="hc-bar" style="width:65%;background:linear-gradient(90deg,#3b82f6,#60a5fa);"></div></div>
        <div style="display:flex;justify-content:space-between;margin-top:.5rem;">
          <span style="font-size:.72rem;color:#9ca3af;">Submitted 1 day ago</span>
          <span style="font-size:.72rem;font-weight:700;color:#1d4ed8;">65% done</span>
        </div>
      </div>

      <div class="hc">
        <div class="hc-top">
          <div class="hc-cat">💡 Street Light</div>
          <span class="hc-badge" style="background:#fffbeb;color:#b45309;">⏳ Pending</span>
        </div>
        <div class="hc-loc">📍 Whitefield Main Road</div>
        <div style="font-size:.8125rem;font-weight:600;color:#374151;margin-bottom:.75rem;">3 street lights not working for over a week</div>
        <div class="hc-bar-wrap"><div class="hc-bar" style="width:12%;background:linear-gradient(90deg,#f59e0b,#fbbf24);"></div></div>
        <div style="display:flex;justify-content:space-between;margin-top:.5rem;">
          <span style="font-size:.72rem;color:#9ca3af;">Just submitted</span>
          <span style="font-size:.72rem;font-weight:700;color:#b45309;">Under review</span>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- STATS -->
<section class="stats">
  <div class="si">
    <div><div class="sn">2,400+</div><div class="sl">Issues Reported</div></div>
    <div><div class="sn">78%</div><div class="sl">Resolution Rate</div></div>
    <div><div class="sn">3.2 days</div><div class="sl">Avg. Response Time</div></div>
    <div><div class="sn">12 Zones</div><div class="sl">Cities Covered</div></div>
  </div>
</section>

<!-- HOW IT WORKS -->
<section class="sec" id="how">
  <div class="sec-in">
    <div style="max-width:580px;margin-bottom:0;">
      <div class="stag">How it works</div>
      <h2 class="stit" style="margin-bottom:.875rem;">Fix roads in 3 simple steps</h2>
      <p class="ssub">No helplines. No paperwork. Report, track and get results — right from your phone.</p>
    </div>
    <div class="steps">
      <div class="step">
        <div class="step-num">📍</div>
        <h3>Pin the Location</h3>
        <p>Use GPS or tap the map to mark the exact spot. Add photos and describe the issue — takes under 2 minutes.</p>
      </div>
      <div class="step">
        <div class="step-num">🔍</div>
        <h3>We Review & Assign</h3>
        <p>Our admin team reviews your report and assigns a qualified field engineer within 24 hours.</p>
      </div>
      <div class="step">
        <div class="step-num">✅</div>
        <h3>Track Until Fixed</h3>
        <p>Get real-time status updates. Once resolved, rate the fix quality and close the loop.</p>
      </div>
    </div>
  </div>
</section>

<!-- CATEGORIES -->
<section class="sec" id="categories" style="background:#fafafa;border-top:1px solid #f1f1f1;border-bottom:1px solid #f1f1f1;">
  <div class="sec-in">
    <div style="text-align:center;max-width:540px;margin:0 auto 3rem;">
      <div class="stag">Issue Categories</div>
      <h2 class="stit" style="margin-bottom:.875rem;">What can you report?</h2>
      <p class="ssub" style="margin:0 auto;">From potholes to broken streetlights — if it's a road problem, we handle it.</p>
    </div>
    <!-- Skeleton loading state -->
    <div id="cat-loading" class="cg">
      @for($i = 0; $i < 8; $i++)
      <div style="background:#f3f4f6;border-radius:1.125rem;height:130px;animation:catpulse 1.4s ease-in-out infinite;animation-delay:{{ $i * 0.08 }}s;"></div>
      @endfor
    </div>
    <!-- Populated by JS from GET /api/v1/categories -->
    <div id="cat-grid" class="cg" style="display:none;"></div>
    <p id="cat-error" style="display:none;text-align:center;color:#9ca3af;font-size:.875rem;padding:2rem 0;">Could not load categories.      <a href="javascript:void(0)" onclick="loadCategories()" style="color:#4f46e5;">Retry</a></p>

  </div>
</section>

<!-- CTA -->
<section class="cta">
  <h2>Your city needs your <em>voice.</em></h2>
  <p>Join thousands of citizens making roads safer — one report at a time. It's free, it's fast, and it works.</p>
  <div class="cta-btns" id="cta-btns">
    {{-- Populated by JS --}}
    <div style="width:200px;height:50px;background:rgba(79,70,229,.15);border-radius:.875rem;animation:catpulse 1.4s infinite;"></div>
  </div>
</section>

<!-- FOOTER -->
<footer>
  <div class="fi">
    <a href="/" class="fl">
      <div class="fli">🛣️</div>
      <span class="flt">RoadWatch</span>
    </a>
    <div class="flinks">
      <a href="#how" class="flink">How it works</a>
      <a href="#categories" class="flink">Categories</a>
      <a href="{{ route('login') }}" class="flink">Sign in</a>
      <a href="{{ route('register') }}" class="flink">Register</a>
    </div>
    <div class="fc">© {{ date('Y') }} RoadWatch. Built for safer roads.</div>
  </div>
</footer>

<script>
// Smooth scroll without changing URL
window.scrollTo = function(id) {
  const el = document.getElementById(id);
  if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
};

// Navbar shadow on scroll
window.addEventListener('scroll', () => {
  document.getElementById('navbar').classList.toggle('scrolled', window.scrollY > 10);
});

// Animate progress bars on load
window.addEventListener('load', () => {
  document.querySelectorAll('.hc-bar').forEach(bar => {
    const w = bar.style.width;
    bar.style.width = '0%';
    setTimeout(() => bar.style.width = w, 400);
  });
});

// ── Load user state from API ────────────────────────────────────────────────
async function loadUserState() {
  const navArea   = document.getElementById('nav-user-area');
  const heroCtas  = document.getElementById('hero-ctas');
  const ctaBtns   = document.getElementById('cta-btns');

  try {
    const res  = await fetch('/api/v1/me');
    if (!res.ok) throw new Error('not auth');
    const u = (await res.json()).data;

    // ── Navbar: avatar + first name + dropdown ──
    const avatar = u.profile_photo_url
      ? `<img src="${u.profile_photo_url}" alt="avatar"
             style="width:32px;height:32px;border-radius:50%;object-fit:cover;flex-shrink:0;
                    border:2px solid #e5e7eb;">`
      : `<div style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,#4f46e5,#7c3aed);
              display:flex;align-items:center;justify-content:center;
              color:#fff;font-size:.875rem;font-weight:700;">${(u.name??'U')[0].toUpperCase()}</div>`;

    const firstName = (u.name ?? '').split(' ')[0];
    const role      = u.role ?? 'citizen';

    // ── Role-specific nav links ──
    const roleLinks = role === 'admin' ? `
        <a href="/admin/dashboard"
           style="display:flex;align-items:center;gap:.5rem;padding:.625rem .75rem;border-radius:.625rem;
                  text-decoration:none;font-size:.875rem;color:#374151;font-weight:500;transition:background .15s;"
           onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='transparent'">
          📊 Admin Dashboard
        </a>
        <a href="/admin/complaints"
           style="display:flex;align-items:center;gap:.5rem;padding:.625rem .75rem;border-radius:.625rem;
                  text-decoration:none;font-size:.875rem;color:#374151;font-weight:500;transition:background .15s;"
           onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='transparent'">
          📋 Manage Complaints
        </a>
        <a href="/admin/users"
           style="display:flex;align-items:center;gap:.5rem;padding:.625rem .75rem;border-radius:.625rem;
                  text-decoration:none;font-size:.875rem;color:#374151;font-weight:500;transition:background .15s;"
           onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='transparent'">
          👥 Manage Users
        </a>` : role === 'engineer' ? `
        <a href="/engineer/complaints"
           style="display:flex;align-items:center;gap:.5rem;padding:.625rem .75rem;border-radius:.625rem;
                  text-decoration:none;font-size:.875rem;color:#374151;font-weight:500;transition:background .15s;"
           onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='transparent'">
          🔧 My Assignments
        </a>` : `
        <a href="/citizen/profile"
           style="display:flex;align-items:center;gap:.5rem;padding:.625rem .75rem;border-radius:.625rem;
                  text-decoration:none;font-size:.875rem;color:#374151;font-weight:500;transition:background .15s;"
           onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='transparent'">
          👤 My Profile
        </a>
        <a href="/citizen/complaints"
           style="display:flex;align-items:center;gap:.5rem;padding:.625rem .75rem;border-radius:.625rem;
                  text-decoration:none;font-size:.875rem;color:#374151;font-weight:500;transition:background .15s;"
           onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='transparent'">
          📋 My Complaints
        </a>`;

    // ── Role badge colour ──
    const roleBadgeBg    = role === 'admin' ? '#fef3c7' : role === 'engineer' ? '#dbeafe' : '#eef2ff';
    const roleBadgeColor = role === 'admin' ? '#92400e' : role === 'engineer' ? '#1e40af' : '#4f46e5';

    navArea.innerHTML = `
      <div style="position:relative;">
        <button id="wlc-btn" onclick="toggleWelcomeMenu()"
                style="display:flex;align-items:center;gap:.5rem;background:#fff;
                       border:1.5px solid #e5e7eb;border-radius:2rem;
                       padding:.25rem .75rem .25rem .25rem;cursor:pointer;
                       font-family:inherit;transition:border-color .15s;"
                onmouseover="this.style.borderColor='#a5b4fc'"
                onmouseout="this.style.borderColor='#e5e7eb'">
          ${avatar}
          <span style="font-size:.875rem;font-weight:600;color:#111827;
                       max-width:100px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${firstName}</span>
          <svg id="wlc-chev" style="width:14px;height:14px;color:#9ca3af;transition:transform .2s;"
               fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
          </svg>
        </button>

        <div id="wlc-menu" style="display:none;position:absolute;top:calc(100% + 10px);right:0;width:230px;
             background:#fff;border:1.5px solid #f1f1f1;border-radius:1rem;
             box-shadow:0 16px 48px rgba(0,0,0,.12);z-index:500;overflow:hidden;">
          <div style="padding:.875rem 1rem;border-bottom:1px solid #f3f4f6;">
            <p style="font-size:.875rem;font-weight:700;color:#111827;margin:0;">${u.name}</p>
            <p style="font-size:.75rem;color:#9ca3af;margin:.1rem 0 .375rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${u.email}</p>
            <span style="font-size:.7rem;font-weight:700;background:${roleBadgeBg};color:${roleBadgeColor};padding:.15rem .5rem;border-radius:9999px;">${role.toUpperCase()}</span>
          </div>
          <div style="padding:.5rem;">
            ${roleLinks}
          </div>
          <div style="padding:.5rem;border-top:1px solid #f3f4f6;">
            <form method="POST" action="/logout">
              <input type="hidden" name="_token" value="{{ csrf_token() }}">
              <button type="submit"
                      style="display:flex;align-items:center;gap:.5rem;width:100%;padding:.625rem .75rem;
                             border-radius:.625rem;font-size:.875rem;font-weight:500;color:#ef4444;
                             background:none;border:none;cursor:pointer;font-family:inherit;transition:background .15s;"
                      onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='transparent'">
                🚪 Sign Out
              </button>
            </form>
          </div>
        </div>
      </div>`;

    // ── Hero CTAs (role-aware) ──
    heroCtas.innerHTML = role === 'admin'
      ? `<a href="/admin/dashboard" class="bhp">📊 Admin Dashboard</a>
         <a href="/admin/complaints" class="bhs">📋 Manage Complaints</a>`
      : role === 'engineer'
      ? `<a href="/engineer/complaints" class="bhp">🔧 My Assignments</a>
         <a href="javascript:void(0)" onclick="scrollTo('how')" class="bhs">How it Works</a>`
      : `<a href="/citizen/complaints/create" class="bhp">🚨 Report an Issue</a>
         <a href="/citizen/complaints" class="bhs">📋 My Complaints</a>`;

    // ── CTA section (role-aware) ──
    ctaBtns.innerHTML = role === 'admin'
      ? `<a href="/admin/dashboard" class="bhp">📊 Go to Admin Dashboard</a>
         <a href="/admin/users" class="bhs">👥 Manage Users</a>`
      : role === 'engineer'
      ? `<a href="/engineer/complaints" class="bhp">🔧 View My Assignments</a>`
      : `<a href="/citizen/complaints/create" class="bhp">🚨 Report an Issue Now</a>
         <a href="/citizen/complaints" class="bhs">📋 View My Reports</a>`;


  } catch (_) {
    // ── Not logged in ──
    navArea.innerHTML = `
      <a href="{{ route('login') }}" class="bo">Sign in</a>
      <a href="{{ route('register') }}" class="bp">Get Started</a>`;

    heroCtas.innerHTML = `
      <a href="{{ route('register') }}" class="bhp">🚀 Create Free Account</a>
      <a href="javascript:void(0)" onclick="scrollTo('how')" class="bhs">See How it Works</a>`;

    ctaBtns.innerHTML = `
      <a href="{{ route('register') }}" class="bhp">🚀 Create Free Account</a>
      <a href="{{ route('login') }}" class="bhs">Sign In</a>`;
  }
}

function toggleWelcomeMenu() {
  const menu  = document.getElementById('wlc-menu');
  const chev  = document.getElementById('wlc-chev');
  const open  = menu.style.display === 'block';
  menu.style.display = open ? 'none' : 'block';
  chev.style.transform = open ? '' : 'rotate(180deg)';
}
document.addEventListener('click', e => {
  const btn = document.getElementById('wlc-btn');
  if (btn && !btn.contains(e.target)) {
    const m = document.getElementById('wlc-menu');
    if (m) { m.style.display = 'none'; document.getElementById('wlc-chev').style.transform = ''; }
  }
});

document.addEventListener('DOMContentLoaded', loadUserState);

// Load categories from public API
async function loadCategories() {
  document.getElementById('cat-error').style.display = 'none';
  try {
    const res  = await fetch('/api/v1/categories');
    const json = await res.json();
    const cats = json.data ?? [];

    if (!cats.length) throw new Error('empty');

    document.getElementById('cat-grid').innerHTML = cats.map(c => `
      <div class="ci">
        <div class="ci-em">${c.icon ?? '📌'}</div>
        <div class="ci-nm">${escHtml(c.name)}</div>
        <div class="ci-ds">${escHtml(c.description ?? '')}</div>
      </div>`).join('');

    document.getElementById('cat-loading').style.display = 'none';
    document.getElementById('cat-grid').style.display    = 'grid';
  } catch (e) {
    document.getElementById('cat-loading').style.display = 'none';
    document.getElementById('cat-error').style.display   = 'block';
  }
}

function escHtml(str) {
  return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

document.addEventListener('DOMContentLoaded', loadCategories);
</script>
</body>
</html>
