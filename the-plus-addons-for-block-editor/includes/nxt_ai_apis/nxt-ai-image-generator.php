<?php
if (!defined("ABSPATH")) exit();

class Nxt_AI_Image_Generator {
    
    private $type_modifiers = [
        "landscape" => "landscape photography, wide panoramic vista, natural outdoor scenery",
        "seascape" => "seascape photography, open ocean view, dramatic coastal scenery",
        "mountainscape" => "mountain landscape, towering peaks and valleys, alpine scenery",
        "desert_photography" => "desert photography, arid open landscape, rolling sand dunes and heat haze",
        "forest_photography" => "forest photography, dense woodland scene, trees, foliage, and natural light",
        "aerial_landscape" => "aerial landscape view, expansive terrain from above, sweeping natural patterns",
        "night_sky" => "night sky photography, astrophotography, stars, galaxies, and celestial objects",
        "sunset_sunrise" => "sunset or sunrise scene, golden hour lighting, colorful dramatic sky",
        "portrait" => "portrait photography, single human subject, balanced and focused composition",
        "closeup_portrait" => "close-up portrait, detailed facial features, intimate framing and shallow depth",
        "headshot" => "professional headshot, shoulders and face, clean neutral background",
        "group_portrait" => "group portrait, multiple subjects together, well-arranged and cohesive posing",
        "fashion_photography" => "fashion photography, stylish wardrobe, editorial-quality posing and lighting",
        "lifestyle_photo" => "lifestyle photography, candid everyday moment, natural and authentic scene",
        "street_portrait" => "street portrait, human subject in urban setting, environmental and expressive",
        "wildlife" => "wildlife photography, animals in nature, natural behavior in native habitat",
        "pet_portrait" => "pet portrait, domestic animal, personality and charm clearly captured",
        "bird_photography" => "bird photography, detailed avian subject, sharp plumage and graceful posture",
        "underwater_wildlife" => "underwater wildlife photography, marine animals, immersive aquatic environment",
        "safari_shot" => "safari photography, African wildlife, open savanna setting and warm light",
        "street_photography" => "street photography, real urban life, candid moments and spontaneous scenes",
        "architecture" => "architecture photography, building design, clean lines and structural details",
        "cityscape" => "cityscape photography, urban skyline, wide city view and distant horizon",
        "urban_night" => "urban night photography, city lights, glowing signs and evening atmosphere",
        "industrial" => "industrial photography, factories and machinery, gritty urban industrial mood",
        "macro" => "macro photography, extreme close-up, tiny subjects with fine details",
        "closeup" => "close-up shot, detailed view, shallow depth of field and background blur",
        "texture_detail" => "texture detail photography, surface patterns, intricate and tactile details",
        "product_macro" => "product macro photography, close-up product shot, crisp commercial detail",
        "sports" => "sports photography, athletic action, strong energy and peak movement, dynamic movement",
        "action_shot" => "action shot, frozen motion, dynamic composition with clear subject",
        "motion_blur" => "motion blur effect, streaked movement, high-speed dynamic feel",
        "long_exposure" => "long exposure photography, smooth water or light trails, soft motion effect",
        "aerial" => "aerial photography, overhead view, high altitude and broad perspective",
        "drone_shot" => "drone photography, bird's-eye angle, elevated sweeping view",
        "top_down" => "top-down view, flat lay or overhead, subject seen directly from above",
        "bird_eye" => "bird's-eye view, high angle perspective, looking down on the scene",
        "abstract" => "abstract photography, non-literal subject, focus on shapes, colors, and forms",
        "minimalist" => "minimalist composition, few simple elements, strong clean negative space",
        "black_white" => "black and white photography, monochrome tones, focus on light and shadow",
        "high_contrast" => "high contrast photography, bold lights and deep shadows, dramatic tonality",
        "cinematic" => "cinematic still frame, movie-like quality, dramatic lighting and atmosphere",
        "bokeh" => "shallow depth of field, smooth bokeh effect, softly blurred background",
        "hdr" => "HDR photography look, high dynamic range, enhanced detail and vivid tones",
        "tilt_shift" => "tilt-shift effect, miniature-style look, selective focus and blur",
        "still_life" => "still life photography, carefully arranged objects, balanced artistic composition",
        "food" => "food photography, appetizing dishes, professional culinary styling",
        "product" => "product photography, clean commercial shot, neutral background and clarity",
        "flat_lay" => "flat lay photography, neatly organized items, overhead arrangement layout"
    ];
    
