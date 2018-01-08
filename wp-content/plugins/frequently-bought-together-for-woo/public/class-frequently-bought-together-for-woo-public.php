<?php

class Frequently_Bought_Together_For_Woo_Public {

    private $plugin_name;

    private $version;

    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    public function enqueue_styles() {
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/frequently-bought-together-for-woo-public.css', array(), $this->version, 'all');
    }

    public function enqueue_scripts() {
        
        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/frequently-bought-together-for-woo-public.js', array('jquery'), $this->version, false);

        $labels = array(
            'currency' => get_woocommerce_currency_symbol(),
            'zero_item' => __('Select Atleast One Item', 'frequently-bought-together-for-woo'),
            'one_item' => __('Add To Cart', 'frequently-bought-together-for-woo'),
            'two_items' => __('Add Both Items To Cart', 'frequently-bought-together-for-woo'),
            'n_items' => sprintf(__('Add %s Items To Cart', 'frequently-bought-together-for-woo'), '<span id="total_products"></span>'),
            'site_url' => site_url('?add-to-cart-multiple=')
        );

        wp_localize_script($this->plugin_name, 'labels', $labels);
    }

    /**
     * 
     * Hooked to template_include filter
     * 
     * @param type $template
     * @return type
     */
    function bought_together_include($template) {
        $this->bought_together_to_cart();
        return $template;
    }

    function bought_together_to_cart() {
        if (!empty($_GET['add-to-cart-multiple']) || !empty($_GET['variation_id']) ) {
            $product_ids_array = array();
            $product_ids = !empty($_GET['add-to-cart-multiple']) ? $_GET['add-to-cart-multiple'] : '';
            $product_ids_array = explode(',', $product_ids);
            $variation_id = !empty($_GET['variation_id']) ? $_GET['variation_id'] : 0;
            array_push($product_ids_array, $variation_id);
            
            $quantity = 1;
            $product_titles = array();
            if (count($product_ids_array) > 0) {
                
                foreach ($product_ids_array as $product_id) {
                    if($product_id == 0) {
                        continue;
                    }
                    
                    $product = wc_get_product($product_id);
                    
                    if($product instanceof WC_Product_Variable) {
                        continue;
                    } elseif($product instanceof WC_Product_Variation) {
                        $parent_id = $product->id;
                        $variation_attributes = wc_get_product_variation_attributes($product_id);

                        if (!empty($parent_id) && !empty($variation_attributes)) {
                            if (WC()->cart->add_to_cart($parent_id, $quantity, $product_id, $variation_attributes)) {
                                $product_titles[] = get_the_title($parent_id);
                            }
                        }
                    } else {
                        if (WC()->cart->add_to_cart($product_id, $quantity)) {
                            $product_titles[] = get_the_title($product_id);
                        }
                    }
                    
                    
                }
            }
            
            if(count($product_titles) > 0) {
                $added_text = sprintf( _n( '%s has been added to your cart.', '%s have been added to your cart.', count($product_titles), 'frequently-bought-together-for-woo' ), wc_format_list_of_items($product_titles) );
                wc_add_notice($added_text);
            }
            
            wp_redirect(WC()->cart->get_cart_url());
            exit;
        }
    }

