<div class="box">
    <div class="box-content w50 cart-item-details">
        <div class='cart-item-image-container'>
            <div class="cart-item-image" style='background-image: url(<?php echo get_store_item_image($item->files); ?>)'></div>
        </div>
    </div>

    <div class="box-content cart-item-details-container cart-item-details">
        <div class="ml15">
            <strong><?php echo $item->title; ?></strong>
            <div class='text-off'><?php echo nl2br($item->description ? process_images_from_content($item->description) : ""); ?></div>
        </div>
    </div>

    <div class="box-content cart-item-details">
        <div class="text-center mr15" style="width: 92px;">
            <div class="cart-item-plus-minus-section">
                <?php echo js_anchor('<i data-feather="minus" class="icon-16"></i>', array("class" => "cart-item-quantity-btn float-start mr10 mt5", "data-action" => "minus")); ?>
                <div class="b-a w cart-item-quantity float-start clickable" data-quantity="<?php echo $item->quantity; ?>"><?php echo $item->quantity; ?></div>
                <?php echo js_anchor('<i data-feather="plus" class="icon-16"></i>', array("class" => "cart-item-quantity-btn float-start ml10 mt5", "data-action" => "plus")); ?>
            </div>

            <div class="cart-item-input-section hide">
                <?php echo form_open(get_uri("items/change_cart_item_quantity/input"), array("id" => "item-quantity-form", "class" => "general-form", "role" => "form")); ?>
                <input type="hidden" name="id" value="<?php echo $item->id; ?>" />
                <input type="text" value="<?php echo $item->quantity; ?>" name="item_quantity" autocomplete="off" class="w50 form-control inline-block item-quantity-input-box" />
                <button type="submit" class="btn btn-primary btn-sm item-quantity-btn"><span data-feather="check" class="icon-16"></span></button>
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>

    <div class="box-content cart-item-details text-right">
        <strong><?php echo to_currency($item->total); ?></strong>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $(".cart-item-quantity").on("click", function () {
            $(".cart-item-input-section").removeClass("hide");
            $(".cart-item-plus-minus-section").addClass("hide");
        });

        var $itemRow = $(".cart-item-quantity-btn").closest(".js-item-row");

        $("#item-quantity-form").appForm({
            isModal: false,
            onSuccess: function (response) {
                $itemRow.html(response.data);
                $("#cart-total-section").html(response.cart_total_view);
                appLoader.hide();
            }
        });
    });
</script>