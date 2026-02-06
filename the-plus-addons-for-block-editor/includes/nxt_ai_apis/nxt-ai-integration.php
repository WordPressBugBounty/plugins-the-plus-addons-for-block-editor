<?php
if (!defined("ABSPATH")) exit();

class Nxt_AI_Integration {
    
    private $tone_instructions = [
        "professional" => "Write with a polished, clear, and credible tone that reflects expertise and confidence.",
        "casual" => "Write in a relaxed, friendly, conversational tone that feels natural and approachable.",
        "humorous" => "Write with light, clever humor and playful timing without being exaggerated.",
        "enthusiastic" => "Write with energetic, upbeat language that feels positive and full of momentum.",
        "formal" => "Write with refined, structured, and sophisticated language using proper etiquette.",
        "informal" => "Write in an easy-going, relaxed style that feels simple, warm, and human.",
        "optimistic" => "Write with uplifting, positive language that expresses hope and bright possibilities.",
        "friendly" => "Write with warmth and openness, using approachable language that feels welcoming.",
        "serious" => "Write in a focused, grounded, and no-nonsense tone that conveys importance.",
        "excited" => "Write with lively, animated wording that conveys strong enthusiasm and anticipation.",
        "dramatic" => "Write with expressive, bold language that heightens intensity and emotional impact.",
        "witty" => "Write with clever, sharp phrasing and intelligent humor delivered subtly.",
        "sarcastic" => "Write with dry, ironic language that conveys humor through contrast and understatement.",
        "curious" => "Write with inquisitive, exploratory language that encourages discovery and wonder.",
        "adventurous" => "Write with daring, bold language that evokes excitement, exploration, and risk-taking.",
        "romantic" => "Write with gentle, affectionate language that conveys warmth, tenderness, and emotion.",
        "persuasive" => "Write with compelling, benefit-driven language that guides readers toward agreement.",
        "inspirational" => "Write with uplifting, motivational language that sparks ambition and emotional strength.",
        "educational" => "Write with clear, structured, informative language designed to teach effectively.",
        "motivational" => "Write with energizing, empowering language that encourages action and progress.",
        "empathetic" => "Write with sensitive, understanding language that validates feelings and builds trust.",
        "respectful" => "Write with courteous, considerate language that maintains dignity and professionalism.",
        "sympathetic" => "Write with gentle, supportive language that expresses care and emotional understanding.",
        "encouraging" => "Write with positive, confidence-building language that helps readers feel capable.",
        "hopeful" => "Write with forward-looking, optimistic language that suggests better outcomes ahead.",
        "confident" => "Write with assured, decisive language that demonstrates clarity and strong conviction.",
        "bold" => "Write with strong, fearless language that creates a vivid and memorable impression.",
        "funny" => "Write with playful, amusing language that creates lighthearted comedic moments.",
        "lighthearted" => "Write with cheerful, breezy language that feels enjoyable and carefree.",
        "playful" => "Write with lively, spirited language that adds fun and creative energy.",
        "serene" => "Write with calm, soothing language that creates a peaceful and balanced tone.",
        "calm" => "Write with steady, composed language that feels relaxing and reassuring.",
        "peaceful" => "Write with gentle, harmonious language that evokes quiet and emotional ease.",
        "reflective" => "Write with thoughtful, introspective language that explores ideas meaningfully.",
        "thoughtful" => "Write with deliberate, considerate language that shows care and intention.",
        "introspective" => "Write with self-aware, reflective language that examines deeper meaning."
    ];
    