    /**
     * Get frequently bought together products from products meta
     * 
     * @global type $product
     * @param type $pids
     * @param type $exclude_pids
     * 
     * @return array of prodct ids
     */
    function get_frequently_bought_together_products($product_ids, $exclude_pids = array(), $from_cart_page = false) {
        global $wc_chained_products;

        $product_id = 0;
        $bought_together_product_ids = array();
        $do_query = false;
        if ($from_cart_page) {

            if (count($product_ids) > 0) {
                foreach ($product_ids as $p_id) {
                    $group = get_post_meta($p_id, '_fbtfw_ids', true);

                    if (!empty($group)) {
                        foreach ($group as $the_id) {
                            $bought_together_product_ids[] = $the_id;
                        }
                    } else {
                        $bought_together_product_ids_from_query = $this->get_frequently_bought_together_products_from_order_history(array($p_id));
                        $bought_together_product_ids = array_merge($bought_together_product_ids, $bought_together_product_ids_from_query);
                    }
                }
            }
        } else {
            global $product;

            $group = get_post_meta($product->id, '_fbtfw_ids', true);

            if ($product->product_type == 'grouped' || $product->product_type == 'external') {
                return;
            }

            if (!empty($group)) {
                $product_id = $product->id;
                //$bought_together_product_ids[] = $product_id;
               
                foreach ($group as $the_id) {
                    $bought_together_product_ids[] = $the_id;
                }
            } else {
                $bought_together_product_ids = $this->get_frequently_bought_together_products_from_order_history(array($product->id));
            }
            
            array_unshift($bought_together_product_ids, $product->id);
        }
        
        
        $unique_frequently_bought_together_products = array_unique($bought_together_product_ids);

        
        // Remove Variable Product From Frequently bought together list
        if(count($unique_frequently_bought_together_products) > 0) {
            foreach ($unique_frequently_bought_together_products as $key => $p_id) {
                $p = wc_get_product($p_id);
                if ($p->product_type == 'variable' && $p_id != $product_id) {
                    unset($unique_frequently_bought_together_products[$key]);
                }
            }
        }
        
        // Remove chained products from frequently bought together list
        if (!empty($wc_chained_products)) {
            if (count($unique_frequently_bought_together_products) > 0) {
                foreach ($unique_frequently_bought_together_products as $key => $unique_frequently_bought_together_product_id) {
                    if ($wc_chained_products->is_chained_product($unique_frequently_bought_together_product_id)) {
                        unset($unique_frequently_bought_together_products[$key]);
                    }
                }
            }
        }
        
        // Remove excluded products from unique products
        if(!empty($unique_frequently_bought_together_products))  {
            $unique_frequently_bought_together_products = array_diff($unique_frequently_bought_together_products, $exclude_pids);
        }

        return $unique_frequently_bought_together_products;
    }

