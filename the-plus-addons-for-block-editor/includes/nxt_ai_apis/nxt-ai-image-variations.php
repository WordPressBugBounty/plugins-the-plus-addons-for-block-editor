<?php
if (!defined("ABSPATH")) exit();

class Nxt_AI_Image_Variations {
    
    public function generate_variations($args = []) {
        $args = wp_parse_args($args, [
            "image" => "",
            "n" => 1,
            "size" => "1024x1024"
        ]);
        
        if (empty($args["image"])) {
            return ["success" => false, "message" => "Image parameter is required"];
        }
        
        $n = absint($args["n"]);
        if ($n < 1 || $n > 10) {
            return ["success" => false, "message" => "Number of variations must be between 1 and 10"];
        }
        
        $settings_raw = Tp_Blocks_Helper::get_extra_option('nxtAiSettings');
        $encrypted = '';
        
        if (is_string($settings_raw)) {
            $encrypted = $settings_raw;
        } elseif (is_array($settings_raw)) {
            if (isset($settings_raw[0]) && is_string($settings_raw[0])) {
                $encrypted = $settings_raw[0];
            } elseif (count($settings_raw) === 1) {
                $encrypted = reset($settings_raw);
            }
        }
        
        if (is_string($encrypted) && !empty($encrypted)) {
            $decrypted = Tp_Blocks_Helper::tpgb_simple_decrypt($encrypted, 'dy');
            $settings = json_decode($decrypted, true);
        } else {
            $settings = [];
        }
        
        $api_key = $settings["chatgptApiKey"];
        $img_enabled = $settings["chatgptEnableImage"];
        
        if ($img_enabled !== true) {
            return ["success" => false, "message" => "AI Image Generation is not enabled"];
        }
        
        if (empty($api_key)) {
            return ["success" => false, "message" => "OpenAI API key is not configured"];
        }
        
        try {
            $img_data = $this->process_to_png($args["image"]);
            
            if (is_wp_error($img_data)) {
                return ["success" => false, "message" => $img_data->get_error_message()];
            }
            
            $api_result = $this->send_request($api_key, $img_data, $n, $args["size"]);
            
            if (is_wp_error($api_result)) {
                return ["success" => false, "message" => $api_result->get_error_message()];
            }
            
            return [
                "success" => true,
                "data" => $api_result["data"],
                "created" => $api_result["created"],
                "total_tokens" => $api_result["total_tokens"]
            ];
        } catch (Exception $e) {
            return ["success" => false, "message" => $e->getMessage()];
        }
    }
    
    private function process_to_png($image) {
        $img_resource = $this->load_resource($image);
        
        if (is_wp_error($img_resource)) {
            return $img_resource;
        }
        
        $width = imagesx($img_resource);
        $height = imagesy($img_resource);
        
        if ($width !== $height) {
            $square = $this->make_square($img_resource, $width, $height);
            imagedestroy($img_resource);
            $img_resource = $square;
        }
        
        ob_start();
        imagepng($img_resource, null, 9);
        $png_data = ob_get_clean();
        imagedestroy($img_resource);
        
        if (strlen($png_data) > 4 * 1024 * 1024) {
            return new WP_Error("size_limit", "Image must be less than 4MB");
        }
        
        return $png_data;
    }
    
    private function load_resource($image) {
        $img_data = null;
        
        if (strpos($image, "data:image") === 0) {
            preg_match("/data:image\/(\w+);base64,(.*)/", $image, $matches);
            
            if (empty($matches[2])) {
                return new WP_Error("invalid_base64", "Invalid base64 image data");
            }
            
            $img_data = base64_decode($matches[2]);
        } elseif (filter_var($image, FILTER_VALIDATE_URL)) {
            $img_data = @file_get_contents($image);
            
            if ($img_data === false) {
                return new WP_Error("fetch_failed", "Failed to fetch image from URL");
            }
        } else {
            return new WP_Error("invalid_format", "Invalid image format. Must be base64 data URL or valid URL");
        }
        
        $resource = @imagecreatefromstring($img_data);
        
        if ($resource === false) {
            return new WP_Error("invalid_image", "Invalid image data");
        }
        
        return $resource;
    }
    
    private function make_square($resource, $width, $height) {
        $size = min($width, $height);
        $x = ($width - $size) / 2;
        $y = ($height - $size) / 2;
        
        $square = imagecreatetruecolor($size, $size);
        
        imagealphablending($square, false);
        imagesavealpha($square, true);
        
        imagecopyresampled($square, $resource, 0, 0, $x, $y, $size, $size, $size, $size);
        
        return $square;
    }
    
    private function send_request($api_key, $img_data, $n, $size) {
        $boundary = wp_generate_password(24, false);
        $eol = "\r\n";
        
        $body = "";
        
        $body .= "--" . $boundary . $eol;
        $body .= 'Content-Disposition: form-data; name="image"; filename="image.png"' . $eol;
        $body .= "Content-Type: image/png" . $eol . $eol;
        $body .= $img_data . $eol;
        
        $body .= "--" . $boundary . $eol;
        $body .= 'Content-Disposition: form-data; name="n"' . $eol . $eol;
        $body .= $n . $eol;
        
        $body .= "--" . $boundary . $eol;
        $body .= 'Content-Disposition: form-data; name="size"' . $eol . $eol;
        $body .= $size . $eol;
        
        $body .= "--" . $boundary . "--" . $eol;
        
        $response = wp_remote_post("https://api.openai.com/v1/images/variations", [
            "headers" => [
                "Authorization" => "Bearer " . $api_key,
                "Content-Type" => "multipart/form-data; boundary=" . $boundary
            ],
            "body" => $body,
            "timeout" => 60,
            "sslverify" => true
        ]);
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if ($code !== 200) {
            $error_msg = isset($data["error"]["message"]) ? $data["error"]["message"] : "Unknown error occurred";
            return new WP_Error("api_error", $error_msg);
        }
        
        return [
            "data" => $data["data"] ?? [],
            "created" => $data["created"] ?? time(),
            "total_tokens" => null
        ];
    }
    
    function nxt_ai_variations($params) {
        $variations = Nxt_AI_Image_Variations::get_instance();
        
        $args = [
            'image' => $params->get_param('image'),
            'n' => intval($params->get_param('n')) ?: 1,
            'size' => $params->get_param('size') ?: '1024x1024'
        ];
        
        return $variations->generate_variations($args);
    }
    
    public static function get_instance() {
        static $instance = null;
        if (null === $instance) {
            $instance = new self();
        }
        return $instance;
    }
}