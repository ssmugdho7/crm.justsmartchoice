<?php

defined('BASEPATH') or exit('No direct script access allowed');
class Publishx_model extends App_Model
{
 public function addPost($data){$this->db->insert(db_prefix().'publishx_posts',$this->clean_post_data($data)); return $this->db->insert_id() ?: false;}
 public function updatePost($id,$data){$data=$this->clean_post_data($data);$data['updated_at']=date('Y-m-d H:i:s');$this->db->where('id',(int)$id)->update(db_prefix().'publishx_posts',$data);return $this->db->affected_rows()>=0;}
 private function clean_post_data($data){$allowed=['category_id','author_id','website_id','post_title','post_slug','short_content','full_content','meta_title','meta_description','meta_keywords','featured_image','featured_image_url','hero_video_url','media_gallery','template_key','language_id','post_parent_id','views','status','scheduled','target_folder','published_url','published_file','last_published_at','created_at','updated_at'];return array_intersect_key($data,array_flip($allowed));}
 public function getPost($id){return $this->db->where('id',(int)$id)->get(db_prefix().'publishx_posts')->row();}
 public function getPosts($status=null){if($status!==null)$this->db->where('status',$status);return $this->db->order_by('created_at','desc')->get(db_prefix().'publishx_posts')->result_array();}
 public function get_due_scheduled_posts(){return $this->db->where('status',2)->where('scheduled <=',date('Y-m-d H:i:s'))->get(db_prefix().'publishx_posts')->result_array();}
 public function deletePost($id){$this->db->where('id',(int)$id)->delete(db_prefix().'publishx_posts');return $this->db->affected_rows()>0;}
 public function getCategories(){return $this->db->order_by('category_name')->get(db_prefix().'publishx_categories')->result_array();}
 public function getCategory($id){return $this->db->where('id',(int)$id)->get(db_prefix().'publishx_categories')->row();}
 public function addCategory($d){if(empty($d['slug']))$d['slug']=slug_it($d['category_name']);$this->db->insert(db_prefix().'publishx_categories',$d);return $this->db->insert_id();}
 public function updateCategory($id,$d){if(isset($d['category_name']))$d['slug']=slug_it($d['category_name']);$this->db->where('id',(int)$id)->update(db_prefix().'publishx_categories',$d);return true;}
 public function deleteCategory($id){if(is_reference_in_table('category_id',db_prefix().'publishx_posts',$id))return ['referenced'=>true];$this->db->where('id',(int)$id)->delete(db_prefix().'publishx_categories');return true;}
 public function getLanguages(){return $this->db->where_in('code',['en','es'])->order_by('id')->get(db_prefix().'publishx_languages')->result_array();}
 public function getWebsites(){return $this->db->order_by('is_default','desc')->order_by('name')->get(db_prefix().'publishx_websites')->result_array();}
 public function getWebsite($id){return $this->db->where('id',(int)$id)->get(db_prefix().'publishx_websites')->row();}
 public function saveWebsite($data,$id=null){$allowed=['name','base_url','document_root','default_folder','header_include','footer_include','logo_url','favicon_url','ga_measurement_id','publish_method','webhook_url','webhook_secret','is_default','active'];$data=array_intersect_key($data,array_flip($allowed));$data['updated_at']=date('Y-m-d H:i:s');if(!empty($data['is_default'])){$this->db->update(db_prefix().'publishx_websites',['is_default'=>0]);}if($id){$this->db->where('id',(int)$id)->update(db_prefix().'publishx_websites',$data);return $id;}$data['created_at']=date('Y-m-d H:i:s');$this->db->insert(db_prefix().'publishx_websites',$data);return $this->db->insert_id();}
 public function deleteWebsite($id){if(is_reference_in_table('website_id',db_prefix().'publishx_posts',$id))return ['referenced'=>true];$this->db->where('id',(int)$id)->delete(db_prefix().'publishx_websites');return true;}
 public function publish_to_website($postId){$post=$this->getPost($postId);if(!$post||!$post->website_id)return ['success'=>false,'message'=>'Select a website first.'];$site=$this->getWebsite($post->website_id);if(!$site||!$site->active)return ['success'=>false,'message'=>'Website profile is inactive or missing.'];if($site->publish_method==='webhook')return $this->publish_webhook($post,$site);return $this->publish_local($post,$site);}
 private function publish_local($post, $site)
 {
  $root = $this->resolve_document_root($site);
  if (!$root['success']) {
   return $root;
  }

  $folder = trim((string) ($post->target_folder ?: $site->default_folder), '/\\');
  if ($folder !== '' && (strpos($folder, '..') !== false || preg_match('/[^a-zA-Z0-9_\-\/]/', $folder))) {
   return ['success' => false, 'message' => 'Invalid target folder. Use letters, numbers, dashes, underscores, and forward slashes only.'];
  }

  $dir = $root['path'] . ($folder !== '' ? DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $folder) : '');
  if (!is_dir($dir) && !@mkdir($dir, 0755, true)) {
   return ['success' => false, 'message' => 'Unable to create target folder: ' . $dir];
  }
  if (!is_dir($dir)) {
   return ['success' => false, 'message' => 'The target folder was not created: ' . $dir];
  }
  if (!is_writable($dir)) {
   return ['success' => false, 'message' => 'Target folder is not writable: ' . $dir];
  }

