<form action="<?php echo get_uri("collect_leads/save"); ?>" role="form" method="post" accept-charset="utf-8">

    <input type="text" name="company_name" id="company_name" placeholder="<?php echo app_lang('company_name'); ?>" required="required" />
    <input type="text" name="first_name" id="first_name" placeholder="<?php echo app_lang('first_name'); ?>" >
    <input type="text" name="last_name" id="last_name" placeholder="<?php echo app_lang('last_name'); ?>" required="required">
    <input type="email" name="email" id="email" placeholder="<?php echo app_lang('email'); ?>" autocomplete="off">
    <textarea name="address" cols="40" rows="10" id="address" placeholder="<?php echo app_lang('address'); ?>"></textarea>
    <input type="text" name="city" id="city" placeholder="<?php echo app_lang('city'); ?>">
    <input type="text" name="state" id="state" placeholder="<?php echo app_lang('state'); ?>">
    <input type="text" name="zip" id="zip" placeholder="<?php echo app_lang('zip'); ?>">
    <input type="text" name="country" id="country" placeholder="<?php echo app_lang('country'); ?>">
    <input type="text" name="phone" id="phone" placeholder="<?php echo app_lang('phone'); ?>">

    <button type="submit"><?php echo app_lang('submit'); ?></button>

</form>