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

class MOTDClient
{
    private $dbh;
    private $server;
    private $motdh;
    private $steamid64;
    private $url;
    private $ip;
    
    public function __construct($dbh, $server, $motdh)
    {
        $this->dbh = $dbh;
        $this->server = $server;
        $this->motdh = $motdh;
    }

    public function is_valid ($check_url = true)
    {
        return ($this->steamid64) && ($this->ip) && ($check_url ? $this->url : true);
    }

    public function register_url ()
    {
        $this->steamid64 = filter_input(INPUT_POST, "steamid64", FILTER_SANITIZE_STRING);
        $this->ip = ip2long(filter_input(INPUT_POST, "clientip", FILTER_VALIDATE_IP));
        $this->url = filter_input (INPUT_POST, "url", FILTER_VALIDATE_URL);

        if ($this->server->is_valid(true) && $this->server->is_token_valid() && $this->is_valid()) {
            $result = $this->dbh->query("INSERT INTO ".LINKS_TABLE_NAME." (steamid64, url, client_ip, server_ip, server_port, created_at)" .
                " VALUES (:steamid64, :url, :client_ip, :server_ip, :server_port, :created_at)")
                ->bind(":steamid64", $this->steamid64)
                ->bind(":url", $this->url)
                ->bind(":client_ip", $this->ip)
                ->bind(":server_ip", $this->server->ip)
                ->bind(":server_port", $this->server->port)
                ->bind(":created_at", time())
                ->execute();
            
            $this->motdh->create_response(0, false, "Client URL Registered.", $result);
        } else {
            $this->motdh->create_response(0, $this->server->is_blocked(), "Error registering URL.", false);
        }
    }

    public function load_url ()
    {
        $this->steamid64 = filter_input(INPUT_GET, "sid", FILTER_SANITIZE_STRING);
        $this->ip = $this->motdh->get_real_ip();

        if ($this->is_valid(false)) {
            $result = $this->dbh->query("SELECT * FROM ".LINKS_TABLE_NAME." WHERE steamid64 = :steamid64 AND client_ip = :client_ip")
                ->bind(":steamid64", $this->steamid64)
                ->bind(":client_ip", $this->ip)
                ->single();
            if ($result) {
                $this->url = $result["url"];
                printf("<object width=\"960\" height=\"700\" data=\"%s\" type=\"text/html\"></object>", $this->url);
                $this->delete_urls($result["steamid64"]);
            }
        }
    }

    public function delete_urls ($steamid64) {
        return $this->dbh->query("DELETE FROM ".LINKS_TABLE_NAME." WHERE steamid64 = :steamid64")
            ->bind(":steamid64", $steamid64)
            ->execute();
    }
}

?>