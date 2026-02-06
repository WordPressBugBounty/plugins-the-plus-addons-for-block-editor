<?php
if (!defined("ABSPATH")) {
    exit();
}

class Nxt_AI_Image_Saver
{
    private $allowed_mime_types = [
        "image/jpeg",
        "image/jpg",
        "image/png",
        "image/gif",
        "image/webp",
    ];

    public function save_image($args = [])
    {
        $args = wp_parse_args($args, [
            "image" => "",
            "title" => "",
            "alt" => "",
            "caption" => "",
            "description" => "",
        ]);

        $img = $args["image"];

        if (empty($img)) {
            return ["success" => false, "message" => "No image data provided"];
        }

        if (filter_var($img, FILTER_VALIDATE_URL)) {
            $img_data = $this->download_from_url($img);
        } else {
            $img_data = $this->decode_base64($img);
        }

        if (is_wp_error($img_data)) {
            return [
                "success" => false,
                "message" => $img_data->get_error_message(),
            ];
        }

        $validation = $this->validate_image($img_data);
        if (is_wp_error($validation)) {
            return [
                "success" => false,
                "message" => $validation->get_error_message(),
            ];
        }

        $ext = $this->get_extension($img_data);
        $filename = $this->generate_filename($ext);

        return $this->upload_to_wp($img_data, $filename, $args);
    }

    private function download_from_url($url)
    {
        $response = wp_remote_get($url, [
            "timeout" => 120,
            "sslverify" => false,
            "redirection" => 10,
            "httpversion" => "1.1",
            "blocking" => true,
            "user-agent" =>
                "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36",
            "headers" => [
                "Accept" => "image/webp,image/apng,image/*,*/*;q=0.8",
                "Accept-Language" => "en-US,en;q=0.9",
            ],
        ]);

        if (is_wp_error($response)) {
            return new WP_Error(
                "download_failed",
                "Failed to download image: " . $response->get_error_message()
            );
        }

        $data = wp_remote_retrieve_body($response);
        $code = wp_remote_retrieve_response_code($response);

        if ($code !== 200 || empty($data)) {
            return new WP_Error(
                "invalid_response",
                "Failed to download image. HTTP Code: " . $code
            );
        }

        return $data;
    }

    private function decode_base64($base64_str)
    {
        if (strpos($base64_str, "data:image") === 0) {
            $base64_str = preg_replace(
                "/^data:image\/\w+;base64,/",
                "",
                $base64_str
            );
        }

        $data = base64_decode($base64_str);

        if ($data === false || empty($data)) {
            return new WP_Error("invalid_base64", "Invalid base64 image data");
        }

        return $data;
    }

