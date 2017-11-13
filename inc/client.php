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
        return $this->steamid64 && $this->client_ip && ($check_url ? $this->panel_url : true);
    }

    public function grab_get_data ()
    {
        $this->motdh->log_to_file("client->grab_get_data : running");
        $this->steamid64 = filter_input(INPUT_GET, "sid", FILTER_SANITIZE_STRING);
        $this->client_ip = filter_input(INPUT_GET, "ip", FILTER_VALIDATE_IP);
        // Still keep the old format working correctly - if no IP sent in the query string
        if (!$this->client_ip) {
            $this->motdh->log_to_file("client->grab_get_data : no client ip sent");
            $this->client_ip = $this->motdh->get_real_ip();
        }
    }

    public function grab_post_data ()
    {
        $this->motdh->log_to_file("client->grab_post_data : running");
        $this->steamid64 = filter_input(INPUT_POST, "steamid64", FILTER_SANITIZE_STRING);
        $this->client_ip = filter_input(INPUT_POST, "clientip", FILTER_VALIDATE_IP);
        $this->panel_url = filter_input (INPUT_POST, "panel_url", FILTER_VALIDATE_URL);
        $this->panel_title = filter_input(INPUT_POST, "panel_title", FILTER_SANITIZE_STRING);
        $this->panel_hidden = filter_input(INPUT_POST, "panel_hidden", FILTER_VALIDATE_INT);
        $this->panel_width = filter_input(INPUT_POST, "panel_width", FILTER_VALIDATE_INT);
        $this->panel_height = filter_input(INPUT_POST, "panel_height", FILTER_VALIDATE_INT);
    }

    public function register_url ()
    {
        $this->grab_post_data();
        $this->motdh->log_to_file("client->register_url : running");

        if ($this->server->is_valid(true) && $this->server->is_token_valid() && $this->is_valid(true)) {
            $this->motdh->log_to_file("client->register_url : checks passed - running query");
            $result = $this->dbh->query("INSERT INTO ".LINKS_TABLE_NAME.
                " (steamid64, panel_url, client_ip, sent_ip, sent_port, panel_title, panel_hidden, panel_width, panel_height, created_at)".
                " VALUES".
                " (:steamid64, :panel_url, :client_ip, :server_ip, :server_port, :panel_title, :panel_hidden, :panel_width, :panel_height, :created_at)")
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

            $this->motdh->create_response(0, false, "Client URL Registered.", $result, true);
        } else {
            $this->motdh->log_to_file("client->register_url : checks failed");
            $this->motdh->create_response(0, $this->server->is_blocked(), "Error registering URL.", false, $this->server->is_token_valid());
        }
    }

    public function load_url ()
    {
        $this->grab_get_data();
        $this->motdh->log_to_file("client->load_url : running");

        if ($this->is_valid(false)) {
            $this->motdh->log_to_file("client->load_url : initial is_client_valid check passed");
            $result = $this->dbh->query("SELECT * FROM ".LINKS_TABLE_NAME.
                " WHERE".
                " steamid64 = :steamid64".
                " AND".
                " client_ip = :client_ip")
                ->bind(":steamid64", $this->steamid64)
                ->bind(":client_ip", $this->client_ip)
                ->single();
            if ($result) {

                $this->motdh->log_to_file("client->load_url : get client from ip and steamid64 check passed");
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

                $this->delete_urls_for_steamid($result["steamid64"]);
            }
        }
    }

    public function delete_urls ()
    {
        $this->grab_get_data();
        $this->motdh->log_to_file("client->delete_urls : running");

        if ($this->is_valid(false) && $this->server->is_token_valid()) {
            $this->motdh->log_to_file("client->delete_urls : client and token checks passed");
            return $this->dbh->query("DELETE FROM ".LINKS_TABLE_NAME.
                " WHERE".
                " steamid64 = :steamid64".
                " AND".
                " client_ip = :client_ip")
                ->bind(":steamid64", $this->steamid64)
                ->bind(":client_ip", $this->client_ip)
                ->executeRows();
        }
    }

    public function delete_urls_for_steamid ($steamid64)
    {
        $this->motdh->log_to_file("client->delete_urls_for_steamid : running");
        return $this->dbh->query("DELETE FROM ".LINKS_TABLE_NAME.
            " WHERE".
            " steamid64 = :steamid64")
            ->bind(":steamid64", $steamid64)
            ->executeRows();
    }
}

?>