  $slug = slug_it($post->post_slug ?: $post->post_title);
  if ($slug === '') {
   $slug = 'blog-post-' . (int) $post->id;
  }

  $file = $dir . DIRECTORY_SEPARATOR . $slug . '.php';
  $url  = rtrim($site->base_url, '/') . '/' . ($folder !== '' ? trim($folder, '/') . '/' : '') . $slug . '.php';

  // Set the final URL before rendering canonical and social metadata.
  $post->published_url = $url;
  $post->short_content = publishx_strip_code_fences($post->short_content);
  $post->full_content = publishx_strip_code_fences($post->full_content);
  $post->meta_title = trim(strip_tags(publishx_strip_code_fences($post->meta_title)));
  $post->meta_description = trim(strip_tags(publishx_strip_code_fences($post->meta_description)));
  $post->meta_keywords = trim(strip_tags(publishx_strip_code_fences($post->meta_keywords)));
  $post->hero_video_url = publishx_normalize_video_url($post->hero_video_url);
  $html = $this->render_body_page($post, $site);

  $bytes = @file_put_contents($file, $html, LOCK_EX);
  if ($bytes === false || !is_file($file)) {
   return ['success' => false, 'message' => 'Unable to write the website file: ' . $file];
  }

  @chmod($dir, 0755);
  @chmod($file, 0644);
  clearstatcache(true, $file);

  if (!is_readable($file) || filesize($file) < 200) {
   return ['success' => false, 'message' => 'The generated page file is empty, unreadable, or incomplete: ' . $file];
  }

  $this->updatePost($post->id, [
   'post_slug'        => $slug,
   'published_url'    => $url,
   'published_file'   => $file,
   'last_published_at'=> date('Y-m-d H:i:s'),
  ]);

  $online = $this->verify_public_url($url, 'data-blogging-post-id="' . (int) $post->id . '"');
  if (!$online['success']) {
   return [
    'success' => false,
    'url'     => $url,
    'file'    => $file,
    'message' => 'The page was written to the confirmed JustSmartChoice.com root, but the public URL returned an error. ' . $online['message'] . ' File: ' . $file . ' URL: ' . $url,
   ];
  }

