<?php
if( ! class_exists('BeRocket_conditions') ) {
    class BeRocket_conditions {
        public $conditions = array();
        public $option_name, $hook_name;
        public function __construct($option_name, $hook_name, $conditions = array()) {
            $conditions = apply_filters($hook_name.'_conditions_list', $conditions);
            $this->conditions = $conditions;
            $this->option_name = $option_name;
            $this->hook_name = $hook_name;
            $ready_conditions = static::get_conditions();
            add_filter($hook_name.'_types', array($this, 'types'));
            foreach($conditions as $condition) {
                if( isset($ready_conditions[$condition]) ) {
                    //CONDITIONS HTML
                    add_filter($hook_name.'_type_'.$ready_conditions[$condition]['type'], array( get_class($this), $condition), 10, 3);
                    //CONDITIONS CHECK
                    add_filter($hook_name.'_check_type_'.$ready_conditions[$condition]['type'], array( get_class($this), $ready_conditions[$condition]['func']), 10, 3);
                } else {
                    do_action($hook_name.'_condition_not_exist', $condition);
                }
            }
        }
        public function types($types) {
            $ready_conditions = static::get_conditions();
            foreach($this->conditions as $condition) {
                if( isset($ready_conditions[$condition]) ) {
                    $types[$ready_conditions[$condition]['type']] = $ready_conditions[$condition]['name'];
                }
            }
            return $types;
        }
        public function build(&$value, $additional = array()) {
            if( ! is_array($additional) ) $additional = array();
            $additional['hook_name'] = $this->hook_name;
            return static::builder($this->option_name, $value, $additional);
        }
        public static function builder($name, &$value, $additional = array()) {
            if( ! isset($value) || ! is_array($value) ) {
                $value = array();
            }
            ob_start();
            include_once(plugin_dir_path( __DIR__ ) . "templates/conditions.php");
            $html = ob_get_clean();
            return $html;
        }
        public static function check($conditions_data, $hook_name, $additional = array()) {
            if( ! is_array($conditions_data) || count($conditions_data) == 0 ) {
                $condition_status = true;
            } else {
                $condition_status = false;
                foreach($conditions_data as $conditions) {
                    $condition_status = false;
                    foreach($conditions as $condition) {
                        $condition_status = apply_filters($hook_name . '_check_type_' . $condition['type'], false, $condition, $additional);
                        if( !$condition_status ) {
                            break;
                        }
                    }
                    if( $condition_status ) {
                        break;
                    }
                }
            }
            return $condition_status;
        }
        public static function get_conditions() {
            return array(
                'condition_product' => array('func' => 'check_condition_product', 'type' => 'product', 'name' => __('Product', 'BeRocket_domain')),
                'condition_product_sale' => array('func' => 'check_condition_product_sale', 'type' => 'sale', 'name' => __('On Sale', 'BeRocket_domain')),
                'condition_product_bestsellers' => array('func' => 'check_condition_product_bestsellers', 'type' => 'bestsellers', 'name' => __('Bestsellers', 'BeRocket_domain')),
                'condition_product_price' => array('func' => 'check_condition_product_price', 'type' => 'price', 'name' => __('Price', 'BeRocket_domain')),
                'condition_product_stockstatus' => array('func' => 'check_condition_product_stockstatus', 'type' => 'stockstatus', 'name' => __('Stock status', 'BeRocket_domain')),
                'condition_product_totalsales' => array('func' => 'check_condition_product_totalsales', 'type' => 'totalsales', 'name' => __('Total sales', 'BeRocket_domain')),
                'condition_product_category' => array('func' => 'check_condition_product_category', 'type' => 'category', 'name' => __('Category', 'BeRocket_domain')),
                'condition_product_attribute' => array('func' => 'check_condition_product_attribute', 'type' => 'attribute', 'name' => __('Product attribute', 'BeRocket_domain')),
                'condition_product_age' => array('func' => 'check_condition_product_age', 'type' => 'age', 'name' => __('Product age', 'BeRocket_domain')),
                'condition_product_saleprice' => array('func' => 'check_condition_product_saleprice', 'type' => 'saleprice', 'name' => __('Sale price', 'BeRocket_domain')),
                'condition_product_stockquantity' => array('func' => 'check_condition_product_stockquantity', 'type' => 'stockquantity', 'name' => __('Stock quantity', 'BeRocket_domain')),
            );
        }
        public static function get_condition($condition) {
            $conditions = static::get_conditions_product();
            return ( isset($conditions[$condition]) ? $conditions[$condition] : '' );
        }
        public static function supcondition($name, $options, $extension = array()) {
            $equal = 'equal';
            if( is_array($options) && isset($options['equal'] ) ) {
                $equal = $options['equal'];
            }
            $equal_list = array(
                'equal' => __('Equal', 'BeRocket_domain'),
                'not_equal' => __('Not equal', 'BeRocket_domain'),
            );
            if( ! empty($extension['equal_less']) ) {
                $equal_list['equal_less'] = __('Equal or less', 'BeRocket_domain');
            }
            if( ! empty($extension['equal_more']) ) {
                $equal_list['equal_more'] = __('Equal or more', 'BeRocket_domain');
            }
            $html = '<select name="' . $name . '[equal]">';
            foreach($equal_list as $equal_slug => $equal_name) {
                $html .= '<option value="' . $equal_slug . '"' . ($equal == $equal_slug ? ' selected' : '') . '>' . $equal_name . '</option>';
            }
            $html .= '</select>';
            return $html;
        }
        public static function supcondition_check($value1, $value2, $condition) {
            $equal = 'equal';
            if( is_array($condition) && isset($condition['equal'] ) ) {
                $equal = $condition['equal'];
            }
            $check = true;
            switch($equal) {
                case 'equal':
                    $check = $value1 == $value2;
                    break;
                case 'not_equal':
                    $check = $value1 != $value2;
                    break;
                case 'equal_less':
                    $check = $value1 <= $value2;
                    break;
                case 'equal_more':
                    $check = $value1 >= $value2;
                    break;
            }
            return $check;
        }

        //PRODUCT CONDITION

        //HTML FOR PRODUCT CONDITIONS IN ADMIN PANEL
        public static function condition_product($html, $name, $options) {
            $def_options = array('product' => array());
            $options = array_merge($def_options, $options);
            $html .= static::supcondition($name, $options) . '
            <div class="br_framework_settings">' . br_products_selector( $name . '[product]', $options['product']).'</div>';
            return $html;
        }

        public static function condition_product_sale($html, $name, $options) {
            $def_options = array('sale' => 'yes');
            $options = array_merge($def_options, $options);
            $html .= '<label>' . __('Is on sale', 'BeRocket_domain') . '<select name="' . $name . '[sale]">
                <option value="yes"' . ($options['sale'] == 'yes' ? ' selected' : '') . '>' . __('Yes', 'BeRocket_domain') . '</option>
                <option value="no"' . ($options['sale'] == 'no' ? ' selected' : '') . '>' . __('No', 'BeRocket_domain') . '</option>
            </select></label>';
            return $html;
        }

        public static function condition_product_bestsellers($html, $name, $options) {
            $def_options = array('bestsellers' => '1');
            $options = array_merge($def_options, $options);
            $html .= '<label>' . __('Count of product', 'BeRocket_domain') . '<input type="number" min="1" name="' . $name . '[bestsellers]" value="' . $options['bestsellers'] . '"></label>';
            return $html;
        }

        public static function condition_product_price($html, $name, $options) {
            $def_options = array('price' => array('from' => '1', 'to' => '1'));
            $options = array_merge($def_options, $options);
            if( ! is_array($options['price']) ) {
                $options['price'] = array();
            }
            $options['price'] = array_merge($def_options['price'], $options['price']);
            $html .= static::supcondition($name, $options);
            $html .= __('From:', 'BeRocket_domain') . '<input class="price_from" type="number" min="0" name="' . $name . '[price][from]" value="' . $options['price']['from'] . '">' .
                     __('To:', 'BeRocket_domain')   . '<input class="price_to"   type="number" min="1" name="' . $name . '[price][to]"   value="' . $options['price']['to']   . '">';
            return $html;
        }

        public static function condition_product_stockstatus($html, $name, $options) {
            $def_options = array('stockstatus' => 'in_stock');
            $options = array_merge($def_options, $options);
            $html .= '
            <select name="' . $name . '[stockstatus]">
                <option value="in_stock"' . ($options['stockstatus'] == 'in_stock' ? ' selected' : '') . '>' . __('In stock', 'BeRocket_domain') . '</option>
                <option value="out_of_stock"' . ($options['stockstatus'] == 'out_of_stock' ? ' selected' : '') . '>' . __('Out of stock', 'BeRocket_domain') . '</option>
            </select>';
            return $html;
        }

        public static function condition_product_totalsales($html, $name, $options) {
            $def_options = array('totalsales' => '1');
            $options = array_merge($def_options, $options);
            $html .= static::supcondition($name, $options, array('equal_less' => true, 'equal_more' => true));
            $html .= '<label>' . __('Count of product', 'BeRocket_domain') . '<input type="number" min="0" name="' . $name . '[totalsales]" value="' . $options['totalsales'] . '"></label>';
            return $html;
        }

        public static function condition_product_category($html, $name, $options) {
            if( ! is_array($options['category']) ) {
                $options['category'] = array($options['category']);
            }
            $product_categories = get_terms( 'product_cat' );
            if( is_array($product_categories) && count($product_categories) > 0 ) {
                $def_options = array('category' => '');
                $options = array_merge($def_options, $options);
                $html .= static::supcondition($name, $options);
                $html .= '<label><input type="checkbox" name="' . $name . '[subcats]" value="1"' . (empty($options['subcats']) ? '' : ' checked') . '>' . __('Include subcategories', 'BeRocket_domain') . '</label>';
                $html .= '<div style="max-height:70px;overflow:auto;border:1px solid #ccc;padding: 5px;">';
                foreach($product_categories as $category) {
                    $html .= '<div><label>
                    <input type="checkbox" name="' . $name . '[category][]" value="' . $category->term_id . '"' . ( (! empty($options['category']) && is_array($options['category']) && in_array($category->term_id, $options['category']) ) ? ' checked' : '' ) . '>
                    ' . $category->name . '
                    </label></div>';
                }
                $html .= '</div>';
            }
            return $html;
        }

        public static function condition_product_attribute($html, $name, $options) {
            $def_options = array('attribute' => '');
            $options = array_merge($def_options, $options);
            $attributes = get_object_taxonomies( 'product', 'objects');
            $product_attributes = array();
            foreach( $attributes as $attribute ) {
                $attribute_i = array();
                $attribute_i['name'] = $attribute->name;
                $attribute_i['label'] = $attribute->label;
                $attribute_i['value'] = array();
                $terms = get_terms($attribute->name);
                foreach($terms as $term) {
                    $attribute_i['value'][$term->term_id] = $term->name;
                }
                $product_attributes[] = $attribute_i;
            }
            $html .= static::supcondition($name, $options);
            $html .= '<label>' . __('Select attribute', 'BeRocket_domain') . '</label>';
            $html .= '<select name="' . $name . '[attribute]" class="br_cond_attr_select">';
            $has_selected_attr = false;
            foreach($product_attributes as $attribute) {
                $html .= '<option value="' . $attribute['name'] . '"' . ( isset($options['attribute']) && $attribute['name'] == $options['attribute'] ? ' selected' : '' ) . '>' . $attribute['label'] . '</option>';
                if( $attribute['name'] == $options['attribute'] ) {
                    $has_selected_attr = true;
                }
            }
            $html .= '</select>';
            $is_first_attr = ! $has_selected_attr;
            foreach($product_attributes as $attribute) {
                $html .= '<select class="br_attr_values br_attr_value_' . $attribute['name'] . '" name="' . $name . '[values][' . $attribute['name'] . ']"' . ($is_first_attr || $attribute['name'] == $options['attribute'] ? '' : ' style="display:none;"') . '>';
                foreach($attribute['value'] as $term_id => $term_name) {
                    $html .= '<option value="' . $term_id . '"' . (! empty($options['values'][$attribute['name']]) && $options['values'][$attribute['name']] == $term_id ? ' selected' : '') . '>' . $term_name . '</option>';
                }
                $html .= '</select>';
                $is_first_attr = false;
            }
            return $html;
        }

        public static function condition_product_age($html, $name, $options) {
            $def_options = array('age' => '1');
            $options = array_merge($def_options, $options);
            $html .= br_supcondition_equal($name, $options, array('equal_less' => true, 'equal_more' => true));
            $html .= '<input type="number" min="0" name="' . $name . '[age]" value="' . $options['age'] . '">' . __('day(s)', 'BeRocket_domain');
            return $html;
        }

        public static function condition_product_saleprice($html, $name, $options) {
            $def_options = array('saleprice' => array('from' => '1', 'to' => '1'));
            $options = array_merge($def_options, $options);
            if( ! is_array($options['saleprice']) ) {
                $options['saleprice'] = array();
            }
            $options['price'] = array_merge($def_options['saleprice'], $options['saleprice']);
            $html .= br_supcondition_equal($name, $options);
            $html .= __('From:', 'BeRocket_domain') . '<input class="price_from" type="number" min="0" name="' . $name . '[saleprice][from]" value="' . $options['saleprice']['from'] . '">' .
                     __('To:', 'BeRocket_domain')   . '<input class="price_to"   type="number" min="1" name="' . $name . '[saleprice][to]"   value="' . $options['saleprice']['to']   . '">';
            return $html;
        }

        public static function condition_product_stockquantity($html, $name, $options) {
            $def_options = array('stockquantity' => '1');
            $options = array_merge($def_options, $options);
            $html .= br_supcondition_equal($name, $options, array('equal_less' => true, 'equal_more' => true));
            $html .= __('Products in stock', 'BeRocket_domain');
            $html .= '<input type="number" min="0" name="' . $name . '[stockquantity]" value="' . $options['stockquantity'] . '">';
            return $html;
        }

        //CHECK PRODUCT CONDITIONS
        public static function check_condition_product($show, $condition, $additional) {
            if( isset($condition['product']) && is_array($condition['product']) ) {
                $show = in_array($additional['product_id'], $condition['product']);
                if( $condition['equal'] == 'not_equal' ) {
                    $show = ! $show;
                }
            }
            return $show;
        }

        public static function check_condition_product_sale($show, $condition, $additional) {
            $show = $additional['product']->is_on_sale();
            if( $condition['sale'] == 'no' ) {
                $show = ! $show;
            }
            return $show;
        }

        public static function check_condition_product_bestsellers($show, $condition, $additional) {
            $args = array(
                'post_type'             => 'product',
                'post_status'           => 'publish',
                'ignore_sticky_posts'   => 1,
                'posts_per_page'        => $condition['bestsellers'],
                'meta_key' 	            => 'total_sales',
                'orderby'               => 'meta_value_num',
                'meta_query'            => array(
                    array(
                        'key'       => '_visibility',
                        'value'     => array( 'catalog', 'visible' ),
                        'compare'   => 'IN'
                    )
                )
            );
            $posts = get_posts( $args );
            if( is_array( $posts ) ) {
                foreach($posts as $post) {
                    if( $additional['product_id'] == $post->ID ) {
                        $show = true;
                        break;
                    }
                }
            }
            return $show;
        }

        public static function check_condition_product_price($show, $condition, $additional) {
            $product_price = br_wc_get_product_attr($additional['product'], 'price');
            $show = $product_price >= $condition['price']['from'] && $product_price <= $condition['price']['to'];
            if( $condition['equal'] == 'not_equal' ) {
                $show = ! $show;
            }
            return $show;
        }

        public static function check_condition_product_stockstatus($show, $condition, $additional) {
            $show = $additional['product']->is_in_stock();
            if( $condition['stockstatus'] == 'out_of_stock' ) {
                $show = ! $show;
            }
            return $show;
        }

        public static function check_condition_product_totalsales($show, $condition, $additional) {
            $total_sales = get_post_meta( $additional['product_id'], 'total_sales', true );
            $show = static::supcondition_check($condition['totalsales'], $total_sales, $condition);
            return $show;
        }
    
        public static function check_condition_product_category($show, $condition, $additional) {
            if( ! is_array($condition['category']) ) {
                $condition['category'] = array($condition['category']);
            }
            $terms = get_the_terms( $additional['product_id'], 'product_cat' );
            if( is_array( $terms ) ) {
                foreach( $terms as $term ) {
                    if( in_array($term->term_id, $condition['category']) ) {
                        $show = true;
                    }
                    if( ! empty($condition['subcats']) ) {
                        foreach($condition['category'] as $category) {
                            $show = term_is_ancestor_of($category, $term->term_id, 'product_cat');
                            if( $show_filters ) {
                                break;
                            }
                        }
                    }
                    if($show) break;
                }
            }
            if( $condition['equal'] == 'not_equal' ) {
                $show = ! $show;
            }
            return $show;
        }

        public static function check_condition_product_attribute($show, $condition, $additional) {
            $terms = get_the_terms( $additional['product_id'], $condition['attribute'] );
            if( is_array( $terms ) ) {
                foreach( $terms as $term ) {
                    if( $term->term_id == $condition['values'][$condition['attribute']]) {
                        $show = true;
                        break;
                    }
                }
            }
            if( $condition['equal'] == 'not_equal' ) {
                $show = ! $show;
            }
            return $show;
        }

        public static function check_condition_product_age($show, $condition, $additional) {
            $post_date = $additional['product_post']->post_date;
            $post_date = date( 'Y-m-d', strtotime( $post_date ) );
            $value = $condition['age'];
            $test_date = date( 'Y-m-d', strtotime( "-$value days", time() ) );
            $show = static::supcondition_check($test_date, $post_date, $condition);
            return $show;
        }

        public static function check_condition_product_saleprice($show, $condition, $additional) {
            $product_sale = br_wc_get_product_attr($additional['product'], 'sale_price');
            $show = $product_sale >= $condition['saleprice']['from'] && $product_sale <= $condition['saleprice']['to'];
            if( $condition['equal'] == 'not_equal' ) {
                $show = ! $show;
            }
            return $show;
        }

        public static function check_condition_product_stockquantity($show, $condition, $additional) {
            $product = $additional['product'];
            if( method_exists($product, 'get_stock_quantity') ) {
                $product_stock = $product->get_stock_quantity('edit');
            } else {
                $product_stock = $product->stock;
            }
            $show = static::supcondition_check($condition['stockquantity'], $product_stock, $condition);
            return $show;
        }
        //PAGE CONDITIONS

        //HTML FOR PRODUCT CONDITIONS IN ADMIN PANEL

        //CHECK PRODUCT CONDITIONS
        
    }
}
