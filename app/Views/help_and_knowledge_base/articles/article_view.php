<h2 class="mb20 pb10 b-b"> <?php echo $article_info->title; ?></h2>
<div>
    <?php echo process_images_from_content($article_info->description); ?>
</div>
<div class="mt20 mb20">
    <?php
    if ($article_info->files) {
        $files = unserialize($article_info->files);
        $total_files = count($files);
        echo view("includes/timeline_preview", array("files" => $files));

        if ($total_files) {
            $download_caption = app_lang('download');
            if ($total_files > 1) {
                $download_caption = sprintf(app_lang('download_files'), $total_files);
            }
            echo anchor(get_uri($article_info->type . "/download_files/" . $article_info->id), $download_caption, array("class" => "", "title" => $download_caption));
        }
    }
    ?>
</div>

<?php if ($article_info->type == "knowledge_base") { ?>
    <?php if (!$article_info->article_helpful_status) { ?>
        <div class="b-t">
            <div class="feedback-section mt-3">
                <div class="mb-2"><?php echo app_lang("was_this_article_helpful"); ?></div>

                <?php echo js_anchor("<i data-feather='check-circle' class='icon-16'></i> " . app_lang('yes'), array("class" => "btn btn-success mr5 article_vote_button", "data-action-url" => get_uri($article_info->type . "/article_helpful_status/" . $article_info->id . "/yes"), "data-article-id" => "$article_info->id", "data-feedback-status" => "yes")); ?>
                <?php echo js_anchor("<i data-feather='x' class='icon-16'></i> " . app_lang('no'), array("class" => "btn btn-default article_vote_button", "data-action-url" => get_uri($article_info->type . "/article_helpful_status/" . $article_info->id . "/no"), "data-article-id" => "$article_info->id", "data-feedback-status" => "no")); ?>
            </div>
        </div>
    <?php } ?>
<?php } ?>
