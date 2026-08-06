<?php

return [
    'timestamp_tolerance' => (int) env('OPENAPI_TIMESTAMP_TOLERANCE', 300),
    'nonce_ttl' => (int) env('OPENAPI_NONCE_TTL', 600),
];
