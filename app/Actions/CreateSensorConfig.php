<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Node;
use App\Models\NodeSetting;
use App\Services\TotpService;
use ZipArchive;

class CreateSensorConfig
{
    public function __invoke(Node $sensor): string|bool
    {

        $certDir = storage_path('certs');

        $key = "{$certDir}/{$sensor->id}.key";
        $crt = "{$certDir}/{$sensor->id}.crt";
        $csr = "{$certDir}/{$sensor->id}.csr";
        $pfx = "{$certDir}/{$sensor->id}.pfx";
        $intermediaCa = "{$certDir}/intermediate-ca.crt";
        $intermediaCaKey = "{$certDir}/intermediate-ca.key";
        $caChain = "{$certDir}/ca-chain.crt";
        $subject = escapeshellarg("/C=US/ST=YourState/L=YourCity/O=YourOrg/CN={$sensor->name}");

        if (! file_exists($intermediaCa)) {
            abort(500, 'Intermediate CA file not found');
        }

        if (! file_exists($intermediaCaKey)) {
            abort(500, 'Intermediate CA file not found');
        }

        if (! file_exists($caChain)) {
            abort(500, 'CA chain file not found');
        }

        shell_exec("openssl genrsa -out {$key} 2048");
        if (! file_exists($key)) {
            abort(500, 'Private key file not found');
        }

        shell_exec("openssl req -new -key {$key} -out {$csr} -subj {$subject} -addext \"subjectAltName=DNS:{$sensor->name}\"");
        if (! file_exists($csr)) {
            abort(500, 'CSR file not found');
        }

        shell_exec("openssl x509 -req -in {$csr} -CA {$intermediaCa} -CAkey {$intermediaCaKey} -CAcreateserial -out {$crt} -days 365 -extfile <(printf \"subjectAltName=DNS:{$sensor->name}\nkeyUsage=digitalSignature,keyEncipherment\nextendedKeyUsage=serverAuth,clientAuth\")");
        if (! file_exists($crt)) {
            abort(500, 'Certificate file not found');
        }

        shell_exec("openssl pkcs12 -export -out {$pfx} -inkey {$key} -in {$crt} -certfile {$caChain} -name ".escapeshellarg($sensor->name).' -passout pass: -keypbe AES-256-CBC -certpbe AES-256-CBC -macalg SHA256');
        if (! file_exists($pfx)) {
            abort(500, 'Certificate pfx not found');
        }

        unlink($csr);
        unlink($key);
        unlink($crt);

        // create TOTP token
        $token = TotpService::generateSecret();
        NodeSetting::create(['node_id' => $sensor->id, 'name' => 'totp_secret', 'value' => $token, 'cast' => 'string']);

        $zip = new ZipArchive;
        $zipFileName = tempnam(sys_get_temp_dir(), 'zip');
        if ($zip->open($zipFileName, ZipArchive::CREATE) === true) {
            $zip->addFile($pfx, 'certificate.pfx');
            $zip->addFile($caChain, 'ca-chain.crt');
            $zip->addFromString('config.json', json_encode([
                'token' => $token,
                'name' => $sensor->name,
            ]));
            $zip->close();

            unlink($pfx);

            return $zipFileName;
        } else {
            return false;
        }
    }
}
