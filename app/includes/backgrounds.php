<?php
/**
 * backgrounds.php — Animated background configurations.
 *
 * Each background has:
 *   'name'    — display name
 *   'icon'    — a small visual hint for the picker
 *   'html'    — the markup placed inside .background-container
 *   'css'     — the <style> block with shapes + animations
 */

function getBackgrounds(): array
{
    return [

        // ─────────────────────────────────────────────
        // 1. Lava Blobs (the original)
        // ─────────────────────────────────────────────
        'blobs' => [
            'name' => 'Lava Blobs',
            'icon' => '🫧',
            'html' => <<<'HTML'
<div class="blob blob-1"></div>
<div class="blob blob-2"></div>
<div class="blob blob-3"></div>
HTML,
            'css' => <<<'CSS'
<style>
.blob { position: absolute; will-change: border-radius, transform; }
.blob-1 {
    background-color: var(--fground-colour);
    width: 90vmax; height: 90vmax;
    bottom: -25vmax; left: -25vmax;
    animation: blobRotate 50s linear infinite, blobMorph1 20s ease-in-out infinite alternate;
}
.blob-2 {
    background-color: var(--cground-colour);
    width: 80vmax; height: 80vmax;
    top: -20vmax; right: -20vmax;
    animation: blobRotateR 60s linear infinite, blobMorph2 25s ease-in-out infinite alternate;
}
.blob-3 {
    background-color: var(--highlights-colour);
    width: 35vmax; height: 35vmax;
    top: 20%; left: 20%;
    animation: blobFloat 30s ease-in-out infinite alternate, blobMorph3 10s ease-in-out infinite alternate;
}
@keyframes blobRotate  { to { transform: rotate(360deg); } }
@keyframes blobRotateR { to { transform: rotate(-360deg); } }
@keyframes blobMorph1  { 0%{border-radius:40% 60% 70% 30%/40% 50% 60% 50%} 100%{border-radius:60% 40% 30% 70%/60% 30% 70% 40%} }
@keyframes blobMorph2  { 0%{border-radius:60% 40% 30% 70%/60% 30% 70% 40%} 100%{border-radius:30% 70% 70% 30%/30% 30% 70% 70%} }
@keyframes blobMorph3  { 0%{border-radius:30% 70% 70% 30%/30% 30% 70% 70%} 50%{border-radius:80% 20% 40% 60%/50% 60% 30% 60%} 100%{border-radius:40% 60% 20% 80%/60% 30% 70% 40%} }
@keyframes blobFloat   { 0%{transform:translate(0,0) rotate(0)} 100%{transform:translate(15vw,5vh) rotate(135deg)} }
</style>
CSS,
        ],

        // ─────────────────────────────────────────────
        // 2. Orbit Rings
        // ─────────────────────────────────────────────
        'orbits' => [
            'name' => 'Orbit Rings',
            'icon' => '🪐',
            'html' => <<<'HTML'
<div class="orbit orbit-1"></div>
<div class="orbit orbit-2"></div>
<div class="orbit orbit-3"></div>
<div class="orbit-dot orbit-dot-1"></div>
<div class="orbit-dot orbit-dot-2"></div>
HTML,
            'css' => <<<'CSS'
<style>
.orbit {
    position: absolute; border-radius: 50%;
    border: 1.5px solid var(--cground-colour);
    top: 50%; left: 50%;
    transform: translate(-50%, -50%);
    opacity: 0.35;
}
.orbit-1 { width: 50vmax; height: 50vmax; animation: orbitSpin 80s linear infinite; }
.orbit-2 { width: 72vmax; height: 72vmax; animation: orbitSpin 120s linear infinite reverse; border-color: var(--fground-colour); border-width: 2px; }
.orbit-3 { width: 95vmax; height: 95vmax; animation: orbitSpin 160s linear infinite; border-color: var(--highlights-colour); opacity: 0.18; }
.orbit-dot {
    position: absolute; width: 12px; height: 12px;
    border-radius: 50%; background: var(--highlights-colour);
    top: 50%; left: 50%;
    box-shadow: 0 0 20px var(--highlights-colour), 0 0 60px var(--highlights-colour);
}
.orbit-dot-1 { animation: orbitDot1 80s linear infinite; }
.orbit-dot-2 { animation: orbitDot2 120s linear infinite reverse; }
@keyframes orbitSpin { to { transform: translate(-50%, -50%) rotate(360deg); } }
@keyframes orbitDot1 {
    0%   { transform: translate(-50%, -50%) rotate(0deg)   translateX(25vmax); }
    100% { transform: translate(-50%, -50%) rotate(360deg) translateX(25vmax); }
}
@keyframes orbitDot2 {
    0%   { transform: translate(-50%, -50%) rotate(0deg)   translateX(36vmax); }
    100% { transform: translate(-50%, -50%) rotate(-360deg) translateX(36vmax); }
}
</style>
CSS,
        ],

        // ─────────────────────────────────────────────
        // 3. Floating Diamonds
        // ─────────────────────────────────────────────
        'diamonds' => [
            'name' => 'Floating Diamonds',
            'icon' => '◆',
            'html' => <<<'HTML'
<div class="diamond diamond-1"></div>
<div class="diamond diamond-2"></div>
<div class="diamond diamond-3"></div>
<div class="diamond diamond-4"></div>
<div class="diamond diamond-5"></div>
HTML,
            'css' => <<<'CSS'
<style>
.diamond {
    position: absolute;
    width: 18vmax; height: 18vmax;
    transform: rotate(45deg);
    border-radius: 4px;
    opacity: 0.2;
}
.diamond-1 { background:var(--fground-colour); top:10%; left:5%;  width:28vmax; height:28vmax; animation: diamFloat1 25s ease-in-out infinite alternate; opacity:0.35; }
.diamond-2 { background:var(--cground-colour); top:55%; right:8%; width:22vmax; height:22vmax; animation: diamFloat2 30s ease-in-out infinite alternate; opacity:0.25; }
.diamond-3 { background:var(--highlights-colour); bottom:15%; left:30%; width:14vmax; height:14vmax; animation: diamFloat3 20s ease-in-out infinite alternate; opacity:0.15; }
.diamond-4 { border:2px solid var(--cground-colour); top:25%; right:25%; width:20vmax; height:20vmax; animation: diamFloat4 35s ease-in-out infinite alternate; opacity:0.12; }
.diamond-5 { border:1.5px solid var(--highlights-colour); bottom:30%; left:8%; width:10vmax; height:10vmax; animation: diamFloat5 22s ease-in-out infinite alternate; opacity:0.18; }
@keyframes diamFloat1 { 0%{transform:rotate(45deg) translate(0,0)}    100%{transform:rotate(50deg) translate(5vw,3vh)} }
@keyframes diamFloat2 { 0%{transform:rotate(45deg) translate(0,0)}    100%{transform:rotate(40deg) translate(-4vw,6vh)} }
@keyframes diamFloat3 { 0%{transform:rotate(45deg) scale(1)}          100%{transform:rotate(48deg) scale(1.2)} }
@keyframes diamFloat4 { 0%{transform:rotate(45deg) translate(0,0)}    100%{transform:rotate(52deg) translate(3vw,-4vh)} }
@keyframes diamFloat5 { 0%{transform:rotate(45deg) translate(0,0) scale(1)} 100%{transform:rotate(38deg) translate(-2vw,5vh) scale(1.3)} }
</style>
CSS,
        ],

        // ─────────────────────────────────────────────
        // 4. Wave Layers
        // ─────────────────────────────────────────────
        'waves' => [
            'name' => 'Wave Layers',
            'icon' => '🌊',
            'html' => <<<'HTML'
<svg class="wave-svg" viewBox="0 0 1440 900" preserveAspectRatio="none">
    <path class="wave wave-1" d="M0,600 C360,500 720,700 1080,550 C1260,490 1380,580 1440,600 L1440,900 L0,900Z"/>
    <path class="wave wave-2" d="M0,650 C320,580 640,720 960,620 C1200,560 1360,650 1440,670 L1440,900 L0,900Z"/>
    <path class="wave wave-3" d="M0,720 C280,670 560,780 840,700 C1120,630 1320,740 1440,750 L1440,900 L0,900Z"/>
</svg>
HTML,
            'css' => <<<'CSS'
<style>
.wave-svg {
    position: absolute; bottom: 0; left: 0;
    width: 100%; height: 100%;
}
.wave { fill-opacity: 0.25; }
.wave-1 { fill: var(--fground-colour); animation: waveShift1 12s ease-in-out infinite alternate; fill-opacity: 0.4; }
.wave-2 { fill: var(--cground-colour); animation: waveShift2 16s ease-in-out infinite alternate; fill-opacity: 0.3; }
.wave-3 { fill: var(--highlights-colour); animation: waveShift3 10s ease-in-out infinite alternate; fill-opacity: 0.15; }
@keyframes waveShift1 {
    0%  { d: path("M0,600 C360,500 720,700 1080,550 C1260,490 1380,580 1440,600 L1440,900 L0,900Z"); }
    100%{ d: path("M0,580 C300,680 660,520 1020,620 C1220,680 1360,560 1440,580 L1440,900 L0,900Z"); }
}
@keyframes waveShift2 {
    0%  { d: path("M0,650 C320,580 640,720 960,620 C1200,560 1360,650 1440,670 L1440,900 L0,900Z"); }
    100%{ d: path("M0,680 C380,730 700,600 1040,690 C1240,720 1380,630 1440,650 L1440,900 L0,900Z"); }
}
@keyframes waveShift3 {
    0%  { d: path("M0,720 C280,670 560,780 840,700 C1120,630 1320,740 1440,750 L1440,900 L0,900Z"); }
    100%{ d: path("M0,740 C340,790 620,690 900,760 C1160,800 1340,710 1440,730 L1440,900 L0,900Z"); }
}
</style>
CSS,
        ],

        // ─────────────────────────────────────────────
        // 5. Grid Pulse
        // ─────────────────────────────────────────────
        'grid' => [
            'name' => 'Grid Pulse',
            'icon' => '▦',
            'html' => <<<'HTML'
<div class="grid-bg"></div>
<div class="grid-glow grid-glow-1"></div>
<div class="grid-glow grid-glow-2"></div>
HTML,
            'css' => <<<'CSS'
<style>
.grid-bg {
    position: absolute; inset: 0;
    background-image:
        linear-gradient(var(--cground-colour) 1px, transparent 1px),
        linear-gradient(90deg, var(--cground-colour) 1px, transparent 1px);
    background-size: 60px 60px;
    opacity: 0.12;
    animation: gridDrift 40s linear infinite;
}
.grid-glow {
    position: absolute; border-radius: 50%;
    filter: blur(80px);
}
.grid-glow-1 {
    width: 40vmax; height: 40vmax;
    background: var(--highlights-colour); opacity: 0.12;
    top: 20%; left: 10%;
    animation: gridPulse1 8s ease-in-out infinite alternate;
}
.grid-glow-2 {
    width: 35vmax; height: 35vmax;
    background: var(--cground-colour); opacity: 0.18;
    bottom: 10%; right: 10%;
    animation: gridPulse2 12s ease-in-out infinite alternate;
}
@keyframes gridDrift   { to { background-position: 60px 60px; } }
@keyframes gridPulse1  { 0%{opacity:0.08;transform:scale(1)} 100%{opacity:0.18;transform:scale(1.15)} }
@keyframes gridPulse2  { 0%{opacity:0.12;transform:scale(1)} 100%{opacity:0.22;transform:scale(1.1)} }
</style>
CSS,
        ],

        // ─────────────────────────────────────────────
        // 6. Aurora Bands
        // ─────────────────────────────────────────────
        'aurora' => [
            'name' => 'Aurora Bands',
            'icon' => '🌌',
            'html' => <<<'HTML'
<div class="aurora aurora-1"></div>
<div class="aurora aurora-2"></div>
<div class="aurora aurora-3"></div>
HTML,
            'css' => <<<'CSS'
<style>
.aurora {
    position: absolute; width: 200%; height: 50vh;
    left: -50%; filter: blur(70px);
    border-radius: 50%;
}
.aurora-1 {
    background: linear-gradient(90deg, transparent 0%, var(--highlights-colour) 30%, var(--cground-colour) 60%, transparent 100%);
    top: -10%; opacity: 0.2;
    animation: auroraDrift1 20s ease-in-out infinite alternate;
}
.aurora-2 {
    background: linear-gradient(90deg, transparent 0%, var(--fground-colour) 40%, var(--highlights-colour) 70%, transparent 100%);
    top: 15%; opacity: 0.15;
    animation: auroraDrift2 28s ease-in-out infinite alternate;
}
.aurora-3 {
    background: linear-gradient(90deg, transparent 0%, var(--cground-colour) 35%, var(--fground-colour) 65%, transparent 100%);
    top: 40%; opacity: 0.12;
    animation: auroraDrift3 24s ease-in-out infinite alternate;
}
@keyframes auroraDrift1 { 0%{transform:translateX(-10%) skewY(-2deg)} 100%{transform:translateX(10%) skewY(2deg)} }
@keyframes auroraDrift2 { 0%{transform:translateX(8%) skewY(1deg)}   100%{transform:translateX(-12%) skewY(-3deg)} }
@keyframes auroraDrift3 { 0%{transform:translateX(-5%) skewY(2deg)}  100%{transform:translateX(8%) skewY(-1deg)} }
</style>
CSS,
        ],

        // ─────────────────────────────────────────────
        // 7. Floating Circles
        // ─────────────────────────────────────────────
        'circles' => [
            'name' => 'Floating Circles',
            'icon' => '◎',
            'html' => <<<'HTML'
<div class="circ circ-1"></div>
<div class="circ circ-2"></div>
<div class="circ circ-3"></div>
<div class="circ circ-4"></div>
<div class="circ circ-5"></div>
<div class="circ circ-6"></div>
HTML,
            'css' => <<<'CSS'
<style>
.circ {
    position: absolute; border-radius: 50%;
}
.circ-1 { width:30vmax; height:30vmax; border:2px solid var(--cground-colour); top:5%; left:8%;   opacity:0.15; animation:circDrift1 30s ease-in-out infinite alternate; }
.circ-2 { width:20vmax; height:20vmax; background:var(--fground-colour);        top:50%; right:5%; opacity:0.25; animation:circDrift2 24s ease-in-out infinite alternate; }
.circ-3 { width:12vmax; height:12vmax; background:var(--highlights-colour);      bottom:20%; left:25%; opacity:0.1; animation:circDrift3 18s ease-in-out infinite alternate; }
.circ-4 { width:45vmax; height:45vmax; border:1px solid var(--fground-colour);   top:30%; left:40%;   opacity:0.08; animation:circDrift4 40s ease-in-out infinite alternate; }
.circ-5 { width:8vmax;  height:8vmax;  background:var(--cground-colour);         top:15%; right:30%;  opacity:0.2;  animation:circDrift5 15s ease-in-out infinite alternate; }
.circ-6 { width:16vmax; height:16vmax; border:1.5px solid var(--highlights-colour); bottom:10%; right:20%; opacity:0.12; animation:circDrift6 22s ease-in-out infinite alternate; }
@keyframes circDrift1 { 0%{transform:translate(0,0) scale(1)}    100%{transform:translate(3vw,4vh) scale(1.05)} }
@keyframes circDrift2 { 0%{transform:translate(0,0) scale(1)}    100%{transform:translate(-5vw,-3vh) scale(0.9)} }
@keyframes circDrift3 { 0%{transform:translate(0,0) scale(1)}    100%{transform:translate(4vw,-5vh) scale(1.2)} }
@keyframes circDrift4 { 0%{transform:translate(0,0) rotate(0)}   100%{transform:translate(-3vw,2vh) rotate(30deg)} }
@keyframes circDrift5 { 0%{transform:translate(0,0) scale(1)}    100%{transform:translate(2vw,6vh) scale(1.3)} }
@keyframes circDrift6 { 0%{transform:translate(0,0) scale(1)}    100%{transform:translate(-4vw,3vh) scale(1.1)} }
</style>
CSS,
        ],

        // ─────────────────────────────────────────────
        // 8. Starfield
        // ─────────────────────────────────────────────
        'starfield' => [
            'name' => 'Starfield',
            'icon' => '✦',
            'html' => <<<'HTML'
<div class="star star-1"></div><div class="star star-2"></div><div class="star star-3"></div>
<div class="star star-4"></div><div class="star star-5"></div><div class="star star-6"></div>
<div class="star star-7"></div><div class="star star-8"></div><div class="star star-9"></div>
<div class="star star-10"></div><div class="star star-11"></div><div class="star star-12"></div>
<div class="star star-13"></div><div class="star star-14"></div><div class="star star-15"></div>
<div class="star star-16"></div><div class="star star-17"></div><div class="star star-18"></div>
<div class="star-glow star-glow-1"></div>
<div class="star-glow star-glow-2"></div>
HTML,
            'css' => <<<'CSS'
<style>
.star {
    position:absolute; border-radius:50%; background:var(--primtext-colour);
    animation: starTwinkle var(--dur) ease-in-out infinite alternate;
}
.star-1  { width:2px;height:2px;top:8%;left:12%;  --dur:3s;animation-delay:0s; }
.star-2  { width:3px;height:3px;top:15%;left:45%; --dur:4s;animation-delay:0.5s; }
.star-3  { width:2px;height:2px;top:22%;left:78%; --dur:2.5s;animation-delay:1s; }
.star-4  { width:1px;height:1px;top:35%;left:25%; --dur:5s;animation-delay:0.2s; }
.star-5  { width:3px;height:3px;top:42%;left:60%; --dur:3.5s;animation-delay:1.5s; }
.star-6  { width:2px;height:2px;top:55%;left:90%; --dur:4.5s;animation-delay:0.8s; }
.star-7  { width:1px;height:1px;top:65%;left:35%; --dur:3s;animation-delay:2s; }
.star-8  { width:2px;height:2px;top:72%;left:70%; --dur:5s;animation-delay:0.3s; }
.star-9  { width:3px;height:3px;top:80%;left:15%; --dur:4s;animation-delay:1.2s; }
.star-10 { width:1px;height:1px;top:88%;left:50%; --dur:3s;animation-delay:0.7s; }
.star-11 { width:2px;height:2px;top:12%;left:30%; --dur:4.5s;animation-delay:1.8s; }
.star-12 { width:1px;height:1px;top:48%;left:5%;  --dur:3.5s;animation-delay:0.4s; }
.star-13 { width:2px;height:2px;top:30%;left:55%; --dur:5s;animation-delay:2.2s; }
.star-14 { width:3px;height:3px;top:60%;left:82%; --dur:3s;animation-delay:0.9s; }
.star-15 { width:1px;height:1px;top:75%;left:42%; --dur:4s;animation-delay:1.6s; }
.star-16 { width:2px;height:2px;top:18%;left:65%; --dur:3.5s;animation-delay:0.1s; }
.star-17 { width:1px;height:1px;top:92%;left:28%; --dur:5s;animation-delay:2.5s; }
.star-18 { width:2px;height:2px;top:50%;left:18%; --dur:4s;animation-delay:1.1s; }
.star-glow {
    position:absolute; border-radius:50%; filter:blur(60px);
}
.star-glow-1 { width:25vmax;height:25vmax;top:10%;left:15%;background:var(--cground-colour);opacity:0.1;animation:starGlow1 15s ease-in-out infinite alternate; }
.star-glow-2 { width:20vmax;height:20vmax;bottom:15%;right:10%;background:var(--highlights-colour);opacity:0.06;animation:starGlow2 20s ease-in-out infinite alternate; }
@keyframes starTwinkle { 0%{opacity:0.15;transform:scale(1)} 100%{opacity:0.9;transform:scale(1.5)} }
@keyframes starGlow1 { 0%{opacity:0.06;transform:scale(1)} 100%{opacity:0.14;transform:scale(1.2)} }
@keyframes starGlow2 { 0%{opacity:0.04;transform:scale(1)} 100%{opacity:0.1;transform:scale(1.15)} }
</style>
CSS,
        ],

        // ─────────────────────────────────────────────
        // 9. Hexagons
        // ─────────────────────────────────────────────
        'hexagons' => [
            'name' => 'Hexagons',
            'icon' => '⬡',
            'html' => <<<'HTML'
<svg class="hex-svg" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid slice">
    <defs>
        <pattern id="hexP" width="28" height="49" patternUnits="userSpaceOnUse" patternTransform="scale(2)">
            <polygon points="14,2 25,9 25,23 14,30 3,23 3,9" fill="none" stroke="var(--cground-colour)" stroke-width="0.3"/>
            <polygon points="14,19 25,26 25,40 14,47 3,40 3,26" fill="none" stroke="var(--cground-colour)" stroke-width="0.3"/>
        </pattern>
    </defs>
    <rect width="100%" height="100%" fill="url(#hexP)"/>
</svg>
<div class="hex-glow hex-glow-1"></div>
<div class="hex-glow hex-glow-2"></div>
HTML,
            'css' => <<<'CSS'
<style>
.hex-svg { position:absolute;inset:0;width:100%;height:100%;opacity:0.15;animation:hexDrift 60s linear infinite; }
.hex-glow { position:absolute;border-radius:50%;filter:blur(80px); }
.hex-glow-1 { width:40vmax;height:40vmax;top:15%;left:10%;background:var(--highlights-colour);opacity:0.08;animation:hexPulse1 10s ease-in-out infinite alternate; }
.hex-glow-2 { width:35vmax;height:35vmax;bottom:10%;right:15%;background:var(--fground-colour);opacity:0.15;animation:hexPulse2 14s ease-in-out infinite alternate; }
@keyframes hexDrift { to { transform:translate(28px, 49px); } }
@keyframes hexPulse1 { 0%{opacity:0.05;transform:scale(1)} 100%{opacity:0.12;transform:scale(1.15)} }
@keyframes hexPulse2 { 0%{opacity:0.1;transform:scale(1)} 100%{opacity:0.2;transform:scale(1.1)} }
</style>
CSS,
        ],

        // ─────────────────────────────────────────────
        // 10. Meteor Shower
        // ─────────────────────────────────────────────
        'meteors' => [
            'name' => 'Meteor Shower',
            'icon' => '☄',
            'html' => <<<'HTML'
<div class="meteor meteor-1"></div>
<div class="meteor meteor-2"></div>
<div class="meteor meteor-3"></div>
<div class="meteor meteor-4"></div>
<div class="meteor meteor-5"></div>
<div class="meteor meteor-6"></div>
HTML,
            'css' => <<<'CSS'
<style>
.meteor {
    position:absolute; width:2px; border-radius:2px;
    background:linear-gradient(to bottom, var(--highlights-colour), transparent);
    opacity:0;
    transform: rotate(-35deg);
}
.meteor-1 { height:80px;top:-5%;left:20%; animation:meteorFall 4s 0s linear infinite; }
.meteor-2 { height:120px;top:-8%;left:45%; animation:meteorFall 5s 1.5s linear infinite; }
.meteor-3 { height:60px;top:-4%;left:70%; animation:meteorFall 3.5s 0.8s linear infinite; }
.meteor-4 { height:100px;top:-6%;left:35%; animation:meteorFall 6s 3s linear infinite; }
.meteor-5 { height:50px;top:-3%;left:85%; animation:meteorFall 4.5s 2s linear infinite; }
.meteor-6 { height:90px;top:-5%;left:55%; animation:meteorFall 5.5s 4s linear infinite; }
@keyframes meteorFall {
    0%   { opacity:0; transform:rotate(-35deg) translate(0,0); }
    5%   { opacity:0.8; }
    60%  { opacity:0.3; }
    100% { opacity:0; transform:rotate(-35deg) translate(-50vw, 120vh); }
}
</style>
CSS,
        ],

        // ─────────────────────────────────────────────
        // 11. Smoke
        // ─────────────────────────────────────────────
        'smoke' => [
            'name' => 'Smoke',
            'icon' => '🌫',
            'html' => <<<'HTML'
<div class="smoke smoke-1"></div>
<div class="smoke smoke-2"></div>
<div class="smoke smoke-3"></div>
<div class="smoke smoke-4"></div>
HTML,
            'css' => <<<'CSS'
<style>
.smoke {
    position:absolute; border-radius:50%; filter:blur(100px);
    will-change:transform, opacity;
}
.smoke-1 { width:60vmax;height:60vmax;top:-20%;left:-15%;background:var(--fground-colour);opacity:0.35;animation:smokeDrift1 30s ease-in-out infinite alternate; }
.smoke-2 { width:50vmax;height:50vmax;top:30%;right:-20%;background:var(--cground-colour);opacity:0.25;animation:smokeDrift2 25s ease-in-out infinite alternate; }
.smoke-3 { width:40vmax;height:40vmax;bottom:-10%;left:20%;background:var(--highlights-colour);opacity:0.08;animation:smokeDrift3 35s ease-in-out infinite alternate; }
.smoke-4 { width:55vmax;height:55vmax;top:10%;left:40%;background:var(--fground-colour);opacity:0.2;animation:smokeDrift4 28s ease-in-out infinite alternate; }
@keyframes smokeDrift1 { 0%{transform:translate(0,0) scale(1)} 100%{transform:translate(10vw,8vh) scale(1.2)} }
@keyframes smokeDrift2 { 0%{transform:translate(0,0) scale(1)} 100%{transform:translate(-8vw,5vh) scale(1.15)} }
@keyframes smokeDrift3 { 0%{transform:translate(0,0) scale(1)} 100%{transform:translate(6vw,-6vh) scale(1.3)} }
@keyframes smokeDrift4 { 0%{transform:translate(0,0) scale(1)} 100%{transform:translate(-5vw,-8vh) scale(0.9)} }
</style>
CSS,
        ],

        // ─────────────────────────────────────────────
        // 12. DNA Helix
        // ─────────────────────────────────────────────
        'helix' => [
            'name' => 'DNA Helix',
            'icon' => '🧬',
            'html' => <<<'HTML'
<div class="helix-container">
    <div class="helix-dot hd-1"></div><div class="helix-dot hd-2"></div>
    <div class="helix-dot hd-3"></div><div class="helix-dot hd-4"></div>
    <div class="helix-dot hd-5"></div><div class="helix-dot hd-6"></div>
    <div class="helix-dot hd-7"></div><div class="helix-dot hd-8"></div>
    <div class="helix-dot hd-9"></div><div class="helix-dot hd-10"></div>
    <div class="helix-dot hd-11"></div><div class="helix-dot hd-12"></div>
    <div class="helix-dot hd-13"></div><div class="helix-dot hd-14"></div>
    <div class="helix-dot hd-15"></div><div class="helix-dot hd-16"></div>
    <div class="helix-line hl-1"></div><div class="helix-line hl-2"></div>
    <div class="helix-line hl-3"></div><div class="helix-line hl-4"></div>
    <div class="helix-line hl-5"></div><div class="helix-line hl-6"></div>
    <div class="helix-line hl-7"></div><div class="helix-line hl-8"></div>
</div>
HTML,
            'css' => <<<'CSS'
<style>
.helix-container { position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:300px;height:90vh; }
.helix-dot {
    position:absolute; width:8px; height:8px; border-radius:50%;
    left:50%;
    animation: helixSpin var(--spd) linear infinite;
}
.helix-dot:nth-child(odd)  { background:var(--highlights-colour); opacity:0.5; }
.helix-dot:nth-child(even) { background:var(--cground-colour); opacity:0.4; animation-direction:reverse; }
.hd-1  { top:3%;  --spd:4s;animation-delay:0s; }    .hd-2  { top:3%;  --spd:4s;animation-delay:2s; }
.hd-3  { top:15%; --spd:4s;animation-delay:0.5s; }  .hd-4  { top:15%; --spd:4s;animation-delay:2.5s; }
.hd-5  { top:27%; --spd:4s;animation-delay:1s; }    .hd-6  { top:27%; --spd:4s;animation-delay:3s; }
.hd-7  { top:39%; --spd:4s;animation-delay:1.5s; }  .hd-8  { top:39%; --spd:4s;animation-delay:3.5s; }
.hd-9  { top:51%; --spd:4s;animation-delay:0.25s; } .hd-10 { top:51%; --spd:4s;animation-delay:2.25s; }
.hd-11 { top:63%; --spd:4s;animation-delay:0.75s; } .hd-12 { top:63%; --spd:4s;animation-delay:2.75s; }
.hd-13 { top:75%; --spd:4s;animation-delay:1.25s; } .hd-14 { top:75%; --spd:4s;animation-delay:3.25s; }
.hd-15 { top:87%; --spd:4s;animation-delay:1.75s; } .hd-16 { top:87%; --spd:4s;animation-delay:3.75s; }
.helix-line {
    position:absolute; left:50%; width:100px; height:1px;
    background:var(--cground-colour); opacity:0.12;
    transform-origin:left center;
    animation: helixLine 4s linear infinite;
}
.hl-1 { top:3%;animation-delay:0s; }     .hl-2 { top:15%;animation-delay:0.5s; }
.hl-3 { top:27%;animation-delay:1s; }    .hl-4 { top:39%;animation-delay:1.5s; }
.hl-5 { top:51%;animation-delay:0.25s; } .hl-6 { top:63%;animation-delay:0.75s; }
.hl-7 { top:75%;animation-delay:1.25s; } .hl-8 { top:87%;animation-delay:1.75s; }
@keyframes helixSpin { 0%{transform:translateX(calc(-50% + 80px))} 25%{transform:translateX(-50%)} 50%{transform:translateX(calc(-50% - 80px))} 75%{transform:translateX(-50%)} 100%{transform:translateX(calc(-50% + 80px))} }
@keyframes helixLine { 0%{transform:scaleX(1) translateX(-50px);opacity:0.12} 25%{transform:scaleX(0) translateX(0);opacity:0} 50%{transform:scaleX(1) translateX(-50px);opacity:0.12} 75%{transform:scaleX(0) translateX(0);opacity:0} 100%{transform:scaleX(1) translateX(-50px);opacity:0.12} }
</style>
CSS,
        ],

        // ─────────────────────────────────────────────
        // 13. Prism / Refracted Light
        // ─────────────────────────────────────────────
        'prism' => [
            'name' => 'Prism',
            'icon' => '🔺',
            'html' => <<<'HTML'
<div class="prism-beam prism-beam-1"></div>
<div class="prism-beam prism-beam-2"></div>
<div class="prism-beam prism-beam-3"></div>
<div class="prism-beam prism-beam-4"></div>
<div class="prism-beam prism-beam-5"></div>
<div class="prism-core"></div>
HTML,
            'css' => <<<'CSS'
<style>
.prism-core {
    position:absolute;top:50%;left:50%;width:4px;height:4px;
    transform:translate(-50%,-50%);border-radius:50%;
    background:var(--primtext-colour);opacity:0.4;
    box-shadow:0 0 40px 15px var(--highlights-colour);
    animation:prismPulse 5s ease-in-out infinite alternate;
}
.prism-beam {
    position:absolute; top:50%; left:50%;
    width:150vmax; height:1px;
    transform-origin:0 0;
    opacity:0.08;
}
.prism-beam-1 { background:linear-gradient(90deg,var(--highlights-colour),transparent 70%);transform:rotate(0deg);animation:prismRotate 40s linear infinite; }
.prism-beam-2 { background:linear-gradient(90deg,var(--cground-colour),transparent 60%);transform:rotate(72deg);animation:prismRotate 40s 2s linear infinite; }
.prism-beam-3 { background:linear-gradient(90deg,var(--fground-colour),transparent 80%);transform:rotate(144deg);animation:prismRotate 40s 4s linear infinite; opacity:0.12; }
.prism-beam-4 { background:linear-gradient(90deg,var(--highlights-colour),transparent 50%);transform:rotate(216deg);animation:prismRotate 40s 6s linear infinite; }
.prism-beam-5 { background:linear-gradient(90deg,var(--cground-colour),transparent 65%);transform:rotate(288deg);animation:prismRotate 40s 8s linear infinite; }
@keyframes prismRotate { to { transform:rotate(calc(var(--start,0deg) + 360deg)); } }
@keyframes prismPulse { 0%{opacity:0.2;box-shadow:0 0 30px 10px var(--highlights-colour)} 100%{opacity:0.5;box-shadow:0 0 60px 25px var(--highlights-colour)} }
</style>
CSS,
        ],

        // ─────────────────────────────────────────────
        // 14. Topography
        // ─────────────────────────────────────────────
        'topography' => [
            'name' => 'Topography',
            'icon' => '🗺',
            'html' => <<<'HTML'
<svg class="topo-svg" viewBox="0 0 1000 1000" preserveAspectRatio="xMidYMid slice">
    <ellipse cx="500" cy="500" rx="420" ry="350" fill="none" stroke="var(--cground-colour)" stroke-width="0.8" opacity="0.08"/>
    <ellipse cx="500" cy="500" rx="350" ry="280" fill="none" stroke="var(--cground-colour)" stroke-width="0.8" opacity="0.1"/>
    <ellipse cx="480" cy="490" rx="280" ry="220" fill="none" stroke="var(--cground-colour)" stroke-width="0.8" opacity="0.12"/>
    <ellipse cx="470" cy="480" rx="210" ry="160" fill="none" stroke="var(--highlights-colour)" stroke-width="0.6" opacity="0.08"/>
    <ellipse cx="460" cy="470" rx="150" ry="110" fill="none" stroke="var(--cground-colour)" stroke-width="0.8" opacity="0.14"/>
    <ellipse cx="450" cy="460" rx="90" ry="65" fill="none" stroke="var(--cground-colour)" stroke-width="0.8" opacity="0.16"/>
    <ellipse cx="440" cy="455" rx="40" ry="28" fill="none" stroke="var(--highlights-colour)" stroke-width="0.6" opacity="0.1"/>
</svg>
<div class="topo-glow"></div>
HTML,
            'css' => <<<'CSS'
<style>
.topo-svg { position:absolute;inset:0;width:100%;height:100%;animation:topoDrift 50s ease-in-out infinite alternate; }
.topo-glow { position:absolute;width:30vmax;height:30vmax;top:30%;left:30%;border-radius:50%;background:var(--highlights-colour);filter:blur(90px);opacity:0.05;animation:topoGlow 12s ease-in-out infinite alternate; }
@keyframes topoDrift { 0%{transform:translate(0,0) scale(1) rotate(0)} 100%{transform:translate(3vw,2vh) scale(1.05) rotate(5deg)} }
@keyframes topoGlow { 0%{opacity:0.03;transform:scale(1)} 100%{opacity:0.08;transform:scale(1.2)} }
</style>
CSS,
        ],

        // ─────────────────────────────────────────────
        // 15. Rain
        // ─────────────────────────────────────────────
        'rain' => [
            'name' => 'Rain',
            'icon' => '🌧',
            'html' => <<<'HTML'
<div class="rain-drop rd-1"></div><div class="rain-drop rd-2"></div><div class="rain-drop rd-3"></div>
<div class="rain-drop rd-4"></div><div class="rain-drop rd-5"></div><div class="rain-drop rd-6"></div>
<div class="rain-drop rd-7"></div><div class="rain-drop rd-8"></div><div class="rain-drop rd-9"></div>
<div class="rain-drop rd-10"></div><div class="rain-drop rd-11"></div><div class="rain-drop rd-12"></div>
<div class="rain-drop rd-13"></div><div class="rain-drop rd-14"></div><div class="rain-drop rd-15"></div>
<div class="rain-drop rd-16"></div><div class="rain-drop rd-17"></div><div class="rain-drop rd-18"></div>
<div class="rain-drop rd-19"></div><div class="rain-drop rd-20"></div>
HTML,
            'css' => <<<'CSS'
<style>
.rain-drop {
    position:absolute; width:1px; background:linear-gradient(to bottom, var(--highlights-colour), transparent);
    opacity:0; top:-5%;
    animation: rainFall var(--spd) var(--del) linear infinite;
}
.rd-1  { left:5%;  height:30px;--spd:1.2s;--del:0s; }
.rd-2  { left:10%; height:25px;--spd:1.0s;--del:0.3s; }
.rd-3  { left:18%; height:35px;--spd:1.4s;--del:0.1s; }
.rd-4  { left:24%; height:20px;--spd:0.9s;--del:0.6s; }
.rd-5  { left:30%; height:28px;--spd:1.1s;--del:0.2s; }
.rd-6  { left:36%; height:32px;--spd:1.3s;--del:0.8s; }
.rd-7  { left:42%; height:22px;--spd:1.0s;--del:0.4s; }
.rd-8  { left:48%; height:30px;--spd:1.2s;--del:0.15s; }
.rd-9  { left:54%; height:26px;--spd:0.95s;--del:0.55s; }
.rd-10 { left:60%; height:34px;--spd:1.35s;--del:0.25s; }
.rd-11 { left:66%; height:20px;--spd:1.0s;--del:0.7s; }
.rd-12 { left:72%; height:28px;--spd:1.15s;--del:0.05s; }
.rd-13 { left:78%; height:24px;--spd:0.85s;--del:0.45s; }
.rd-14 { left:84%; height:32px;--spd:1.25s;--del:0.35s; }
.rd-15 { left:90%; height:22px;--spd:1.05s;--del:0.65s; }
.rd-16 { left:14%; height:26px;--spd:1.1s;--del:0.9s; }
.rd-17 { left:38%; height:30px;--spd:1.3s;--del:1.0s; }
.rd-18 { left:56%; height:20px;--spd:0.9s;--del:0.75s; }
.rd-19 { left:68%; height:34px;--spd:1.4s;--del:0.5s; }
.rd-20 { left:82%; height:24px;--spd:1.0s;--del:0.85s; }
@keyframes rainFall {
    0%   { opacity:0; transform:translateY(0); }
    10%  { opacity:0.35; }
    90%  { opacity:0.15; }
    100% { opacity:0; transform:translateY(105vh); }
}
</style>
CSS,
        ],

        // ─────────────────────────────────────────────
        // 16. Concentric Squares
        // ─────────────────────────────────────────────
        'squares' => [
            'name' => 'Concentric Squares',
            'icon' => '⧈',
            'html' => <<<'HTML'
<div class="csq csq-1"></div>
<div class="csq csq-2"></div>
<div class="csq csq-3"></div>
<div class="csq csq-4"></div>
<div class="csq csq-5"></div>
<div class="csq csq-6"></div>
HTML,
            'css' => <<<'CSS'
<style>
.csq {
    position:absolute;top:50%;left:50%;
    border:1px solid var(--cground-colour);
    border-radius:6px;
    transform:translate(-50%,-50%) rotate(var(--rot));
    animation: csqPulse var(--spd) ease-in-out infinite alternate;
}
.csq-1 { width:15vmax;height:15vmax;--rot:0deg;--spd:8s;opacity:0.2; }
.csq-2 { width:28vmax;height:28vmax;--rot:15deg;--spd:10s;opacity:0.15;border-color:var(--highlights-colour); }
.csq-3 { width:42vmax;height:42vmax;--rot:30deg;--spd:12s;opacity:0.1; }
.csq-4 { width:56vmax;height:56vmax;--rot:45deg;--spd:14s;opacity:0.08;border-color:var(--highlights-colour); }
.csq-5 { width:70vmax;height:70vmax;--rot:60deg;--spd:16s;opacity:0.06; }
.csq-6 { width:85vmax;height:85vmax;--rot:75deg;--spd:18s;opacity:0.04;border-color:var(--highlights-colour); }
@keyframes csqPulse {
    0%  { transform:translate(-50%,-50%) rotate(var(--rot)) scale(1); opacity:var(--start-op,0.15); }
    100%{ transform:translate(-50%,-50%) rotate(calc(var(--rot) + 20deg)) scale(1.06); }
}
</style>
CSS,
        ],

        // ─────────────────────────────────────────────
        // 17. Fireflies
        // ─────────────────────────────────────────────
        'fireflies' => [
            'name' => 'Fireflies',
            'icon' => '✨',
            'html' => <<<'HTML'
<div class="ff ff-1"></div><div class="ff ff-2"></div><div class="ff ff-3"></div>
<div class="ff ff-4"></div><div class="ff ff-5"></div><div class="ff ff-6"></div>
<div class="ff ff-7"></div><div class="ff ff-8"></div><div class="ff ff-9"></div>
<div class="ff ff-10"></div><div class="ff ff-11"></div><div class="ff ff-12"></div>
HTML,
            'css' => <<<'CSS'
<style>
.ff {
    position:absolute; width:4px; height:4px; border-radius:50%;
    background:var(--highlights-colour);
    box-shadow:0 0 8px 3px var(--highlights-colour);
    animation: ffFloat var(--spd) ease-in-out infinite alternate, ffGlow var(--glow) ease-in-out infinite alternate;
}
.ff-1  { top:20%;left:15%;--spd:12s;--glow:3s; }
.ff-2  { top:35%;left:65%;--spd:15s;--glow:4s;animation-delay:1s; }
.ff-3  { top:70%;left:30%;--spd:10s;--glow:2.5s;animation-delay:0.5s; }
.ff-4  { top:15%;left:80%;--spd:14s;--glow:3.5s;animation-delay:2s; }
.ff-5  { top:55%;left:45%;--spd:11s;--glow:4.5s;animation-delay:0.8s; }
.ff-6  { top:80%;left:75%;--spd:13s;--glow:3s;animation-delay:1.5s; }
.ff-7  { top:45%;left:10%;--spd:16s;--glow:5s;animation-delay:3s; }
.ff-8  { top:60%;left:88%;--spd:9s;--glow:2s;animation-delay:0.3s; }
.ff-9  { top:25%;left:40%;--spd:14s;--glow:3.8s;animation-delay:2.5s; }
.ff-10 { top:85%;left:55%;--spd:12s;--glow:4.2s;animation-delay:1.2s; }
.ff-11 { top:10%;left:50%;--spd:11s;--glow:3.2s;animation-delay:0.7s; }
.ff-12 { top:50%;left:25%;--spd:13s;--glow:2.8s;animation-delay:1.8s; }
@keyframes ffFloat {
    0%  { transform:translate(0,0); }
    33% { transform:translate(calc(var(--spd) * 0.3), calc(var(--spd) * -0.2)); }
    66% { transform:translate(calc(var(--spd) * -0.2), calc(var(--spd) * 0.15)); }
    100%{ transform:translate(3vw, -2vh); }
}
@keyframes ffGlow {
    0%  { opacity:0.15; box-shadow:0 0 4px 1px var(--highlights-colour); }
    100%{ opacity:0.7; box-shadow:0 0 14px 5px var(--highlights-colour); }
}
</style>
CSS,
        ],

        // ─────────────────────────────────────────────
        // 18. None / Clean
        // ─────────────────────────────────────────────
        'none' => [
            'name' => 'None',
            'icon' => '∅',
            'html' => '',
            'css'  => '',
        ],

    ];
}

/**
 * Get the active background key from settings, defaulting to 'blobs'.
 */
function loadActiveBackground(PDO $pdo): string
{
    $stmt = $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'active_background'");
    $row = $stmt->fetch();
    $key = $row ? $row['setting_value'] : 'blobs';

    $bgs = getBackgrounds();
    return isset($bgs[$key]) ? $key : 'blobs';
}

/**
 * Render the active background: outputs HTML inside .background-container and the <style> block.
 */
function renderBackground(string $key): string
{
    $bgs = getBackgrounds();
    $bg = $bgs[$key] ?? $bgs['blobs'];
    return $bg['html'] . "\n" . $bg['css'];
}