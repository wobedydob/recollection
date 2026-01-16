<?php
// Check if built frontend exists
if (file_exists(__DIR__ . '/public/index.html')) {
    // Serve the SPA
    include __DIR__ . '/public/index.html';
} else {
    // Frontend not built yet
    echo '<h1>Recollection</h1>';
    echo '<p>Frontend is not built yet. Run:</p>';
    echo '<pre>cd ' . __DIR__ . '/frontend && npm install && npm run build && cd .. && ./build.sh</pre>';
    echo '<p>Then refresh this page.</p>';
}
