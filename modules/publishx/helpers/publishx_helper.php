<?php

defined('BASEPATH') or exit('No direct script access allowed');
function publishx_post_statuses(){return [['value'=>0,'name'=>_l('publishx_published')],['value'=>1,'name'=>_l('publishx_draft')],['value'=>2,'name'=>_l('publishx_scheduled')]];}
function publishx_supported_blog_themes(){
 $names=[
 'smart_choice_pro'=>'Smart Choice Pro','trade_showcase'=>'Trade Showcase','project_story'=>'Project Story','service_authority'=>'Service Authority','video_feature'=>'Video Feature','carousel_gallery'=>'Carousel Gallery','local_city'=>'Local City SEO','before_after'=>'Before & After','engineering_brief'=>'Engineering Brief','minimal_article'=>'Minimal Article',
 'roofing_insight'=>'Roofing Insight','plumbing_solution'=>'Plumbing Solution','electrical_safety'=>'Electrical Safety','kitchen_luxury'=>'Kitchen Luxury','bathroom_spa'=>'Bathroom Spa','project_timeline'=>'Project Timeline','cost_guide'=>'Cost Guide','faq_expert'=>'FAQ Expert','case_study'=>'Case Study','news_magazine'=>'News Magazine',
 'executive_journal'=>'Executive Journal','luxury_portfolio'=>'Luxury Portfolio','interactive_story'=>'Interactive Story','service_carousel'=>'Service Carousel','project_gallery'=>'Project Gallery','local_authority'=>'Local Authority','modern_editorial'=>'Modern Editorial','conversion_landing'=>'Conversion Landing','visual_casebook'=>'Visual Casebook','expert_insights'=>'Expert Insights'];
 $out=[];foreach($names as $id=>$title)$out[]=['id'=>$id,'title'=>$title,'thumbnail'=>module_dir_url('publishx','assets/images/templates/'.$id.'.svg')];return $out;
}
function publishx_handle_post_feature_image_upload($postId)
{
    if (empty($_FILES['featured_image']['name'])) {
        return ['success' => false];
    }
    $ext = strtolower(pathinfo($_FILES['featured_image']['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg','jpeg','png','webp','gif'], true)) {
        return ['success' => false, 'message' => 'Unsupported image type.'];
    }
    $path = module_dir_path('publishx', 'uploads/posts/' . (int) $postId . '/');
    _maybe_create_upload_path($path);
    $name = slug_it(pathinfo($_FILES['featured_image']['name'], PATHINFO_FILENAME)) . '-' . time() . '.' . $ext;
    if (move_uploaded_file($_FILES['featured_image']['tmp_name'], $path . $name)) {
        return ['success' => true, 'file_name' => $name, 'url' => module_dir_url('publishx', 'uploads/posts/' . (int) $postId . '/' . rawurlencode($name))];
    }
    return ['success' => false, 'message' => 'The image could not be uploaded.'];
}

function publishx_media_root_path()
{
    return module_dir_path('publishx', 'uploads/media/');
}

function publishx_media_root_url()
{
    return module_dir_url('publishx', 'uploads/media/');
}

function publishx_handle_library_upload()
{
    if (empty($_FILES['blogging_media']['name'])) {
        return ['success' => false, 'message' => 'Choose an image or video first.'];
    }
    $ext = strtolower(pathinfo($_FILES['blogging_media']['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg','jpeg','png','webp','gif','mp4','webm'], true)) {
        return ['success' => false, 'message' => 'Unsupported media type.'];
    }
    $path = publishx_media_root_path();
    _maybe_create_upload_path($path);
    $name = slug_it(pathinfo($_FILES['blogging_media']['name'], PATHINFO_FILENAME)) . '-' . time() . '.' . $ext;
    if (move_uploaded_file($_FILES['blogging_media']['tmp_name'], $path . $name)) {
        return ['success' => true, 'file_name' => $name, 'url' => publishx_media_root_url() . rawurlencode($name)];
    }
    return ['success' => false, 'message' => 'The media file could not be uploaded.'];
}

