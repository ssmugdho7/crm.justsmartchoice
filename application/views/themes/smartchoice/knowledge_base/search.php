<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<section class="jumbotron kb-search-jumbotron sc-knowledge-search" aria-labelledby="sc-knowledge-title">
    <header class="sc-account-heading"><span class="sc-account-eyebrow">CUSTOMER RESOURCES</span><h1 id="sc-knowledge-title">Knowledge Base</h1><p>Find answers about your project, payments, appointments, and customer portal.</p></header>
    <div class="kb-search">
        <div class="container">
            <div class="row">
                <div class="col-md-8 col-md-offset-2">
                    <div class="text-center">
                        <label for="sc-knowledge-query" class="kb-search-heading">
                            <?= _l('kb_search_articles'); ?>
                        </label>
                        <?= form_open(site_url('knowledge-base/search'), ['method' => 'GET', 'id' => 'kb-search-form']); ?>
                        <div class="form-group has-feedback has-feedback-left">
                            <div class="input-group">
                                <input type="search" name="q" id="sc-knowledge-query"
                                    placeholder="<?= _l('have_a_question'); ?>"
                                    class="form-control kb-search-input"
                                    value="<?= e($this->input->get('q', false)); ?>">
                                <span class="input-group-btn">
                                    <button type="submit"
                                        class="btn btn-primary kb-search-button"><?= _l('kb_search'); ?></button>
                                </span>
                                <i class="fa-solid fa-magnifying-glass form-control-feedback kb-search-icon"></i>
                            </div>
                        </div>
                        <?= form_close(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
