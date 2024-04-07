<?php
error_reporting(E_ALL);
ini_set('display_error',1);
// load the AWS SDK
require 'vendor/autoload.php';
use Aws\BedrockRuntime\BedrockRuntimeClient;

if ($_SERVER["REQUEST_METHOD"] == "GET") {
	$image_prompt = $_GET["image_prompt"];
}

if (empty($image_prompt)){
	echo "You did not provide an image prompt";
	exit;
}

//Create a Bedrock Client
$bedrockRuntimeClient = new BedrockRuntimeClient([
    'region' => 'us-west-2',
    'version' => 'latest'
]);

//$image_prompt = "stylized image of a cute steampunk robot";

$diffusionSeed = rand(0, 4294967295);
$style_preset = "photographic";

$base64 = invokeStableDiffusion($image_prompt, $diffusionSeed, $style_preset, $bedrockRuntimeClient);
$image_path = saveImage($base64, 'stability.stable-diffusion-xl');
echo "<h2><i>Your super rad image: </i></h2>";
echo "<p><img src=$image_path></img>";
echo "<p>Click <a href=$image_path>here</a></p>";


function saveImage($base64_image_data, $model_id): string
{
	$output_dir = "output";

	if (!file_exists($output_dir)) {
	    mkdir($output_dir);
	}

	$i = 1;
	while (file_exists("$output_dir/$model_id" . '_' . "$i.png")) {
	    $i++;
	}

	$image_data = base64_decode($base64_image_data);

	$file_path = "$output_dir/$model_id" . '_' . "$i.png";

	$file = fopen($file_path, 'wb');
	fwrite($file, $image_data);
	fclose($file);

	return $file_path;
}

function invokeStableDiffusion(string $prompt, int $seed, string $style_preset, $client)
    {
        $base64_image_data = "";

        try {
            $modelId = 'stability.stable-diffusion-xl';

            $body = [
                'text_prompts' => [
                    ['text' => $prompt]
                ],
                'seed' => $seed,
                'cfg_scale' => 10,
                'steps' => 30
            ];

            if ($style_preset) {
                $body['style_preset'] = $style_preset;
            }

            //$result = $this->bedrockRuntimeClient->invokeModel([
            $result = $client->invokeModel([
                'contentType' => 'application/json',
                'body' => json_encode($body),
                'modelId' => $modelId,
            ]);

            $response_body = json_decode($result['body']);

            $base64_image_data = $response_body->artifacts[0]->base64;
        } catch (Exception $e) {
            echo "Error: ({$e->getCode()}) - {$e->getMessage()}\n";
        }

        return $base64_image_data;
}
?>
