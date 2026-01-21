import '../bootstrap';
import React from 'react';
import ReactDOM from 'react-dom/client';
import App from './App';

// Pastikan string di dalam kurung ini SAMA dengan ID di Blade
const rootElement = document.getElementById('signage-root'); 

if (!rootElement) {
    // Ini error yang kamu lihat di browser tadi
    throw new Error('Could not find root element to mount to'); 
}

ReactDOM.createRoot(rootElement).render(
    <React.StrictMode>
        <App />
    </React.StrictMode>
);