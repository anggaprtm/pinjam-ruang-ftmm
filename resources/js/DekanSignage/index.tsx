import React from 'react';
import ReactDOM from 'react-dom/client';
import DekanPage from './DekanPage';
import '../bootstrap';// sesuaikan path CSS-mu

ReactDOM.createRoot(document.getElementById('root')!).render(
    <React.StrictMode>
        <DekanPage />
    </React.StrictMode>
);