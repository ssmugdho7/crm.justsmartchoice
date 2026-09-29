(function () {
    'use strict';

    function initSignaturePad() {
        var canvas = document.getElementById('signature-pad');
        var signatureInput = document.getElementById('signature-data');
        var clearButton = document.getElementById('clear-signature');
        var form = document.querySelector('.sc-job-form');

        if (!canvas || !signatureInput || !clearButton) {
            return;
        }

        var ctx = canvas.getContext('2d');
        if (!ctx) {
            return;
        }

        var drawing = false;
        var hasSignature = false;
        var activePointerId = null;
        var lastPoint = null;

        function configureContext() {
            ctx.lineWidth = 2.2;
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';
            ctx.strokeStyle = '#0E6F5B';
        }

        function resizeCanvas(preserve) {
            var rect = canvas.getBoundingClientRect();
            if (!rect.width || !rect.height) {
                return;
            }

            var snapshot = null;
            if (preserve && hasSignature) {
                snapshot = document.createElement('canvas');
                snapshot.width = canvas.width;
                snapshot.height = canvas.height;
                snapshot.getContext('2d').drawImage(canvas, 0, 0);
            }

            var ratio = Math.max(window.devicePixelRatio || 1, 1);
            canvas.width = Math.max(1, Math.round(rect.width * ratio));
            canvas.height = Math.max(1, Math.round(rect.height * ratio));

            ctx.setTransform(ratio, 0, 0, ratio, 0, 0);
            configureContext();

            if (snapshot) {
                ctx.drawImage(snapshot, 0, 0, snapshot.width, snapshot.height, 0, 0, rect.width, rect.height);
                updateSignatureValue();
            }
        }

        function pointFromClient(clientX, clientY) {
            var rect = canvas.getBoundingClientRect();
            return {
                x: clientX - rect.left,
                y: clientY - rect.top
            };
        }

        function drawTo(point) {
            if (!lastPoint) {
                lastPoint = point;
                return;
            }

            ctx.beginPath();
            ctx.moveTo(lastPoint.x, lastPoint.y);
            ctx.lineTo(point.x, point.y);
            ctx.stroke();
            lastPoint = point;
            hasSignature = true;
        }

        function updateSignatureValue() {
            signatureInput.value = hasSignature ? canvas.toDataURL('image/png') : '';
        }

        function clearSignature() {
            var rect = canvas.getBoundingClientRect();
            ctx.clearRect(0, 0, rect.width, rect.height);
            signatureInput.value = '';
            hasSignature = false;
            drawing = false;
            activePointerId = null;
            lastPoint = null;
        }

        function pointerDown(event) {
            if (event.pointerType === 'mouse' && event.button !== 0) {
                return;
            }

            drawing = true;
            activePointerId = event.pointerId;
            lastPoint = pointFromClient(event.clientX, event.clientY);

            if (canvas.setPointerCapture) {
                try { canvas.setPointerCapture(event.pointerId); } catch (ignore) {}
            }

            event.preventDefault();
        }

        function pointerMove(event) {
            if (!drawing || (activePointerId !== null && event.pointerId !== activePointerId)) {
                return;
            }

            drawTo(pointFromClient(event.clientX, event.clientY));
            updateSignatureValue();
            event.preventDefault();
        }

        function pointerUp(event) {
            if (!drawing || (activePointerId !== null && event.pointerId !== activePointerId)) {
                return;
            }

            if (lastPoint && !hasSignature) {
                ctx.beginPath();
                ctx.arc(lastPoint.x, lastPoint.y, 1.1, 0, Math.PI * 2);
                ctx.fillStyle = '#0E6F5B';
                ctx.fill();
                hasSignature = true;
            }

            updateSignatureValue();
            drawing = false;
            lastPoint = null;

            if (canvas.releasePointerCapture) {
                try { canvas.releasePointerCapture(event.pointerId); } catch (ignore) {}
            }
            activePointerId = null;
            event.preventDefault();
        }

        function installLegacyEvents() {
            function mouseDown(event) {
                if (event.button !== 0) return;
                drawing = true;
                lastPoint = pointFromClient(event.clientX, event.clientY);
                event.preventDefault();
            }
            function mouseMove(event) {
                if (!drawing) return;
                drawTo(pointFromClient(event.clientX, event.clientY));
                updateSignatureValue();
                event.preventDefault();
            }
            function mouseUp(event) {
                if (!drawing) return;
                updateSignatureValue();
                drawing = false;
                lastPoint = null;
                event.preventDefault();
            }
            function touchPoint(event) {
                var touch = event.touches[0] || event.changedTouches[0];
                return touch ? pointFromClient(touch.clientX, touch.clientY) : null;
            }
            function touchStart(event) {
                var point = touchPoint(event);
                if (!point) return;
                drawing = true;
                lastPoint = point;
                event.preventDefault();
            }
            function touchMove(event) {
                if (!drawing) return;
                var point = touchPoint(event);
                if (!point) return;
                drawTo(point);
                updateSignatureValue();
                event.preventDefault();
            }
            function touchEnd(event) {
                if (!drawing) return;
                updateSignatureValue();
                drawing = false;
                lastPoint = null;
                event.preventDefault();
            }

            canvas.addEventListener('mousedown', mouseDown, false);
            window.addEventListener('mousemove', mouseMove, false);
            window.addEventListener('mouseup', mouseUp, false);
            canvas.addEventListener('touchstart', touchStart, { passive: false });
            canvas.addEventListener('touchmove', touchMove, { passive: false });
            canvas.addEventListener('touchend', touchEnd, { passive: false });
            canvas.addEventListener('touchcancel', touchEnd, { passive: false });
        }

        resizeCanvas(false);

        if (window.PointerEvent) {
            canvas.addEventListener('pointerdown', pointerDown, { passive: false });
            canvas.addEventListener('pointermove', pointerMove, { passive: false });
            canvas.addEventListener('pointerup', pointerUp, { passive: false });
            canvas.addEventListener('pointercancel', pointerUp, { passive: false });
        } else {
            installLegacyEvents();
        }

        clearButton.addEventListener('click', clearSignature);

        if (form) {
            form.addEventListener('reset', function () {
                window.setTimeout(clearSignature, 0);
            });

            form.addEventListener('submit', function (event) {
                updateSignatureValue();
                if (!signatureInput.value) {
                    event.preventDefault();
                    window.alert(canvas.getAttribute('data-required-message') || 'Please sign inside the signature box before submitting.');
                }
            });
        }

        var resizeTimer = null;
        window.addEventListener('resize', function () {
            window.clearTimeout(resizeTimer);
            resizeTimer = window.setTimeout(function () {
                resizeCanvas(true);
            }, 150);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSignaturePad);
    } else {
        initSignaturePad();
    }
}());
