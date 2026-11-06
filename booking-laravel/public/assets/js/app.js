// Interaksi umum: animasi muncul 3D saat scroll, kartu tilt 3D, penghitung angka,
// navbar saat scroll, dan notifikasi yang hilang otomatis.
(function () {
    document.documentElement.classList.remove('no-js');
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const finePointer = window.matchMedia('(pointer: fine)').matches;

    document.addEventListener('DOMContentLoaded', () => {
        // Navbar
        const nav = document.querySelector('.nav-glass');
        const onScroll = () => nav && nav.classList.toggle('scrolled', window.scrollY > 10);
        onScroll();
        window.addEventListener('scroll', onScroll, { passive: true });

        // Muncul saat scroll
        const revealEls = document.querySelectorAll('[data-reveal]');
        if (reduceMotion || !('IntersectionObserver' in window)) {
            revealEls.forEach((el) => el.classList.add('is-visible'));
        } else {
            const io = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        io.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
            revealEls.forEach((el) => io.observe(el));
        }

        // Tilt 3D mengikuti kursor
        if (finePointer && !reduceMotion) {
            document.querySelectorAll('[data-tilt]').forEach((el) => {
                const max = parseFloat(el.dataset.tilt) || 8;
                el.addEventListener('pointermove', (e) => {
                    const r = el.getBoundingClientRect();
                    const x = (e.clientX - r.left) / r.width - 0.5;
                    const y = (e.clientY - r.top) / r.height - 0.5;
                    el.style.transform = `perspective(900px) rotateX(${(-y * max).toFixed(2)}deg) rotateY(${(x * max).toFixed(2)}deg) translateZ(6px)`;
                });
                el.addEventListener('pointerleave', () => { el.style.transform = ''; });
            });
        }

        // Kartu flip juga bisa diketuk di layar sentuh
        document.querySelectorAll('.flip').forEach((card) => {
            card.addEventListener('click', () => card.classList.toggle('flipped'));
            card.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    card.classList.toggle('flipped');
                }
            });
        });

        // Penghitung angka
        const counters = document.querySelectorAll('[data-count]');
        const runCounter = (el) => {
            const end = parseFloat(el.dataset.count) || 0;
            const decimals = (el.dataset.count.split('.')[1] || '').length;
            const suffix = el.dataset.suffix || '';
            if (reduceMotion) {
                el.textContent = end.toLocaleString('id-ID', { minimumFractionDigits: decimals }) + suffix;
                return;
            }
            const start = performance.now();
            const dur = 1400;
            const step = (now) => {
                const p = Math.min((now - start) / dur, 1);
                const eased = 1 - Math.pow(1 - p, 3);
                el.textContent = (end * eased).toLocaleString('id-ID', {
                    minimumFractionDigits: decimals, maximumFractionDigits: decimals,
                }) + suffix;
                if (p < 1) requestAnimationFrame(step);
            };
            requestAnimationFrame(step);
        };
        if ('IntersectionObserver' in window) {
            const co = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        runCounter(entry.target);
                        co.unobserve(entry.target);
                    }
                });
            });
            counters.forEach((el) => co.observe(el));
        } else {
            counters.forEach(runCounter);
        }

        // Notifikasi hilang otomatis
        document.querySelectorAll('.flash-wrap .alert').forEach((alert) => {
            setTimeout(() => window.jQuery && window.jQuery(alert).alert('close'), 6000);
        });

        // Pratinjau foto sebelum upload
        document.querySelectorAll('[data-preview-target]').forEach((input) => {
            input.addEventListener('change', () => {
                const file = input.files[0];
                const img = document.getElementById(input.dataset.previewTarget);
                if (!file || !img) return;
                if (file.size > 2 * 1024 * 1024) {
                    alert('Ukuran file terlalu besar. Maksimal 2MB.');
                    input.value = '';
                    return;
                }
                const reader = new FileReader();
                reader.onload = (e) => {
                    img.src = e.target.result;
                    img.style.display = 'block';
                    img.classList.remove('d-none');
                };
                reader.readAsDataURL(file);
            });
        });

        // Konfirmasi sebelum aksi penting
        document.querySelectorAll('form[data-confirm]').forEach((form) => {
            form.addEventListener('submit', (e) => {
                if (!confirm(form.dataset.confirm)) e.preventDefault();
            });
        });
    });
})();
