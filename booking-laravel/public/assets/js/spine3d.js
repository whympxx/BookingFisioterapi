/*
 * Model 3D tulang belakang (Three.js) untuk hero & halaman login.
 * Pemakaian: <div class="stage-3d" data-spine3d data-variant="light|dark">...</div>
 * Jika WebGL tidak tersedia, gambar fallback di dalam .stage-3d tetap tampil.
 */
(function () {
    if (!window.THREE) return;
    const THREE = window.THREE;
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function webglAvailable() {
        try {
            const c = document.createElement('canvas');
            return !!(window.WebGLRenderingContext && (c.getContext('webgl') || c.getContext('experimental-webgl')));
        } catch (e) {
            return false;
        }
    }

    function createScene(stage) {
        const onGradient = stage.dataset.variant === 'light';
        const width = () => stage.clientWidth || 400;
        const height = () => stage.clientHeight || 400;

        const renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
        renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
        renderer.setSize(width(), height());
        renderer.outputEncoding = THREE.sRGBEncoding;
        stage.appendChild(renderer.domElement);
        renderer.domElement.setAttribute('aria-hidden', 'true');
        stage.classList.add('has-webgl');

        const scene = new THREE.Scene();
        const camera = new THREE.PerspectiveCamera(35, width() / height(), 0.1, 100);
        camera.position.set(0, 0, 13);

        // Cahaya
        scene.add(new THREE.AmbientLight(0xffffff, 0.55));
        const key = new THREE.DirectionalLight(0xffffff, 0.9);
        key.position.set(5, 6, 8);
        scene.add(key);
        const tealLight = new THREE.PointLight(0x2dd4bf, 1.4, 30);
        tealLight.position.set(-5, -2, 5);
        scene.add(tealLight);
        const skyLight = new THREE.PointLight(0x38bdf8, 0.9, 30);
        skyLight.position.set(5, 4, -4);
        scene.add(skyLight);

        const root = new THREE.Group();
        scene.add(root);

        // Material
        const boneMat = new THREE.MeshStandardMaterial({ color: 0xf8fafc, roughness: 0.38, metalness: 0.05 });
        const boneShade = new THREE.MeshStandardMaterial({ color: 0xe2e8f0, roughness: 0.5, metalness: 0.05 });
        const discMat = new THREE.MeshStandardMaterial({
            color: 0x14b8a6, emissive: 0x0d9488, emissiveIntensity: 0.55,
            roughness: 0.25, metalness: 0.1, transparent: true, opacity: 0.92,
        });

        // Tulang belakang: ruas disusun mengikuti lengkung S alami
        const spine = new THREE.Group();
        root.add(spine);
        const count = 17;
        const spacing = 0.46;
        const vertebrae = [];
        const discs = [];
        const bodyGeo = new THREE.CylinderGeometry(0.42, 0.46, 0.3, 32);
        const discGeo = new THREE.CylinderGeometry(0.4, 0.4, 0.11, 32);
        const spinousGeo = new THREE.BoxGeometry(0.16, 0.16, 0.62);
        const transverseGeo = new THREE.BoxGeometry(0.62, 0.11, 0.14);
        const archGeo = new THREE.TorusGeometry(0.2, 0.06, 10, 24, Math.PI);

        for (let i = 0; i < count; i++) {
            const t = i / (count - 1);
            const y = (i - (count - 1) / 2) * spacing;
            const z = Math.sin(t * Math.PI * 2) * 0.45;
            const scale = 0.72 + t * 0.5; // ruas bawah lebih besar

            const v = new THREE.Group();
            const body = new THREE.Mesh(bodyGeo, boneMat);
            v.add(body);

            const arch = new THREE.Mesh(archGeo, boneShade);
            arch.rotation.x = Math.PI / 2;
            arch.position.z = -0.42;
            v.add(arch);

            const spinous = new THREE.Mesh(spinousGeo, boneShade);
            spinous.position.set(0, -0.06, -0.85);
            spinous.rotation.x = -0.35;
            v.add(spinous);

            [-1, 1].forEach((side) => {
                const tp = new THREE.Mesh(transverseGeo, boneShade);
                tp.position.set(side * 0.5, 0, -0.45);
                tp.rotation.z = side * 0.15;
                v.add(tp);
            });

            v.position.set(0, y, z);
            v.scale.setScalar(scale);
            v.userData = { baseY: y, baseZ: z, phase: i * 0.38 };
            spine.add(v);
            vertebrae.push(v);

            if (i < count - 1) {
                const nz = Math.sin(((i + 1) / (count - 1)) * Math.PI * 2) * 0.45;
                const d = new THREE.Mesh(discGeo, discMat.clone());
                d.position.set(0, y + spacing / 2, (z + nz) / 2);
                d.scale.setScalar(scale);
                spine.add(d);
                discs.push(d);
            }
        }
        spine.rotation.z = 0.08;

        // Titik "pemulihan" di area punggung bawah yang berdenyut
        const healCore = new THREE.Mesh(
            new THREE.SphereGeometry(0.22, 24, 24),
            new THREE.MeshBasicMaterial({ color: 0x5eead4 })
        );
        const healGlow = new THREE.Mesh(
            new THREE.SphereGeometry(0.5, 24, 24),
            new THREE.MeshBasicMaterial({ color: 0x2dd4bf, transparent: true, opacity: 0.25 })
        );
        const healTarget = vertebrae[3];
        healCore.position.copy(healTarget.position).add(new THREE.Vector3(0.9, 0, 0.4));
        healGlow.position.copy(healCore.position);
        spine.add(healCore, healGlow);

        // Cincin orbit
        const rings = [];
        [[2.3, 0x14b8a6, 0.5], [2.9, 0x38bdf8, -0.35]].forEach(([r, color, tilt]) => {
            const ring = new THREE.Mesh(
                new THREE.TorusGeometry(r, 0.018, 8, 140),
                new THREE.MeshBasicMaterial({ color, transparent: true, opacity: onGradient ? 0.55 : 0.4 })
            );
            ring.rotation.x = Math.PI / 2 + tilt;
            root.add(ring);
            rings.push(ring);

            const bead = new THREE.Mesh(
                new THREE.SphereGeometry(0.09, 16, 16),
                new THREE.MeshBasicMaterial({ color })
            );
            ring.add(bead);
            ring.userData.bead = bead;
            ring.userData.r = r;
        });

        // Molekul melayang
        const shapes = [];
        const shapeGeos = [new THREE.IcosahedronGeometry(0.28, 0), new THREE.OctahedronGeometry(0.25, 0), new THREE.TorusKnotGeometry(0.16, 0.05, 48, 8)];
        for (let i = 0; i < 9; i++) {
            const geo = shapeGeos[i % shapeGeos.length];
            const mat = new THREE.MeshStandardMaterial({
                color: [0x14b8a6, 0x38bdf8, 0xfb7185][i % 3], roughness: 0.3, metalness: 0.2,
                wireframe: i % 2 === 0,
            });
            const m = new THREE.Mesh(geo, mat);
            const angle = (i / 9) * Math.PI * 2;
            m.position.set(Math.cos(angle) * (3 + (i % 3) * 0.5), (Math.random() - 0.5) * 6, Math.sin(angle) * 2 - 1);
            m.userData = { speed: 0.3 + Math.random() * 0.6, offset: Math.random() * 10, baseY: m.position.y };
            root.add(m);
            shapes.push(m);
        }

        // Partikel
        const pCount = 260;
        const positions = new Float32Array(pCount * 3);
        for (let i = 0; i < pCount; i++) {
            positions[i * 3] = (Math.random() - 0.5) * 12;
            positions[i * 3 + 1] = (Math.random() - 0.5) * 10;
            positions[i * 3 + 2] = (Math.random() - 0.5) * 6 - 1;
        }
        const pGeo = new THREE.BufferGeometry();
        pGeo.setAttribute('position', new THREE.BufferAttribute(positions, 3));
        const particles = new THREE.Points(pGeo, new THREE.PointsMaterial({
            color: onGradient ? 0xffffff : 0x14b8a6, size: 0.045, transparent: true, opacity: 0.7,
        }));
        scene.add(particles);

        // Interaksi mouse: model condong mengikuti kursor
        const target = { x: 0, y: 0 };
        const current = { x: 0, y: 0 };
        stage.addEventListener('pointermove', (e) => {
            const rect = stage.getBoundingClientRect();
            target.x = ((e.clientX - rect.left) / rect.width - 0.5) * 2;
            target.y = ((e.clientY - rect.top) / rect.height - 0.5) * 2;
        });
        stage.addEventListener('pointerleave', () => { target.x = 0; target.y = 0; });

        let running = true;
        let visible = true;
        const clock = new THREE.Clock();

        function frame() {
            const t = clock.getElapsedTime();

            current.x += (target.x - current.x) * 0.05;
            current.y += (target.y - current.y) * 0.05;
            root.rotation.y = t * 0.35 + current.x * 0.6;
            root.rotation.x = current.y * 0.25;

            // Gelombang lembut di sepanjang tulang belakang
            vertebrae.forEach((v) => {
                v.position.z = v.userData.baseZ + Math.sin(t * 1.4 + v.userData.phase) * 0.05;
                v.rotation.y = Math.sin(t * 0.8 + v.userData.phase) * 0.08;
            });
            discs.forEach((d, i) => {
                d.material.emissiveIntensity = 0.45 + Math.sin(t * 2.2 - i * 0.45) * 0.35;
            });

            const pulse = (Math.sin(t * 2.5) + 1) / 2;
            healGlow.scale.setScalar(1 + pulse * 0.8);
            healGlow.material.opacity = 0.35 - pulse * 0.25;

            rings.forEach((ring, i) => {
                ring.rotation.z = t * (i ? -0.4 : 0.3);
                const a = t * (i ? -1.1 : 0.9);
                ring.userData.bead.position.set(Math.cos(a) * ring.userData.r, Math.sin(a) * ring.userData.r, 0);
            });

            shapes.forEach((s) => {
                s.rotation.x = t * s.userData.speed;
                s.rotation.y = t * s.userData.speed * 1.3;
                s.position.y = s.userData.baseY + Math.sin(t * s.userData.speed + s.userData.offset) * 0.35;
            });

            particles.rotation.y = t * 0.03;
            renderer.render(scene, camera);
        }

        function loop() {
            if (!running) return;
            if (visible) frame();
            requestAnimationFrame(loop);
        }

        if (reduceMotion) {
            frame();
        } else {
            loop();
        }

        // Hemat baterai: berhenti merender saat tidak terlihat
        if ('IntersectionObserver' in window) {
            new IntersectionObserver((entries) => {
                visible = entries[0].isIntersecting;
            }).observe(stage);
        }
        document.addEventListener('visibilitychange', () => {
            running = !document.hidden;
            if (running && !reduceMotion) {
                clock.getDelta();
                loop();
            }
        });

        const resize = () => {
            camera.aspect = width() / height();
            // Jauhkan kamera di layar sempit agar model tetap utuh
            camera.position.z = camera.aspect < 0.8 ? 16 : 13;
            camera.updateProjectionMatrix();
            renderer.setSize(width(), height());
            if (reduceMotion) frame();
        };
        if ('ResizeObserver' in window) {
            new ResizeObserver(resize).observe(stage);
        } else {
            window.addEventListener('resize', resize);
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        if (!webglAvailable()) return;
        document.querySelectorAll('[data-spine3d]').forEach((stage) => {
            try {
                createScene(stage);
            } catch (e) {
                stage.classList.remove('has-webgl');
            }
        });
    });
})();