function publishx_scan_media_library()
{
    $root = publishx_media_root_path();
    $items = [];
    if (!is_dir($root)) {
        return $items;
    }
    $allowed = ['jpg','jpeg','png','webp','gif','mp4','webm'];
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if (!$file->isFile() || !in_array(strtolower($file->getExtension()), $allowed, true)) {
            continue;
        }
        $relative = ltrim(str_replace($root, '', $file->getPathname()), '/\\');
        $items[] = [
            'name' => $file->getFilename(),
            'url'  => publishx_media_root_url() . str_replace(DIRECTORY_SEPARATOR, '/', $relative),
            'type' => in_array(strtolower($file->getExtension()), ['mp4','webm'], true) ? 'video' : 'image',
        ];
    }
    return $items;
}

function publishx_feature_image_url($post)
{
    if (!empty($post->featured_image_url)) {
        return $post->featured_image_url;
    }
    if (!empty($post->featured_image) && !empty($post->id)) {
        return module_dir_url('publishx', 'uploads/posts/' . (int) $post->id . '/' . rawurlencode($post->featured_image));
    }
    return base_url('assets/images/image-placeholder.png');
}

function publishx_get_openai_key()
{
    foreach (['openai_api_key','open_ai_api_key','smart_choice_openai_api_key','publishx_openai_key'] as $key) {
        $value = trim((string) get_option($key));
        if ($value !== '') return $value;
    }
    return '';
}

function publishx_strip_code_fences($content)
{
    $content = trim((string) $content);
    $content = preg_replace('/^```(?:html|php|xml|markdown|md)?\s*/i', '', $content);
    $content = preg_replace('/\s*```$/', '', $content);
    $content = preg_replace('/^`{3,}\s*/', '', $content);
    $content = preg_replace('/\s*`{3,}$/', '', $content);
    return trim($content);
}

function publishx_normalize_video_url($value)
{
    $value = trim((string) $value);
    if ($value === '') return '';
    if (preg_match('~(?:youtube\.com/watch\?v=|youtu\.be/|youtube\.com/embed/)([A-Za-z0-9_-]{6,})~i', $value, $m)) {
        return 'https://www.youtube.com/embed/' . $m[1];
    }
    if (preg_match('~vimeo\.com/(?:video/)?([0-9]+)~i', $value, $m)) {
        return 'https://player.vimeo.com/video/' . $m[1];
    }
    if (preg_match('/^[A-Za-z0-9_-]{6,}$/', $value)) {
        return 'https://www.youtube.com/embed/' . $value;
    }
    if (preg_match('~^https?://~i', $value) && preg_match('/\.(mp4|webm|ogg)(\?.*)?$/i', $value)) {
        return $value;
    }
    return '';
}

function publishx_handle_post_video_upload($postId)
{
    if (empty($_FILES['hero_video_file']['name'])) return null;
    if (get_option('publishx_allow_video_upload') !== '1') return ['success'=>false,'message'=>'Video uploads are disabled in Blogging settings.'];
    $allowed=['mp4','webm','ogg'];
    $ext=strtolower(pathinfo($_FILES['hero_video_file']['name'],PATHINFO_EXTENSION));
    if(!in_array($ext,$allowed,true)) return ['success'=>false,'message'=>'Allowed video formats: MP4, WebM, OGG.'];
    $path=module_dir_path('publishx','uploads/posts/'.(int)$postId.'/');
    if(!is_dir($path) && !mkdir($path,0755,true)) return ['success'=>false,'message'=>'Unable to create the post video folder.'];
    $name='video-'.date('YmdHis').'-'.slug_it(pathinfo($_FILES['hero_video_file']['name'],PATHINFO_FILENAME)).'.'.$ext;
    if(!move_uploaded_file($_FILES['hero_video_file']['tmp_name'],$path.$name)) return ['success'=>false,'message'=>'Unable to upload the video.'];
    return ['success'=>true,'url'=>module_dir_url('publishx','uploads/posts/'.(int)$postId.'/'.rawurlencode($name))];
}

