import Alpine from 'alpinejs';

window.Alpine = Alpine;

import './panitia/medis-manager';
import './panitia/poster-manager';

// Dispatch alpine:init to allow inline scripts to register components
document.dispatchEvent(new CustomEvent('alpine:init'));

Alpine.start();
