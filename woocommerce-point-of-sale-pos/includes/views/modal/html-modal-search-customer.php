<!-- Search Customer Popup box -->
<div class="md-modal md-dynamicmodal md-close-by-overlay" id="modal-search-customer">
    <div class="md-content woocommerce">
        <h1><?php _e('Search Customer', 'wc_point_of_sale'); ?><span class="md-close"></span></h1>
        <h2 class="nav-tab-wrapper woo-nav-tab-wrapper">
            <input type="text" id="search-customer-input" placeholder="<?php _e('Enter customer name', 'wc_point_of_sale'); ?>" autocompleate="off" >
        </h2>
        <div class="full-height">
            <div id="customer_search_result"></div>
        </div>
    </div>
</div>