function publishx_ai_generate($field,$title,$instructions='')
{
    $key=publishx_get_openai_key();
    if($key==='') return ['status'=>'error','message'=>_l('publishx_missing_openai_key')];
    $prompts=[
      'post_title'=>'Create a professional construction blog title under 65 characters.',
      'short_content'=>'Write a concise construction article summary of 2 paragraphs in clean HTML. Do not use Markdown or code fences.',
      'full_content'=>'Write a complete SEO-friendly construction article in clean HTML using headings, paragraphs, lists, practical examples, safety notes, and a call to action. Do not include html, head, body, style, script, Markdown, or code fences. Return only article-body HTML.',
      'meta_title'=>'Create an SEO title under 60 characters. Return plain text only.',
      'meta_description'=>'Create an SEO meta description between 140 and 160 characters. Return plain text only.',
      'meta_keywords'=>'Create 8 relevant comma-separated SEO keywords. Return plain text only.',
     'seo_instructions'=>'Create a concise, specific SEO content brief for this topic. Include target audience, primary keyword, search intent, location focus, supporting topics, internal-link ideas, factual constraints, and conversion goal. Return plain text only.'
    ];
    $system='You are the senior construction content, branding, and SEO editor for Smart Choice Contractors USA. Preserve factual accuracy and write for homeowners and commercial property owners in Florida. Every article must feel premium, structured, and visually engaging while remaining body-only content. Use semantic headings, concise paragraphs, benefit cards, checklists, project steps, expert tips, safety notes, local-service references, and strong calls to action where appropriate. Follow the Smart Choice brand palette. Do not create a plain or dull article. Do not return inline CSS, JavaScript, a complete HTML document, html, head, body, Markdown, or code fences. Return clean article-body HTML only. The publishing template automatically adds the appointment button, so do not duplicate it inside the article.';
    $globalRules=trim((string)get_option('publishx_ai_global_instructions'));
    $avoid=trim((string)get_option('publishx_ai_disallowed_topics'));
    $facts=trim((string)get_option('publishx_ai_required_facts'));
    if($globalRules!=='') $system.=' Additional business instructions: '.$globalRules;
    if($avoid!=='') $system.=' Do not create or imply the following: '.$avoid;
    if($facts!=='') $system.=' Required facts and disclosures: '.$facts;
    $user=($prompts[$field]??'Improve this content.').' Topic: '.$title.'. Additional instructions: '.$instructions;
    $payload=json_encode(['model'=>'gpt-4.1-mini','input'=>[['role'=>'system','content'=>[['type'=>'input_text','text'=>$system]]],['role'=>'user','content'=>[['type'=>'input_text','text'=>$user]]]],'max_output_tokens'=>1800]);
    $ch=curl_init('https://api.openai.com/v1/responses');curl_setopt_array($ch,[CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>$payload,CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>60,CURLOPT_HTTPHEADER=>['Authorization: Bearer '.$key,'Content-Type: application/json']]);$raw=curl_exec($ch);$code=(int)curl_getinfo($ch,CURLINFO_HTTP_CODE);$err=curl_error($ch);curl_close($ch);
    if($code<200||$code>=300) return ['status'=>'error','message'=>$err?:'AI request returned HTTP '.$code];
    $j=json_decode($raw,true);$text='';foreach($j['output']??[] as $o)foreach($o['content']??[] as $c)if(($c['type']??'')==='output_text')$text.=$c['text'];
    $text=publishx_strip_code_fences($text);
    if(in_array($field,['meta_title','meta_description','meta_keywords','post_title'],true)) $text=trim(strip_tags($text));
    return $text!==''?['status'=>'ok','content'=>$text]:['status'=>'error','message'=>'AI returned no content.'];
}
