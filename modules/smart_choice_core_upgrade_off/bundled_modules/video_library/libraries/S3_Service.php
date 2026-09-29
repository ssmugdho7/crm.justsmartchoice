<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APP_MODULES_PATH . 'video_library/vendor/autoload.php';

// Make sure Composer's autoloader is loaded.
// If $config['composer_autoload'] = TRUE; in config.php, this line is often not strictly needed here,
// but it acts as a safeguard if the autoloader isn't picked up globally for some reason.
// require_once FCPATH . 'vendor/autoload.php';

use Aws\S3\S3Client;
use Aws\Exception\AwsException;
use Aws\S3\Exception\S3Exception; // More specific S3 exceptions

class S3_Service {

    protected $CI;
    protected $s3;
    protected $bucket_name;
    protected $region;

    public function __construct()
    {
        $this->CI =& get_instance();

        // Load S3 configuration from a custom config file

        $aws_access_key_id     = get_option('vl_s3_access_key');
        $aws_secret_access_key = get_option('vl_s3_secret_key');
        $this->bucket_name     = get_option('vl_s3_bucket');
        $this->region          = get_option('vl_s3_region');

        if (empty($aws_access_key_id) || empty($aws_secret_access_key) || empty($this->bucket_name) || empty($this->region)) {
            throw new Exception("AWS S3 configuration is incomplete.");
        }

        try {
            // Initialize the S3Client
            $this->s3 = new S3Client([
                'version'     => 'latest', // Use the latest API version
                'region'      => $this->region,
                'credentials' => [
                    'key'    => $aws_access_key_id,
                    'secret' => $aws_secret_access_key,
                ],
                // Optional: Set a higher connect timeout if you experience frequent timeouts
                // 'http' => [
                //     'connect_timeout' => 5, // seconds
                //     'timeout'         => 10, // seconds
                // ],
            ]);
        } catch (AwsException $e) {
            // Log and re-throw the exception for better error handling in your controller
            log_activity('S3_Service Initialization Error: ' . $e->getMessage());
            throw new Exception("Failed to initialize S3 service: " . $e->getMessage());
        }
    }

    /**
     * Uploads a file to S3.
     *
     * @param string $local_file_path The full path to the local file (e.g., /tmp/your_video.mp4).
     * @param string $s3_key The desired key (path/filename) for the object in the S3 bucket (e.g., 'videos/my_video.mp4').
     * @param string $content_type The MIME type of the file (e.g., 'video/mp4', 'image/jpeg'). Required.
     * @param string $acl Access Control List (e.g., 'private', 'public-read'). Defaults to 'private'.
     * @return string|false The URL of the uploaded file on success, or false on failure.
     */
    public function upload_file(string $local_file_path, string $s3_key, string $content_type, string $acl = 'private')
    {
        if (!file_exists($local_file_path)) {
            log_activity('S3_Service: Local file not found: ' . $local_file_path);
            return false;
        }

        try {
            $result = $this->s3->putObject([
                'Bucket'     => $this->bucket_name,
                'Key'        => $s3_key,
                'SourceFile' => $local_file_path, // SDK handles streaming large files from SourceFile
                'ACL'        => $acl, // 'private' or 'public-read'
                'ContentType' => $content_type, // Crucial for browser playback
            ]);

            // Check if the upload was successful and ObjectURL is present
            if (isset($result['ObjectURL'])) {
                log_activity('File uploaded to S3 successfully: ' . (string)$result['ObjectURL']);
                return (string) $result['ObjectURL'];
            }
        } catch (S3Exception $e) {
            log_activity('S3_Service Upload Error (S3Exception): ' . $e->getMessage() . ' - AWS Request ID: ' . $e->getAwsRequestId());
            return false;
        } catch (AwsException $e) {
            log_activity('S3_Service Upload Error (AwsException): ' . $e->getMessage());
            return false;
        }
        return false;
    }

    /**
     * Generates a pre-signed URL for a private object for a limited time.
     *
     * @param string $s3_key The key (path/filename) of the file in the S3 bucket.
     * @param int $expiration_minutes How long the URL is valid in minutes.
     * @return string|false The pre-signed URL on success, or false on failure.
     */
    public function get_presigned_url(string $s3_key, int $expiration_minutes = 5)
    {
        try {
            $command = $this->s3->getCommand('GetObject', [
                'Bucket' => $this->bucket_name,
                'Key'    => $s3_key
            ]);

            $request = $this->s3->createPresignedRequest($command, '+' . $expiration_minutes . ' minutes');
            return (string) $request->getUri();
        } catch (S3Exception $e) {
            log_activity('S3_Service Presigned URL Error (S3Exception): ' . $e->getMessage() . ' - AWS Request ID: ' . $e->getAwsRequestId());
            return false;
        } catch (AwsException $e) {
            log_activity('S3_Service Presigned URL Error (AwsException): ' . $e->getMessage());
            return false;
        }
        return false;
    }

    /**
     * Deletes a file from S3.
     *
     * @param string $s3_key The key (path/filename) of the file to delete in the S3 bucket.
     * @return bool True on success, false on failure.
     */
    public function delete_file(string $s3_key)
    {
        try {
            $this->s3->deleteObject([
                'Bucket' => $this->bucket_name,
                'Key'    => $s3_key,
            ]);
            log_activity('info', 'File deleted from S3 successfully: ' . $s3_key);
            return true;
        } catch (S3Exception $e) {
            log_activity('S3_Service Delete Error (S3Exception): ' . $e->getMessage() . ' - AWS Request ID: ' . $e->getAwsRequestId());
            return false;
        } catch (AwsException $e) {
            log_activity('S3_Service Delete Error (AwsException): ' . $e->getMessage());
            return false;
        }
        return false;
    }

    /**
     * Checks if an object exists in the S3 bucket.
     *
     * @param string $s3_key The key (path/filename) of the object to check.
     * @return bool True if the object exists, false otherwise.
     */
    public function does_object_exist(string $s3_key)
    {
        try {
            return $this->s3->doesObjectExist($this->bucket_name, $s3_key);
        } catch (AwsException $e) {
            log_activity('S3_Service Object Exists Check Error: ' . $e->getMessage());
            return false;
        }
    }
}