    function get_frequently_bought_together_products_from_order_history($product_ids, $exclude_pids = array()) {
        global $wpdb, $table_prefix;

        $frequently_bought_together_product_ids = array();
        $wc_order_itemmeta_tbl_name = $table_prefix . "woocommerce_order_itemmeta";
        $wc_order_items_tbl_name = $table_prefix . "woocommerce_order_items";

        $product_ids_count = count($product_ids);
        $pid = implode(',', $product_ids);

        $all_products = false;
//      $all_products = wp_cache_get('sa_frequently_bought_together_' . $pid, 'sa_frequently_bought_together_for_woo');    
        if ($product_ids_count > 1 || ($product_ids_count == 1 && !$all_products)) {
            $sql = "SELECT oi.order_id from $wc_order_items_tbl_name oi where oi.order_item_id in (SELECT oim.order_item_id FROM $wc_order_itemmeta_tbl_name oim where oim.meta_key='_product_id' and oim.meta_value in ($pid) or oim.meta_key='_variation_id' and oim.meta_value in ($pid)) limit 100";
            $orders = $wpdb->get_col($sql);
            if (count($orders) > 0) {
                $order_ids_str = implode(',', array_unique($orders));
                $exclude_pids_query = '';
                if (!empty($exclude_pids)) {
                    $exclude_pids_str = implode(', ', $exclude_pids);
                    $exclude_pids_query = " and oim.meta_value not in ($exclude_pids_str)";
                }
                
                $query = "SELECT oim.order_item_id, oim.meta_value as product_id, count(oim.meta_value) as total_count FROM $wc_order_itemmeta_tbl_name oim WHERE oim.meta_key='_product_id'$exclude_pids_query AND oim.order_item_id IN (SELECT oi.order_item_id FROM $wc_order_items_tbl_name oi where oi.order_id IN ($order_ids_str) AND oi.order_item_type='line_item') GROUP BY oim.meta_value ORDER BY total_count DESC LIMIT 0, 15";
                $products = $wpdb->get_results($query, ARRAY_A);
                
                $query = "SELECT oim.order_item_id, oim.meta_value as variation_id, count(oim.meta_value) as total_count FROM $wc_order_itemmeta_tbl_name oim WHERE oim.meta_key='_variation_id' AND oim.order_item_id IN (SELECT oi.order_item_id FROM $wc_order_items_tbl_name oi where oi.order_id IN ($order_ids_str) AND oi.order_item_type='line_item') GROUP BY oim.meta_value ORDER BY total_count DESC LIMIT 0, 15";
                $variations = $wpdb->get_results($query, ARRAY_A);
                
                $frequently_bought_together_products = array();
                
                if (!empty($products) && count($products) > 0) {
                    foreach ($products as $product) {
                        $frequently_bought_together_products[$product['order_item_id']] = $product;
                    }
                    unset($products);
                }
                
                if(!empty($variations) && count($variations) > 0) {
                    foreach ($variations as $variation) {
                        if (in_array($variation['order_item_id'], array_keys($frequently_bought_together_products)) && $variation['variation_id'] != 0) {
                            //Set variation id as product it for easy access
                            $frequently_bought_together_products[$variation['order_item_id']]['product_id'] = $variation['variation_id'];
                            $frequently_bought_together_products[$variation['order_item_id']]['total_count'] = $variation['total_count'];
                        }
                    }
                }
                
                $frequently_bought_together_product_ids = array();
                if (count($frequently_bought_together_products) > 0) {
                    //Get total count of each children
                    $total_count = array_map(function ($inner) {
                        return $inner["total_count"];
                    }, $frequently_bought_together_products);

                    //Sort an array by total_count, DESC
                    array_multisort($total_count, SORT_DESC, $frequently_bought_together_products);

                    //Get all product/variation ids from $frequently_bought_together_products array
                    $frequently_bought_together_product_ids = array_map(function ($inner) {
                        return $inner["product_id"];
                    }, $frequently_bought_together_products);
                }

                if ($product_ids_count == 1) {
                    wp_cache_add('sa_frequently_bought_together_' . $pid, $frequently_bought_together_product_ids, 'sa_frequently_bought_together_for_woo');
                }
            }
        }
        
        return $frequently_bought_together_product_ids;
    }

    /**
     * Hooked to woocommerce_after_single_product_summary action
     */
    function display_frequently_bought_together_on_product_detail() {
        $product_id = get_the_id();

        $exclude_product_ids = array();
        $product = wc_get_product($product_id);
        if($product instanceof WC_Product_Variable) {
            // Exclude variations
            $exclude_product_ids = $product->get_children();
        }
        $products = $this->get_frequently_bought_together_products(array($product_id), $exclude_product_ids);
            
        if ($products) {
            $this->bought_together_addto_cart($product, array_splice($products, 0, MAX_FREQUENTLY_BOUGHT_TOGETHER_PRODUCTS_DISPLAY));
            $this->bought_together_related_products($products);
        }
    }

    /**
     * Hooked to woocommerce_after_cart action
     * 
     * @return type
     */
    function display_frequently_bought_together_on_cart() {
        $cart_contents_count = WC()->cart->cart_contents_count;

        $products_array = array();
        if (!WC()->cart->cart_contents) {
            return;
        }

        $exclude_product_ids = array();
        foreach (WC()->cart->cart_contents as $key => $cart_content) {
            $products_array[] = $cart_content['product_id'];
            $product = wc_get_product($cart_content['product_id']);
            if($product instanceof WC_Product_Variable) {
                $variations = $product->get_children();
                if(!empty($variations)) {
                    $exclude_product_ids = array_merge($variations, $exclude_product_ids);
                }
            }
            
        }
        
        if ($products_array) {
            $products = $this->get_frequently_bought_together_products($products_array, $exclude_product_ids, true);
            $title = __('Customers Who Bought Items in Your Cart Also Bought', 'frequently-bought-together-for-woo');
            $this->bought_together_related_products(array_diff($products, $products_array), $title);
        }
    }

