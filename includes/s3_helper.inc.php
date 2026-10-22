<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../env.php';

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

function getS3Client(): S3Client
{
    $region = getenv('AWS_REGION') ?: 'af-south-1';
    return new S3Client([
        'version' => 'latest',
        'region'  => $region,
    ]);
}

function uploadFileToS3(string $localFilePath, string $s3Key)
{
    $bucket = getenv('S3_BUCKET') ?: null;
    if (!$bucket) {
        error_log("uploadFileToS3: S3_BUCKET environment variable is not set");
        return false;
    }

    $s3 = getS3Client();
    try {
        $s3->putObject([
            'Bucket'     => $bucket,
            'Key'        => $s3Key,
            'SourceFile' => $localFilePath,
        ]);
        return $s3Key;
    } catch (AwsException $e) {
        error_log("S3 upload failed: " . $e->getMessage());
        return false;
    }
}