<?php

class AzureBlobClient
{
    private $accountName = null;
    private $secretKey = null;
    private $host = null;
    private $bucket = null;

    public function __construct(string $accountName, string $secretKey, string $host, string $bucket) {
        $this->accountName = $accountName;
        $this->secretKey = $secretKey;
        $this->host = $host;
        $this->bucket = $bucket;
    }
    private function makeCall(
        $method, 
        $remotePath, 
        $postdata = '', 
        $bucketPath = false, 
        $contentType = 'application/octet-stream'
    ) {
        if ($bucketPath === false) {
            $bucketPath = '/'. $this->bucket;
        }

        $date_utc = new \DateTime("now", new \DateTimeZone("UTC"));
        $date = $date_utc->format(\DateTime::RFC850);
        $blobType = 'x-ms-blob-type:BlockBlob';
        $dateStr = 'x-ms-date:' . $date;
        $date = '';
        $versionStr = 'x-ms-version:2015-02-21';
        $contentLen = strlen($postdata);
        $contentLenEncode = $contentLen == 0 ? '' : $contentLen;

        $parsedPath = parse_url($remotePath);
        $paramPath = '';        
        if (isset($parsedPath['path'])) {
            $paramPath .= $parsedPath['path'];
        }
        if (isset($parsedPath['query'])) {
            $params = explode('&', $parsedPath['query']);
            foreach ($params as $val) {
                $parts = explode('=', $val);
                $paramPath .= "\n" . $parts[0] . ":" . $parts[1];
            }
        }
        
        $sendstr = "$method\n\n\n$contentLenEncode\n\n$contentType\n$date\n\n\n\n\n\n$blobType\n$dateStr\n$versionStr\n/" . $this->accountName . $bucketPath . $paramPath;
       
        $signature = base64_encode(hash_hmac('sha256', $sendstr, base64_decode($this->secretKey), true));
    
        $context = stream_context_create([
            'http' => [
                'method'  => $method,
                'header'  =>
                    "Host: " . $this->accountName . '.blob.' . $this->host . "\r\n" .
                    "Date: $date\r\n" .
                    "Content-Length: " . $contentLen . "\r\n" .
                    "Content-Type: $contentType\r\n" .
                    "$blobType\r\n" .
                    "$dateStr\r\n" .
                    "$versionStr\r\n" .
                    "Authorization: SharedKey " . $this->accountName . ":$signature\r\n",
                'content' => $postdata,
            ]
        ]);

        return @file_get_contents(
            'http://' . $this->accountName . '.blob.' . $this->host . ':10000' .
            $bucketPath . $remotePath, false, $context
        );
    }
    public function getData($remotePath) {
        return $this->makeCall('GET', $remotePath);
    }
    public function putData($remotePath, $data) {
        $response = $this->makeCall('PUT', $remotePath, $data);
        return $response !== false;
    }
    public function exists($remotePath) {
        $response = $this->makeCall('HEAD', $remotePath);
        return $response !== false;
    }
    public function deleteData($remotePath) {
        $response = $this->makeCall('DELETE', $remotePath);
        return $response !== false;
    }
    public function listBuckets() {
        $response = $this->makeCall('GET', '?comp=list', '', '/');

        $xml = new SimpleXMLElement($response);
        $list = array();
        foreach($xml->Containers->Container as $container) {
            array_push($list, $container->Name->__toString());
        }
        return '[' . implode(',', $list) . ']';
    }
    public function createBucket() {
        $response = $this->makeCall('PUT', '?restype=container');
        return $response !== false;
    }
    public function deleleBuckets() {
        $response = $this->makeCall('DELETE', '?restype=container');
        return $response !== false;
    }
}

/*
docker run -p 10000:10000 -v /tmp:/workspace mcr.microsoft.com/azure-storage/azurite \
    azurite-blob --blobHost 0.0.0.0 --blobPort 10000 --debug /workspace/debug.log 
*/
