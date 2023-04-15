<?php
if (get_setting("show_intro")) {

    helper('cookie');
    if ((get_cookie("intro_" . $intro_type)) != "hide") {
        echo view("intro/" . $intro_type);
    }
}
?>


<script>
    $(".js-hide-intro").click(function () {
        setCookie("intro_" + "<?php echo $intro_type; ?>", "hide");
        $(this).closest(".card").fadeOut(200);
    });
</script>