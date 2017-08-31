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
    private $client_ip;
    private $panel_title;
    private $panel_url;
    private $panel_hidden;
    private $panel_width;
    private $panel_height;
    
    public function __construct($dbh, $server, $motdh)
    {
        $this->dbh = $dbh;
        $this->server = $server;
        $this->motdh = $motdh;
    }

    public function is_valid ($check_url = true)
    {
        return ($this->steamid64) && ($this->client_ip) && ($check_url ? $this->panel_url : true);
    }

    public function register_url ()
    {
        $this->steamid64 = filter_input(INPUT_POST, "steamid64", FILTER_SANITIZE_STRING);
        $this->client_ip = filter_input(INPUT_POST, "clientip", FILTER_VALIDATE_IP);
        $this->panel_url = filter_input (INPUT_POST, "panel_url", FILTER_VALIDATE_URL);
        $this->panel_title = filter_input(INPUT_POST, "panel_title", FILTER_SANITIZE_STRING);
        $this->panel_hidden = filter_input(INPUT_POST, "panel_hidden", FILTER_VALIDATE_INT);
        $this->panel_width = filter_input(INPUT_POST, "panel_width", FILTER_VALIDATE_INT);
        $this->panel_height = filter_input(INPUT_POST, "panel_height", FILTER_VALIDATE_INT);

        if ($this->server->is_valid(true) && $this->server->is_token_valid() && $this->is_valid(true)) {
            $result = $this->dbh->query("INSERT INTO ".LINKS_TABLE_NAME.
                " (steamid64, panel_url, client_ip, sent_ip, sent_port, panel_title, panel_hidden, panel_width, panel_height, created_at)".
                " VALUES ".
                "(:steamid64, :panel_url, :client_ip, :server_ip, :server_port, :panel_title, :panel_hidden, :panel_width, :panel_height, :created_at)")
                ->bind(":steamid64", $this->steamid64)
                ->bind(":panel_url", $this->panel_url)
                ->bind(":client_ip", $this->client_ip)
                ->bind(":server_ip", $this->server->sent_ip)
                ->bind(":server_port", $this->server->sent_port)
                ->bind(":panel_title", $this->panel_title)
                ->bind(":panel_hidden", $this->panel_hidden)
                ->bind(":panel_width", $this->panel_width)
                ->bind(":panel_height", $this->panel_height)
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
        $this->client_ip = filter_input(INPUT_GET, "ip", FILTER_VALIDATE_IP);
        // Still keep the old format working correctly - if no IP sent in the query string
        if (!$this->client_ip) {
            $this->client_ip = $this->motdh->get_real_ip();
        }

        if ($this->is_valid(false)) {
            $result = $this->dbh->query("SELECT * FROM ".LINKS_TABLE_NAME." WHERE steamid64 = :steamid64 AND client_ip = :client_ip")
                ->bind(":steamid64", $this->steamid64)
                ->bind(":client_ip", $this->client_ip)
                ->single();
            if ($result) {
                $this->panel_url = $result["panel_url"];
                if ($result["panel_hidden"]) {
                    printf("<object width=\"960\" height=\"700\" data=\"%s\" type=\"text/html\"></object>", $this->panel_url);
                } else {
                    printf("<script type=\"text/javascript\">".
                        "window.open(\"%s\", \"_blank\", \"toolbar=yes, fullscreen=yes, scrollbars=yes, width=%d, height=%d\");".
                        "</script>",
                        $this->panel_url,
                        $result["panel_width"],
                        $result["panel_height"]);
                }

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