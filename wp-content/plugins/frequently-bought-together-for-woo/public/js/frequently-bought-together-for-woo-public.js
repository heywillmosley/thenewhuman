(function ($) {
    'use strict';

    $(document).ready(function () {

        var calculate_data = function() {
            
            var returnArray = new Object();
            var totalPrice = 0;
            var pid, price;
            var productIds = [];
            var totalProducts = 0;
            var button_text = '';
            
            $("#frequently_bought_together_form :checkbox:checked").each(function (i) {
                pid = $(this).val();
                productIds[i] = pid;
                price = $(this).closest(".frequently_bought_product").attr("price");
                totalPrice = Number(totalPrice) + Number(price);
                totalProducts = i + 1;
            });
            
            //var a = 11.95;var b = 9.95;var c = 9.95;var d = a+b+c; d = 31.849999999999998
            totalPrice = totalPrice.toFixed(2); 
            
            returnArray['totalProducts'] = totalProducts;
            returnArray['totalPrice'] = totalPrice;
            returnArray['productIds'] = productIds;
            
            return returnArray;
        };
        
        var display_total_price = function(productIds, totalPrice) {
            $(".frequently_bought_product_price_total").html(labels.currency + totalPrice);
            $("#frequently_bought_together_selected_product_id").val(productIds);
        };
        
        var add_button_text = function(totalProducts) {
            
           $(".frequently_bought_add_to_cart").show();
           if (totalProducts == 0) {
                var button_text = labels.zero_item;
                $(".frequently_bought_add_to_cart").hide();
            } else if (totalProducts == 1) {
                var button_text = labels.one_item;
            } else if (totalProducts == 2) {
                var button_text = labels.two_items;
            } else {
                var button_text = labels.n_items;
            }

            $(".frequently_bought_add_to_cart .single_add_to_cart_button").html(button_text);
            $("#total_products").text(totalProducts); 
        };
        
        var show = function () {
            var calculate_data_array = calculate_data();
            var productIds = calculate_data_array['productIds'];
            var totalPrice = calculate_data_array['totalPrice'];
            var totalProducts = calculate_data_array['totalProducts'];
            display_total_price(productIds, totalPrice);
            add_button_text(totalProducts);
            
        };
        $("#frequently_bought_together_form input:checkbox").click(function () {
            show();
        });

        $(".single_add_to_cart_button").click(function () {
            var addtocarturl = labels.site_url;
            var product_ids = $("#frequently_bought_together_selected_product_id").val();
            if (product_ids) {
                addtocarturl = addtocarturl + product_ids;
                $("a.single_add_to_cart_button").attr("href", addtocarturl);
            }
            
            var variation_id = $("#frequently_bought_together_selected_variation_id").val();
            addtocarturl = addtocarturl + "&variation_id=" + variation_id;
            $("a.single_add_to_cart_button").attr("href", addtocarturl);
        });
        
        $(document).on('found_variation', function (e, data) {
            var is_variation_visible = data.variation_is_visible;
            if(is_variation_visible == true) {
                var variation_id = data.variation_id;
                
                //$("#frequently_bought_together_selected_variation_id").val(variation_id);
                $("#frequently_bought_product_1").attr('price',  data.display_price);
                
                //Set Image
                $('#frequently_bought_product_1 img.attachment-shop_thumbnail').attr('src', data.image_src);
                $('#frequently_bought_product_1 img.attachment-shop_thumbnail').attr('srcset', data.image_src);

                //Set Variation ID
                $('#frequently_bought_product_1 .frequently_bought_product_title input').attr('value', variation_id);
                
                //Set Price
                var priceSpan = '<span class="woocommerce-Price-currencySymbol">' + labels.currency + '</span>' + data.display_price.toFixed(2);
                $('#frequently_bought_product_price_1 span.woocommerce-Price-amount').html(priceSpan);
                
                // Set Title
                var variations = JSON.parse(variations_data);
                
                $('#frequently_bought_product_1 div.frequently_bought_product_title input').attr('value', variation_id);
                $('#frequently_bought_product_1 div.frequently_bought_product_title a').attr('href', variations[variation_id].url);
                $('#frequently_bought_product_1 div.frequently_bought_product_title a').text( variations[variation_id].title);
            } else {
                //$("#frequently_bought_together_selected_variation_id").val(0);
                $("#frequently_bought_product_1").attr('price',  0);
            }
            show();
        });
    });
})(jQuery);
