/**
 * Kingdom of Lyonesse - RPG Audio & Sound FX Engine
 * Web Audio API sound effects with a looping background music track.
 */

(function () {
    'use strict';

    class RPGAudioEngine {
        constructor() {
            this.ctx = null;
            this.sfxGain = null;
            this.masterGain = null;
            this.isBgmPlaying = false;

            // Settings from localStorage
            this.musicEnabled = localStorage.getItem('lyonesse_music_enabled') !== 'false';
            this.sfxEnabled = localStorage.getItem('lyonesse_sfx_enabled') !== 'false';
            this.volume = parseFloat(localStorage.getItem('lyonesse_volume') || '0.5');
            this.bgmAudio = new Audio('/audio/first-memory.wav');
            this.bgmAudio.loop = true;
            this.bgmAudio.preload = 'none';
            this.bgmAudio.volume = this.volume * 0.35;

            this.initOnFirstInteraction = this.initOnFirstInteraction.bind(this);
            this.attachEventListeners();
            this.createHUD();
        }

        initContext() {
            if (!this.ctx) {
                const AudioCtx = window.AudioContext || window.webkitAudioContext;
                if (!AudioCtx) return;
                this.ctx = new AudioCtx();

                this.masterGain = this.ctx.createGain();
                this.masterGain.gain.setValueAtTime(this.volume, this.ctx.currentTime);
                this.masterGain.connect(this.ctx.destination);

                this.sfxGain = this.ctx.createGain();
                this.sfxGain.gain.setValueAtTime(this.sfxEnabled ? 0.8 : 0, this.ctx.currentTime);
                this.sfxGain.connect(this.masterGain);

            } else if (this.ctx.state === 'suspended') {
                this.ctx.resume();
            }
        }

        initOnFirstInteraction() {
            this.initContext();
            if (this.musicEnabled) this.startAmbientBGM();
            document.removeEventListener('click', this.initOnFirstInteraction);
            document.removeEventListener('keydown', this.initOnFirstInteraction);
            document.removeEventListener('touchstart', this.initOnFirstInteraction);
        }

        attachEventListeners() {
            document.addEventListener('click', this.initOnFirstInteraction, { once: true });
            document.addEventListener('keydown', this.initOnFirstInteraction, { once: true });
            document.addEventListener('touchstart', this.initOnFirstInteraction, { once: true });

            // Global click sound delegation
            document.addEventListener('click', (e) => {
                const target = e.target.closest('a, button, input[type="submit"], .main_menu_button, .button_200px, .btn, .tower-preview-btn');
                if (target) {
                    // Check if it's an attack/fight button
                    const text = (target.innerText || target.value || '').toLowerCase();
                    const href = (target.getAttribute('href') || '').toLowerCase();
                    if (text.includes('attack') || text.includes('fight') || text.includes('strike') || href.includes('fight')) {
                        this.playAttack();
                    } else if (text.includes('buy') || text.includes('exchange') || text.includes('claim') || text.includes('ruby')) {
                        this.playCoin();
                    } else {
                        this.playClick();
                    }
                }
            });

            // Hover micro-sound
            document.addEventListener('mouseover', (e) => {
                const target = e.target.closest('a, button, input[type="submit"], .main_menu_button, .button_200px');
                if (target && this.sfxEnabled && this.ctx && this.ctx.state === 'running') {
                    this.playHover();
                }
            });
        }

        // =================================================================
        // Sound Effects (SFX)
        // =================================================================

        playHover() {
            if (!this.sfxEnabled || !this.ctx) return;
            const now = this.ctx.currentTime;
            const osc = this.ctx.createOscillator();
            const gain = this.ctx.createGain();

            osc.type = 'sine';
            osc.frequency.setValueAtTime(440, now);
            osc.frequency.exponentialRampToValueAtTime(520, now + 0.04);

            gain.gain.setValueAtTime(0.015, now);
            gain.gain.exponentialRampToValueAtTime(0.001, now + 0.04);

            osc.connect(gain);
            gain.connect(this.sfxGain);

            osc.start(now);
            osc.stop(now + 0.04);
        }

        playClick() {
            this.initContext();
            if (!this.sfxEnabled || !this.ctx) return;
            const now = this.ctx.currentTime;

            // Wood / Stone tactile click
            const osc = this.ctx.createOscillator();
            const gain = this.ctx.createGain();

            osc.type = 'triangle';
            osc.frequency.setValueAtTime(320, now);
            osc.frequency.exponentialRampToValueAtTime(140, now + 0.07);

            gain.gain.setValueAtTime(0.18, now);
            gain.gain.exponentialRampToValueAtTime(0.001, now + 0.07);

            osc.connect(gain);
            gain.connect(this.sfxGain);

            osc.start(now);
            osc.stop(now + 0.07);
        }

        playAttack() {
            this.initContext();
            if (!this.sfxEnabled || !this.ctx) return;
            const now = this.ctx.currentTime;

            // Sword blade whoosh + impact
            const osc = this.ctx.createOscillator();
            const gain = this.ctx.createGain();

            osc.type = 'sawtooth';
            osc.frequency.setValueAtTime(800, now);
            osc.frequency.exponentialRampToValueAtTime(80, now + 0.15);

            gain.gain.setValueAtTime(0.25, now);
            gain.gain.exponentialRampToValueAtTime(0.001, now + 0.15);

            // Filter for metal ring
            const filter = this.ctx.createBiquadFilter();
            filter.type = 'bandpass';
            filter.frequency.setValueAtTime(1200, now);
            filter.Q.setValueAtTime(3, now);

            osc.connect(filter);
            filter.connect(gain);
            gain.connect(this.sfxGain);

            osc.start(now);
            osc.stop(now + 0.15);
        }

        playCoin() {
            this.initContext();
            if (!this.sfxEnabled || !this.ctx) return;
            const now = this.ctx.currentTime;

            // High double crystal chime
            const playNote = (freq, delay) => {
                const osc = this.ctx.createOscillator();
                const gain = this.ctx.createGain();

                osc.type = 'sine';
                osc.frequency.setValueAtTime(freq, now + delay);

                gain.gain.setValueAtTime(0.12, now + delay);
                gain.gain.exponentialRampToValueAtTime(0.001, now + delay + 0.18);

                osc.connect(gain);
                gain.connect(this.sfxGain);

                osc.start(now + delay);
                osc.stop(now + delay + 0.18);
            };

            playNote(987.77, 0);      // B5
            playNote(1318.51, 0.06);   // E6
        }

        playVictory() {
            this.initContext();
            if (!this.sfxEnabled || !this.ctx) return;
            const now = this.ctx.currentTime;

            // Fanfare chord progression (C5 -> E5 -> G5 -> C6)
            const notes = [523.25, 659.25, 783.99, 1046.50];
            notes.forEach((freq, idx) => {
                const osc = this.ctx.createOscillator();
                const gain = this.ctx.createGain();

                osc.type = 'triangle';
                osc.frequency.setValueAtTime(freq, now + idx * 0.09);

                gain.gain.setValueAtTime(0.15, now + idx * 0.09);
                gain.gain.exponentialRampToValueAtTime(0.001, now + idx * 0.09 + 0.35);

                osc.connect(gain);
                gain.connect(this.sfxGain);

                osc.start(now + idx * 0.09);
                osc.stop(now + idx * 0.09 + 0.35);
            });
        }

        // =================================================================
        // Background Music
        // =================================================================

        startAmbientBGM() {
            if (!this.musicEnabled || this.isBgmPlaying) return;
            this.isBgmPlaying = true;
            this.bgmAudio.play().catch((error) => {
                this.isBgmPlaying = false;
                console.error('Unable to play background music:', error);
            });
        }

        stopAmbientBGM() {
            this.isBgmPlaying = false;
            this.bgmAudio.pause();
        }

        // =================================================================
        // User Controls & Floating HUD
        // =================================================================

        toggleMusic() {
            this.initContext();
            this.musicEnabled = !this.musicEnabled;
            localStorage.setItem('lyonesse_music_enabled', this.musicEnabled);
            if (this.musicEnabled) {
                this.startAmbientBGM();
            } else {
                this.stopAmbientBGM();
            }
            this.updateHUD();
        }

        toggleSFX() {
            this.initContext();
            this.sfxEnabled = !this.sfxEnabled;
            localStorage.setItem('lyonesse_sfx_enabled', this.sfxEnabled);
            if (this.sfxGain && this.ctx) {
                this.sfxGain.gain.setValueAtTime(this.sfxEnabled ? 0.8 : 0, this.ctx.currentTime);
            }
            if (this.sfxEnabled) {
                this.playCoin();
            }
            this.updateHUD();
        }

        createHUD() {
            if (document.getElementById('rpg-audio-hud')) return;

            const hud = document.createElement('div');
            hud.id = 'rpg-audio-hud';
            hud.className = 'rpg-audio-bar';
            hud.innerHTML = `
                <button type="button" id="audio-btn-music" class="rpg-sound-btn ${this.musicEnabled ? 'active' : ''}" title="Toggle Background Music">
                    <span class="icon">${this.musicEnabled ? '🎵' : '🔇'}</span>
                    <span class="label">BGM</span>
                </button>
                <button type="button" id="audio-btn-sfx" class="rpg-sound-btn ${this.sfxEnabled ? 'active' : ''}" title="Toggle Sound Effects">
                    <span class="icon">${this.sfxEnabled ? '🔊' : '🔈'}</span>
                    <span class="label">SFX</span>
                </button>
            `;

            document.body.appendChild(hud);

            document.getElementById('audio-btn-music').addEventListener('click', (e) => {
                e.stopPropagation();
                this.toggleMusic();
            });

            document.getElementById('audio-btn-sfx').addEventListener('click', (e) => {
                e.stopPropagation();
                this.toggleSFX();
            });
        }

        updateHUD() {
            const musicBtn = document.getElementById('audio-btn-music');
            const sfxBtn = document.getElementById('audio-btn-sfx');

            if (musicBtn) {
                musicBtn.className = `rpg-sound-btn ${this.musicEnabled ? 'active' : ''}`;
                musicBtn.querySelector('.icon').textContent = this.musicEnabled ? '🎵' : '🔇';
            }
            if (sfxBtn) {
                sfxBtn.className = `rpg-sound-btn ${this.sfxEnabled ? 'active' : ''}`;
                sfxBtn.querySelector('.icon').textContent = this.sfxEnabled ? '🔊' : '🔈';
            }
        }
    }

    // Initialize globally
    window.LyonesseAudio = new RPGAudioEngine();
})();
