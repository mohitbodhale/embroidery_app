/* TrackBridge — Auth page 3D mouse tracking & optical illusions */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var card = document.querySelector('.auth-card');
        var shapes = document.querySelectorAll('.auth-3d-parallax');
        var isAuthPage = card && shapes.length > 0;
        if (!isAuthPage) return;

        var tiltX = 0;
        var tiltY = 0;
        var targetTiltX = 0;
        var targetTiltY = 0;
        var mouseX = 0;
        var mouseY = 0;
        var rafId = null;

        function updateTilt() {
            tiltX += (targetTiltX - tiltX) * 0.08;
            tiltY += (targetTiltY - tiltY) * 0.08;

            if (Math.abs(tiltX) > 0.01 || Math.abs(tiltY) > 0.01) {
                card.style.transform = 'perspective(1200px) rotateX(' + tiltX.toFixed(3) + 'deg) rotateY(' + tiltY.toFixed(3) + 'deg)';
            }

            shapes.forEach(function (shape, index) {
                var factor = (index + 1) * 4;
                var tx = (mouseX - 0.5) * factor;
                var ty = (mouseY - 0.5) * factor;
                shape.style.transform = 'translateX(' + tx.toFixed(2) + 'px) translateY(' + ty.toFixed(2) + 'px)';
            });

            rafId = requestAnimationFrame(updateTilt);
        }

        document.addEventListener('mousemove', function (e) {
            var rect = card.getBoundingClientRect();
            var centerX = rect.left + rect.width / 2;
            var centerY = rect.top + rect.height / 2;
            targetTiltX = ((e.clientY - centerY) / (window.innerHeight / 2)) * -6;
            targetTiltY = ((e.clientX - centerX) / (window.innerWidth / 2)) * 6;
            mouseX = e.clientX / window.innerWidth;
            mouseY = e.clientY / window.innerHeight;
        });

        document.addEventListener('mouseleave', function () {
            targetTiltX = 0;
            targetTiltY = 0;
            mouseX = 0.5;
            mouseY = 0.5;
            setTimeout(function () {
                card.style.transform = 'perspective(1200px) rotateX(0deg) rotateY(0deg)';
            }, 300);
        });

        // Touch support for mobile
        document.addEventListener('touchmove', function (e) {
            if (e.touches.length > 0) {
                var touch = e.touches[0];
                var rect = card.getBoundingClientRect();
                var centerX = rect.left + rect.width / 2;
                var centerY = rect.top + rect.height / 2;
                targetTiltX = ((touch.clientY - centerY) / (window.innerHeight / 2)) * -6;
                targetTiltY = ((touch.clientX - centerX) / (window.innerWidth / 2)) * 6;
                mouseX = touch.clientX / window.innerWidth;
                mouseY = touch.clientY / window.innerHeight;
            }
        }, { passive: true });

        document.addEventListener('touchend', function () {
            targetTiltX = 0;
            targetTiltY = 0;
            mouseX = 0.5;
            mouseY = 0.5;
            setTimeout(function () {
                card.style.transform = 'perspective(1200px) rotateX(0deg) rotateY(0deg)';
            }, 300);
        });

        // Start animation loop
        rafId = requestAnimationFrame(updateTilt);

        // Generate floating debris particles
        generateDebris();

        // Generate moire rings
        generateMoire();
    });

    function generateDebris() {
        var container = document.querySelector('.auth-debris');
        if (!container) return;

        var count = 20;
        for (var i = 0; i < count; i++) {
            var item = document.createElement('div');
            item.className = 'auth-debris-item';
            var size = 2 + Math.random() * 6;
            item.style.width = size + 'px';
            item.style.height = size + 'px';
            item.style.left = Math.random() * 100 + '%';
            item.style.top = Math.random() * 100 + '%';
            item.style.animationDelay = Math.random() * 20 + 's';
            item.style.animationDuration = (15 + Math.random() * 15) + 's';
            container.appendChild(item);
        }
    }

    function generateMoire() {
        var container = document.querySelector('.auth-moire-enhanced');
        if (!container) return;

        var count = 5;
        for (var i = 0; i < count; i++) {
            var ring = document.createElement('div');
            ring.className = 'auth-moire-ring';
            ring.style.animationDelay = (i * 2.5) + 's';
            container.appendChild(ring);
        }
    }
})();
