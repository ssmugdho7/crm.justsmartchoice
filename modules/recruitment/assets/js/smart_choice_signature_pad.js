(function () {
    'use strict';

    function ready(fn) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', fn, { once: true });
        } else {
            fn();
        }
    }

    ready(function () {
        var canvas = document.getElementById('signature-pad');
        var dataField = document.getElementById('signature-data');
        var clearButton = document.getElementById('clear-signature');
        var form = document.querySelector('.sc-job-form');

        if (!canvas || !dataField || !clearButton) {
            return;
        }

        var ctx = canvas.getContext('2d');
        if (!ctx) {
            return;
        }

        var drawing = false;
        var signed = false;
        var last = null;
        var activePointerId = null;

        canvas.style.touchAction = 'none';
        canvas.style.msTouchAction = 'none';

        function setPen() {
            ctx.lineWidth = 2.2;
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';
            ctx.strokeStyle = '#0E6F5B';
            ctx.fillStyle = '#0E6F5B';
        }

        function resizeCanvas(preserve) {
            var rect = canvas.getBoundingClientRect();
            if (!rect.width) return;

            var oldImage = null;
            if (preserve && signed && canvas.width && canvas.height) {
                oldImage = document.createElement('canvas');
                oldImage.width = canvas.width;
                oldImage.height = canvas.height;
                oldImage.getContext('2d').drawImage(canvas, 0, 0);
            }

            var cssHeight = rect.height || 180;
            var ratio = Math.max(1, window.devicePixelRatio || 1);
            canvas.width = Math.round(rect.width * ratio);
            canvas.height = Math.round(cssHeight * ratio);
            ctx.setTransform(ratio, 0, 0, ratio, 0, 0);
            setPen();

            if (oldImage) {
                ctx.drawImage(oldImage, 0, 0, oldImage.width, oldImage.height, 0, 0, rect.width, cssHeight);
                save();
            }
        }

        function point(clientX, clientY) {
            var rect = canvas.getBoundingClientRect();
            return { x: clientX - rect.left, y: clientY - rect.top };
        }

        function begin(p) {
            drawing = true;
            last = p;
        }

        function stroke(p) {
            if (!drawing || !last) return;
            ctx.beginPath();
            ctx.moveTo(last.x, last.y);
            ctx.lineTo(p.x, p.y);
            ctx.stroke();
            last = p;
            signed = true;
        }

        function dot() {
            if (!last || signed) return;
            ctx.beginPath();
            ctx.arc(last.x, last.y, 1.2, 0, Math.PI * 2);
            ctx.fill();
            signed = true;
        }

        function save() {
            dataField.value = signed ? canvas.toDataURL('image/png') : '';
        }

        function finish() {
            if (!drawing) return;
            dot();
            save();
            drawing = false;
            last = null;
            activePointerId = null;
        }

        function clearSignature() {
            var rect = canvas.getBoundingClientRect();
            ctx.clearRect(0, 0, rect.width || canvas.width, rect.height || 180);
            drawing = false;
            signed = false;
            last = null;
            activePointerId = null;
            dataField.value = '';
        }

        function prevent(e) {
            if (e.cancelable) e.preventDefault();
        }

        if (window.PointerEvent) {
            canvas.addEventListener('pointerdown', function (e) {
                if (e.pointerType === 'mouse' && e.button !== 0) return;
                activePointerId = e.pointerId;
                begin(point(e.clientX, e.clientY));
                if (canvas.setPointerCapture) {
                    try { canvas.setPointerCapture(e.pointerId); } catch (ignore) {}
                }
                prevent(e);
            }, { passive: false });

            canvas.addEventListener('pointermove', function (e) {
                if (!drawing || (activePointerId !== null && e.pointerId !== activePointerId)) return;
                stroke(point(e.clientX, e.clientY));
                save();
                prevent(e);
            }, { passive: false });

            function endPointer(e) {
                if (!drawing || (activePointerId !== null && e.pointerId !== activePointerId)) return;
                finish();
                if (canvas.releasePointerCapture) {
                    try { canvas.releasePointerCapture(e.pointerId); } catch (ignore) {}
                }
                prevent(e);
            }

            canvas.addEventListener('pointerup', endPointer, { passive: false });
            canvas.addEventListener('pointercancel', endPointer, { passive: false });
        } else {
            canvas.addEventListener('mousedown', function (e) {
                if (e.button !== 0) return;
                begin(point(e.clientX, e.clientY));
                prevent(e);
            }, false);
            window.addEventListener('mousemove', function (e) {
                if (!drawing) return;
                stroke(point(e.clientX, e.clientY));
                save();
                prevent(e);
            }, false);
            window.addEventListener('mouseup', function (e) {
                if (!drawing) return;
                finish();
                prevent(e);
            }, false);

            canvas.addEventListener('touchstart', function (e) {
                var t = e.touches && e.touches[0];
                if (!t) return;
                begin(point(t.clientX, t.clientY));
                prevent(e);
            }, { passive: false });
            canvas.addEventListener('touchmove', function (e) {
                if (!drawing) return;
                var t = e.touches && e.touches[0];
                if (!t) return;
                stroke(point(t.clientX, t.clientY));
                save();
                prevent(e);
            }, { passive: false });
            canvas.addEventListener('touchend', function (e) {
                finish();
                prevent(e);
            }, { passive: false });
            canvas.addEventListener('touchcancel', function (e) {
                finish();
                prevent(e);
            }, { passive: false });
        }

        clearButton.addEventListener('click', function (e) {
            clearSignature();
            prevent(e);
        });

        if (form) {
            form.addEventListener('reset', function () {
                window.setTimeout(clearSignature, 0);
            });

            form.addEventListener('submit', function (e) {
                save();
                if (!dataField.value) {
                    e.preventDefault();
                    alert(canvas.getAttribute('data-required-message') || 'Please sign inside the signature box before submitting.');
                    canvas.focus && canvas.focus();
                }
            });
        }

        resizeCanvas(false);
        var resizeTimer;
        window.addEventListener('resize', function () {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function () { resizeCanvas(true); }, 150);
        });
    });
}());
