<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateVapidKeys extends Command
{
    protected $signature = 'vapid:generate';
    protected $description = 'Generate VAPID public/private key pair for Web Push notifications';

    public function handle(): int
    {
        $this->ensureOpensslConfig();

        $this->info('Generating VAPID keys...');

        try {
            // Try the library method first (works on most systems)
            $keys = $this->generateViaLibrary();
        } catch (\Throwable $e) {
            $this->warn('Library method failed: ' . $e->getMessage());
            $this->info('Falling back to OpenSSL CLI...');

            // Fall back to OpenSSL CLI (works on systems where PHP's OpenSSL has config issues)
            try {
                $keys = $this->generateViaCli();
            } catch (\Throwable $e2) {
                $this->error('Failed to generate VAPID keys: ' . $e2->getMessage());
                return Command::FAILURE;
            }
        }

        $this->line('');
        $this->info('Add these lines to your .env file:');
        $this->line('───────────────────────────────────────');
        $this->line("VAPID_PUBLIC_KEY={$keys['publicKey']}");
        $this->line("VAPID_PRIVATE_KEY={$keys['privateKey']}");
        $this->line('───────────────────────────────────────');
        $this->line('');
        $this->warn('Keep your VAPID_PRIVATE_KEY secret! Never commit it to version control.');

        return Command::SUCCESS;
    }

    /**
     * If OPENSSL_CONF is missing, set it so child processes (OpenSSL CLI)
     * can find openssl.cnf.  Does NOT affect the already-loaded OpenSSL
     * extension — putenv() is too late for that — but the CLI fallback
     * and any shell_exec'd openssl commands will see it.
     */
    private function ensureOpensslConfig(): void
    {
        if (getenv('OPENSSL_CONF') && file_exists(getenv('OPENSSL_CONF'))) {
            return;
        }

        $candidates = [
            PHP_BINDIR . '\\openssl.cnf',
            PHP_BINDIR . '\\..\\ssl\\openssl.cnf',
            'C:\\laragon\\bin\\php\\php-8.3.30-Win32-vs16-x64\\openssl.cnf',
            'C:\\Program Files\\Git\\usr\\ssl\\openssl.cnf',
            getenv('SystemRoot') . '\\System32\\openssl.cnf',
        ];

        foreach ($candidates as $path) {
            if (file_exists($path)) {
                putenv('OPENSSL_CONF=' . $path);
                return;
            }
        }
    }

    /**
     * Generate VAPID keys using the minishlink/web-push library.
     */
    private function generateViaLibrary(): array
    {
        return \Minishlink\WebPush\VAPID::createVapidKeys();
    }

    /**
     * Generate VAPID keys using the OpenSSL CLI tool as a fallback.
     * This handles environments where PHP's OpenSSL extension has
     * configuration issues (e.g., OpenSSL 3.x on Windows).
     */
    private function generateViaCli(): array
    {
        // Find openssl binary
        $openssl = $this->findOpensslBinary();
        if (!$openssl) {
            throw new \RuntimeException('Could not find openssl binary in PATH.');
        }

        // Create temp directory
        $tmpDir = sys_get_temp_dir() . '/vapid-' . uniqid();
        if (!mkdir($tmpDir, 0700, true) && !is_dir($tmpDir)) {
            throw new \RuntimeException('Failed to create temp directory: ' . $tmpDir);
        }

        $privateKeyPath = $tmpDir . '/private.pem';
        $publicKeyPath  = $tmpDir . '/public.pem';

        try {
            // Step 1: Generate EC private key (prime256v1 = P-256)
            $cmd = sprintf(
                '"%s" ecparam -genkey -name prime256v1 -noout -out "%s" 2>&1',
                $openssl,
                $privateKeyPath
            );
            $output = shell_exec($cmd);
            if (!file_exists($privateKeyPath)) {
                throw new \RuntimeException('Failed to generate EC key. Output: ' . ($output ?? '(none)'));
            }

            // Step 2: Extract public key
            $cmd = sprintf(
                '"%s" ec -in "%s" -pubout -out "%s" 2>&1',
                $openssl,
                $privateKeyPath,
                $publicKeyPath
            );
            $output = shell_exec($cmd);
            if (!file_exists($publicKeyPath)) {
                throw new \RuntimeException('Failed to extract public key. Output: ' . ($output ?? '(none)'));
            }

            // Step 3: Read the PEM files and extract raw key bytes
            $privatePem = file_get_contents($privateKeyPath);
            $publicPem  = file_get_contents($publicKeyPath);

            if (!$privatePem || !$publicPem) {
                throw new \RuntimeException('Failed to read generated key files.');
            }

            // Step 4: Convert PEM to VAPID keys using PHP's OpenSSL
            // If openssl_pkey_get_private works, use it; otherwise parse DER manually
            $privateKey = openssl_pkey_get_private($privatePem);
            if ($privateKey) {
                $details = openssl_pkey_get_details($privateKey);
                if (!$details || !isset($details['ec'])) {
                    throw new \RuntimeException('Failed to extract EC key details.');
                }
                // x and y are already binary (32 bytes each), not hex
                $vapidPublicKey  = $this->base64urlEncode("\x04" . $details['ec']['x'] . $details['ec']['y']);
                $vapidPrivateKey = $this->base64urlEncode(str_pad($details['ec']['d'], 32, "\0", STR_PAD_LEFT));
                openssl_pkey_free($privateKey);
            } else {
                // Pure PHP fallback: parse the DER-encoded private key manually
                $vapidKeys = $this->parseEcPrivateKeyPem($privatePem, $publicPem);
                $vapidPublicKey  = $vapidKeys['publicKey'];
                $vapidPrivateKey = $vapidKeys['privateKey'];
            }

            return [
                'publicKey'  => $vapidPublicKey,
                'privateKey' => $vapidPrivateKey,
            ];
        } finally {
            // Clean up temp files
            $this->cleanupTemp($tmpDir);
        }
    }

    /**
     * Find the OpenSSL binary in PATH or common locations.
     */
    private function findOpensslBinary(): ?string
    {
        // Check PATH first
        $paths = explode(PATH_SEPARATOR, getenv('PATH') ?: '');

        // Add common Windows locations
        $paths = array_merge($paths, [
            'C:\\Program Files\\Git\\mingw64\\bin',
            'C:\\Program Files\\Git\\usr\\bin',
            'C:\\laragon\\bin\\php\\php-8.3.30-Win32-vs16-x64',
        ]);

        foreach ($paths as $path) {
            $candidates = [
                $path . DIRECTORY_SEPARATOR . 'openssl.exe',
                $path . DIRECTORY_SEPARATOR . 'openssl',
            ];
            foreach ($candidates as $candidate) {
                if (file_exists($candidate) && is_executable($candidate)) {
                    return $candidate;
                }
            }
        }

        // Last resort: try shell command
        $result = shell_exec('where openssl 2>nul');
        if ($result) {
            $lines = explode("\n", trim($result));
            $first = trim($lines[0]);
            if ($first && file_exists($first)) {
                return $first;
            }
        }

        return null;
    }

    /**
     * Parse an EC private key PEM and public key PEM to extract VAPID keys
     * using pure PHP (no OpenSSL extension required).
     */
    private function parseEcPrivateKeyPem(string $privatePem, string $publicPem): array
    {
        // Normalize line endings (handle Windows \r\n)
        $privatePem = str_replace("\r\n", "\n", $privatePem);
        $privatePem = str_replace("\r", "\n", $privatePem);
        $publicPem = str_replace("\r\n", "\n", $publicPem);
        $publicPem = str_replace("\r", "\n", $publicPem);

        // Extract base64 content from PEM (strip headers/footers)
        $privateB64 = preg_replace('/^-+[^-]+-+\s*/m', '', $privatePem);
        $privateB64 = str_replace(["\n", " "], '', $privateB64);
        $publicB64 = preg_replace('/^-+[^-]+-+\s*/m', '', $publicPem);
        $publicB64 = str_replace(["\n", " "], '', $publicB64);

        $privateDer = base64_decode($privateB64);
        $publicDer  = base64_decode($publicB64);

        if (!$privateDer || !$publicDer) {
            throw new \RuntimeException('Failed to decode PEM data.');
        }

        // Parse SEC1 EC private key structure (RFC 5915)
        // Format: SEQUENCE { INTEGER (version), OCTET STRING (private key), [0] EXPLICIT { OID }, [1] EXPLICIT { BIT STRING (public key) } }
        // We can extract the private key from the OCTET STRING
        // And the public key from the SubjectPublicKeyInfo

        // For the private key: find the 32-byte private key value
        // After the version integer (typically 1 byte), the next element is the private key OCTET STRING
        $privateKeyBytes = $this->extractEcPrivateKeyFromDer($privateDer);

        // For the public key: SubjectPublicKeyInfo contains the uncompressed EC point (0x04 + x + y)
        $publicKeyBytes = $this->extractEcPublicKeyFromDer($publicDer);

        if (!$privateKeyBytes || !$publicKeyBytes) {
            throw new \RuntimeException('Failed to extract key bytes from DER.');
        }

        if (strlen($privateKeyBytes) !== 32) {
            throw new \RuntimeException(
                'Invalid private key length: ' . strlen($privateKeyBytes) . ' (expected 32)'
            );
        }

        if (strlen($publicKeyBytes) !== 65) {
            throw new \RuntimeException(
                'Invalid public key length: ' . strlen($publicKeyBytes) . ' (expected 65)'
            );
        }

        return [
            'publicKey'  => $this->base64urlEncode($publicKeyBytes),
            'privateKey' => $this->base64urlEncode($privateKeyBytes),
        ];
    }

    /**
     * Extract the 32-byte EC private key from a SEC1 DER-encoded private key.
     */
    private function extractEcPrivateKeyFromDer(string $der): ?string
    {
        // Simple DER parser for SEC1 ECPrivateKey structure
        $pos = 0;
        $len = strlen($der);

        // Expect SEQUENCE (0x30)
        if ($pos >= $len || ord($der[$pos]) !== 0x30) return null;
        $pos++;
        // Read sequence length
        $seqLen = $this->readDerLength($der, $pos);
        $end = $pos + $seqLen;

        // Version (INTEGER, should be 1)
        if ($pos >= $end || ord($der[$pos]) !== 0x02) return null;
        $pos++;
        $intLen = $this->readDerLength($der, $pos);
        $pos += $intLen;

        // Private key (OCTET STRING, 0x04)
        if ($pos >= $end || ord($der[$pos]) !== 0x04) return null;
        $pos++;
        $keyLen = $this->readDerLength($der, $pos);
        if ($pos + $keyLen > $end) return null;
        $key = substr($der, $pos, $keyLen);
        $pos += $keyLen;

        // Pad to 32 bytes
        return str_pad($key, 32, "\0", STR_PAD_LEFT);
    }

    /**
     * Extract the 65-byte uncompressed EC public key from a SubjectPublicKeyInfo DER.
     */
    private function extractEcPublicKeyFromDer(string $der): ?string
    {
        // SubjectPublicKeyInfo: SEQUENCE { SEQUENCE { OID, ... }, BIT STRING }
        $pos = 0;
        $len = strlen($der);

        // Expect SEQUENCE (0x30)
        if ($pos >= $len || ord($der[$pos]) !== 0x30) return null;
        $pos++;
        $seqLen = $this->readDerLength($der, $pos);
        $end = $pos + $seqLen;

        // Skip AlgorithmIdentifier SEQUENCE
        if ($pos >= $end || ord($der[$pos]) !== 0x30) return null;
        $pos++;
        $algLen = $this->readDerLength($der, $pos);
        $pos += $algLen;

        // BIT STRING containing the public key
        if ($pos >= $end || ord($der[$pos]) !== 0x03) return null;
        $pos++;
        $bitLen = $this->readDerLength($der, $pos);
        // Skip unused bits byte
        $pos++;
        $keyLen = $bitLen - 1;
        if ($pos + $keyLen > $end) return null;

        // The key should start with 0x04 (uncompressed)
        $key = substr($der, $pos, $keyLen);

        // If it's not exactly 65 bytes, it might be in a different format
        if (strlen($key) === 65 && ord($key[0]) === 0x04) {
            return $key;
        }

        // Some PEMs might strip the leading 0x00 byte
        if (strlen($key) === 66 && ord($key[0]) === 0x00 && ord($key[1]) === 0x04) {
            return substr($key, 1);
        }

        return $key ?: null;
    }

    /**
     * Read a DER TLV length value and advance the position.
     */
    private function readDerLength(string $data, int &$pos): int
    {
        if ($pos >= strlen($data)) return 0;
        $byte = ord($data[$pos]);
        $pos++;

        if ($byte < 0x80) {
            return $byte;
        }

        $numBytes = $byte & 0x7F;
        $length = 0;
        for ($i = 0; $i < $numBytes; $i++) {
            if ($pos >= strlen($data)) return 0;
            $length = ($length << 8) | ord($data[$pos]);
            $pos++;
        }
        return $length;
    }

    /**
     * Base64url encode (RFC 4648 section 5, no padding).
     */
    private function base64urlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Recursively remove a temp directory.
     */
    private function cleanupTemp(string $path): void
    {
        if (!is_dir($path)) return;

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $file) {
            if ($file->isDir()) {
                @rmdir($file->getRealPath());
            } else {
                @unlink($file->getRealPath());
            }
        }

        @rmdir($path);
    }
}
