// Procedural 3D product box (Hyabell carton) — plain Three.js, no build step.
// Reconstructed from a real product photo: proportions + colors sampled from
// uploads/products/hyabell-variants/ultra.png. The label graphic (swoosh,
// wordmark, lidocaine stripe) is redrawn on a canvas per variant rather than
// projecting the source photo, since a flat print design reproduces more
// crisply as a generated texture than a perspective-corrected photo crop.

import * as THREE from 'three';
import { OrbitControls } from 'three/addons/controls/OrbitControls.js';

const PALETTE = {
    paper: '#f4f3f6',
    paperShade: '#e7e5ec',
    swooshLight: '#efe6f7',
    swooshMid: '#c99bea',
    swooshDeep: '#8a4fc7',
    stripe: '#853dd5',
    stripeDark: '#6c2bb8',
    ink: '#161316',
    inkSoft: '#6b6570',
};

function roundedRectPath(ctx, x, y, w, h, r) {
    ctx.beginPath();
    ctx.moveTo(x + r, y);
    ctx.arcTo(x + w, y, x + w, y + h, r);
    ctx.arcTo(x + w, y + h, x, y + h, r);
    ctx.arcTo(x, y + h, x, y, r);
    ctx.arcTo(x, y, x + w, y, r);
    ctx.closePath();
}

function drawSwoosh(ctx, cx, cy, scale) {
    ctx.save();
    ctx.translate(cx, cy);
    ctx.scale(scale, scale);

    const wing = (rot, grad) => {
        ctx.save();
        ctx.rotate(rot);
        ctx.beginPath();
        ctx.moveTo(0, -190);
        ctx.bezierCurveTo(110, -160, 150, -40, 60, 0);
        ctx.bezierCurveTo(150, 40, 110, 160, 0, 190);
        ctx.bezierCurveTo(-40, 90, -40, -90, 0, -190);
        ctx.closePath();
        ctx.fillStyle = grad;
        ctx.fill();
        ctx.restore();
    };

    const g1 = ctx.createLinearGradient(-120, -180, 120, 180);
    g1.addColorStop(0, PALETTE.swooshLight);
    g1.addColorStop(0.55, PALETTE.swooshMid);
    g1.addColorStop(1, PALETTE.swooshDeep);
    wing(-0.18, g1);

    const g2 = ctx.createLinearGradient(-100, -150, 100, 150);
    g2.addColorStop(0, PALETTE.swooshDeep);
    g2.addColorStop(0.6, PALETTE.swooshMid);
    g2.addColorStop(1, PALETTE.swooshLight);
    ctx.globalAlpha = 0.85;
    wing(0.35, g2);
    ctx.globalAlpha = 1;

    ctx.restore();
}

function drawFlag(ctx, x, y, w, h) {
    const stripeH = h / 3;
    ctx.fillStyle = '#1a1a1a';
    ctx.fillRect(x, y, w, stripeH);
    ctx.fillStyle = '#d21f2f';
    ctx.fillRect(x, y + stripeH, w, stripeH);
    ctx.fillStyle = '#f2c231';
    ctx.fillRect(x, y + stripeH * 2, w, stripeH);
}

