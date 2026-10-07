<?php
/**
 * Cliente de Conexión a Supabase para PHP 8.x
 * Soporta Supabase PostgREST API (vía cURL) y PDO PostgreSQL
 */

require_once __DIR__ . '/../config.php';

class SupabaseClient {
    private string $url;
    private string $key;
    private int $lastLatencyMs = 0;
    private ?PDO $pdo = null;

    public function __construct(?string $url = null, ?string $key = null) {
        $this->url = rtrim($url ?? SUPABASE_URL, '/');
        $this->key = $key ?? SUPABASE_KEY;
    }

    public function getLastLatencyMs(): int {
        return $this->lastLatencyMs;
    }

    /**
     * Prueba la conexión hacia la API de Supabase y calcula la latencia en milisegundos
     */
    public function testConnection(): array {
        if (!isSupabaseConfigured()) {
            return [
                'success' => false,
                'mode' => 'DEMO_MOCK',
                'latency_ms' => 0,
                'message' => 'Credenciales de Supabase no configuradas en config.php. Operando en modo demostración local.'
            ];
        }

        $startTime = microtime(true);
        $endpoint = $this->url . '/rest/v1/';

        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'apikey: ' . $this->key,
                'Authorization: Bearer ' . $this->key,
            ],
            CURLOPT_TIMEOUT => 5,
            CURLOPT_SSL_VERIFYPEER => false // Compatible con entornos de desarrollo local en Windows
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        $endTime = microtime(true);
        $latencyMs = (int) round(($endTime - $startTime) * 1000);
        $this->lastLatencyMs = $latencyMs;

        if ($error) {
            return [
                'success' => false,
                'mode' => 'ERROR',
                'latency_ms' => $latencyMs,
                'message' => 'Error de conexión cURL: ' . $error
            ];
        }

        // Supabase REST endpoint raíz responde 200 con OpenAPI spec
        if ($httpCode >= 200 && $httpCode < 300) {
            return [
                'success' => true,
                'mode' => 'SUPABASE_LIVE',
                'latency_ms' => $latencyMs,
                'message' => 'Conexión exitosa a Supabase PostgREST (PostgreSQL DirectQuery).'
            ];
        }

        return [
            'success' => false,
            'mode' => 'HTTP_ERROR',
            'latency_ms' => $latencyMs,
            'message' => 'Supabase respondió con código HTTP: ' . $httpCode . '. Verifica tus credenciales (Project URL y API Key).'
        ];
    }

    /**
     * Consulta registros de una tabla en Supabase (GET /rest/v1/{table})
     */
    public function get(string $table, array $params = []): array {
        if (!isSupabaseConfigured()) {
            return [];
        }

        $startTime = microtime(true);
        $queryString = http_build_query($params);
        $endpoint = $this->url . '/rest/v1/' . $table . ($queryString ? '?' . $queryString : '');

        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'apikey: ' . $this->key,
                'Authorization: Bearer ' . $this->key,
                'Accept: application/json'
            ],
            CURLOPT_TIMEOUT => 8,
            CURLOPT_SSL_VERIFYPEER => false
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $this->lastLatencyMs = (int) round((microtime(true) - $startTime) * 1000);

        if ($httpCode >= 200 && $httpCode < 300 && $response) {
            $data = json_decode($response, true);
            return is_array($data) ? $data : [];
        }

        return [];
    }

    /**
     * Inserta uno o varios registros en una tabla de Supabase (POST /rest/v1/{table})
     */
    public function insert(string $table, array $data, bool $upsert = false): array {
        if (!isSupabaseConfigured()) {
            return ['success' => false, 'error' => 'Supabase no está configurado'];
        }

        $startTime = microtime(true);
        $endpoint = $this->url . '/rest/v1/' . $table;

        $headers = [
            'apikey: ' . $this->key,
            'Authorization: Bearer ' . $this->key,
            'Content-Type: application/json',
            'Prefer: return=representation'
        ];

        if ($upsert) {
            $headers[] = 'Prefer: resolution=merge-duplicates';
        }

        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => false
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        $this->lastLatencyMs = (int) round((microtime(true) - $startTime) * 1000);

        if ($curlError) {
            return ['success' => false, 'error' => $curlError];
        }

        if ($httpCode >= 200 && $httpCode < 300) {
            return [
                'success' => true,
                'data' => json_decode($response, true)
            ];
        }

        return [
            'success' => false,
            'http_code' => $httpCode,
            'error' => $response ?: 'Error al insertar en Supabase'
        ];
    }

    /**
     * Actualiza registros en Supabase con condiciones (PATCH /rest/v1/{table}?{filter})
     */
    public function update(string $table, array $data, array $filters = []): array {
        if (!isSupabaseConfigured()) {
            return ['success' => false, 'error' => 'Supabase no está configurado'];
        }

        $startTime = microtime(true);
        $queryString = http_build_query($filters);
        $endpoint = $this->url . '/rest/v1/' . $table . ($queryString ? '?' . $queryString : '');

        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'PATCH',
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'apikey: ' . $this->key,
                'Authorization: Bearer ' . $this->key,
                'Content-Type: application/json',
                'Prefer: return=representation'
            ],
            CURLOPT_TIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => false
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $this->lastLatencyMs = (int) round((microtime(true) - $startTime) * 1000);

        if ($httpCode >= 200 && $httpCode < 300) {
            return ['success' => true, 'data' => json_decode($response, true)];
        }

        return ['success' => false, 'http_code' => $httpCode, 'error' => $response];
    }

    /**
     * Elimina registros en Supabase con condiciones (DELETE /rest/v1/{table}?{filter})
     */
    public function delete(string $table, array $filters = []): array {
        if (!isSupabaseConfigured()) {
            return ['success' => false, 'error' => 'Supabase no está configurado'];
        }

        $startTime = microtime(true);
        $queryString = http_build_query($filters);
        $endpoint = $this->url . '/rest/v1/' . $table . ($queryString ? '?' . $queryString : '');

        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'DELETE',
            CURLOPT_HTTPHEADER => [
                'apikey: ' . $this->key,
                'Authorization: Bearer ' . $this->key,
                'Prefer: return=representation'
            ],
            CURLOPT_TIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => false
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $this->lastLatencyMs = (int) round((microtime(true) - $startTime) * 1000);

        return ['success' => $httpCode >= 200 && $httpCode < 300];
    }
}
