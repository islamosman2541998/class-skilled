// Library uploader used by the asset picker and the media library.
// Files are sent one by one with Livewire's $wire.upload (progress per file),
// then the component method `storeUpload(name, meta)` creates the Asset.
// Videos: duration is read from metadata and a poster frame can be captured
// from the <video> into a canvas and uploaded as an image first. Several videos
// get their poster one after another (the panel shows the next one).
export function registerUploader(Alpine) {
    Alpine.data('assetUploader', ({ maxImageMb = 10, maxVideoMb = 60, maxDocMb = 20, accept = [], messages = {} } = {}) => {
        // $wire and element of the uploader root: `this.$wire` / `this.$dispatch` inside a handler from the
        // poster panel belong to an element that is removed when the panel closes, so they never arrive.
        let wire = null;
        let root = null;

        return {
            dragging: false,
            queue: [],
            video: null,
            accept,

            init() {
                wire = this.$wire;
                root = this.$root;
                // Tells the enclosing picker whether files are still on their way (it stays open meanwhile).
                this.$watch('queue', () => {
                    root.dispatchEvent(new CustomEvent('uploader-busy', { bubbles: true, detail: this.pending() }));
                });
            },

            pending() {
                return this.queue.some((i) => ['waiting', 'poster', 'uploading', 'processing'].includes(i.status));
            },

            kindOf(file) {
                if (['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) return 'image';
                if (['video/mp4', 'video/webm'].includes(file.type)) return 'video';
                if (file.type === 'application/pdf') return 'document';
                return null;
            },

            validate(file) {
                const kind = this.kindOf(file);
                if (!kind || (this.accept.length && !this.accept.includes(kind))) return messages.type;
                const limit = { image: maxImageMb, video: maxVideoMb, document: maxDocMb }[kind];
                if (file.size > limit * 1024 * 1024) return (messages.size || '').replace(':max', limit);
                return null;
            },

            drop(event) {
                this.dragging = false;
                this.add(event.dataTransfer.files);
            },

            add(fileList) {
                Array.from(fileList || []).forEach((file) => {
                    const item = { id: crypto.randomUUID?.() ?? String(Math.random()), file, name: file.name, kind: this.kindOf(file), progress: 0, status: 'waiting', error: null };
                    item.error = this.validate(file);
                    if (item.error) item.status = 'error';
                    this.queue.push(item);
                    if (item.kind === 'video' && !item.error) {
                        item.status = 'poster';
                    }
                });
                this.nextPoster();
                this.next();
            },

            // Shows the poster panel for the next video still waiting for one.
            nextPoster() {
                if (this.video) return;
                const item = this.queue.find((i) => i.status === 'poster');
                if (!item) return;
                item.url = URL.createObjectURL(item.file);
                item.ready = false;
                this.video = item;
            },

            // The panel is re-created for each video (x-if), so the <video> element is fresh.
            finishPoster(item) {
                if (item.url) URL.revokeObjectURL(item.url);
                item.url = null;
                item.status = 'waiting';
                this.video = null;
                this.$nextTick(() => {
                    this.nextPoster();
                    this.next();
                });
            },

            readDuration(el) {
                if (this.video) this.video.duration = Math.round(el.duration || 0);
            },

            markReady(el) {
                if (this.video && el.videoWidth > 0) this.video.ready = true;
            },

            capturePoster(el) {
                const item = this.video;
                if (!item || !el.videoWidth) return;
                const canvas = document.createElement('canvas');
                canvas.width = el.videoWidth;
                canvas.height = el.videoHeight;
                canvas.getContext('2d').drawImage(el, 0, 0, canvas.width, canvas.height);
                canvas.toBlob((blob) => {
                    if (blob) {
                        item.posterFile = new File([blob], item.name.replace(/\.[^.]+$/, '') + '-poster.jpg', { type: 'image/jpeg' });
                    }
                    this.finishPoster(item);
                }, 'image/jpeg', 0.88);
            },

            skipPoster() {
                if (this.video) this.finishPoster(this.video);
            },

            busy() {
                return this.queue.some((i) => ['uploading', 'processing'].includes(i.status));
            },

            async next() {
                if (this.busy()) return;
                const item = this.queue.find((i) => i.status === 'waiting');
                if (!item) {
                    if (!this.video && this.queue.length && this.queue.every((i) => ['done', 'error'].includes(i.status))) {
                        root.dispatchEvent(new CustomEvent('uploads-finished', { bubbles: true }));
                    }
                    return;
                }
                item.status = 'uploading';
                try {
                    let posterId = null;
                    if (item.posterFile) {
                        await this.send('posterUpload', item.posterFile, item, 0.3);
                        // The server makes the poster's image sizes now (a few seconds): show it as processing.
                        item.status = 'processing';
                        posterId = await wire.storeUpload('posterUpload', { kind: 'image' });
                        item.status = 'uploading';
                    }
                    await this.send('upload', item.file, item, posterId ? 0.7 : 1, posterId ? 0.3 : 0);
                    item.status = 'processing';
                    const assetId = await wire.storeUpload('upload', { kind: item.kind, duration: item.duration ?? null, poster_asset_id: posterId });
                    item.status = assetId ? 'done' : 'error';
                    item.progress = 100;
                    if (!assetId) item.error = messages.failed;
                } catch (error) {
                    item.status = 'error';
                    item.error = messages.failed;
                }
                this.next();
            },

            send(property, file, item, share = 1, offset = 0) {
                return new Promise((resolve, reject) => {
                    wire.upload(
                        property,
                        file,
                        () => resolve(),
                        () => reject(new Error('upload failed')),
                        (event) => {
                            item.progress = Math.round((offset + (event.detail.progress / 100) * share) * 100);
                        },
                    );
                });
            },

            clearFinished() {
                this.queue = this.queue.filter((i) => !['done', 'error'].includes(i.status));
            },
        };
    });
}
