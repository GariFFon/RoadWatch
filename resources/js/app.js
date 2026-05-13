import './bootstrap';

import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();

// Maps
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
window.L = L;

// Charts
import Chart from 'chart.js/auto';
window.Chart = Chart;
