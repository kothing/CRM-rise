<div class="color-palet">
    <?php
    $selected_color = $model_info->color ? $model_info->color : "#4A8AF4";
    $colors = array("#83c340", "#29c2c2", "#2d9cdb", "#aab7b7", "#f1c40f", "#e18a00", "#e74c3c", "#d43480", "#ad159e", "#37b4e1", "#34495e", "#dbadff");
    $custom_color_active_class = "active";

    foreach ($colors as $color) {
        $active_class = "";
        if ($selected_color === $color) {
            $active_class = "active";
            $custom_color_active_class = "";
        }
        echo "<span style='background-color:" . $color . "' class='color-tag clickable mr15 " . $active_class . "' data-color='" . $color . "'></span>";
    }
    ?> 
    <input type="color" id="custom-color" class="input-color <?php echo $custom_color_active_class; ?>" name="color" value="<?php echo $model_info->color ? $model_info->color : "#4A8AF4"; ?>" />
</div>

<script type="text/javascript">
    $(document).ready(function () {

        $(".color-palet span").click(function () {
            $(".color-palet").find(".active").removeClass("active");
            $(this).addClass("active");
            $("#custom-color").val($(this).attr("data-color"));
        });

        $(".color-palet #custom-color").click(function () {
            $(".color-palet").find(".active").removeClass("active");
            $(this).addClass("active");
        });

    });
</script>