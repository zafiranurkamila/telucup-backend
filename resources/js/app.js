import Alpine from 'alpinejs';

window.Alpine = Alpine;

import './panitia/medis-manager';
import './panitia/poster-manager';
import './panitia/galeri-manager';
import './panitia/kontingen-manager';
import './panitia/sports-manager';
import './pic-kontingen/anggota-manager';
import './pic-kontingen/registrasi-manager';
import './components/bracket-manager';

// Dispatch alpine:init to allow inline scripts to register components
document.dispatchEvent(new CustomEvent('alpine:init'));

Alpine.start();