    private $style_modifiers = [
        "realistic" => "realistic visual style, natural proportions, lifelike detail and lighting",
        "photorealistic" => "photorealistic rendering, ultra-detailed, indistinguishable from real photo",
        "cinematic" => "cinematic lighting, filmic mood, dramatic composition and depth",
        "film_noir" => "film noir style, high contrast, deep shadows and vintage crime-movie mood",
        "vintage" => "vintage style, retro color palette, slightly aged and nostalgic look",
        "black_white" => "black and white style, rich grayscale tones, emphasis on texture and light",
        "minimalist" => "minimalist style, clean and uncluttered, focus on one main subject",
        "modern" => "modern aesthetic, sleek lines, contemporary and polished design",
        "aesthetic" => "aesthetic composition, visually pleasing balance, stylized artistic mood",
        "fantasy" => "fantasy art, magical atmosphere, imaginative otherworldly elements",
        "surreal" => "surreal imagery, dreamlike scenes, unexpected and unusual combinations",
        "dreamy" => "dreamy atmosphere, soft focus, gentle light and ethereal quality",
        "abstract" => "abstract visual style, non-representational, expressive shapes and colors",
        "concept_art" => "concept art style, detailed designs, production-quality imaginative artwork",
        "digital_painting" => "digital painting style, painterly brushwork, layered artistic rendering",
        "oil_painting" => "oil painting style, textured brushstrokes, classic fine art appearance",
        "watercolor" => "watercolor painting style, soft edges, translucent and flowing colors",
        "pencil_sketch" => "pencil sketch style, hand-drawn lines, monochrome shading and contours",
        "charcoal_drawing" => "charcoal drawing style, bold strokes, dramatic shading and texture",
        "3d_render" => "3D render style, polished computer-generated imagery, realistic lighting",
        "isometric" => "isometric view, angled geometric perspective, technical or game-like layout",
        "voxel_art" => "voxel art style, blocky cube forms, retro 3D pixel appearance",
        "pixel_art" => "pixel art style, low-resolution, retro game pixel aesthetic",
        "low_poly" => "low poly style, faceted geometric shapes, simplified 3D modeling",
        "flat_design" => "flat design style, 2D graphics, no heavy gradients, clean simple shapes",
        "vector_art" => "vector art style, crisp clean lines, scalable graphic illustration",
        "line_art" => "line art style, minimal outlines, simple monochrome or limited color",
        "pop_art" => "pop art style, bold saturated colors, graphic comic-inspired visuals",
        "comic_style" => "comic book style, strong outlines, halftone textures and dynamic framing",
        "anime" => "anime style, Japanese animation look, expressive faces and vibrant colors",
        "manga" => "manga style, black and white line art, dynamic panel-like drawing",
        "cartoon" => "cartoon style, playful and simplified shapes, exaggerated expressions",
        "disney_style" => "Disney animation style, charming expressive characters, polished look",
        "pixar_style" => "Pixar-style 3D character design, appealing proportions and cinematic lighting",
        "studio_ghibli" => "Studio Ghibli style, hand-drawn aesthetic, detailed environments and warmth",
        "cyberpunk" => "cyberpunk aesthetic, neon lights, dense futuristic dystopian cityscape",
        "steampunk" => "steampunk style, Victorian-era influence, gears, brass, and retro technology",
        "futuristic" => "futuristic style, sleek sci-fi design, advanced technology and clean lines",
        "sci_fi" => "science fiction style, space-age concepts, advanced tech and strange worlds",
        "noir" => "noir style, moody atmosphere, shadows, and mysterious storytelling tone",
        "gothic" => "gothic style, dark ornate structures, dramatic and medieval influences",
        "baroque" => "baroque style, ornate decoration, dramatic lighting and rich details",
        "renaissance" => "renaissance art style, classical realism, balanced composition and anatomy",
        "impressionism" => "impressionist style, loose brushwork, strong light and color impressions",
        "expressionism" => "expressionist style, emotional distortion, bold color and form",
        "cubism" => "cubist style, fractured geometric shapes, multiple viewpoints combined",
        "surrealism" => "surrealist art style, dreamlike symbolism, subconscious imagery",
        "hyperrealism" => "hyperrealistic style, extreme detail, almost indistinguishable from reality",
        "hdr" => "HDR visual style, expanded dynamic range, vivid highlights and deep shadows",
        "pastel" => "pastel color palette, soft gentle hues, light and soothing tones",
        "vaporwave" => "vaporwave aesthetic, retro-digital vibe, pink and cyan neon nostalgia",
        "synthwave" => "synthwave style, 1980s-inspired, neon grids, sunsets, and retrowave mood",
        "neon_glow" => "neon glow effect, bright luminous edges, vibrant light accents in the scene"
    ];
    