export function drawFrontLabel(canvas, variantLabel) {
    const w = canvas.width, h = canvas.height;
    const ctx = canvas.getContext('2d');
    ctx.clearRect(0, 0, w, h);

    ctx.fillStyle = PALETTE.paper;
    ctx.fillRect(0, 0, w, h);

    drawSwoosh(ctx, w * 0.38, h * 0.42, (h / 700));

    // "1 x 1 ml" top-left
    ctx.fillStyle = PALETTE.inkSoft;
    ctx.font = `${Math.round(h * 0.045)}px Arial, sans-serif`;
    ctx.textBaseline = 'alphabetic';
    ctx.fillText('1 x 1 ml', w * 0.045, h * 0.14);

    // made in Germany, top-right
    const flagW = w * 0.045, flagH = h * 0.075;
    drawFlag(ctx, w * 0.90, h * 0.10, flagW, flagH);
    ctx.fillStyle = PALETTE.inkSoft;
    ctx.font = `${Math.round(h * 0.035)}px Arial, sans-serif`;
    ctx.fillText('made in', w * 0.90 + flagW + 8, h * 0.135);
    ctx.fillText('Germany', w * 0.90 + flagW + 8, h * 0.135 + h * 0.045);

    // Wordmark
    ctx.fillStyle = PALETTE.ink;
    ctx.font = `900 ${Math.round(h * 0.155)}px Arial, sans-serif`;
    ctx.textBaseline = 'alphabetic';
    const wmY = h * 0.63;
    ctx.fillText('HYABELL', w * 0.045, wmY);
    const wmWidth = ctx.measureText('HYABELL').width;
    ctx.font = `${Math.round(h * 0.06)}px Arial, sans-serif`;
    ctx.fillText('®', w * 0.045 + wmWidth + 4, wmY - h * 0.09);

    // Variant name
    ctx.fillStyle = PALETTE.swooshDeep;
    ctx.font = `800 ${Math.round(h * 0.08)}px Arial, sans-serif`;
    ctx.fillText(variantLabel.toUpperCase(), w * 0.045, wmY + h * 0.11);

    // Tagline
    ctx.fillStyle = PALETTE.inkSoft;
    ctx.font = `${Math.round(h * 0.05)}px Arial, sans-serif`;
    ctx.fillText('The soft-tissue filler', w * 0.045, h * 0.90);

    // Lidocaine stripe (right edge, diagonal cut)
    const stripeX = w * 0.72;
    ctx.save();
    ctx.beginPath();
    ctx.moveTo(stripeX + w * 0.03, h * 0.50);
    ctx.lineTo(w, h * 0.44);
    ctx.lineTo(w, h * 0.62);
    ctx.lineTo(stripeX, h * 0.62);
    ctx.closePath();
    const sg = ctx.createLinearGradient(stripeX, 0, w, 0);
    sg.addColorStop(0, PALETTE.stripeDark);
    sg.addColorStop(1, PALETTE.stripe);
    ctx.fillStyle = sg;
    ctx.fill();
    ctx.restore();

    ctx.fillStyle = '#ffffff';
    ctx.font = `700 ${Math.round(h * 0.04)}px Arial, sans-serif`;
    ctx.fillText('+ LIDOCAINE', stripeX + w * 0.02, h * 0.565);
}

export function drawSpineLabel(canvas, variantLabel) {
    const w = canvas.width, h = canvas.height;
    const ctx = canvas.getContext('2d');
    ctx.clearRect(0, 0, w, h);
    ctx.fillStyle = PALETTE.paperShade;
    ctx.fillRect(0, 0, w, h);
    ctx.save();
    ctx.translate(w * 0.5, h * 0.5);
    ctx.fillStyle = PALETTE.ink;
    ctx.font = `900 ${Math.round(h * 0.34)}px Arial, sans-serif`;
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    ctx.fillText('HYABELL', 0, -h * 0.06);
    ctx.fillStyle = PALETTE.swooshDeep;
    ctx.font = `700 ${Math.round(h * 0.2)}px Arial, sans-serif`;
    ctx.fillText(variantLabel.toUpperCase(), 0, h * 0.26);
    ctx.restore();
}

function plainFaceCanvas(w, h, color) {
    const c = document.createElement('canvas');
    c.width = w; c.height = h;
    const ctx = c.getContext('2d');
    roundedRectPath(ctx, 0, 0, w, h, 0);
    ctx.fillStyle = color;
    ctx.fill();
    return c;
}

