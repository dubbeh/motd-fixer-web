<?php

/*
 * MOTD Fixer Server Script
 *
 * Fixes the MOTD loading of data under Counter-Strike : Global Offensive
 *
 * Coded by dubbeh - www.dubbeh.net
 *
 * Licensed under the GPLv3
 *
 */

class MOTDHelpers
{
    /*
	 * Gets the real IP address even when behind a CloudFlare proxy
	 */
	public function get_real_ip ()
	{
        return filter_var(
                isset($_SERVER["HTTP_CF_CONNECTING_IP"]) ? $_SERVER["HTTP_CF_CONNECTING_IP"] : $_SERVER['REMOTE_ADDR'],
            FILTER_VALIDATE_IP);
	}

    /*
     * Simple method to create the JSON Responses
     */
    public function create_response($token, $is_blocked, $msg, $success)
    {
        return printf(json_encode(
                array (
                    "token" => $token,
                    "is_blocked" => $is_blocked,
                    "msg" => $msg,
                    "success" => $success
                )
            ));
    }

    public function log_to_file ($data)
    {
        if (MOTDF_DEBUG) {
            $fp = fopen("..\motdf_log.txt", "a+");
            if ($fp) {
                fwrite($fp, date(DATE_RFC2822). ": ");
                fwrite($fp, $data);
                fwrite($fp, "\r\n");
                fclose($fp);
            }
        }
    }
}

?>
