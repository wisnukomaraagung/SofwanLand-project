/**
 * Upload / kamera / OCR kuitansi — modul barang masuk
 */
(function (global) {
    'use strict';

    const TESS_VER = '5.1.1';
    const TESS_CDN_DIST = `https://cdn.jsdelivr.net/npm/tesseract.js@${TESS_VER}/dist`;
    const TESS_CDN_CORE = `https://cdn.jsdelivr.net/npm/tesseract.js-core@${TESS_VER}`;

    let config = { ocrEnabled: false, assetBase: '' };
    let currentMode = 'upload';
    let stream = null;
    let ocrWorkerPromise = null;
    let cameraReady = false;

    function $(id) {
        return document.getElementById(id);
    }

    function setOcrStatus(message, type) {
        const status = $('ocr-status');
        if (!status) return;
        status.style.display = 'block';
        const colors = {
            info: '#b8860b',
            success: '#27ae60',
            error: '#c0392b',
        };
        status.style.color = colors[type] || colors.info;
        status.innerText = message;
    }

    function hideOcrStatus(delayMs) {
        const status = $('ocr-status');
        if (!status) return;
        setTimeout(() => {
            status.style.display = 'none';
            status.style.color = '#b8860b';
        }, delayMs || 4000);
    }

    function updateOcrProgress(m) {
        if (!config.ocrEnabled) return;
        if (m.status === 'loading tesseract core') {
            setOcrStatus('Memuat engine OCR...', 'info');
        } else if (m.status === 'initializing tesseract') {
            setOcrStatus('Menginisialisasi OCR...', 'info');
        } else if (m.status === 'loading language traineddata') {
            setOcrStatus('Mengunduh data bahasa (pertama kali bisa agak lama)...', 'info');
        } else if (m.status === 'recognizing text' && typeof m.progress === 'number') {
            setOcrStatus(`Memindai kuitansi... ${Math.round(m.progress * 100)}%`, 'info');
        }
    }

    function getTesseractPaths() {
        const localBase = (config.assetBase || global.BARANG_OCR_ASSETS || '').replace(/\/$/, '');
        if (localBase) {
            return {
                workerPath: `${localBase}/worker.min.js`,
                corePath: localBase,
                workerBlobURL: false,
            };
        }
        return {
            workerPath: `${TESS_CDN_DIST}/worker.min.js`,
            corePath: TESS_CDN_CORE,
            workerBlobURL: true,
        };
    }

    async function getOcrWorker() {
        if (typeof global.Tesseract === 'undefined') {
            throw new Error('Tesseract.js belum dimuat. Muat ulang halaman.');
        }
        if (!ocrWorkerPromise) {
            const paths = getTesseractPaths();
            ocrWorkerPromise = global.Tesseract.createWorker('ind+eng', 1, {
                workerPath: paths.workerPath,
                corePath: paths.corePath,
                langPath: 'https://tessdata.projectnaptha.com/4.0.0',
                workerBlobURL: paths.workerBlobURL,
                gzip: true,
                logger: updateOcrProgress,
            });
        }
        return ocrWorkerPromise;
    }

    function preprocessImage(file) {
        return new Promise((resolve, reject) => {
            const img = new Image();
            const url = URL.createObjectURL(file);
            img.onload = () => {
                URL.revokeObjectURL(url);
                const maxW = 2000;
                let w = img.width;
                let h = img.height;
                if (w > maxW) {
                    h = Math.round((h * maxW) / w);
                    w = maxW;
                }
                const canvas = document.createElement('canvas');
                canvas.width = w;
                canvas.height = h;
                const ctx = canvas.getContext('2d');
                ctx.fillStyle = '#fff';
                ctx.fillRect(0, 0, w, h);
                ctx.drawImage(img, 0, 0, w, h);

                const imageData = ctx.getImageData(0, 0, w, h);
                const d = imageData.data;
                for (let i = 0; i < d.length; i += 4) {
                    let g = 0.299 * d[i] + 0.587 * d[i + 1] + 0.114 * d[i + 2];
                    g = Math.min(255, Math.max(0, (g - 128) * 1.35 + 128));
                    d[i] = d[i + 1] = d[i + 2] = g;
                }
                ctx.putImageData(imageData, 0, 0);

                canvas.toBlob(
                    (blob) => {
                        if (!blob) {
                            reject(new Error('Gagal memproses gambar'));
                            return;
                        }
                        resolve(new File([blob], 'kuitansi.jpg', { type: 'image/jpeg' }));
                    },
                    'image/jpeg',
                    0.92
                );
            };
            img.onerror = () => {
                URL.revokeObjectURL(url);
                reject(new Error('Gagal membaca gambar'));
            };
            img.src = url;
        });
    }

    function parseIndonesianNumber(raw) {
        if (!raw) return null;
        const s = String(raw).trim().replace(/\s/g, '');
        if (/^\d{1,3}(\.\d{3})+(,\d+)?$/.test(s)) {
            return parseInt(s.replace(/\./g, '').split(',')[0], 10);
        }
        if (/^\d{1,3}(,\d{3})+(\.\d+)?$/.test(s)) {
            return parseInt(s.replace(/,/g, '').split('.')[0], 10);
        }
        const digits = s.replace(/[^\d]/g, '');
        if (!digits) return null;
        const n = parseInt(digits, 10);
        return Number.isFinite(n) ? n : null;
    }

    function collectAmounts(text) {
        const amounts = [];
        const patterns = [
            /(?:total|jumlah\s*bayar|grand\s*total|sub\s*total|bayar|amount)[^\d]{0,20}(?:rp\.?\s*)?([\d][\d.,\s]*)/gi,
            /(?:rp\.?|idr)\s*([\d][\d.,\s]*)/gi,
        ];
        for (const pat of patterns) {
            let m;
            while ((m = pat.exec(text)) !== null) {
                const n = parseIndonesianNumber(m[1]);
                if (n && n >= 100 && n < 1e12) amounts.push(n);
            }
        }
        return amounts;
    }

    function parseReceiptText(text) {
        const lines = text
            .split(/\r?\n/)
            .map((l) => l.trim())
            .filter((l) => l.length > 1);

        const amounts = collectAmounts(text);
        const bestPrice = amounts.length ? Math.max(...amounts) : 0;

        const hargaSatuan = $('harga_satuan');
        const jumlahInput = $('jumlah');
        if (hargaSatuan && bestPrice > 0) {
            const qty = jumlahInput ? parseInt(jumlahInput.value, 10) || 0 : 0;
            if (qty > 1 && bestPrice / qty >= 100) {
                hargaSatuan.value = Math.round(bestPrice / qty);
            } else {
                hargaSatuan.value = bestPrice;
            }
        }

        const supplier = $('supplier');
        const skipLine = /^(pt\.?|cv\.?|toko|warung|total|tanggal|tgl|qty|jumlah|no\.?|struk|invoice|kasir|terima\s*kasih|thank)/i;
        if (supplier && !supplier.value) {
            for (const line of lines.slice(0, 10)) {
                if (
                    line.length >= 3 &&
                    line.length <= 80 &&
                    !skipLine.test(line) &&
                    !/^\d+[.,\d\s]*$/.test(line) &&
                    !/^(rp|idr)/i.test(line)
                ) {
                    supplier.value = line;
                    break;
                }
            }
        }

        const tanggal = $('tanggal');
        const dateMatch = text.match(/\b(\d{1,2})[\/\-.](\d{1,2})[\/\-.](\d{2,4})\b/);
        if (tanggal && dateMatch) {
            let d = parseInt(dateMatch[1], 10);
            let mo = parseInt(dateMatch[2], 10);
            let y = parseInt(dateMatch[3], 10);
            if (y < 100) y += 2000;
            if (mo >= 1 && mo <= 12 && d >= 1 && d <= 31) {
                tanggal.value = `${y}-${String(mo).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
            }
        }

        const keterangan = $('keterangan');
        if (keterangan && !keterangan.value.trim()) {
            const snippet = lines.slice(0, 4).join('\n');
            if (snippet) keterangan.value = snippet.substring(0, 500);
        }

        setOcrStatus('Pemindaian selesai. Periksa data di form sebelum menyimpan.', 'success');
        hideOcrStatus(6000);
    }

    async function runOCR(imageFile) {
        if (!config.ocrEnabled) return;
        if (!$('harga_satuan')) return;

        setOcrStatus('Menyiapkan pemindaian...', 'info');

        try {
            const processed = await preprocessImage(imageFile);
            const worker = await getOcrWorker();
            const ret = await worker.recognize(processed);
            const text = (ret.data && ret.data.text) ? ret.data.text : '';
            if (!text.trim()) {
                setOcrStatus('Teks tidak terbaca. Coba foto lebih terang dan fokus.', 'error');
                hideOcrStatus(5000);
                return;
            }
            parseReceiptText(text);
        } catch (err) {
            console.error('OCR error:', err);
            setOcrStatus(
                'Gagal memindai: ' + (err.message || 'periksa koneksi internet lalu coba lagi'),
                'error'
            );
            hideOcrStatus(6000);
        }
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
            setOcrStatus('File harus berupa gambar (JPG, PNG, dll).', 'error');
            hideOcrStatus(4000);
            return;
        }
        const reader = new FileReader();
        reader.onload = (ev) => {
            showPreview(ev.target.result);
            if (config.ocrEnabled) runOCR(file);
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
            setOcrStatus('Tidak dapat mengakses kamera: ' + err.message, 'error');
            hideOcrStatus(5000);
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
            setOcrStatus('Kamera belum siap. Tunggu 1–2 detik lalu ketuk lagi.', 'error');
            hideOcrStatus(3000);
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
                if (config.ocrEnabled) runOCR(file);
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
        init(options) {
            config = {
                ocrEnabled: false,
                assetBase: global.BARANG_OCR_ASSETS || '',
                ...options,
            };
            bindDropzone();
            switchUploadMode('upload');
            if (config.ocrEnabled && typeof global.Tesseract === 'undefined') {
                setOcrStatus('Library OCR gagal dimuat. Muat ulang halaman.', 'error');
            }
        },
    };

    window.addEventListener('beforeunload', () => {
        stopCamera();
        if (ocrWorkerPromise) {
            ocrWorkerPromise.then((w) => w.terminate()).catch(() => {});
        }
    });
})(window);
