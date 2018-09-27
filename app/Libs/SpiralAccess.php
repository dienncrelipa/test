<?php
/**
 * Created by PhpStorm.
 * User: macintosh
 * Date: 1/28/16
 * Time: 1:59 AM
 */

namespace App\Libs;


class SpiralAccess
{
    var $_url              = '';//ƒT[ƒrƒX—p‚ÌURL (ƒƒP[ƒ^‚©‚çŽæ“¾‚Å‚«‚é)
    var $_token            = '';//ƒg[ƒNƒ“
    var $_secret           = '';//ƒV[ƒNƒŒƒbƒgƒg[ƒNƒ“
    var $_passkey          = 0;//passkey
    var $_db_title         = '';//ƒAƒNƒZƒXDB
    var $_header           = '';//api_header
    var $parameters        = array();
    var $stream            = array();

    function __construct($url, $token, $secret) {
        $this->_url = $url;
        $this->_token = $token;
        $this->_secret = $secret;
    }

    function make_header ($api,$meth) {
        $api_header  = "";
        $api_header .= "X-SPIRAL-API: {$api}/{$meth}/request\r\n";
        $api_header .= "Content-Type: application/json; charset=UTF-8\r\n";
        $this->_header = $api_header;
    }

    function make_signature(){
        $this->_passkey = time();
        $key       = $this->_token . "&" . $this->_passkey;
        $secret    = $this->_secret;
        return hash_hmac('sha1', $key, $secret, false);
    }

    function make_parameters($db_title){
        $this->_db_title = $db_title;
        $this->parameters = array();
        $this->parameters["spiral_api_token"] = $this->_token;
        $this->parameters["signature"]        = $this->make_signature();
        $this->parameters["passkey"]          = $this->_passkey;
        $this->parameters["db_title"]         = $this->_db_title;
    }

    function make_stream($json){
        $this->stream = array();
        $this->stream = stream_context_create(
            array('http' => array(
                'method'           => 'POST',
                'protocol_version' => '1.0',
                'header'           => $this->_header,
                'content'          => $json
            )));
    }

    function access($api,$meth,$db_title,$add_param=array()){
        $this->make_header($api,$meth);
        $this->make_parameters($db_title);

        if(count($add_param) !== 0){
            foreach($add_param as $key => $value){
                $this->parameters[$key] = $value;
            }
        }

        $this->make_stream(json_encode($this->parameters));

        return file_get_contents($this->_url, false, $this->stream);
    }
}