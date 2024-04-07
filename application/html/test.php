<?php
// load the AWS SDK
require 'vendor/autoload.php';
use Aws\BedrockRuntime\BedrockRuntimeClient;


//Create a Bedrock Client
$bedrockRuntimeClient = new BedrockRuntimeClient([
    'region' => 'us-west-2',
    'version' => 'latest'
]);


function invokeLlama2($prompt, $client)
{
# The different model providers have individual request and response formats.
# For the format, ranges, and default values for Meta Llama 2 Chat, refer to:
# https://docs.aws.amazon.com/bedrock/latest/userguide/model-parameters-meta.html

$completion = "";
try {
    $modelId = 'meta.llama2-13b-chat-v1';

    $body = [
        'prompt' => $prompt,
        'temperature' => 0.5,
        'max_gen_len' => 512,
    ];

    #$result = $this->bedrockRuntimeClient->invokeModel([
    $result = $client->invokeModel([
        'contentType' => 'application/json',
        'body' => json_encode($body),
        'modelId' => $modelId,
    ]);

    $response_body = json_decode($result['body']);

    $completion = $response_body->generation;
} catch (Exception $e) {
	// this is just a TEST page, so do not throw errors
    //echo "Error: ({$e->getCode()}) - {$e->getMessage()}\n";
}

return $completion;
}

$response = invokeLlama2("Hello world", $bedrockRuntimeClient);

// TODO:
// Run a bunch of tests here to see if you can connect to AWS
// Then return OK if everything is good
if (strlen($response) > 0) {
	echo "OK";
} else {
	echo "BAD";
}
?>
