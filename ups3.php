<?php
require 'vendor/autoload.php';

use Aws\S3\S3Client;

// DIRETORIO QUE SERÁ FEITO UPLOAD
$diretorio = '/Users/rabelo/www/teste';
// PARTE A SER DESCARTADA AO CRIAR ESTRUTURA NO S3
$remove_estrutura_aws = "/Users/rabelo/www/";

// INFORMAÇOES S3.
$aws_key    = 'test';
$aws_secret = 'test';
$aws_region = 'us-east-1';
$aws_bucket = 'tag';
$aws_endpoint = 'http://s3.localhost.localstack.cloud:4566';

$s3Client = new S3Client([
    'credentials' => [
        'key'    => $aws_key,
        'secret' => $aws_secret,
    ],
    'region'                    => $aws_region,
    'use_path_style_endpoint'   => true,
    'endpoint'                  => $aws_endpoint,
]);

if (!is_dir($diretorio)) {
    echo "O diretório especificado não existe.\n";
    return;
}

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($diretorio));

$local = 'log.txt';
$log = fopen($local, "a+");
foreach ($iterator as $arquivo) {
    if ($arquivo->isFile()) {

        $file_Path = $arquivo->getPathname();
        $key = str_replace($remove_estrutura_aws,'',$arquivo->getPathname());

        try {
            $result = $s3Client->putObject([
                'Bucket' => $aws_bucket ,
                'Key'    => $key,
                'Body'   => fopen($file_Path, 'r'),
            ]);

            echo "sucesso: " . $result->get('ObjectURL')."\n";
            $textoLog = basename($arquivo->getPathname())."|".$arquivo->getPathname()."|".$result->get('ObjectURL')."\n";
            fwrite($log, $textoLog);    

        } catch (Aws\S3\Exception\S3Exception $e) {
            echo "Erro ao enviar arquivo\n";
            echo $e->getMessage();
        }
    }
}

fclose($log);
exit();