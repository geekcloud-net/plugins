<?php
class wpuf_pro_page_installer extends WPUF_Admin_Installer {

    public function install_pro_version_pages ( $profile_options ) {

        $reg_page = false;

        $reg_form       = $this->create_reg_form();

        if ( $reg_form ) {
            $reg_page = $this->create_page( __( 'Registration', 'wpuf-pro' ), '[wpuf_profile type="registration" id="' . $reg_form . '"]' );

            if ( $reg_page ) {
                $profile_options['reg_override_page'] = $reg_page;
            }
        }

        $data =  array(
            'profile_options' => $profile_options,
            'reg_page' => $reg_page
        );
        return $data;

    }
}