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
        $this->motdh->log_to_file("auth->auth_ip : php_self=".$_SERVER["PHP_SELF"]);

        if ($this->is_ip_allowed()) {
            $this->motdh->log_to_file("auth->auth_ip : ip check passed");
            if ($_SERVER["PHP_SELF"] == "redirect.php") {
                $this->client->load_url();
                return;
            } else if ($this->server->is_valid(true)) {
                $this->motdh->log_to_file("auth->auth_ip : server->is_valid() passed");
                if ($_SERVER["PHP_SELF"] == "register.php") {
                    if (filter_input(INPUT_GET, "server", FILTER_VALIDATE_BOOLEAN) == true) {
                        $this->motdh->create_response(0, false, "No need to register using IP based authentication.", false);
                    } else if (filter_input(INPUT_GET, "client", FILTER_VALIDATE_BOOLEAN) == true) {
                        $this->client->register_url();
                    }
                } else if ($_SERVER["PHP_SELF"] == "delete.php") {
                    $this->client->delete_urls();
                }

                return;
            }

            $this->motdh->create_response(0, false, "Auth IP: Invalid Usage.", false);
        } else {
            $this->motdh->create_response(0, true, "IP not allowed to use this script.", false);
        }
    }

    private function auth_registration()
    {
        $this->motdh->log_to_file("auth->auth_registration : php_self=".$_SERVER["PHP_SELF"]);

        if ($_SERVER["PHP_SELF"] == "redirect.php") {
            $this->motdh->log_to_file("auth->auth_registration : running client->load_url()");
            $this->client->load_url();
            return;
        } else if ($this->server->is_valid(true)) {
            $this->motdh->log_to_file("auth->auth_registration : running server->is_valid() passed code");
            if ($_SERVER["PHP_SELF"] == "register.php") {
                if (filter_input(INPUT_GET, "server", FILTER_VALIDATE_BOOLEAN) == true) {
                    $this->server->register();
                } else if (filter_input(INPUT_GET, "client", FILTER_VALIDATE_BOOLEAN) == true) {
                    $this->client->register_url();
                }
            } else if ($_SERVER["PHP_SELF"] == "delete.php") {
                $this->client->delete_urls();
            }
            return;
        }

        $this->motdh->create_response(0, false, "Auth Registration: Invalid usage", false);
    }

    private function is_ip_allowed()
    {
        return in_array($this->motdh->get_real_ip(), AUTH_ALLOWED_IPS);
    }
}

?>