  return ['success' => true, 'url' => $url, 'file' => $file, 'message' => 'Page created and publicly verified: ' . $url];
 }

 private function resolve_document_root($site)
 {
  $configured = rtrim(trim((string) $site->document_root), '/\\');
  $baseUrl    = strtolower(rtrim(trim((string) $site->base_url), '/'));

  // The user confirmed this exact Bluehost document root for JustSmartChoice.com.
  if (in_array($baseUrl, ['https://justsmartchoice.com', 'https://www.justsmartchoice.com'], true)) {
   $configured = '/home2/scusawco/public_html/justsmartchoice.com';
  }

  if ($configured === '') {
   return ['success' => false, 'message' => 'Website document root is empty.'];
  }
  if (!is_dir($configured)) {
   return ['success' => false, 'message' => 'Website document root does not exist: ' . $configured];
  }
  if (!is_file($configured . DIRECTORY_SEPARATOR . 'index.php')) {
   return ['success' => false, 'message' => 'The selected website root does not contain index.php: ' . $configured];
  }
  if (!is_dir($configured . DIRECTORY_SEPARATOR . 'sections-library')) {
   return ['success' => false, 'message' => 'The selected website root does not contain sections-library: ' . $configured];
  }
  if (!is_writable($configured)) {
   return ['success' => false, 'message' => 'Website document root is not writable by PHP: ' . $configured];
  }

  return ['success' => true, 'path' => $configured];
 }

 public function testWebsite($id)
 {
  $site = $this->getWebsite($id);
  if (!$site) {
   return ['success' => false, 'message' => 'Website profile not found.'];
  }

  $root = $this->resolve_document_root($site);
  if (!$root['success']) {
   return $root;
  }

  $folder = trim((string) $site->default_folder, '/\\');
  $dir = $root['path'] . ($folder !== '' ? DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $folder) : '');

  if (!is_dir($dir) && !@mkdir($dir, 0755, true)) {
   return ['success' => false, 'message' => 'Unable to create default folder: ' . $dir];
  }

  $token = 'BLOGGING_TEST_' . date('YmdHis') . '_' . mt_rand(1000, 9999);
  $name  = 'blogging-write-test-' . strtolower(substr(md5($token), 0, 10)) . '.php';
  $test  = $dir . DIRECTORY_SEPARATOR . $name;
  $testContent = '<?php header("Content-Type: text/plain; charset=UTF-8"); ?>' . $token;

  if (@file_put_contents($test, $testContent, LOCK_EX) === false || !is_file($test)) {
   return ['success' => false, 'message' => 'The module cannot create a PHP test page in: ' . $dir];
  }

  @chmod($test, 0644);
  $testUrl = rtrim($site->base_url, '/') . '/' . ($folder !== '' ? trim($folder, '/') . '/' : '') . $name;
  $online  = $this->verify_public_url($testUrl, $token);
  @unlink($test);

  if (!$online['success']) {
   return ['success' => false, 'message' => 'Folder creation and PHP file creation succeeded, but the domain did not serve the test file. ' . $online['message'] . ' Folder: ' . $dir . ' URL: ' . $testUrl];
  }

  return ['success' => true, 'message' => 'Publishing is operational. Folder and public URL are connected: ' . $dir];
 }

 private function verify_public_url($url, $expectedText = '')
 {
  if (!function_exists('curl_init')) {
   return ['success' => false, 'message' => 'PHP cURL is unavailable, so the public URL cannot be verified.'];
  }

  $ch = curl_init($url . (strpos($url, '?') === false ? '?' : '&') . 'blogging_verify=' . time());
  curl_setopt_array($ch, [
   CURLOPT_RETURNTRANSFER => true,
   CURLOPT_FOLLOWLOCATION => true,
   CURLOPT_MAXREDIRS      => 5,
   CURLOPT_TIMEOUT        => 20,
   CURLOPT_CONNECTTIMEOUT => 10,
   CURLOPT_USERAGENT      => 'SmartChoiceBlogging/1.1.5',
   CURLOPT_HTTPHEADER     => ['Cache-Control: no-cache', 'Pragma: no-cache'],
  ]);

  $body  = curl_exec($ch);
  $code  = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
  $final = (string) curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
  $error = curl_error($ch);
  curl_close($ch);

  if ($error !== '') {
   return ['success' => false, 'message' => 'Connection error: ' . $error];
  }
  if ($code < 200 || $code >= 400) {
   return ['success' => false, 'message' => 'Public URL returned HTTP ' . $code . '. Final URL: ' . $final];
  }
  if ($expectedText !== '' && strpos((string) $body, $expectedText) === false) {
   return ['success' => false, 'message' => 'The URL opened, but it did not return the file created by Blogging. Final URL: ' . $final];
  }

  return ['success' => true, 'message' => 'Public URL verified.'];
 }

 private function render_body_page($post, $site)
 {
  $category = $this->getCategory($post->category_id);
  $template = preg_replace('/[^a-z0-9_\-]/i', '', $post->template_key ?: 'smart_choice_pro');
  $templateFile = module_views_path('publishx', 'website_templates/' . $template . '.php');
  if (!is_file($templateFile)) {
   $templateFile = module_views_path('publishx', 'website_templates/smart_choice_pro.php');
  }

  $websiteRoot = '/home2/scusawco/public_html/justsmartchoice.com';
  $cookieHead  = $websiteRoot . '/includes/cookie-consent-head.php';
  $cookieBody  = $websiteRoot . '/includes/cookie-consent.php';
  $headerFile  = $websiteRoot . '/sections-library/header.php';
  $footerFile  = $websiteRoot . '/sections-library/footer.php';
  $ga          = trim((string) $site->ga_measurement_id);
  $appointmentUrl = trim((string) get_option('publishx_appointment_url'));
  if ($appointmentUrl === '') {
   $appointmentUrl = 'https://crm.justsmartchoice.com/appointly/appointments_public/book?col=col-md-8+col-md-offset-2';
  }

  ob_start();
  include $templateFile;
  $body = ob_get_clean();

  $title       = html_escape($post->meta_title ?: $post->post_title);
  $description = html_escape($post->meta_description ?: strip_tags($post->short_content));
  $keywords    = html_escape($post->meta_keywords);
  $canonical   = html_escape($post->published_url);
  $lang        = 'en';

  $phpCookieHead = is_file($cookieHead) ? "<?php include '" . addslashes($cookieHead) . "'; ?>" : '';
  $phpCookieBody = is_file($cookieBody) ? "<?php include '" . addslashes($cookieBody) . "'; ?>" : '';
  $phpHeader     = "<?php include '" . addslashes($headerFile) . "'; ?>";
  $phpFooter     = "<?php include '" . addslashes($footerFile) . "'; ?>";

  return '<!DOCTYPE html>\n'
   . '<html lang="' . $lang . '">\n<head>\n'
   . $phpCookieHead . "\n"
   . '<meta charset="utf-8">\n'
   . '<meta http-equiv="X-UA-Compatible" content="IE=edge">\n'
   . '<meta name="viewport" content="width=device-width, initial-scale=1.0, height=device-height">\n'
   . '<title>' . $title . '</title>\n'
   . '<link rel="canonical" href="' . $canonical . '">\n'
   . '<meta name="description" content="' . $description . '">\n'
   . '<meta name="keywords" content="' . $keywords . '">\n'
   . '<meta name="author" content="Just Smart Choice Construction USA">\n'
   . '<meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">\n'
   . '<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Raleway:100,200,300,400,500,600,700,800,900">\n'
   . '<link rel="stylesheet" href="/css/style.css">\n'
   . '<link rel="stylesheet" href="/css/custom.css?v=60">\n'
   . '</head>\n<body data-blogging-post-id="' . (int) $post->id . '">\n'
   . $phpCookieBody . "\n"
   . $phpHeader . "\n"
   . $body . "\n"
   . '<a class="sc-blog-floating-estimate" href="' . html_escape($appointmentUrl) . '" target="_blank" rel="noopener" aria-label="Schedule a Free Estimate Now"><span class="sc-blog-floating-icon">&#128197;</span><span>Schedule a Free Estimate Now</span></a>' . "\n"
   . '<img src="https://crm.justsmartchoice.com/publishx/blog_track/view/' . (int) $post->id . '?v=' . time() . '" width="1" height="1" alt="" style="position:absolute;left:-9999px;width:1px;height:1px" referrerpolicy="strict-origin-when-cross-origin">' . "\n"
   . '<style>.sc-blog-floating-estimate{position:fixed;right:24px;top:50%;transform:translateY(-50%);z-index:9999;width:156px;height:156px;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:10px;background:linear-gradient(145deg,#f28c28 0%,#ee7d1d 48%,#0e6f5b 100%);color:#fff!important;padding:18px;border-radius:22px;border:3px solid rgba(255,255,255,.92);box-shadow:0 18px 42px rgba(14,111,91,.35),0 7px 18px rgba(0,0,0,.24);font-family:Raleway,sans-serif!important;font-size:15px;font-weight:800;letter-spacing:.2px;text-align:center;text-decoration:none;line-height:1.25;animation:scBlogFloat 3.2s ease-in-out infinite;transition:box-shadow .2s,filter .2s}.sc-blog-floating-estimate:hover{filter:brightness(1.06);box-shadow:0 24px 50px rgba(14,111,91,.42),0 10px 24px rgba(0,0,0,.28);color:#fff!important}.sc-blog-floating-icon{font-size:32px;line-height:1}.sc-blog-body{font-family:Raleway,sans-serif}.sc-blog-body *{box-sizing:border-box}@keyframes scBlogFloat{0%,100%{transform:translateY(-50%)}50%{transform:translateY(calc(-50% - 9px))}}@media(max-width:900px){.sc-blog-floating-estimate{right:10px;width:132px;height:132px;padding:13px;font-size:13px;border-radius:18px}}@media(max-width:600px){.sc-blog-floating-estimate{right:8px;top:54%;width:112px;height:112px;padding:10px;font-size:11px}.sc-blog-floating-icon{font-size:25px}}</style>' . "\n"
   . '<!-- Blogging updated: ' . date('c') . ' -->' . "\n"
   . $phpFooter . "\n"
   . '</body>\n</html>';
 }
 private function publish_webhook($post,$site){if(empty($site->webhook_url))return ['success'=>false,'message'=>'Webhook URL is missing.'];$payload=json_encode(['post'=>$post,'body'=>$this->render_body_page($post,$site)]);$ch=curl_init($site->webhook_url);curl_setopt_array($ch,[CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>$payload,CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>30,CURLOPT_HTTPHEADER=>['Content-Type: application/json','X-Blogging-Signature: '.hash_hmac('sha256',$payload,(string)$site->webhook_secret)]]);$response=curl_exec($ch);$code=(int)curl_getinfo($ch,CURLINFO_HTTP_CODE);$error=curl_error($ch);curl_close($ch);if($code<200||$code>=300)return ['success'=>false,'message'=>$error?:'Publishing webhook returned HTTP '.$code];$decoded=json_decode($response,true);$url=$decoded['url']??'';$this->updatePost($post->id,['published_url'=>$url,'last_published_at'=>date('Y-m-d H:i:s')]);return ['success'=>true,'url'=>$url];}
 public function recordView($postId,$data=[]){$data=array_merge(['post_id'=>(int)$postId,'viewed_at'=>date('Y-m-d H:i:s')],$data);$this->db->insert(db_prefix().'publishx_views',$data);$this->db->set('views','views+1',false)->where('id',(int)$postId)->update(db_prefix().'publishx_posts');}
 public function reportSummary(){return ['posts'=>(int)$this->db->count_all(db_prefix().'publishx_posts'),'published'=>(int)$this->db->where('status',0)->count_all_results(db_prefix().'publishx_posts'),'views'=>(int)($this->db->select_sum('views')->get(db_prefix().'publishx_posts')->row()->views??0),'websites'=>(int)$this->db->where('active',1)->count_all_results(db_prefix().'publishx_websites')];}
}
