// Library uploader used by the asset picker and the media library.
// Files are sent one by one with Livewire's $wire.upload (progress per file),
// then the component method `storeUpload(name, meta)` creates the Asset.
// Videos: duration is read from metadata and a poster frame can be captured
// from the <video> into a canvas and uploaded as an image first.
export function registerUploader(Alpine) {
    Alpine.data('assetUploader', ({ maxImageMb = 10, maxVideoMb = 60, maxDocMb = 20, accept = [], messages = {} } = {}) => ({
        dragging: false,
        queue: [],
        video: null,
        accept,

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
                    this.prepareVideo(item);
                }
            });
            this.next();
        },

        prepareVideo(item) {
            item.status = 'poster';
            item.url = URL.createObjectURL(item.file);
            this.video = item;
        },

        readDuration(el) {
            if (this.video) this.video.duration = Math.round(el.duration || 0);
        },

        capturePoster(el) {
            const item = this.video;
            if (!item) return;
            const canvas = document.createElement('canvas');
            canvas.width = el.videoWidth;
            canvas.height = el.videoHeight;
            canvas.getContext('2d').drawImage(el, 0, 0, canvas.width, canvas.height);
            canvas.toBlob((blob) => {
                item.posterFile = new File([blob], item.name.replace(/\.[^.]+$/, '') + '-poster.jpg', { type: 'image/jpeg' });
                item.status = 'waiting';
                this.video = null;
                this.next();
            }, 'image/jpeg', 0.88);
        },

        skipPoster() {
            if (!this.video) return;
            this.video.status = 'waiting';
            this.video = null;
            this.next();
        },

        busy() {
            return this.queue.some((i) => ['uploading', 'processing'].includes(i.status));
        },

        async next() {
            if (this.busy()) return;
            const item = this.queue.find((i) => i.status === 'waiting');
            if (!item) {
                if (this.queue.length && this.queue.every((i) => ['done', 'error'].includes(i.status))) {
                    this.$dispatch('uploads-finished');
                }
                return;
            }
            item.status = 'uploading';
            try {
                let posterId = null;
                if (item.posterFile) {
                    await this.send('posterUpload', item.posterFile, item, 0.3);
                    posterId = await this.$wire.storeUpload('posterUpload', { kind: 'image' });
                }
                await this.send('upload', item.file, item, posterId ? 0.7 : 1, posterId ? 0.3 : 0);
                item.status = 'processing';
                const assetId = await this.$wire.storeUpload('upload', { kind: item.kind, duration: item.duration ?? null, poster_asset_id: posterId });
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
                this.$wire.upload(
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
    }));
}