    public function generate_text($args = []) {
        $args = wp_parse_args($args, [
            "prompt" => "",
            "temperature" => 0.7,
            "tone" => "professional",
            "model" => "gemini"
        ]);
        
        $prompt = sanitize_text_field($args["prompt"]);
        $temp = floatval($args["temperature"]);
        $tone = sanitize_text_field($args["tone"]);
        $model = sanitize_text_field($args["model"]);
        
        if (empty($prompt)) {
            return ["success" => false, "message" => "Prompt is required"];
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
        
        $chatgpt_key = $settings["chatgptApiKey"];
        $chatgpt_txt = $settings["chatgptEnableText"];
        $chatgpt_tokens = $settings["chatgptTextMaxTokens"];
        
        $gemini_key = $settings["geminiApiKey"];
        $gemini_txt = $settings["geminiEnableText"];
        $gemini_tokens = $settings["geminiTextMaxTokens"];
        
        $biz_ctx = $settings["businessContext"];
        $audience = $settings["targetedAudience"];
        
        $use_gemini = false;
        $use_chatgpt = false;
        $api_key = "";
        $max_tokens = null;
        
        if ($gemini_txt === true && !empty($gemini_key)) {
            $use_gemini = true;
            $api_key = $gemini_key;
            $max_tokens = !empty($gemini_tokens) ? intval($gemini_tokens) : null;
        } elseif ($chatgpt_txt === true && !empty($chatgpt_key)) {
            $use_chatgpt = true;
            $api_key = $chatgpt_key;
            $max_tokens = !empty($chatgpt_tokens) ? intval($chatgpt_tokens) : null;
        }
        
        if (!$use_gemini && !$use_chatgpt) {
            return ["success" => false, "message" => "No AI service is enabled or API key is missing"];
        }
        
        if (empty($api_key)) {
            return ["success" => false, "message" => "API Key is empty"];
        }
        
        $template = $this->build_template($prompt, $tone, $biz_ctx, $audience);
        
        if ($use_gemini) {
            return $this->call_gemini($api_key, $template, $temp, $max_tokens);
        } elseif ($use_chatgpt) {
            return $this->call_chatgpt($api_key, $template, $temp, "gpt-4o-mini", $max_tokens);
        }
        
        return ["success" => false, "message" => "Unsupported model"];
    }
    
    private function build_template($prompt, $tone, $biz_ctx = "", $audience = "") {
        $tone_txt = $this->tone_instructions[$tone] ?? $this->tone_instructions["professional"];
        
        $ctx_section = "";
        if (!empty($biz_ctx) || !empty($audience)) {
            $ctx_section = "\n\nContext Information:";
            if (!empty($biz_ctx)) {
                $ctx_section .= "\n- Business Context: " . sanitize_text_field($biz_ctx);
            }
            if (!empty($audience)) {
                $ctx_section .= "\n- Target Audience: " . sanitize_text_field($audience);
            }
        }
        
        return "You are a content generator for WordPress websites. Generate clean, plain text content based on the following requirements:

Primary Task: {$prompt}
{$ctx_section}

Requirements:
- {$tone_txt}
- Write in plain text only, with NO HTML tags, NO markdown, and NO special formatting characters such as asterisks (*), underscores (_), hashes (#), bullets, or symbols.
- Do not use bold, italics, headers, lists, decorative characters, or stylized formatting.
- Begin directly with the content. Do not add phrases like \"Here is the content\" or \"Sure, let me help.\"
-Write as a human would speak: natural flow, clear sentences, steady pacing, and meaningful expression.
- Stay focused on the primary task. Treat the context as helpful background only.
- Do not include explanations, meta-commentary, or system-like replies. Output ONLY the final content requested.
- Maintain the exact tone specified in the tone requirement.
- Avoid filler language, clichés, repetitive phrasing, or overly generic statements.

Generate the content now:";
    }
    
    private function call_gemini($key, $template, $temp, $tokens = null) {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-exp:generateContent";
        
        $gen_config = ["temperature" => $temp];
        
        if ($tokens !== null && $tokens > 0) {
            $gen_config["maxOutputTokens"] = $tokens;
        }
        
        $body = [
            "contents" => [["parts" => [["text" => $template]]]],
            "generationConfig" => $gen_config
        ];
        
        $response = wp_remote_post($url, [
            "headers" => [
                "Content-Type" => "application/json",
                "x-goog-api-key" => $key
            ],
            "body" => wp_json_encode($body),
            "timeout" => 30
        ]);
        
        if (is_wp_error($response)) {
            return ["success" => false, "message" => $response->get_error_message()];
        }
        
        $code = wp_remote_retrieve_response_code($response);
        
        if ($code !== 200) {
            return ["success" => false, "message" => "API Error: {$code}"];
        }
        
        $data = json_decode(wp_remote_retrieve_body($response), true);
        
        $text = $data["candidates"][0]["content"]["parts"][0]["text"] ?? "";
        $text = $this->clean_response($text);
        
        $total_tokens = $data["usageMetadata"]["totalTokenCount"] ?? 0;
        
        return [
            "success" => true,
            "message" => "Gemini AI response fetched",
            "data" => $text,
            "total_tokens" => $total_tokens
        ];
    }
    
    private function call_chatgpt($key, $template, $temp, $model, $tokens = null) {
        $url = "https://api.openai.com/v1/chat/completions";
        
        $body = [
            "model" => $model,
            "messages" => [["role" => "user", "content" => $template]],
            "temperature" => $temp
        ];
        
        if ($tokens !== null && $tokens > 0) {
            $body["max_tokens"] = $tokens;
        }
        
        $response = wp_remote_post($url, [
            "headers" => [
                "Content-Type" => "application/json",
                "Authorization" => "Bearer " . $key
            ],
            "body" => wp_json_encode($body),
            "timeout" => 90
        ]);
        
        if (is_wp_error($response)) {
            return ["success" => false, "message" => $response->get_error_message()];
        }
        
        $code = wp_remote_retrieve_response_code($response);
        
        if ($code !== 200) {
            return ["success" => false, "message" => "API Error: {$code}"];
        }
        
        $data = json_decode(wp_remote_retrieve_body($response), true);
        
        $text = $data["choices"][0]["message"]["content"] ?? "";
        $text = $this->clean_response($text);
        
        $total_tokens = $data["usage"]["total_tokens"] ?? 0;
        
        return [
            "success" => true,
            "message" => "ChatGPT AI response fetched",
            "data" => $text,
            "total_tokens" => $total_tokens
        ];
    }
    
    private function clean_response($text) {
        $text = trim($text);
        $text = preg_replace("/\s+/", " ", $text);
        return $text;
    }
    
    public function nxt_ai_integration($params) {
        $ai = Nxt_AI_Integration::get_instance();
        
        $args = [
            'prompt' => $params->get_param('prompt'),
            'temperature' => floatval($params->get_param('temperature')),
            'tone' => $params->get_param('tone'),
            'model' => $params->get_param('model')
        ];
        
        $result = $ai->generate_text($args);
        
        if ($result['success']) {
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