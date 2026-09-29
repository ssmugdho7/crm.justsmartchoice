<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Sc_ai_video_studio_model extends App_Model
{
    public function get_videos(): array
    {
        $this->db->order_by('id', 'DESC');
        return $this->db->get(db_prefix() . 'sc_video_jobs')->result_array();
    }

    public function get_video(int $id): ?array
    {
        $row = $this->db->where('id', $id)->get(db_prefix() . 'sc_video_jobs')->row_array();
        return $row ?: null;
    }

    public function save_video(array $data, int $id = 0): int
    {
        $payload = [
            'title' => trim((string) ($data['title'] ?? 'Untitled Video')),
            'script_text' => (string) ($data['script_text'] ?? ''),
            'language' => (string) ($data['language'] ?? 'English'),
            'voice_id' => (int) ($data['voice_id'] ?? 0),
            'avatar_id' => (int) ($data['avatar_id'] ?? 0),
            'status' => (string) ($data['status'] ?? 'Draft'),
            'logo_enabled' => isset($data['logo_enabled']) ? 1 : 0,
            'logo_position' => (string) ($data['logo_position'] ?? 'Top Right'),
            'intro_thumbnail_url' => (string) ($data['intro_thumbnail_url'] ?? ''),
            'outro_thumbnail_url' => (string) ($data['outro_thumbnail_url'] ?? ''),
            'video_url' => (string) ($data['video_url'] ?? ''),
            'embed_code' => (string) ($data['embed_code'] ?? ''),
            'dateupdated' => date('Y-m-d H:i:s'),
        ];
        if ($id > 0) {
            $this->db->where('id', $id)->update(db_prefix() . 'sc_video_jobs', $payload);
            $this->save_layers($id, $data);
            return $id;
        }
        $payload['created_by'] = get_staff_user_id();
        $payload['datecreated'] = date('Y-m-d H:i:s');
        $this->db->insert(db_prefix() . 'sc_video_jobs', $payload);
        $newId = (int) $this->db->insert_id();
        $this->save_layers($newId, $data);
        return $newId;
    }

    public function save_layers(int $videoId, array $data): void
    {
        $this->db->where('video_id', $videoId)->delete(db_prefix() . 'sc_video_text_layers');
        for ($i = 1; $i <= 4; $i++) {
            $text = trim((string) ($data['text_layer_' . $i] ?? ''));
            if ($text === '') {
                continue;
            }
            $this->db->insert(db_prefix() . 'sc_video_text_layers', [
                'video_id' => $videoId,
                'layer_order' => $i,
                'text_value' => $text,
                'start_second' => (float) ($data['text_start_' . $i] ?? 0),
                'duration_second' => (float) ($data['text_duration_' . $i] ?? 3),
                'animation' => (string) ($data['text_animation_' . $i] ?? 'Fade In'),
                'datecreated' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    public function get_layers(int $videoId): array
    {
        return $this->db->where('video_id', $videoId)->order_by('layer_order', 'ASC')->get(db_prefix() . 'sc_video_text_layers')->result_array();
    }

    public function delete_video(int $id): bool
    {
        $this->db->where('video_id', $id)->delete(db_prefix() . 'sc_video_text_layers');
        return (bool) $this->db->where('id', $id)->delete(db_prefix() . 'sc_video_jobs');
    }

    public function get_voices(): array
    {
        return $this->db->order_by('voice_name', 'ASC')->get(db_prefix() . 'sc_video_voices')->result_array();
    }

    public function get_avatars(): array
    {
        return $this->db->order_by('id', 'ASC')->get(db_prefix() . 'sc_video_avatars')->result_array();
    }

    public function save_voice(array $data, int $id = 0): int
    {
        $payload = [
            'voice_name' => trim((string) ($data['voice_name'] ?? 'New Voice')),
            'provider_voice_id' => (string) ($data['provider_voice_id'] ?? ''),
            'language' => (string) ($data['language'] ?? 'English'),
            'gender' => (string) ($data['gender'] ?? 'Neutral'),
            'tone' => (string) ($data['tone'] ?? 'Professional'),
            'sample_url' => (string) ($data['sample_url'] ?? ''),
            'is_active' => isset($data['is_active']) ? 1 : 0,
            'dateupdated' => date('Y-m-d H:i:s'),
        ];
        if ($id > 0) {
            $this->db->where('id', $id)->update(db_prefix() . 'sc_video_voices', $payload);
            return $id;
        }
        $payload['created_by'] = get_staff_user_id();
        $payload['datecreated'] = date('Y-m-d H:i:s');
        $this->db->insert(db_prefix() . 'sc_video_voices', $payload);
        return (int) $this->db->insert_id();
    }

    public function save_avatar(array $data, int $id = 0): int
    {
        $payload = [
            'avatar_name' => trim((string) ($data['avatar_name'] ?? 'New Avatar')),
            'avatar_type' => (string) ($data['avatar_type'] ?? 'Preset'),
            'position_name' => (string) ($data['position_name'] ?? 'Front'),
            'image_url' => (string) ($data['image_url'] ?? ''),
            'provider_avatar_id' => (string) ($data['provider_avatar_id'] ?? ''),
            'is_active' => isset($data['is_active']) ? 1 : 0,
            'dateupdated' => date('Y-m-d H:i:s'),
        ];
        if ($id > 0) {
            $this->db->where('id', $id)->update(db_prefix() . 'sc_video_avatars', $payload);
            return $id;
        }
        $payload['created_by'] = get_staff_user_id();
        $payload['datecreated'] = date('Y-m-d H:i:s');
        $this->db->insert(db_prefix() . 'sc_video_avatars', $payload);
        return (int) $this->db->insert_id();
    }

    public function counts(): array
    {
        return [
            'videos' => (int) $this->db->count_all_results(db_prefix() . 'sc_video_jobs'),
            'voices' => (int) $this->db->count_all_results(db_prefix() . 'sc_video_voices'),
            'avatars' => (int) $this->db->count_all_results(db_prefix() . 'sc_video_avatars'),
        ];
    }
}
