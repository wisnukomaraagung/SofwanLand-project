/**
 * Upload / kamera / OCR kuitansi — modul barang masuk
 */
(function (global) {
    'use strict';

    let currentMode = 'upload';
    let stream = null;
    let cameraReady = false;

    function $(id) {
        return document.getElementById(id);
    }


    function showPreview(dataUrl) {
        const imgPreview = $('image-preview');
        const uploadUi = $('upload-ui');
        if (imgPreview) {
            imgPreview.src = dataUrl;
            imgPreview.style.display = 'block';
        }
        if (uploadUi) uploadUi.style.display = 'none';
    }

    function applyFileToInput(file) {
        const input = $('file-input');
        if (!input) return;
        const dt = new DataTransfer();
        dt.items.add(file);
        input.files = dt.files;
    }

    function handleFileSelect(e) {
        const file = (e.target && e.target.files && e.target.files[0]) || null;
        if (!file) return;
        if (!file.type.startsWith('image/')) {
            alert('File harus berupa gambar (JPG, PNG, dll).');
            return;
        }
        const reader = new FileReader();
        reader.onload = (ev) => {
            showPreview(ev.target.result);
        };
        reader.readAsDataURL(file);
    }

    global.switchUploadMode = function switchUploadMode(mode) {
        currentMode = mode;
        const tabUpload = $('tab-upload');
        const tabKamera = $('tab-kamera');
        if (tabUpload) tabUpload.classList.toggle('active', mode === 'upload');
        if (tabKamera) tabKamera.classList.toggle('active', mode === 'kamera');

        const ui = $('upload-ui');
        const video = $('kamera-preview');
        const img = $('image-preview');
        const dropzone = $('dropzone');
        if (!dropzone) return;

        if (mode === 'kamera') {
            if (ui) ui.style.display = 'none';
            if (img) img.style.display = 'none';
            if (video) video.style.display = 'block';
            dropzone.onclick = takeSnapshot;
            startCamera();
        } else {
            stopCamera();
            if (video) video.style.display = 'none';
            if (img && img.src && img.src.indexOf('data:') === 0) {
                img.style.display = 'block';
                if (ui) ui.style.display = 'none';
            } else {
                if (ui) ui.style.display = 'block';
                if (img) img.style.display = 'none';
            }
            dropzone.onclick = () => {
                const input = $('file-input');
                if (input) input.click();
            };
        }
    };

    async function startCamera() {
        cameraReady = false;
        const video = $('kamera-preview');
        if (!video) return;
        try {
            stopCamera();
            stream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: { ideal: 'environment' }, width: { ideal: 1920 } },
                audio: false,
            });
            video.srcObject = stream;
            video.onloadedmetadata = () => {
                cameraReady = video.videoWidth > 0 && video.videoHeight > 0;
            };
            await video.play();
        } catch (err) {
            alert('Tidak dapat mengakses kamera: ' + err.message);
            switchUploadMode('upload');
        }
    }

    function stopCamera() {
        if (stream) {
            stream.getTracks().forEach((t) => t.stop());
            stream = null;
        }
        cameraReady = false;
    }

    function takeSnapshot() {
        const video = $('kamera-preview');
        if (!video || !cameraReady || !video.videoWidth) {
            alert('Kamera belum siap. Tunggu 1–2 detik lalu ketuk lagi.');
            return;
        }

        const canvas = document.createElement('canvas');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        canvas.getContext('2d').drawImage(video, 0, 0);

        const dataUrl = canvas.toDataURL('image/jpeg', 0.92);
        showPreview(dataUrl);

        canvas.toBlob(
            (blob) => {
                if (!blob) return;
                const file = new File([blob], 'snapshot.jpg', { type: 'image/jpeg' });
                applyFileToInput(file);
                switchUploadMode('upload');
            },
            'image/jpeg',
            0.92
        );
    }

    global.syncBarangId = function syncBarangId(input) {
        const datalist = $('barang-list');
        const hiddenInput = $('id_barang');
        if (!datalist || !hiddenInput) return;
        hiddenInput.value = '0';
        for (let i = 0; i < datalist.options.length; i++) {
            if (datalist.options[i].value === input.value) {
                hiddenInput.value = datalist.options[i].getAttribute('data-id');
                break;
            }
        }
    };

    global.handleFileSelect = handleFileSelect;

    function bindDropzone() {
        const dropzone = $('dropzone');
        const fileInput = $('file-input');
        if (!dropzone) return;

        dropzone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropzone.classList.add('dragover');
        });
        dropzone.addEventListener('dragleave', () => dropzone.classList.remove('dragover'));
        dropzone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropzone.classList.remove('dragover');
            if (currentMode !== 'upload') return;
            if (e.dataTransfer.files && e.dataTransfer.files[0]) {
                applyFileToInput(e.dataTransfer.files[0]);
                handleFileSelect({ target: fileInput });
            }
        });

        if (fileInput) {
            fileInput.addEventListener('change', handleFileSelect);
        }
    }

    global.BarangKuitansi = {
        init() {
            bindDropzone();
            switchUploadMode('upload');
        },
    };

    window.addEventListener('beforeunload', () => {
        stopCamera();
    });
})(window);