    private function validate_image($img_data)
    {
        if (!class_exists("finfo")) {
            $info = @getimagesizefromstring($img_data);
            if ($info === false) {
                return new WP_Error("invalid_image", "Invalid image data");
            }
            return true;
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->buffer($img_data);

        if (!in_array($mime, $this->allowed_mime_types)) {
            return new WP_Error(
                "invalid_mime_type",
                "Downloaded file is not a valid image. MIME type: " . $mime
            );
        }

        return true;
    }

    private function get_extension($img_data)
    {
        $info = @getimagesizefromstring($img_data);
        $ext = "png";

        if ($info !== false) {
            switch ($info[2]) {
                case IMAGETYPE_JPEG:
                    $ext = "jpg";
                    break;
                case IMAGETYPE_PNG:
                    $ext = "png";
                    break;
                case IMAGETYPE_GIF:
                    $ext = "gif";
                    break;
                case IMAGETYPE_WEBP:
                    $ext = "webp";
                    break;
            }
        }

        return $ext;
    }

    private function generate_filename($ext)
    {
        return "nxt-ai-image-" .
            time() .
            "-" .
            wp_generate_password(6, false) .
            "." .
            $ext;
    }

    private function upload_to_wp($img_data, $filename, $args)
    {
        $upload = wp_upload_bits($filename, null, $img_data);

        if ($upload["error"]) {
            return [
                "success" => false,
                "message" => "Upload failed: " . $upload["error"],
            ];
        }

        $filepath = $upload["file"];
        $fileurl = $upload["url"];

        $attach_id = $this->create_attachment($filepath, $args);

        if (is_wp_error($attach_id)) {
            @unlink($filepath);
            return [
                "success" => false,
                "message" =>
                    "Failed to create attachment: " .
                    $attach_id->get_error_message(),
            ];
        }

        $this->generate_metadata($attach_id, $filepath);

        $attach_url = wp_get_attachment_url($attach_id);

        return [
            "success" => true,
            "message" => "Image saved to media library successfully",
            "url" => $attach_url,
            "image_url" => $attach_url,
            "image_id" => $attach_id,
            "attachment_id" => $attach_id,
        ];
    }

    private function create_attachment($filepath, $args)
    {
        $filetype = wp_check_filetype(basename($filepath), null);

        $title = !empty($args["title"])
            ? sanitize_text_field($args["title"])
            : sanitize_file_name(pathinfo($filepath, PATHINFO_FILENAME));

        $attachment = [
            "post_mime_type" => $filetype["type"],
            "post_title" => $title,
            "post_content" => !empty($args["description"])
                ? sanitize_textarea_field($args["description"])
                : "",
            "post_excerpt" => !empty($args["caption"])
                ? sanitize_textarea_field($args["caption"])
                : "",
            "post_status" => "inherit",
        ];

        $attach_id = wp_insert_attachment($attachment, $filepath);

        if (!is_wp_error($attach_id) && !empty($args["alt"])) {
            update_post_meta(
                $attach_id,
                "_wp_attachment_image_alt",
                sanitize_text_field($args["alt"])
            );
        }

        return $attach_id;
    }

    private function generate_metadata($attach_id, $filepath)
    {
        require_once ABSPATH . "wp-admin/includes/image.php";
        $data = wp_generate_attachment_metadata($attach_id, $filepath);
        wp_update_attachment_metadata($attach_id, $data);
    }

    public function save_multiple_images($images, $common_args = [])
    {
        $results = [];

        foreach ($images as $idx => $img) {
            $args = array_merge($common_args, ["image" => $img]);

            if (empty($args["title"])) {
                $args["title"] = "AI Generated Image " . ($idx + 1);
            }

            $result = $this->save_image($args);
            $results[] = $result;
        }

        return [
            "success" => true,
            "message" => "Processed " . count($images) . " images",
            "results" => $results,
        ];
    }

    public function get_attachment_details($attach_id)
    {
        if (!$attach_id) {
            return null;
        }

        $attachment = get_post($attach_id);
        if (!$attachment) {
            return null;
        }

        return [
            "id" => $attach_id,
            "title" => get_the_title($attach_id),
            "url" => wp_get_attachment_url($attach_id),
            "alt" => get_post_meta(
                $attach_id,
                "_wp_attachment_image_alt",
                true
            ),
            "caption" => $attachment->post_excerpt,
            "description" => $attachment->post_content,
            "mime_type" => $attachment->post_mime_type,
            "sizes" => wp_get_attachment_metadata($attach_id),
        ];
    }

    public function delete_attachment($attach_id, $force = false)
    {
        if (!$attach_id) {
            return ["success" => false, "message" => "Invalid attachment ID"];
        }

        $deleted = wp_delete_attachment($attach_id, $force);

        if ($deleted) {
            return [
                "success" => true,
                "message" => "Attachment deleted successfully",
            ];
        }

        return ["success" => false, "message" => "Failed to delete attachment"];
    }

    public function nxt_ai_save($params)
    {
        $saver = Nxt_AI_Image_Saver::get_instance();

        $args = [
            "image" => $params->get_param("img"),
            "title" => $params->get_param("title"),
            "alt" => $params->get_param("alt"),
            "caption" => $params->get_param("caption"),
            "description" => $params->get_param("description"),
        ];

        $result = $saver->save_image($args);

        if ($result["success"]) {
            return new WP_REST_Response($result, 200);
        } else {
            return new WP_REST_Response($result, 400);
        }
    }

    public static function get_instance()
    {
        static $instance = null;
        if (null === $instance) {
            $instance = new self();
        }
        return $instance;
    }
}
