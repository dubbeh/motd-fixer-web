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
 * Make sure all requests go through this as a base class
 *
 */


require_once ("../config.php");
require_once ("helpers.php");
require_once ("db.php");
require_once ("client.php");
require_once ("server.php");

class MOTDAuth {

    private $dbh;
    private $server;
    private $client;
    private $motdh;

    public function auth_run()
    {
        $this->dbh = new MOTDDB();
        $this->motdh = new MOTDHelpers();
        $this->server = new MOTDServer($this->dbh, $this->motdh);
        $this->client = new MOTDClient($this->dbh, $this->server, $this->motdh);

        if (AUTH_TYPE == AUTH_REGISTRATION) {
            $this->auth_registration();
        } else if (AUTH_TYPE == AUTH_IP) {
            $this->auth_ip();
        }
    }

    private function auth_ip()
    {
        if ($this->is_ip_allowed()) {
            if ($this->motdh->get_script_filename() == "register.php") {
                if (filter_input(INPUT_GET, "server", FILTER_VALIDATE_BOOLEAN) == true) {
                    $this->motdh->create_response(0, false, "No need to register using IP based authentication.", false);
                    return;
                } else if (filter_input(INPUT_GET, "client", FILTER_VALIDATE_BOOLEAN) == true) {
                    $this->client->register_url(false);
                    return;
                }
            } else if ($this->motdh->get_script_filename() == "redirect.php") {
                $this->client->load_url();
                return;
            }
            $this->motdh->create_response(0, false, "Auth IP: Invalid Usage.", false);
        } else {
            $this->motdh->create_response(0, true, "IP not allowed to use this script.", false);
        }
    }

    private function auth_registration()
    {
        if ($this->motdh->get_script_filename() == "register.php" && $this->server->is_valid(true)) {
            if (filter_input(INPUT_GET, "server", FILTER_VALIDATE_BOOLEAN) == true) {
                $this->server->register();
            } else if (filter_input(INPUT_GET, "client", FILTER_VALIDATE_BOOLEAN) == true) {
                $this->client->register_url(true);
            }

            return;
        } else if ($this->motdh->get_script_filename() == "redirect.php") {
            $this->client->load_url();
            return;
        }

        $this->motdh->create_response(0, false, "Auth unknown usage", false);
    }

    private function is_ip_allowed()
    {
        return in_array($this->motdh->get_real_ip(), AUTH_ALLOWED_IPS);
    }
}

?>
