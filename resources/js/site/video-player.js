// Custom video player (Alpine `videoPlayer`): a native <video> with our own glass controls
// (play, seek with buffered range, time, mute + volume, full screen; auto-hide while playing).
// Bubbles `media-play` / `media-ended` so a gallery can hold or resume its autoplay, and
// pauses on `media-stop` (sent by the gallery to slides that leave the stage).
const pad = (n) => String(n).padStart(2, '0');

export function formatTime(seconds) {
    if (!Number.isFinite(seconds) || seconds < 0) return '0:00';
    const s = Math.floor(seconds);
    const h = Math.floor(s / 3600);
    const m = Math.floor((s % 3600) / 60);
    return h > 0 ? `${h}:${pad(m)}:${pad(s % 60)}` : `${m}:${pad(s % 60)}`;
}

export function registerVideoPlayer(Alpine) {
    Alpine.data('videoPlayer', () => ({
        started: false,
        playing: false,
        waiting: false,
        muted: false,
        volume: 1,
        current: 0,
        duration: 0,
        buffered: 0,
        idle: false,
        fullscreen: false,
        idleTimer: null,
        init() {
            const video = this.$refs.video;
            this.duration = Number(video.dataset.duration) || 0;
            const emit = (name) => this.$root.dispatchEvent(new CustomEvent(name, { bubbles: true }));
            const sync = () => {
                if (Number.isFinite(video.duration) && video.duration > 0) this.duration = video.duration;
            };

            video.addEventListener('loadedmetadata', sync);
            video.addEventListener('durationchange', sync);
            video.addEventListener('timeupdate', () => (this.current = video.currentTime));
            video.addEventListener('progress', () => {
                const ranges = video.buffered;
                this.buffered = ranges.length && this.duration ? ranges.end(ranges.length - 1) / this.duration : 0;
            });
            video.addEventListener('play', () => {
                this.started = true;
                this.playing = true;
                emit('media-play');
                this.wake();
            });
            video.addEventListener('pause', () => {
                this.playing = false;
                this.idle = false;
            });
            video.addEventListener('ended', () => {
                this.playing = false;
                this.idle = false;
                emit('media-ended');
            });
            video.addEventListener('waiting', () => (this.waiting = true));
            video.addEventListener('playing', () => (this.waiting = false));
            video.addEventListener('canplay', () => (this.waiting = false));
            video.addEventListener('volumechange', () => {
                this.muted = video.muted || video.volume === 0;
                this.volume = video.volume;
            });

            this.onFullscreen = () => {
                this.fullscreen = (document.fullscreenElement || document.webkitFullscreenElement) === this.$root;
            };
            document.addEventListener('fullscreenchange', this.onFullscreen);
            document.addEventListener('webkitfullscreenchange', this.onFullscreen);
        },
        destroy() {
            clearTimeout(this.idleTimer);
            document.removeEventListener('fullscreenchange', this.onFullscreen);
            document.removeEventListener('webkitfullscreenchange', this.onFullscreen);
        },
        get percent() {
            return this.duration ? Math.min(100, (this.current / this.duration) * 100) : 0;
        },
        time: formatTime,
        toggle() {
            const video = this.$refs.video;
            if (video.paused || video.ended) {
                video.play().catch(() => {});
            } else {
                video.pause();
            }
        },
        stop() {
            this.$refs.video.pause();
        },
        // A tap on the picture first brings back hidden controls; a click toggles playback.
        surface(event) {
            if (event.pointerType === 'touch' && this.playing && this.idle) {
                this.wake();
                return;
            }
            this.toggle();
        },
        seek(value) {
            if (!this.duration) return;
            this.$refs.video.currentTime = (Number(value) / 1000) * this.duration;
            this.wake();
        },
        setVolume(value) {
            const video = this.$refs.video;
            video.volume = Number(value);
            video.muted = video.volume === 0;
        },
        toggleMute() {
            const video = this.$refs.video;
            if (video.muted || video.volume === 0) {
                video.muted = false;
                if (video.volume === 0) video.volume = 0.6;
            } else {
                video.muted = true;
            }
        },
        toggleFullscreen() {
            const root = this.$root;
            const video = this.$refs.video;
            if (document.fullscreenElement || document.webkitFullscreenElement) {
                (document.exitFullscreen || document.webkitExitFullscreen)?.call(document);
            } else if (root.requestFullscreen) {
                root.requestFullscreen().catch(() => {});
            } else if (root.webkitRequestFullscreen) {
                root.webkitRequestFullscreen();
            } else if (video.webkitEnterFullscreen) {
                // iPhone: only the <video> element itself can go full screen.
                video.webkitEnterFullscreen();
            }
        },
        // Controls hide after a short idle while playing; any movement brings them back.
        wake() {
            this.idle = false;
            clearTimeout(this.idleTimer);
            this.idleTimer = setTimeout(() => {
                if (this.playing) this.idle = true;
            }, 2600);
        },
        key(event) {
            if (event.target.closest('input, textarea')) return;
            const k = event.key.toLowerCase();
            if (k === 'k' || (k === ' ' && !event.target.closest('button'))) {
                event.preventDefault();
                this.toggle();
            } else if (k === 'm') {
                this.toggleMute();
            } else if (k === 'f') {
                this.toggleFullscreen();
            }
        },
    }));
}
