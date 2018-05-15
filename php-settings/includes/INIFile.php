<?php
/**
 * @package   PHP Settings
 * @date      2017-03-04
 * @version   1.0.6
 * @author    Askupa Software <hello@askupasoftware.com>
 * @link      http://products.askupasoftware.com/php-settings
 * @copyright 2017 Askupa Software
 */

class INIFile 
{
    private function __construct() {}
    
    static function get_dir_path()
    {
        return ABSPATH;
    }
    
    static function get_ini_file_names()
    {
        return array(
            self::get_dir_path().'.user.ini',
            self::get_dir_path().'php.ini',
            self::get_dir_path().'php5.ini'
        );
    }
    
    static function write( $filepath, $content )
    {
        $fd = fopen( $filepath, 'w+' );
        if( false !== $fd )
        {
            $success = fwrite( $fd, $content );
            fclose( $fd );
            
            if( false === $success )
            {
                throw new Exception( 'Unable to write to file' );
            }
        }
        else throw new Exception( 'Unable to open file' );
    }
    
    static function read( $filepath )
    {
        if( !file_exists( $filepath ) ) return;
        
        $filesize = filesize( $filepath );
        if( $filesize === 0 ) return;
        
        $fd = fopen( $filepath, 'r' );
        if( false !== $fd )
        {
            $content = fread( $fd, $filesize );
            fclose( $fd );
            return $content;
        }
        // throw error
    }
    
    static function delete( $filepath )
    {
        if( !file_exists( $filepath ) ) return;
        if( false === unlink( $filepath ) )
            throw new Exception('Unable to delete file');
    }
    
    static function get_content()
    {
        $files = self::get_ini_file_names();
        return self::read( $files[0] );
    }
    
    static function set_content( $content )
    {
        foreach( self::get_ini_file_names() as $filepath )
        {
            self::write( $filepath, $content );
        }
    }
    
    static function remove_files()
    {
        foreach( self::get_ini_file_names() as $filepath )
        {
            self::delete( $filepath );
        }
    }
    
    static function is_writable()
    {
        return is_writable(self::get_dir_path());
    }
}