export function createHyabellBox(variantLabel) {
    const group = new THREE.Group();

    // Real-world proportions sampled from the reference photo: a slim,
    // wide carton (~3.4 : 1.3 : 0.55).
    const W = 3.4, H = 1.3, D = 0.55;
    const geo = new THREE.BoxGeometry(W, H, D, 1, 1, 1);

    const frontCanvas = document.createElement('canvas');
    frontCanvas.width = 1024; frontCanvas.height = 384;
    drawFrontLabel(frontCanvas, variantLabel);
    const frontTex = new THREE.CanvasTexture(frontCanvas);
    frontTex.colorSpace = THREE.SRGBColorSpace;

    const spineCanvas = document.createElement('canvas');
    spineCanvas.width = 384; spineCanvas.height = 160;
    drawSpineLabel(spineCanvas, variantLabel);
    const spineTex = new THREE.CanvasTexture(spineCanvas);
    spineTex.colorSpace = THREE.SRGBColorSpace;

    const plainTex = new THREE.CanvasTexture(plainFaceCanvas(64, 64, PALETTE.paperShade));
    plainTex.colorSpace = THREE.SRGBColorSpace;

    const matOptions = { roughness: 0.82, metalness: 0.02 };
    // BoxGeometry face order: +x, -x, +y, -y, +z, -z
    const materials = [
        new THREE.MeshStandardMaterial({ map: spineTex, ...matOptions }),   // +x (right edge)
        new THREE.MeshStandardMaterial({ map: spineTex, ...matOptions }),   // -x (left edge)
        new THREE.MeshStandardMaterial({ map: plainTex, ...matOptions }),   // +y (top)
        new THREE.MeshStandardMaterial({ map: plainTex, ...matOptions }),   // -y (bottom)
        new THREE.MeshStandardMaterial({ map: frontTex, ...matOptions }),   // +z (front)
        new THREE.MeshStandardMaterial({ map: plainTex, ...matOptions }),   // -z (back)
    ];

    const mesh = new THREE.Mesh(geo, materials);
    mesh.castShadow = true;
    mesh.receiveShadow = true;
    group.add(mesh);

    group.userData.setVariant = (label) => {
        drawFrontLabel(frontCanvas, label);
        frontTex.needsUpdate = true;
        drawSpineLabel(spineCanvas, label);
        spineTex.needsUpdate = true;
    };

    return group;
}

export function mountProductStage(container, initialVariant) {
    const width = container.clientWidth || 600;
    const height = container.clientHeight || 600;

    const scene = new THREE.Scene();
    const camera = new THREE.PerspectiveCamera(32, width / height, 0.1, 100);
    camera.position.set(3.4, 1.7, 3.6);

    const renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    renderer.setSize(width, height);
    renderer.outputColorSpace = THREE.SRGBColorSpace;
    renderer.shadowMap.enabled = true;
    container.appendChild(renderer.domElement);

    const key = new THREE.DirectionalLight(0xffffff, 2.2);
    key.position.set(4, 5, 3);
    key.castShadow = true;
    key.shadow.mapSize.set(1024, 1024);
    scene.add(key);
    const fill = new THREE.DirectionalLight(0xd9c9f5, 0.6);
    fill.position.set(-4, 2, -2);
    scene.add(fill);
    const rim = new THREE.DirectionalLight(0xffffff, 0.5);
    rim.position.set(0, 3, -5);
    scene.add(rim);
    scene.add(new THREE.AmbientLight(0xffffff, 0.35));

    const shadowGeo = new THREE.PlaneGeometry(14, 14);
    const shadowMat = new THREE.ShadowMaterial({ opacity: 0.18 });
    const shadowPlane = new THREE.Mesh(shadowGeo, shadowMat);
    shadowPlane.rotation.x = -Math.PI / 2;
    shadowPlane.position.y = -0.65; // half of the box height (H=1.3 in createHyabellBox)
    shadowPlane.receiveShadow = true;
    scene.add(shadowPlane);

    const box = createHyabellBox(initialVariant || 'Ultra');
    scene.add(box);

    const controls = new OrbitControls(camera, renderer.domElement);
    controls.enableDamping = true;
    controls.dampingFactor = 0.08;
    controls.enablePan = false;
    controls.minDistance = 2.2;
    controls.maxDistance = 7;
    controls.autoRotate = true;
    controls.autoRotateSpeed = 1.4;
    controls.target.set(0, 0, 0);

    let disposed = false;
    function animate() {
        if (disposed) return;
        controls.update();
        renderer.render(scene, camera);
        requestAnimationFrame(animate);
    }
    animate();

    function onResize() {
        const w = container.clientWidth, h = container.clientHeight;
        if (!w || !h) return;
        camera.aspect = w / h;
        camera.updateProjectionMatrix();
        renderer.setSize(w, h);
    }
    window.addEventListener('resize', onResize);

    return {
        setVariant(label) { box.userData.setVariant(label); },
        setAutoRotate(on) { controls.autoRotate = on; },
        dispose() {
            disposed = true;
            window.removeEventListener('resize', onResize);
            renderer.dispose();
            container.removeChild(renderer.domElement);
        },
    };
}
