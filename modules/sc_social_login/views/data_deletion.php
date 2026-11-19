<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Facebook account connections and data deletion | Smart Choice CRM</title>
<style>body{margin:0;padding:32px 18px;background:#f4f7f5;color:#18342b;font:16px/1.65 system-ui,sans-serif}main{max-width:760px;margin:auto;padding:28px;background:white;border:1px solid #d9e3dd;border-radius:12px}h1{font-size:28px;line-height:1.3}h2{font-size:20px}a{color:#07694d}li{margin:8px 0}</style></head><body><main>
<h1>Facebook account connections and data deletion</h1>
<p>These instructions apply to Facebook sign-in for Smart Choice CRM.</p>
<h2>Remove Facebook as a sign-in method</h2>
<ol><li>Sign in to your CRM account using your email and password or another connected login method.</li>
<li>Open Account Connections: <a href="<?= html_escape(site_url('sc_social_login/account')); ?>">Customer account</a> or <a href="<?= html_escape(admin_url('sc_social_login/connections')); ?>">Staff account</a>.</li>
<li>Find the Facebook connection, choose Remove, and complete the confirmation shown.</li></ol>
<p>Removing a connection stops that Facebook identity from being used to sign in to that CRM account. It does not delete the CRM account, contracts, invoices, or other business records.</p>
<h2>Request deletion of your account data</h2>
<p>To request deletion of personal data associated with your Facebook-connected CRM account, email <a href="mailto:Legal@justsmartchoiceusa.com">Legal@justsmartchoiceusa.com</a>, the contact listed in our privacy policy. Identify your CRM account email and state that your request concerns Facebook-connected account data. Do not send passwords, authentication codes, or access tokens.</p>
<p>For information about your data rights and applicable limits, read our <a href="<?= html_escape(site_url('privacy-policy')); ?>">Privacy Policy</a>.</p>
</main></body></html>
