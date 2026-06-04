
import Alpine from 'alpinejs';

window.Alpine = Alpine;

// Dispatch alpine:init to allow inline scripts to register components
document.dispatchEvent(new CustomEvent('alpine:init'));

Alpine.start();