    /**
     * Get Related priducts
     * @param type $products
     * @param type $title
     * @return type
     */
    function bought_together_related_products($products, $title = '') {

        if (count($products) == 0) {
            return;
        }

        $products = array_splice($products, 0, MAX_FREQUENTLY_BOUGHT_TOGETHER_PRODUCTS_DISPLAY);

        $args = array(
            'post_type' => 'product',
            'ignore_sticky_posts' => 1,
            'no_found_rows' => 1,
            'posts_per_page' => 12,
            'post__in' => $products
        );
        $products_list = new WP_Query($args);
        if (!$title) {
            $title = __('Customers Who Bought Items in Your Cart Also Bought', 'frequently-bought-together-for-woo');
        }

        if ($products_list->have_posts()) {
            echo "<div class='related products'><h2>" . $title . "</h2>";
            woocommerce_product_loop_start();
            while ($products_list->have_posts()) {
                $products_list->the_post();
                wc_get_template_part('content', 'product');
            }
            woocommerce_product_loop_end();
            echo "</div>";
        }
        wp_reset_postdata();
    }

    /**
     * Function to display products with add to cart button 
     */
    function bought_together_addto_cart($product, $products) {
        
        if (count($products) <= 1) {
            return;
        }
        
        $default_variation_id = 0;
        if ($product instanceof WC_Product_Variable) {
            
        $variations = $product->get_children();
        if(count($variations > 0)) {
            $variations_data = array();
            $all_variations = array_reverse($variations);
            $default_variation_id = array_shift($all_variations);
            foreach ($variations as $variation_id) {
                $v_p = wc_get_product($variation_id);
                $formatted_attributes = $v_p->get_formatted_variation_attributes(true);
                $extra_data = ' - ' . $formatted_attributes;
                $name =  sprintf(__('%s%s', 'woocommerce'), $v_p->get_title(), $extra_data);
                
                $variations_data["{$variation_id}"]['title'] = $name;
                $variations_data["{$variation_id}"]['price'] = $v_p->get_display_price();
                $variations_data["{$variation_id}"]['url'] = $v_p->get_permalink();
            }
                    
            ?>                    
                <script>
                    var variations_data = '<?php echo json_encode($variations_data); ?>';
                </script>
            <?php         
            
            }

            $default_attributes = $product->get_variation_default_attributes();
            if (!empty($default_attributes)) {
                foreach ($default_attributes as $k => $v) {
                    $default_attributes['attribute_' . $k] = $v;
                    unset($default_attributes[$k]);
                }
                $default_variation_id = $product->get_matching_variation($default_attributes);
            }
            
            if(!is_null($product)) {
                $product_id = $product->id;
                foreach ($products as $key => $p_id) {
                    if ($product_id == $p_id) {
                        unset($products[$key]);
                    }
                }
            }
        }
        
        if($default_variation_id > 0) {
            array_unshift($products, $default_variation_id);
        }
        
        $products = array_splice($products, 0, MAX_FREQUENTLY_BOUGHT_TOGETHER_PRODUCTS_DISPLAY);
        
        $args = array(
            'post_type' => array('product', 'product_variation'),
            'ignore_sticky_posts' => 1,
            'no_found_rows' => 1,
            'posts_per_page' => 10,
            'post__in' => $products,
            'orderby' => 'post__in'
        );

        $products_to_buy_together = new WP_Query($args);
        
        if ($products_to_buy_together) {
            $add_to_cart_pid_arr = array();
            $add_to_cart_arr = array();
            $total_price = 0;
            if ($products_to_buy_together->have_posts()) {
                $i = 1;
                while ($products_to_buy_together->have_posts()) {
                    $products_to_buy_together->the_post();
                    $size = 'shop_thumbnail';
                    $pid = get_the_id();
                    global $product;
                    $post = get_post($pid);
                    $is_product_varition = false;
                    $prd_link = get_permalink();
                    if($post->post_type == 'product_variation') {
                        $variation_id = $post->ID;
                        $variation = wc_get_product($variation_id);
                        $parent_id = $post->post_parent;
                        $pid = $parent_id;
                        $prd_link = $variation->get_permalink();
                        $is_product_varition = true;
                        $add_to_cart_pid_arr[] = $variation_id;
                        
                        $attr = $variation->get_variation_attributes();
                    } else {
                        $add_to_cart_pid_arr[] = $pid;
                        $attr = array();
                    }
                    
                    echo $products_to_buy_together->add_to_cart_url();
                    $post_id_for_image = ($is_product_varition) ? $variation_id : $pid;
                    if (has_post_thumbnail($post_id_for_image)) {
                        $image = get_the_post_thumbnail($post_id_for_image, $size);
                    } elseif (wc_placeholder_img_src()) {
                        $image = wc_placeholder_img($size);
                    }
                    
                    $prd_price = $product->get_display_price();
                    $total_price += $prd_price;
                    $cart_content = '';
                    $cart_content .= '<div class="frequently_bought_product" id="frequently_bought_product_' . $i . '" price="' . $prd_price . '">';
                    $cart_content .= '<a href="' . $prd_link . '">' . $image . '</a>';
                    
                    if($is_product_varition) {
                        $formatted_attributes = $variation->get_formatted_variation_attributes(true);
                        $extra_data = ' &ndash; ' . $formatted_attributes;
                        $name =  sprintf(__('%s%s', 'woocommerce'), $variation->get_title(), $extra_data);
                        $cart_content .= '<div class="frequently_bought_product_title"><input type="checkbox" name="bought_pid[]" value="' . $variation_id . '" checked > <a href="' . $prd_link . '">' . $name . '</a></div>';
                    } else {
                        $cart_content .= '<div class="frequently_bought_product_title"><input type="checkbox" name="bought_pid[]" value="' . $pid . '" checked > <a href="' . $prd_link . '">' . get_the_title($pid) . '</a></div>';
                    }
                    
                    $cart_content .= '<div class="frequently_bought_product_price" id="frequently_bought_product_price_' . $i . '">' . $product->get_price_html() . '</div>';
                    $cart_content .= '</div>';
                    $add_to_cart_arr[] = $cart_content;
                    $i++;
                }

                wp_reset_postdata(); // to rest the loop counter

            }

            
                    
            if ($add_to_cart_pid_arr) {
                if (count($add_to_cart_pid_arr) == 1) {
                    $add_to_cart_str = 'Add To Cart';
                } elseif (count($add_to_cart_pid_arr) == 2) {
                    $add_to_cart_str = 'Add Both Items To Cart';
                } else {
                    $add_to_cart_str = 'Add ' . count($add_to_cart_pid_arr) . ' Items To Cart';
                }

                echo '<h4>' . __('Frequently Bought Together', 'frequently-bought-together-for-woo') . '</h4>';
                echo '<div id="frequently_bought_together_form">';
                echo '<div class="frequently_bought_together_products">';
                echo implode('<div class="frequently_bought_products">&nbsp;+&nbsp;</div> ', $add_to_cart_arr);
                echo '</div>';
                echo '<div class="frequently_bought_add_to_cart"><div class="frequently_bought_product_price_total" id="frequently_bought_product_price_total" >' . wc_price($total_price) . '</div><a class="single_add_to_cart_button button also_bought_css_button" href="#">' . sprintf(__('%s', 'frequently-bought-together-for-woo'), $add_to_cart_str) . '</a></div>';
                $pids = implode(',', $add_to_cart_pid_arr);
                echo '<input type="hidden" name="frequently_bought_together_selected_product_id" value="' . $pids . '" id="frequently_bought_together_selected_product_id" >';
                //echo '<input type="hidden" name="frequently_bought_together_selected_variation_id" value="' . $default_variation_id . '" id="frequently_bought_together_selected_variation_id" >';
                echo '</div>';
            }
        }
    }

}
