<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

if (!function_exists('assets_url()')) {

    function assets_url() {
        return base_url() . 'assets/';
    }

}

if (!function_exists('css_url()')) {

    function css_url() {
        return base_url() . 'assets/css/';
    }

}

if (!function_exists('admin_css_url()')) {

    function admin_css_url() {
        return base_url() . 'assets/admin/css/';
    }

}

if (!function_exists('js_url()')) {

    function js_url() {

        return base_url() . 'assets/js/';
    }

}

if (!function_exists('admin_js_url()')) {

    function admin_js_url() {

        return base_url() . 'assets/admin/js/';
    }

}

if (!function_exists('img_url()')) {

    function img_url() {
        return base_url() . 'assets/images/';
    }

}

if (!function_exists('admin_img_url()')) {

    function admin_img_url() {
        return base_url() . 'assets/admin/images/';
    }

}

if (!function_exists('ckeditor_url()')) {

    function ckeditor_url() {
        return base_url() . 'assets/ckeditor/';
    }

}



if (!function_exists('generateRandomString()')) {

    function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

}

if (!function_exists('set_checkbox_custom()')) {

    function set_checkbox_custom() {
        if (isset($_POST['remember_me']) && $_POST['remember_me'] == 1) {
            return 'checked = checked';
        }
        return false;
    }

}


if (!function_exists('base64_to_png')) {

    function base64_to_png($image, $directorypath1) {
        $imageName = time() . '_' . generateRandomString(10) . '.png';
        $prof_img_string = str_replace('data:image/PNG;base64,', '', $image);
        $prof_img_string1 = str_replace('', '+', $prof_img_string);
        $encoded_string = base64_decode($prof_img_string1);
        $im = imagecreatefromstring($encoded_string);
        imagepng($im, $directorypath1 . $imageName, 9);
        $filename = $directorypath1 . $imageName;
        list($width_orig, $height_orig) = getimagesize($filename);
        $thumb_width = 60;
        $thumb_height = 60;

        $image_p = imagecreatetruecolor($thumb_width, $thumb_height);
        $imagethumb = imagecreatefrompng($filename);
        imagecopyresampled($image_p, $imagethumb, 0, 0, 0, 0, $thumb_width, $thumb_height, $width_orig, $height_orig);
        imagepng($image_p, $directorypath1 . 'thumbnail/' . $imageName, 9);
        return $imageName;
    }

}