    private $size_map = [
        "dall-e-3" => ["1:1" => "1024x1024", "16:9" => "1792x1024", "9:16" => "1024x1792"],
        "gpt-image-1" => ["1:1" => "1024x1024", "3:2" => "1536x1024", "2:3" => "1024x1536"],
        "gpt-image-1-mini" => ["1:1" => "1024x1024", "3:2" => "1536x1024", "2:3" => "1024x1536"],
        "imagen-3.0-generate-001" => ["1:1" => "1024x1024", "3:4" => "768x1024", "4:3" => "1024x768", "9:16" => "576x1024", "16:9" => "1024x576"]
    ];
    
    public function generate_image($args = []) {
        $args = wp_parse_args($args, [
            "prompt" => "",
            "model" => "dall-e-2",
            "n" => 1,
            "aspect_ratio" => "1:1",
            "image_type" => "",
            "image_style" => "",
            "original_image" => "",
            "mask_image" => "",
            "image_quality" => ""
        ]);
        
        $prompt = sanitize_text_field($args["prompt"]);
        $model = !empty($args["model"]) ? sanitize_text_field($args["model"]) : "dall-e-2";
        $n = absint($args["n"]);
        $aspect_ratio = sanitize_text_field($args["aspect_ratio"]);
        $image_type = sanitize_text_field($args["image_type"]);
        $image_style = sanitize_text_field($args["image_style"]);
        $image_quality = sanitize_text_field($args["image_quality"]);
        
        if (empty($prompt)) {
            return ["success" => false, "message" => "Prompt is required"];
        }
        
        $settings_raw = Tp_Blocks_Helper::get_extra_option("nxtAiSettings");
        $encrypted = "";
        
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
            $decrypted = Tp_Blocks_Helper::tpgb_simple_decrypt($encrypted, "dy");
            $settings = json_decode($decrypted, true);
        } else {
            $settings = [];
        }
        
        $chatgpt_key = $settings["chatgptApiKey"];
        $chatgpt_img = $settings["chatgptEnableImage"];
        $chatgpt_tokens = $settings["chatgptImageMaxTokens"];
        
        $gemini_key = $settings["geminiApiKey"];
        $gemini_img = $settings["geminiEnableImage"];
        $gemini_tokens = $settings["geminiImageMaxTokens"];
        
        $biz_context = $settings["businessContext"];
        $audience = $settings["targetedAudience"];
        
        $is_enable = $gemini_img === true || $chatgpt_img === true ? "1" : "0";
        
        if ($is_enable !== "1") {
            return ["success" => false, "message" => "AI Image Generation is not enabled"];
        }
        
        $use_gemini = false;
        $use_chatgpt = false;
        $api_key = "";
        $max_tokens = 0;
        
        if ($gemini_img === true && !empty($gemini_key)) {
            $use_gemini = true;
            $api_key = $gemini_key;
            $max_tokens = absint($gemini_tokens);
        } elseif ($chatgpt_img === true && !empty($chatgpt_key)) {
            $use_chatgpt = true;
            $api_key = $chatgpt_key;
            $max_tokens = absint($chatgpt_tokens);
        }
        
        if (!$use_gemini && !$use_chatgpt) {
            return ["success" => false, "message" => "No AI image service is enabled or API key is missing"];
        }
        
        if (empty($api_key)) {
            return ["success" => false, "message" => "API Key is empty"];
        }
        
        $enhanced = $this->enhance_prompt($prompt, $image_type, $image_style, $biz_context, $audience);
        $size = $this->get_size($model, $aspect_ratio);
        
        if ($use_gemini) {
            return $this->call_gemini_api($api_key, $enhanced, $model, $n, $aspect_ratio, $max_tokens);
        } elseif ($use_chatgpt) {
            return $this->call_openai_api($api_key, $enhanced, $model, $n, $size, $image_quality, $max_tokens);
        }
        
        return ["success" => false, "message" => "Unsupported model"];
    }
    
    private function enhance_prompt($prompt, $type, $style, $biz_ctx = "", $audience = "") {
        $type_mod = isset($this->type_modifiers[$type]) ? $this->type_modifiers[$type] : "";
        $style_mod = isset($this->style_modifiers[$style]) ? $this->style_modifiers[$style] : "";
        
        $ctx_mods = [];
        if (!empty($biz_ctx)) {
            $ctx_mods[] = "Context: " . sanitize_text_field($biz_ctx);
        }
        if (!empty($audience)) {
            $ctx_mods[] = "For audience: " . sanitize_text_field($audience);
        }
        
        $all_mods = array_filter([
            $prompt,
            $type_mod,
            $style_mod,
            !empty($ctx_mods) ? implode(", ", $ctx_mods) : ""
        ]);
        
        return implode(", ", $all_mods);
    }
    
    private function get_size($model, $aspect) {
        return $this->size_map[$model][$aspect] ?? "1024x1024";
    }
    
    private function call_openai_api($key, $prompt, $model, $n, $size, $quality, $tokens = 0) {
        $url = "https://api.openai.com/v1/images/generations";
        
        $body = [
            "model" => $model ?? "dall-e-2",
            "prompt" => $prompt,
            "n" => $n,
            "size" => $size
        ];
        
        if (!empty($quality)) {
            $body["quality"] = $quality;
        }
        
        $response = wp_remote_post($url, [
            "headers" => [
                "Content-Type" => "application/json",
                "Authorization" => "Bearer " . $key
            ],
            "body" => wp_json_encode($body),
            "timeout" => 60
        ]);
        
        if (is_wp_error($response)) {
            return ["success" => false, "message" => $response->get_error_message()];
        }
        
        $code = wp_remote_retrieve_response_code($response);
        
        if ($code !== 200) {
            $content = wp_remote_retrieve_body($response);
            return ["success" => false, "message" => "API Error: {$code} - {$content}"];
        }
        
        $data = json_decode(wp_remote_retrieve_body($response), true);
        
        if (empty($data["data"])) {
            return ["success" => false, "message" => "No image data in response"];
        }
        
        $images = [];
        foreach ($data["data"] as $img) {
            if (isset($img["url"])) {
                $images[] = $img["url"];
            } elseif (isset($img["b64_json"])) {
                $images[] = "data:image/png;base64," . $img["b64_json"];
            }
        }
        
        return [
            "success" => true,
            "message" => "OpenAI Image response fetched",
            "data" => $images,
            "total_tokens" => "undefined",
            "max_tokens" => $tokens > 0 ? $tokens : null,
            "note" => $tokens > 0 ? "DALL-E does not support token limits" : null
        ];
    }
    
    private function call_gemini_api($key, $prompt, $model, $n, $aspect, $tokens = 0) {
        $gemini_model = "gemini-2.5-flash-image";
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$gemini_model}:generateContent?key={$key}";
        
        $names = [];
        $base = $prompt;
        
        if (preg_match("/user's text:\s*(.+?)(?:\.|$)/i", $prompt, $matches)) {
            $names_str = trim($matches[1]);
            $names = array_map("trim", explode(",", $names_str));
            $base = preg_replace("/user's text:\s*[^.]+/i", "", $prompt);
            $base = trim($base);
        }
        
        $all_imgs = [];
        $errors = [];
        $total_tkns = 0;
        
        for ($i = 0; $i < $n; $i++) {
            $iter_prompt = $base;
            
            if (!empty($names) && isset($names[$i])) {
                $iter_prompt .= " user's text: " . $names[$i];
            } elseif (!empty($names)) {
                $idx = $i % count($names);
                $iter_prompt .= " user's text: " . $names[$idx];
            } else {
                $iter_prompt = $prompt;
            }
            
            $body = [
                "contents" => [["parts" => [["text" => $iter_prompt]]]],
                "generationConfig" => [
                    "responseModalities" => ["IMAGE"],
                    "temperature" => 0.4,
                    "topK" => 32,
                    "topP" => 1
                ]
            ];
            
            if ($tokens > 0) {
                $body["generationConfig"]["maxOutputTokens"] = $tokens;
            }
            
            if (!empty($aspect) && $aspect !== "1:1") {
                $body["generationConfig"]["imageConfig"] = ["aspectRatio" => $aspect];
            }
            
            $response = wp_remote_post($url, [
                "headers" => ["Content-Type" => "application/json"],
                "body" => wp_json_encode($body),
                "timeout" => 90
            ]);
            
            if (is_wp_error($response)) {
                $errors[] = "Request " . ($i + 1) . ": " . $response->get_error_message();
                continue;
            }
            
            $code = wp_remote_retrieve_response_code($response);
            
            if ($code !== 200) {
                $content = wp_remote_retrieve_body($response);
                $err_data = json_decode($content, true);
                $err_msg = isset($err_data["error"]["message"]) ? $err_data["error"]["message"] : "API Error: {$code}";
                $errors[] = "Request " . ($i + 1) . ": " . $err_msg;
                continue;
            }
            
            $data = json_decode(wp_remote_retrieve_body($response), true);
            
            if (isset($data["usageMetadata"]["totalTokenCount"])) {
                $total_tkns += $data["usageMetadata"]["totalTokenCount"];
            }
            
            if (!empty($data["candidates"]) && !empty($data["candidates"][0]["content"]["parts"])) {
                foreach ($data["candidates"] as $candidate) {
                    if (!empty($candidate["content"]["parts"])) {
                        foreach ($candidate["content"]["parts"] as $part) {
                            if (isset($part["inlineData"]["data"])) {
                                $mime = $part["inlineData"]["mimeType"] ?? "image/png";
                                $all_imgs[] = "data:{$mime};base64," . $part["inlineData"]["data"];
                            }
                        }
                    }
                }
            } else {
                $errors[] = "Request " . ($i + 1) . ": No image data in response";
            }
            
            if ($i < $n - 1) {
                usleep(500000);
            }
        }
        
        if (empty($all_imgs)) {
            $err_msg = !empty($errors) ? implode("; ", $errors) : "No images generated. Please try again with a different prompt.";
            return ["success" => false, "message" => $err_msg];
        }
        
        return [
            "success" => true,
            "message" => "Gemini Image response fetched (" . count($all_imgs) . " of " . $n . " images generated)",
            "data" => $all_imgs,
            "total_tokens" => $total_tkns > 0 ? $total_tkns : "undefined",
            "max_tokens" => $tokens > 0 ? $tokens : null
        ];
    }
    
    public function get_available_types() {
        return array_keys($this->type_modifiers);
    }
    
    public function get_available_styles() {
        return array_keys($this->style_modifiers);
    }
    
    public function get_available_aspect_ratios($model) {
        return isset($this->size_map[$model]) ? array_keys($this->size_map[$model]) : ["1:1"];
    }
    
    public function nxt_ai_image_gen($params) {
        $ai = Nxt_AI_Image_Generator::get_instance();
        
        $args = [
            "prompt" => $params->get_param("prompt"),
            "model" => $params->get_param("model"),
            "n" => $params->get_param("n"),
            "aspect_ratio" => $params->get_param("aspect_ratio"),
            "image_type" => $params->get_param("image_type"),
            "image_style" => $params->get_param("image_style"),
            "image_quality" => $params->get_param("image_quality")
        ];
        
        $result = $ai->generate_image($args);
        
        if ($result["success"]) {
            return new WP_REST_Response($result, 200);
        } else {
            return new WP_REST_Response($result, 400);
        }
    }
    
    public static function get_instance() {
        static $instance = null;
        if (null === $instance) {
            $instance = new self();
        }
        return $instance;
    }
}